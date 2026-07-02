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
                <form method="post" action="{{ route('supervisor.approvals.update', $approval) }}"
                  class="row g-2 js-approval-form" data-case-code="{{ $approval->caseLog->case_code }}"
                  data-resident-name="{{ $approval->caseLog->resident->user->name }}"
                  data-current-role="{{ str_replace('_', ' ', $approval->caseLog->role) }}"
                  data-current-procedure="{{ $approval->caseLog->procedure->name }}">
                  @csrf
                  @method('patch')
                  <div class="col-md-4">
                    <select class="form-select form-select-sm js-status-select" name="status" required>
                      @foreach ($decisionStatuses as $status)
                        <option value="{{ $status->code }}">{{ $status->label }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-5">
                    <input class="form-control form-control-sm js-feedback-input" name="feedback"
                      placeholder="{{ __('app.feedback_optional') }}">
                  </div>
                  <div class="col-md-4">
                    <select class="form-select form-select-sm js-approved-role" name="approved_role">
                      <option value="">Approved role (optional)</option>
                      @foreach ($operationRoles as $role)
                        <option value="{{ $role->code }}" @selected($approval->caseLog->role === $role->code)>
                          {{ $role->label }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <select class="form-select form-select-sm js-approved-procedure" name="approved_procedure_id">
                      <option value="">Approved procedure (optional)</option>
                      @foreach ($procedures as $procedure)
                        <option value="{{ $procedure->id }}" @selected($approval->caseLog->procedure_id === $procedure->id)>
                          {{ $procedure->name }}</option>
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

  <div class="modal fade" id="supervisorRejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="post" id="supervisorRejectForm">
          @csrf
          @method('patch')
          <input type="hidden" name="status" value="rejected">
          <div class="modal-header">
            <h5 class="modal-title">{{ __('app.review_rejection_details') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-2"><strong>{{ __('app.col_case_code') }}:</strong> <span id="rejectModalCaseCode">-</span>
            </div>
            <div class="mb-2"><strong>{{ __('app.col_resident') }}:</strong> <span
                id="rejectModalResidentName">-</span>
            </div>
            <div class="mb-2"><strong>{{ __('app.current_role') }}:</strong> <span id="rejectModalCurrentRole">-</span>
            </div>
            <div class="mb-3"><strong>{{ __('app.current_procedure') }}:</strong> <span
                id="rejectModalCurrentProcedure">-</span></div>

            <div class="mb-3">
              <label for="rejectApprovedRole" class="form-label">{{ __('app.col_role') }}</label>
              <select class="form-select" id="rejectApprovedRole" name="approved_role">
                <option value="">Approved role (optional)</option>
                @foreach ($operationRoles as $role)
                  <option value="{{ $role->code }}">{{ $role->label }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label for="rejectApprovedProcedure" class="form-label">{{ __('app.col_procedure') }}</label>
              <select class="form-select" id="rejectApprovedProcedure" name="approved_procedure_id">
                <option value="">Approved procedure (optional)</option>
                @foreach ($procedures as $procedure)
                  <option value="{{ $procedure->id }}">{{ $procedure->name }}</option>
                @endforeach
              </select>
            </div>

            <div>
              <label for="rejectFeedback" class="form-label">{{ __('app.feedback_optional') }}</label>
              <textarea class="form-control" id="rejectFeedback" name="feedback" rows="3"
                placeholder="{{ __('app.feedback_optional') }}"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary"
              data-bs-dismiss="modal">{{ __('app.close') }}</button>
            <button type="submit" class="btn btn-danger">{{ __('app.reject_case_log') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    (function() {
      var rejectModalEl = document.getElementById('supervisorRejectModal');
      var rejectForm = document.getElementById('supervisorRejectForm');

      if (!rejectModalEl || !rejectForm) {
        return;
      }

      var rejectModal = typeof bootstrap !== 'undefined' ? bootstrap.Modal.getOrCreateInstance(rejectModalEl) : null;

      document.querySelectorAll('.js-approval-form').forEach(function(form) {
        form.addEventListener('submit', function(event) {
          var statusSelect = form.querySelector('.js-status-select');
          var approvedRole = form.querySelector('.js-approved-role');
          var approvedProcedure = form.querySelector('.js-approved-procedure');
          var feedback = form.querySelector('.js-feedback-input');
          var isRejected = statusSelect && statusSelect.value === 'rejected';

          if (!isRejected) {
            return;
          }

          event.preventDefault();

          rejectForm.setAttribute('action', form.getAttribute('action'));
          document.getElementById('rejectModalCaseCode').textContent = form.getAttribute('data-case-code') ||
            '-';
          document.getElementById('rejectModalResidentName').textContent = form.getAttribute(
            'data-resident-name') || '-';
          document.getElementById('rejectModalCurrentRole').textContent = form.getAttribute(
            'data-current-role') || '-';
          document.getElementById('rejectModalCurrentProcedure').textContent = form.getAttribute(
            'data-current-procedure') || '-';

          document.getElementById('rejectApprovedRole').value = approvedRole ? approvedRole.value : '';
          document.getElementById('rejectApprovedProcedure').value = approvedProcedure ? approvedProcedure
            .value : '';
          document.getElementById('rejectFeedback').value = feedback ? feedback.value : '';

          if (rejectModal) {
            rejectModal.show();
          } else {
            rejectForm.submit();
          }
        });
      });
    })();
  </script>
@endsection
