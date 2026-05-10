@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="card-title m-0 me-2">{{ __('app.pending_case_approvals') }}</h5>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead class="table-light">
          <tr>
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
              <td>{{ $approval->caseLog->resident->user->name }}</td>
              <td>{{ $approval->caseLog->procedure->name }}</td>
              <td>{{ $approval->caseLog->operation_date?->format('Y-m-d') }}</td>
              <td>{{ str_replace('_', ' ', $approval->caseLog->role->value) }}</td>
              <td>
                <form method="post" action="{{ route('supervisor.approvals.update', $approval) }}" class="row g-2">
                  @csrf
                  @method('patch')
                  <div class="col-md-4">
                    <select class="form-select form-select-sm" name="status" required>
                      <option value="approved">{{ __('app.approve') }}</option>
                      <option value="rejected">{{ __('app.reject') }}</option>
                    </select>
                  </div>
                  <div class="col-md-5">
                    <input class="form-control form-control-sm" name="feedback"
                      placeholder="{{ __('app.feedback_optional') }}">
                  </div>
                  <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-primary w-100">{{ __('app.save') }}</button>
                  </div>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted">{{ __('app.no_pending_approvals') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-body">{{ $approvals->links() }}</div>
  </div>
@endsection
