@extends('layouts.app')

@section('content')
  <div class="row g-4 mb-4">
    <div class="col-md-3">
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Total Users</h6>
          <h3 class="mb-0">{{ $userCount }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Administrators</h6>
          <h3 class="mb-0">{{ $adminCount }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Procedures</h6>
          <h3 class="mb-0">{{ $procedureCount }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Case Logs</h6>
          <h3 class="mb-0">{{ $caseLogCount }}</h3>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h5 class="card-title">System Control Center</h5>
      <p class="text-muted">Use the left menu to manage users, roles, procedures, and yearly operation targets.</p>

      <form method="post" action="{{ route('admin.dashboard.send-monthly-digest') }}" class="mt-3">
        @csrf
        <button type="submit" class="btn btn-primary">Send Monthly Digest Now</button>
      </form>
    </div>
  </div>
@endsection
