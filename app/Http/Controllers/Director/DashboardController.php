<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CaseApproval;
use App\Models\CaseLog;
use App\Models\Procedure;
use App\Models\Resident;
use App\Support\ProgressCalculator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
  public function index(): View
  {
    $procedures = Procedure::with(['trainingRequirement', 'yearlyTargets'])->orderBy('name')->get();
    $residents = Resident::with('user')->orderBy('training_year')->get();

    $rows = [];

    foreach ($residents as $resident) {
      foreach ($procedures as $procedure) {
        if (! $procedure->trainingRequirement) {
          continue;
        }

        $completed = ProgressCalculator::completedCount($resident, $procedure);
        $expected = ProgressCalculator::expectedByTrainingYear($resident, $procedure->trainingRequirement);
        $ratio = ProgressCalculator::completionRatio($completed, $expected);
        $status = ProgressCalculator::status($ratio);

        $rows[] = [
          'resident' => $resident,
          'procedure' => $procedure,
          'expected' => $expected,
          'completed' => $completed,
          'progress_percent' => (int) round(min(200, $ratio * 100)),
          'status' => $status,
          'status_label' => ProgressCalculator::statusLabel($status),
        ];
      }
    }

    $statusCounts = [
      'On Track' => collect($rows)->where('status', 'green')->count(),
      'At Risk' => collect($rows)->where('status', 'yellow')->count(),
      'Behind' => collect($rows)->where('status', 'red')->count(),
    ];

    $months = collect(range(5, 1))->map(fn(int $offset) => Carbon::now()->subMonths($offset)->format('Y-m'));
    $months = $months->push(Carbon::now()->format('Y-m'));

    $monthlyApprovals = CaseApproval::query()
      ->where('status', 'approved')
      ->whereDate('decided_at', '>=', Carbon::now()->startOfMonth()->subMonths(5))
      ->orderBy('decided_at', 'asc')
      ->get()
      ->groupBy(fn(CaseApproval $approval) => $approval->decided_at?->format('Y-m'));

    $monthlySeries = $months->map(fn(string $month) => $monthlyApprovals->get($month)?->count() ?? 0)->values();

    $activityStart = Carbon::now()->subDays(14)->startOfDay();
    $activityResidents = Resident::query()->with('user')->orderBy('training_year')->orderBy('id')->get();

    $recentLogsByResident = CaseLog::query()
      ->where('operation_date', '>=', $activityStart->toDateString())
      ->get(['resident_id', 'role'])
      ->groupBy('resident_id');

    $activityRows = $activityResidents
      ->map(function (Resident $resident) use ($recentLogsByResident) {
        $residentLogs = $recentLogsByResident->get($resident->id, collect());

        $operationsCount = $residentLogs
          ->filter(fn(CaseLog $log) => in_array($log->role, ['primary', 'supervised_primary'], true))
          ->count();

        $assistanceCount = $residentLogs
          ->filter(fn(CaseLog $log) => in_array($log->role, ['assistant', 'first_assistant'], true))
          ->count();

        return [
          'resident_name' => $resident->user->name,
          'operations_count' => $operationsCount,
          'assistance_count' => $assistanceCount,
          'total' => $operationsCount + $assistanceCount,
        ];
      })
      ->filter(fn(array $row) => $row['total'] > 0)
      ->values();

    if ($activityRows->isEmpty()) {
      $activityRows = $activityResidents
        ->map(fn(Resident $resident) => [
          'resident_name' => $resident->user->name,
          'operations_count' => 0,
          'assistance_count' => 0,
          'total' => 0,
        ])
        ->values();
    }

    $recommendationRows = $procedures
      ->filter(fn(Procedure $procedure) => (bool) $procedure->trainingRequirement)
      ->map(function (Procedure $procedure) use ($residents) {
        $residentProgress = $residents->map(function (Resident $resident) use ($procedure) {
          $completed = ProgressCalculator::completedCount($resident, $procedure);
          $expected = ProgressCalculator::expectedByTrainingYear($resident, $procedure->trainingRequirement);
          $ratio = ProgressCalculator::completionRatio($completed, $expected);
          $status = ProgressCalculator::status($ratio);
          $shortfall = max(0, $expected - $completed);

          return [
            'resident_name' => $resident->user->name,
            'training_year' => $resident->training_year,
            'completed' => $completed,
            'expected' => $expected,
            'progress_percent' => (int) round(min(200, $ratio * 100)),
            'status' => $status,
            'shortfall' => $shortfall,
          ];
        })->values();

        $recommendedResidents = $residentProgress
          ->filter(fn(array $row) => $row['expected'] > 0 && $row['shortfall'] > 0)
          ->sort(function (array $a, array $b) {
            $priorityMap = ['red' => 0, 'yellow' => 1, 'green' => 2];
            $priorityDiff = ($priorityMap[$a['status']] ?? 3) <=> ($priorityMap[$b['status']] ?? 3);

            if ($priorityDiff !== 0) {
              return $priorityDiff;
            }

            return $b['shortfall'] <=> $a['shortfall'];
          })
          ->values();

        return [
          'procedure_name' => $procedure->name,
          'resident_progress' => $residentProgress->all(),
          'recommended_residents' => $recommendedResidents->all(),
        ];
      })
      ->values();

    return view('director.dashboard', [
      'rows' => $rows,
      'recommendationRows' => $recommendationRows,
      'procedures' => $procedures,
      'residentCount' => $residents->count(),
      'procedureCount' => $procedures->count(),
      'approvedTotal' => CaseApproval::query()->where('status', 'approved')->count(),
      'pendingTotal' => CaseApproval::query()->where('status', 'pending')->count(),
      'statusCounts' => $statusCounts,
      'chartLabels' => $months->values(),
      'chartSeries' => $monthlySeries,
      'activityLabels' => $activityRows->pluck('resident_name')->all(),
      'activityOperationsSeries' => $activityRows->pluck('operations_count')->all(),
      'activityAssistanceSeries' => $activityRows->pluck('assistance_count')->all(),
    ]);
  }

  public function residentsProgress(Request $request): View
  {
    $residents = Resident::query()->with('user')->orderBy('training_year')->orderBy('id')->get();
    $initialStatusFilter = $request->query('status');

    if (! in_array($initialStatusFilter, ['green', 'yellow', 'red'], true)) {
      $initialStatusFilter = '';
    }

    return view('director.residents-progress', [
      'residentProgressRows' => $this->buildResidentProgressRows($residents),
      'initialStatusFilter' => $initialStatusFilter,
    ]);
  }

  private function buildResidentProgressRows(Collection $residents): Collection
  {
    $procedures = Procedure::with(['trainingRequirement', 'yearlyTargets'])->orderBy('name')->get();

    return $residents->map(function (Resident $resident) use ($procedures) {
      $completedTotal = 0;
      $expectedTotal = 0;
      $procedureDetails = [];

      foreach ($procedures as $procedure) {
        if (! $procedure->trainingRequirement) {
          continue;
        }

        $completed = ProgressCalculator::completedCount($resident, $procedure);
        $expected = ProgressCalculator::expectedByTrainingYear($resident, $procedure->trainingRequirement);
        $ratio = ProgressCalculator::completionRatio($completed, $expected);
        $status = ProgressCalculator::status($ratio);

        $completedTotal += $completed;
        $expectedTotal += $expected;

        $procedureDetails[] = [
          'procedure_name' => $procedure->name,
          'completed' => $completed,
          'expected' => $expected,
          'progress_percent' => (int) round(min(200, $ratio * 100)),
          'status' => $status,
          'status_label' => ProgressCalculator::statusLabel($status),
        ];
      }

      $ratio = ProgressCalculator::completionRatio($completedTotal, $expectedTotal);
      $status = ProgressCalculator::status($ratio);

      return [
        'resident_name' => $resident->user->name,
        'training_year' => $resident->training_year,
        'completed_total' => $completedTotal,
        'expected_total' => $expectedTotal,
        'progress_percent' => (int) round(min(200, $ratio * 100)),
        'status' => $status,
        'status_label' => ProgressCalculator::statusLabel($status),
        'procedure_details' => $procedureDetails,
      ];
    })->values();
  }
}
