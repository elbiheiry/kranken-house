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
            <th>{{ __('app.col_details') }}</th>
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
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                  data-bs-target="#caseDetails{{ $approval->id }}" aria-expanded="false">
                  {{ __('app.view_details') }}
                </button>
                <div class="collapse mt-2" id="caseDetails{{ $approval->id }}">
                  <dl class="row mb-0 small">
                    <dt class="col-sm-5">{{ __('app.operation_type') }}</dt>
                    <dd class="col-sm-7">{{ ucfirst($approval->caseLog->operation_type) }}</dd>
                    <dt class="col-sm-5">{{ __('app.difficulty_level') }}</dt>
                    <dd class="col-sm-7">{{ $approval->caseLog->difficulty_level }}</dd>
                    <dt class="col-sm-5">{{ __('app.external_case') }}</dt>
                    <dd class="col-sm-7">{{ $approval->caseLog->is_external ? __('app.yes') : __('app.no') }}</dd>
                    <dt class="col-sm-5">{{ __('app.note') }}</dt>
                    <dd class="col-sm-7 text-wrap">{{ $approval->caseLog->note ?: '-' }}</dd>
                  </dl>
                </div>
              </td>
              <td>
                <form method="post" action="{{ route('supervisor.approvals.update.post', $approval) }}"
                  class="d-flex flex-wrap gap-2 js-approval-form" data-case-code="{{ $approval->caseLog->case_code }}"
                  data-resident-name="{{ $approval->caseLog->resident->user->name }}"
                  data-current-role="{{ str_replace('_', ' ', $approval->caseLog->role) }}"
                  data-current-procedure="{{ $approval->caseLog->procedure->name }}">
                  @csrf
                  <input type="hidden" name="feedback" value="">
                  <div class="w-100">
                    <select class="form-select form-select-sm js-approved-role" name="approved_role">
                      <option value="">Approved role (optional)</option>
                      @foreach ($operationRoles as $role)
                        <option value="{{ $role->code }}" @selected($approval->caseLog->role === $role->code)>
                          {{ $role->label }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="w-100">
                    <select class="form-select form-select-sm js-approved-procedure" name="approved_procedure_id">
                      <option value="">Approved procedure (optional)</option>
                      @foreach ($procedures as $procedure)
                        <option value="{{ $procedure->id }}" @selected($approval->caseLog->procedure_id === $procedure->id)>
                          {{ $procedure->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="d-flex gap-2">
                    <button type="submit" name="status" value="approved"
                      class="btn btn-sm btn-success">{{ __('app.approve') }}</button>
                    <button type="submit" name="status" value="rejected"
                      class="btn btn-sm btn-danger">{{ __('app.reject') }}</button>
                  </div>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted">{{ __('app.no_pending_approvals') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-body">{{ $approvals->links() }}</div>
  </div>

@endsection
