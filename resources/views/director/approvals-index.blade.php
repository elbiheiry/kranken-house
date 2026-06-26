@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="card-title m-0 me-2">{{ __('app.pending_case_approvals') }}</h5>
      <form method="get" class="d-flex align-items-center gap-2">
        <label for="caseScopeFilter" class="form-label m-0">{{ __('app.case_type_filter') }}</label>
        <select id="caseScopeFilter" name="case_scope" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="" @selected(($caseScope ?? '') === '')>{{ __('app.case_type_all') }}</option>
          <option value="external" @selected(($caseScope ?? '') === 'external')>{{ __('app.case_type_external') }}</option>
          <option value="internal" @selected(($caseScope ?? '') === 'internal')>{{ __('app.case_type_internal') }}</option>
        </select>
      </form>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead class="table-light">
          <tr>
            <th>{{ __('app.col_case_code') }}</th>
            <th>{{ __('app.col_resident') }}</th>
            <th>{{ __('app.col_procedure') }}</th>
            <th>{{ __('app.col_date') }}</th>
            <th>{{ __('app.col_role') }}</th>
            <th>{{ __('app.col_action') }}</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @forelse($approvals as $approval)
            <tr>
              <td>{{ $approval->caseLog->case_code }}</td>
              <td>{{ $approval->caseLog->resident->user->name }}</td>
              <td>
                {{ $approval->caseLog->procedure->name }}
                @if ($approval->caseLog->is_external)
                  <span class="badge bg-label-info ms-1">{{ __('app.external_case_short') }}</span>
                @endif
              </td>
              <td>{{ $approval->caseLog->operation_date?->format('Y-m-d') }}</td>
              <td>{{ str_replace('_', ' ', $approval->caseLog->role) }}</td>
              <td>
                <form method="post" action="{{ route('director.approvals.update', $approval) }}" class="row g-2">
                  @csrf
                  @method('patch')
                  <div class="col-md-4">
                    <select class="form-select form-select-sm" name="status" required>
                      @foreach ($decisionStatuses as $status)
                        <option value="{{ $status->code }}">{{ $status->label }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-5">
                    <input class="form-control form-control-sm" name="feedback"
                      placeholder="{{ __('app.feedback_optional') }}">
                  </div>
                  <div class="col-md-4">
                    <select class="form-select form-select-sm" name="approved_role">
                      <option value="">Approved role (optional)</option>
                      @foreach ($operationRoles as $role)
                        <option value="{{ $role->code }}">{{ $role->label }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <select class="form-select form-select-sm" name="approved_procedure_id">
                      <option value="">Approved procedure (optional)</option>
                      @foreach ($procedures as $procedure)
                        <option value="{{ $procedure->id }}">{{ $procedure->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-primary w-100">{{ __('app.save') }}</button>
                  </div>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted">{{ __('app.no_pending_approvals') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-body">{{ $approvals->links() }}</div>
  </div>
@endsection
