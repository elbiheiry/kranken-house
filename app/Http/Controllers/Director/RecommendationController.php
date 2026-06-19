<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\CaseLog;
use App\Models\Procedure;
use App\Models\Resident;
use App\Support\NotificationService;
use App\Support\ProgressCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
  public function store(Request $request, NotificationService $notificationService): RedirectResponse
  {
    $validated = $request->validate([
      'procedure_id' => ['required', 'exists:procedures,id'],
    ]);

    $procedure = Procedure::with(['trainingRequirement', 'yearlyTargets'])->findOrFail($validated['procedure_id']);

    $best = Resident::with('user')
      ->get()
      ->map(function (Resident $resident) use ($procedure) {
        $completed = ProgressCalculator::completedCount($resident, $procedure);
        $expected = ProgressCalculator::expectedByTrainingYear($resident, $procedure->trainingRequirement);
        $ratio = ProgressCalculator::completionRatio($completed, $expected);
        $status = ProgressCalculator::status($ratio);

        $recentExposure = CaseLog::query()
          ->where('resident_id', $resident->id)
          ->where('procedure_id', $procedure->id)
          ->where('operation_date', '>=', now()->subDays(90)->toDateString())
          ->count();

        $behindScore = max(0, 1 - $ratio) * 100;
        $levelWeight = $resident->training_year >= 3 ? 15 : 5;
        $exposureWeight = max(0, 10 - $recentExposure);
        $priority = round($behindScore + $levelWeight + $exposureWeight, 2);

        return [
          'resident' => $resident,
          'status' => $status,
          'recent_exposure' => $recentExposure,
          'priority' => $priority,
        ];
      })
      ->sortByDesc('priority')
      ->first();

    if (! $best) {
      return back()->with('status', __('app.flash_no_resident_data'));
    }

    $assignment = Assignment::create([
      'resident_id' => $best['resident']->id,
      'procedure_id' => $procedure->id,
      'recommended_by_id' => $request->user()->id,
      'priority_score' => $best['priority'],
      'reason' => sprintf(
        '%s status for %s, training year R%d, %d recent similar cases in last 90 days.',
        ProgressCalculator::statusLabel($best['status']),
        $procedure->name,
        $best['resident']->training_year,
        $best['recent_exposure']
      ),
      'status' => 'suggested',
    ]);

    $notificationService->notifyAllExcept(
      $request->user()->id,
      'assignment-created',
      'New procedure recommendation',
      sprintf('%s recommended %s for %s.', $request->user()->name, $best['resident']->user->name, $procedure->name),
      ['assignment_id' => $assignment->id]
    );

    return back()->with('status', sprintf('Recommended resident: %s for %s.', $best['resident']->user->name, $procedure->name));
  }
}
