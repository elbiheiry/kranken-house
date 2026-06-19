@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header">
      <h5 class="m-0">{{ $isEdit ? 'Edit User' : 'Create User' }}</h5>
    </div>
    <div class="card-body">
      <form method="post" action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}">
        @csrf
        @if ($isEdit)
          @method('put')
        @endif

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
              value="{{ old('name', $user->name) }}" required>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
              value="{{ old('email', $user->email) }}" required>
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Role</label>
            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
              @foreach ($roles as $role)
                <option value="{{ $role->code }}" @selected(old('role', $user->role) === $role->code)>{{ $role->label }}</option>
              @endforeach
            </select>
            @error('role')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Password {{ $isEdit ? '(leave blank to keep current)' : '' }}</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
              {{ $isEdit ? '' : 'required' }}>
            @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Training Year (Residents only)</label>
            <input type="number" min="1" max="6" name="training_year"
              class="form-control @error('training_year') is-invalid @enderror"
              value="{{ old('training_year', $user->residentProfile?->training_year) }}">
            @error('training_year')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-12">
            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Create' }}</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection
