<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\CaseApproval;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
  public function index(): View
  {
    $supervisorId = Auth::id();

    $baseQuery = CaseApproval::query()->where('supervisor_id', $supervisorId);

    $pendingCount = (clone $baseQuery)->where('status', 'pending')->count();
    $approvedCount = (clone $baseQuery)->where('status', 'approved')->count();
    $rejectedCount = (clone $baseQuery)->where('status', 'rejected')->count();

    $months = collect(range(5, 1))->map(fn(int $offset) => Carbon::now()->subMonths($offset)->format('Y-m'));
    $months = $months->push(Carbon::now()->format('Y-m'));

    $monthlyDecisions = (clone $baseQuery)
      ->whereNotNull('decided_at')
      ->whereDate('decided_at', '>=', Carbon::now()->startOfMonth()->subMonths(5))
      ->orderBy('decided_at', 'asc')
      ->get()
      ->groupBy(fn(CaseApproval $approval) => $approval->decided_at?->format('Y-m'));

    $monthlySeries = $months->map(fn(string $month) => $monthlyDecisions->get($month)?->count() ?? 0)->values();

    $pendingApprovals = (clone $baseQuery)
      ->with(['caseLog.resident.user', 'caseLog.procedure'])
      ->where('status', 'pending')
      ->latest('created_at')
      ->take(8)
      ->get();

    return view('supervisor.dashboard', [
      'pendingCount' => $pendingCount,
      'approvedCount' => $approvedCount,
      'rejectedCount' => $rejectedCount,
      'chartLabels' => $months->values(),
      'chartSeries' => $monthlySeries,
      'pendingApprovals' => $pendingApprovals,
    ]);
  }
}
