@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="card-title m-0 me-2">{{ __('app.my_case_logs') }}</h5>
      <a href="{{ route('resident.case-logs.create') }}" class="btn btn-sm btn-primary">{{ __('app.new_log') }}</a>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead class="table-light">
          <tr>
            <th>{{ __('app.col_date') }}</th>
            <th>{{ __('app.col_case_code') }}</th>
            <th>{{ __('app.col_procedure') }}</th>
            <th>{{ __('app.col_role') }}</th>
            <th>{{ __('app.col_status') }}</th>
            <th>{{ __('app.col_feedback') }}</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @forelse($logs as $log)
            <tr>
              <td>{{ $log->operation_date?->format('Y-m-d') }}</td>
              <td>{{ $log->case_code }}</td>
              <td>{{ $log->procedure->name }}</td>
              <td>{{ str_replace('_', ' ', $log->role) }}</td>
              <td>
                @php $status = $log->approval?->status ?? 'pending'; @endphp
                @if ($status === 'approved')
                  <span class="badge bg-label-success">{{ __('app.approved') }}</span>
                @elseif($status === 'rejected')
                  <span class="badge bg-label-danger">{{ __('app.rejected') }}</span>
                @else
                  <span class="badge bg-label-warning">{{ __('app.pending') }}</span>
                @endif
              </td>
              <td>{{ $log->approval?->feedback ?? '-' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted">{{ __('app.no_case_logs') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-body">{{ $logs->links() }}</div>
  </div>
@endsection
