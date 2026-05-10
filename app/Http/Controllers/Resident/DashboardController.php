<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\CaseLog;
use App\Models\Procedure;
use App\Support\ProgressCalculator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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
      'chartLabels' => $months->values(),
      'chartSeries' => $monthlySeries,
    ]);
  }
}
