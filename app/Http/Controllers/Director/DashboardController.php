<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\CaseApproval;
use App\Models\Procedure;
use App\Models\Resident;
use App\Support\ProgressCalculator;
use Illuminate\Support\Carbon;
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
}
