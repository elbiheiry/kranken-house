@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="m-0">Procedures And Yearly Targets</h5>
      <a href="{{ route('admin.procedures.create') }}" class="btn btn-sm btn-primary">Create Procedure</a>
    </div>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead class="table-light">
          <tr>
            <th>Procedure</th>
            <th>Slug</th>
            <th>Yearly Targets (R1-R6)</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($procedures as $procedure)
            @php
              $targets = collect(range(1, 6))->map(function ($year) use ($procedure) {
                  return $procedure->yearlyTargets->firstWhere('training_year', $year)?->required_cases ?? 0;
              });
            @endphp
            <tr>
              <td>{{ $procedure->name }}</td>
              <td>{{ $procedure->slug }}</td>
              <td>{{ $targets->join(' / ') }}</td>
              <td>
                <div class="d-flex gap-2">
                  <a href="{{ route('admin.procedures.edit', $procedure) }}"
                    class="btn btn-sm btn-outline-primary">Edit</a>
                  <form method="post" action="{{ route('admin.procedures.destroy', $procedure) }}"
                    onsubmit="return confirm('Delete this procedure?')">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center text-muted py-4">No procedures found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-body">{{ $procedures->links() }}</div>
  </div>
@endsection
