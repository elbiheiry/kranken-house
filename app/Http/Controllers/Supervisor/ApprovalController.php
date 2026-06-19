<?php

namespace App\Http\Controllers\Supervisor;

use App\Models\ApprovalStatusOption;
use App\Models\CaseApproval;
use App\Models\OperationRoleOption;
use App\Models\Procedure;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Support\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApprovalController extends Controller
{
  public function index(): View
  {
    $approvals = CaseApproval::query()
      ->with(['caseLog.resident.user', 'caseLog.procedure'])
      ->where('supervisor_id', Auth::id())
      ->where('status', 'pending')
      ->latest()
      ->paginate(20);

    return view('supervisor.approvals-index', [
      'approvals' => $approvals,
      'decisionStatuses' => ApprovalStatusOption::query()
        ->where(function ($query) {
          $query->where('code', 'approved')
            ->orWhere('code', 'rejected');
        })
        ->orderBy('id')
        ->get(),
      'operationRoles' => OperationRoleOption::query()->orderBy('id', 'asc')->get(),
      'procedures' => Procedure::query()->orderBy('name', 'asc')->get(),
    ]);
  }

  public function update(Request $request, CaseApproval $approval, NotificationService $notificationService): RedirectResponse
  {
    abort_unless($approval->supervisor_id === Auth::id(), 403);

    $allowedStatuses = ApprovalStatusOption::query()
      ->where(function ($query) {
        $query->where('code', 'approved')
          ->orWhere('code', 'rejected');
      })
      ->pluck('code')
      ->all();

    $allowedRoles = OperationRoleOption::query()->pluck('code')->all();

    $validated = $request->validate([
      'status' => ['required', Rule::in($allowedStatuses)],
      'feedback' => ['nullable', 'string', 'max:1000'],
      'approved_role' => ['nullable', Rule::in($allowedRoles)],
      'approved_procedure_id' => ['nullable', 'exists:procedures,id'],
    ]);

    $approval->update([
      ...$validated,
      'decided_at' => now(),
    ]);

    // Supervisors are allowed to correct role/procedure categorization during review.
    $approval->caseLog()->update([
      'role' => $validated['approved_role'] ?? $approval->caseLog->role,
      'procedure_id' => $validated['approved_procedure_id'] ?? $approval->caseLog->procedure_id,
    ]);

    $approval->loadMissing('caseLog.resident.user');

    $directorIds = User::query()
      ->where('role', 'director')
      ->pluck('id')
      ->all();

    $recipientIds = collect($directorIds)
      ->push($approval->caseLog->resident->user_id)
      ->unique()
      ->values()
      ->all();

    $notificationService->notifyUsers(
      $recipientIds,
      'case-approval-updated',
      'Case approval decision',
      sprintf(
        'Case %s for %s was marked as %s by %s.',
        $approval->caseLog->case_code,
        $approval->caseLog->resident->user->name,
        $validated['status'],
        $request->user()->name,
      ),
      ['approval_id' => $approval->id, 'status' => $validated['status']]
    );

    return back()->with('status', __('app.flash_review_saved'));
  }
}
