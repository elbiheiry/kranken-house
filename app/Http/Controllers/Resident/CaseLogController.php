<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\CaseApproval;
use App\Models\CaseLog;
use App\Models\OperationRoleOption;
use App\Models\OperationTypeOption;
use App\Models\Procedure;
use App\Models\User;
use App\Support\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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
    $operationTypes = OperationTypeOption::query()->orderBy('id', 'asc')->get();
    $operationRoles = OperationRoleOption::query()->orderBy('id', 'asc')->get();

    return view('resident.case-log-create', [
      'procedures' => Procedure::query()->orderBy('name', 'asc')->get(),
      'supervisors' => User::query()->where('role', 'supervisor')->orderBy('name', 'asc')->get(),
      'operationTypes' => $operationTypes,
      'operationRoles' => $operationRoles,
    ]);
  }

  public function store(Request $request, NotificationService $notificationService): RedirectResponse
  {
    $resident = Auth::user()->residentProfile;
    $operationTypeCodes = OperationTypeOption::query()->pluck('code')->all();
    $operationRoleCodes = OperationRoleOption::query()->pluck('code')->all();

    $validated = $request->validate([
      'case_code'       => ['required', 'string', 'max:64', 'unique:case_logs,case_code,NULL,id,resident_id,' . $resident->id],
      'procedure_id'    => ['required', 'exists:procedures,id'],
      'operation_type'  => ['required', Rule::in($operationTypeCodes)],
      'difficulty_level' => ['required', 'integer', 'min:1', 'max:5'],
      'role'            => ['required', Rule::in($operationRoleCodes)],
      'operation_date'  => ['required', 'date', 'before_or_equal:today'],
      'supervisor_id'   => ['nullable', 'exists:users,id'],
      'note'            => ['nullable', 'string', 'max:1000'],
    ]);

    $log = CaseLog::create([
      'resident_id' => $resident->id,
      ...$validated,
    ]);

    $assignedSupervisorId = $validated['supervisor_id'] ?? User::query()->where('role', 'supervisor')->value('id');

    CaseApproval::create([
      'case_log_id' => $log->id,
      'supervisor_id' => $assignedSupervisorId,
      'status' => 'pending',
    ]);

    $directorIds = User::query()
      ->where('role', 'director')
      ->pluck('id')
      ->all();

    $recipientIds = collect($directorIds)
      ->push($assignedSupervisorId)
      ->filter()
      ->unique()
      ->values()
      ->all();

    $notificationService->notifyUsers(
      $recipientIds,
      'case-log-created',
      'New case log submitted',
      sprintf('%s submitted case %s for approval.', $request->user()->name, $log->case_code),
      ['case_log_id' => $log->id]
    );

    return redirect()->route('resident.case-logs.index')->with('status', __('app.flash_case_submitted'));
  }
}
