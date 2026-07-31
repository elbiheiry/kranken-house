@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header">
      <h5 class="m-0">My Profile</h5>
    </div>
    <div class="card-body">
      <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

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
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
              placeholder="Leave blank to keep current password">
            @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          @if ($user->role === 'resident')
            <div class="col-md-6">
              <label class="form-label">Training Year</label>
              <input type="number" min="1" max="6" name="training_year"
                class="form-control @error('training_year') is-invalid @enderror"
                value="{{ old('training_year', $user->residentProfile?->training_year) }}">
              @error('training_year')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          @endif

          <div class="col-12">
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection
