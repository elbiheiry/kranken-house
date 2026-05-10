<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\CaseApproval;
use App\Models\CaseLog;
use App\Models\Procedure;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CaseLogController extends Controller
{
  public function index(): View
  {
    $resident = Auth::user()->residentProfile;

    $logs = CaseLog::query()
      ->with(['procedure', 'approval'])
      ->where('resident_id', $resident->id)
      ->latest('operation_date')
      ->paginate(15);

    return view('resident.case-logs-index', ['logs' => $logs]);
  }

  public function create(): View
  {
    return view('resident.case-log-create', [
      'procedures' => Procedure::query()->orderBy('name', 'asc')->get(),
      'supervisors' => User::query()->where('role', 'supervisor')->orderBy('name', 'asc')->get(),
    ]);
  }

  public function store(Request $request): RedirectResponse
  {
    $resident = Auth::user()->residentProfile;

    $validated = $request->validate([
      'case_code'       => ['required', 'string', 'max:64', 'unique:case_logs,case_code,NULL,id,resident_id,' . $resident->id],
      'procedure_id'    => ['required', 'exists:procedures,id'],
      'operation_type'  => ['required', 'in:emergency,elective'],
      'difficulty_level' => ['required', 'integer', 'min:1', 'max:5'],
      'role'            => ['required', 'in:assistant,first_assistant,primary,supervised_primary'],
      'operation_date'  => ['required', 'date', 'before_or_equal:today'],
      'supervisor_id'   => ['nullable', 'exists:users,id'],
      'note'            => ['nullable', 'string', 'max:1000'],
    ]);

    $log = CaseLog::create([
      'resident_id' => $resident->id,
      ...$validated,
    ]);

    CaseApproval::create([
      'case_log_id' => $log->id,
      'supervisor_id' => $validated['supervisor_id'] ?? User::query()->where('role', 'supervisor')->value('id'),
      'status' => 'pending',
    ]);

    return redirect()->route('resident.case-logs.index')->with('status', __('app.flash_case_submitted'));
  }
}
