<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\ApprovalStatusOption;
use App\Models\CaseApproval;
use App\Models\OperationRoleOption;
use App\Models\Procedure;
use App\Support\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    return view('director.approvals-index', [
      'approvals' => $approvals,
      'caseScope' => $caseScope,
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

    // Directors can also correct role/procedure categorization during review.
    $approval->caseLog()->update([
      'role' => $validated['approved_role'] ?? $approval->caseLog->role,
      'procedure_id' => $validated['approved_procedure_id'] ?? $approval->caseLog->procedure_id,
    ]);

    $approval->loadMissing('caseLog.resident.user');

    $notificationService->notifyUsers(
      [$approval->caseLog->resident->user_id],
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
