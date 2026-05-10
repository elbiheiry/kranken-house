<?php

namespace App\Http\Controllers\Supervisor;

use App\Models\CaseApproval;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    return view('supervisor.approvals-index', ['approvals' => $approvals]);
  }

  public function update(Request $request, CaseApproval $approval): RedirectResponse
  {
    abort_unless($approval->supervisor_id === Auth::id(), 403);

    $validated = $request->validate([
      'status' => ['required', 'in:approved,rejected'],
      'feedback' => ['nullable', 'string', 'max:1000'],
      'approved_role' => ['nullable', 'in:assistant,first_assistant,primary,supervised_primary'],
      'approved_procedure_id' => ['nullable', 'exists:procedures,id'],
    ]);

    $approval->update([
      ...$validated,
      'decided_at' => now(),
    ]);

    return back()->with('status', __('app.flash_review_saved'));
  }
}
