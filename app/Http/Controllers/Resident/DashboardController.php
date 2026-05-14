<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\CaseLog;
use App\Models\Procedure;
use App\Models\Resident;
use App\Support\ProgressCalculator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
  public function index(): View
  {
    $user = Auth::user();
    $resident = $user->residentProfile;

    abort_unless($resident, 403);

    $baseQuery = CaseLog::query()->where('resident_id', $resident->id);

    $submittedCount = (clone $baseQuery)->count();
    $approvedCount = (clone $baseQuery)->whereHas('approval', fn($query) => $query->where('status', 'approved'))->count();
    $pendingCount = (clone $baseQuery)->whereHas('approval', fn($query) => $query->where('status', 'pending'))->count();
    $rejectedCount = (clone $baseQuery)->whereHas('approval', fn($query) => $query->where('status', 'rejected'))->count();

    $procedures = Procedure::with('trainingRequirement')->orderBy('name', 'asc')->get();
    $progressRows = [];

    foreach ($procedures as $procedure) {
      if (! $procedure->trainingRequirement) {
        continue;
      }

      $completed = ProgressCalculator::completedCount($resident, $procedure);
      $expected = ProgressCalculator::expectedByTrainingYear($resident, $procedure->trainingRequirement);
      $ratio = ProgressCalculator::completionRatio($completed, $expected);
      $status = ProgressCalculator::status($ratio);

      $progressRows[] = [
        'procedure' => $procedure->name,
        'completed' => $completed,
        'expected' => $expected,
        'progress_percent' => (int) round(min(200, $ratio * 100)),
        'status' => $status,
        'status_label' => ProgressCalculator::statusLabel($status),
      ];
    }

    $progressChartRows = collect($progressRows)
      ->filter(fn(array $row) => $row['completed'] > 0 || $row['expected'] > 0)
      ->values();

    $totalCompleted = $progressChartRows->sum('completed');
    $totalExpected = $progressChartRows->sum('expected');
    $overallProgressPercent = (int) round(min(200, ProgressCalculator::completionRatio($totalCompleted, $totalExpected) * 100));

    $months = collect(range(5, 1))->map(fn(int $offset) => Carbon::now()->subMonths($offset)->format('Y-m'));
    $months = $months->push(Carbon::now()->format('Y-m'));

    $monthlyCounts = (clone $baseQuery)
      ->whereDate('operation_date', '>=', Carbon::now()->startOfMonth()->subMonths(5))
      ->orderBy('operation_date', 'asc')
      ->get()
      ->groupBy(fn(CaseLog $log) => $log->operation_date?->format('Y-m'));

    $monthlySeries = $months->map(fn(string $month) => $monthlyCounts->get($month)?->count() ?? 0)->values();

    return view('resident.dashboard', [
      'submittedCount' => $submittedCount,
      'approvedCount' => $approvedCount,
      'pendingCount' => $pendingCount,
      'rejectedCount' => $rejectedCount,
      'progressRows' => $progressRows,
      'progressChartLabels' => $progressChartRows->pluck('procedure')->all(),
      'progressChartSeries' => $progressChartRows->pluck('completed')->all(),
      'progressChartColors' => $progressChartRows
        ->map(fn(array $row, int $index) => ['#696cff', '#71dd37', '#03c3ec', '#ffab00', '#ff3e1d', '#8592a3'][$index % 6])
        ->all(),
      'totalCompletedProcedures' => $progressChartRows->count(),
      'totalCompletedCases' => $totalCompleted,
      'totalExpectedCases' => $totalExpected,
      'overallProgressPercent' => $overallProgressPercent,
      'chartLabels' => $months->values(),
      'chartSeries' => $monthlySeries,
    ]);
  }

  public function peersProgress(): View
  {
    $currentResident = Auth::user()->residentProfile;

    abort_unless($currentResident, 403);

    $residents = Resident::query()
      ->with('user')
      ->where('id', '!=', $currentResident->id)
      ->orderBy('training_year')
      ->orderBy('id')
      ->get();

    return view('resident.peers-progress', [
      'peerRows' => $this->buildResidentProgressRows($residents),
    ]);
  }

  private function buildResidentProgressRows(Collection $residents): Collection
  {
    $procedures = Procedure::with('trainingRequirement')->orderBy('name', 'asc')->get();

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
