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
  public function index(Request $request): View
  {
    $caseScope = $request->query('case_scope', '');

    if (! in_array($caseScope, ['', 'external', 'internal'], true)) {
      $caseScope = '';
    }

    $approvalsQuery = CaseApproval::query()
      ->with(['caseLog.resident.user', 'caseLog.procedure'])
      ->where('supervisor_id', Auth::id())
      ->whereHas('caseLog', fn($query) => $query->where('supervisor_id', Auth::id()))
      ->where('status', 'pending');

    if ($caseScope === 'external') {
      $approvalsQuery->whereHas('caseLog', fn($query) => $query->where('is_external', true));
    }

    if ($caseScope === 'internal') {
      $approvalsQuery->whereHas('caseLog', fn($query) => $query->where('is_external', false));
    }

    $approvals = $approvalsQuery
      ->latest()
      ->paginate(20)
      ->withQueryString();

    return view('supervisor.approvals-index', [
      'approvals' => $approvals,
      'caseScope' => $caseScope,
      'decisionStatuses' => ApprovalStatusOption::query()
        ->where(function ($query) {
          $query->where('code', 'approved')
            ->orWhere('code', 'rejected');
        })
        ->orderBy('id')
        ->get(),
      'operationRoles' => OperationRoleOption::query()
        ->whereIn('code', ['assistant', 'primary'])
        ->orderBy('id', 'asc')
        ->get(),
      'procedures' => Procedure::query()->orderBy('name', 'asc')->get(),
    ]);
  }

  public function update(Request $request, CaseApproval $approval, NotificationService $notificationService): RedirectResponse
  {
    abort_unless(
      $approval->supervisor_id === Auth::id()
        && $approval->caseLog()->where('supervisor_id', Auth::id())->exists(),
      403
    );

    $validated = $request->validate([
      'status' => ['required', Rule::in(['approved', 'rejected'])],
      'feedback' => ['nullable', 'string', 'max:1000'],
      'approved_role' => ['nullable', Rule::in(['assistant', 'primary'])],
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
