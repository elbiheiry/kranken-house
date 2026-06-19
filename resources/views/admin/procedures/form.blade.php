@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header">
      <h5 class="m-0">{{ $isEdit ? 'Edit Procedure' : 'Create Procedure' }}</h5>
    </div>
    <div class="card-body">
      <form method="post"
        action="{{ $isEdit ? route('admin.procedures.update', $procedure) : route('admin.procedures.store') }}">
        @csrf
        @if ($isEdit)
          @method('put')
        @endif

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Procedure Name</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
              value="{{ old('name', $procedure->name) }}" required>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
              value="{{ old('slug', $procedure->slug) }}" placeholder="optional-auto-generated">
            @error('slug')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-12">
            <label class="form-label d-block">Yearly Required Cases</label>
            <div class="row g-2">
              @foreach (range(1, 6) as $year)
                <div class="col-md-2 col-6">
                  <label class="form-label">R{{ $year }}</label>
                  <input type="number" min="0" name="yearly_targets[{{ $year }}]"
                    class="form-control @error('yearly_targets.' . $year) is-invalid @enderror"
                    value="{{ old('yearly_targets.' . $year, $yearlyTargets[$year] ?? 0) }}" required>
                  @error('yearly_targets.' . $year)
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              @endforeach
            </div>
          </div>

          <div class="col-12 form-check mt-3 ms-1">
            <input type="checkbox" name="is_major" value="1" class="form-check-input" id="is_major"
              @checked(old('is_major', $procedure->is_major ?? true))>
            <label for="is_major" class="form-check-label">Major Procedure</label>
          </div>

          <div class="col-12">
            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Create' }}</button>
            <a href="{{ route('admin.procedures.index') }}" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection
