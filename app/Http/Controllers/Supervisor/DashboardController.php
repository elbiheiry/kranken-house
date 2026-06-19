<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\CaseApproval;
use App\Models\Procedure;
use App\Models\Resident;
use App\Support\ProgressCalculator;
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

    $procedures = Procedure::with(['trainingRequirement', 'yearlyTargets'])->orderBy('name')->get();
    $behindResidents = Resident::query()->with('user')->orderBy('training_year')->orderBy('id')->get()
      ->map(function (Resident $resident) use ($procedures) {
        $completedTotal = 0;
        $expectedTotal = 0;
        $procedureRows = collect();

        foreach ($procedures as $procedure) {
          if (! $procedure->trainingRequirement) {
            continue;
          }

          $completed = ProgressCalculator::completedCount($resident, $procedure);
          $expected = ProgressCalculator::expectedByTrainingYear($resident, $procedure->trainingRequirement);
          $procedureRatio = ProgressCalculator::completionRatio($completed, $expected);
          $procedureStatus = ProgressCalculator::status($procedureRatio);

          $completedTotal += $completed;
          $expectedTotal += $expected;
          $procedureRows->push([
            'name' => $procedure->name,
            'ratio' => $procedureRatio,
            'status' => $procedureStatus,
          ]);
        }

        $ratio = ProgressCalculator::completionRatio($completedTotal, $expectedTotal);
        $status = ProgressCalculator::status($ratio);

        $behindProcedureRows = $procedureRows
          ->where('status', 'red')
          ->sortBy('ratio')
          ->values();

        $primaryBehindProcedure = $behindProcedureRows->first()['name'] ?? null;
        $behindLabel = $primaryBehindProcedure
          ? ($behindProcedureRows->count() > 1
            ? $primaryBehindProcedure . ' (+' . ($behindProcedureRows->count() - 1) . ')'
            : $primaryBehindProcedure)
          : 'N/A';

        return [
          'name' => $resident->user->name,
          'chart_label' => $resident->user->name . ' - ' . $behindLabel,
          'behind_procedures' => $behindProcedureRows->pluck('name')->all(),
          'progress_percent' => (int) round(min(200, $ratio * 100)),
          'status' => $status,
        ];
      })
      ->where('status', 'red')
      ->sortBy('progress_percent')
      ->values();

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
      'behindProcedureRows' => $behindResidents->map(fn(array $row) => [
        'resident_name' => $row['name'],
        'progress_percent' => $row['progress_percent'],
        'procedures' => $row['behind_procedures'],
      ])->values(),
      'behindResidentsCount' => $behindResidents->count(),
      'pendingApprovals' => $pendingApprovals,
    ]);
  }
}
