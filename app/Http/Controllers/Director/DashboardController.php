<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CaseApproval;
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
    $procedures = Procedure::with('trainingRequirement')->orderBy('name')->get();
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
        $recommendation = $this->buildRecommendation($completed, $expected, $ratio, $status);

        $rows[] = [
          'resident' => $resident,
          'procedure' => $procedure,
          'expected' => $expected,
          'completed' => $completed,
          'progress_percent' => (int) round(min(200, $ratio * 100)),
          'status' => $status,
          'status_label' => ProgressCalculator::statusLabel($status),
          'recommendation' => $recommendation['title'],
          'recommendation_reason' => $recommendation['reason'],
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

    return view('director.dashboard', [
      'rows' => $rows,
      'procedures' => $procedures,
      'residentCount' => $residents->count(),
      'procedureCount' => $procedures->count(),
      'approvedTotal' => CaseApproval::query()->where('status', 'approved')->count(),
      'pendingTotal' => CaseApproval::query()->where('status', 'pending')->count(),
      'statusCounts' => $statusCounts,
      'chartLabels' => $months->values(),
      'chartSeries' => $monthlySeries,
    ]);
  }

  private function buildRecommendation(int $completed, int $expected, float $ratio, string $status): array
  {
    if ($expected <= 0) {
      return [
        'title' => __('app.rec_observe_exposure'),
        'reason' => __('app.rec_reason_no_current_year_requirement'),
      ];
    }

    $shortfall = max(0, $expected - $completed);

    if ($status === 'red') {
      return [
        'title' => __('app.rec_urgent_remediation'),
        'reason' => __('app.rec_reason_red', [
          'completed' => $completed,
          'expected' => $expected,
          'shortfall' => $shortfall,
        ]),
      ];
    }

    if ($status === 'yellow') {
      return [
        'title' => __('app.rec_targeted_practice'),
        'reason' => __('app.rec_reason_yellow', [
          'completed' => $completed,
          'expected' => $expected,
          'shortfall' => $shortfall,
        ]),
      ];
    }

    if ($ratio >= 1.2) {
      return [
        'title' => __('app.rec_increase_complexity'),
        'reason' => __('app.rec_reason_exceeding', [
          'completed' => $completed,
          'expected' => $expected,
        ]),
      ];
    }

    return [
      'title' => __('app.rec_maintain_pace'),
      'reason' => __('app.rec_reason_on_track', [
        'completed' => $completed,
        'expected' => $expected,
      ]),
    ];
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
    $procedures = Procedure::with('trainingRequirement')->orderBy('name')->get();

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
