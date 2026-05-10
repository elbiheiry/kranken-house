@extends('layouts.app')

@section('content')
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
      <div class="card px-sm-6 px-0">
        <div class="card-body">
          <div class="d-flex justify-content-end mb-2 gap-1">
            <a href="{{ route('locale.switch', 'en') }}"
              class="btn btn-sm {{ app()->getLocale() === 'en' ? 'btn-primary' : 'btn-outline-secondary' }}">EN</a>
            <a href="{{ route('locale.switch', 'de') }}"
              class="btn btn-sm {{ app()->getLocale() === 'de' ? 'btn-primary' : 'btn-outline-secondary' }}">DE</a>
          </div>

          <h4 class="mb-2">{{ __('app.welcome') }}</h4>
          <p class="mb-4">{{ __('app.sign_in_subtitle') }}</p>

          <form method="post" action="{{ route('login.perform') }}" class="mb-4">
            @csrf
            <div class="mb-3">
              <label for="email" class="form-label">{{ __('app.email') }}</label>
              <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                name="email" value="{{ old('email') }}" required>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3 form-password-toggle">
              <label class="form-label" for="password">{{ __('app.password') }}</label>
              <input id="password" type="password" class="form-control" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary d-grid w-100">{{ __('app.sign_in') }}</button>
          </form>

          <div class="text-muted small">
            {{ __('app.seeded_hint') }}
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
