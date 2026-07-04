@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header">
      <h5 class="card-title m-0 me-2">{{ __('app.log_case_title') }}</h5>
    </div>
    <div class="card-body">
      <p class="text-muted">{{ __('app.anonymized_hint') }}</p>
      <form method="post" action="{{ route('resident.case-logs.store') }}">
        @csrf
        <div class="row g-4">
          <div class="col-md-6">
            <label class="form-label">{{ __('app.case_code') }}</label>
            <input class="form-control @error('case_code') is-invalid @enderror" name="case_code"
              value="{{ old('case_code') }}" required>
            @error('case_code')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('app.col_procedure') }}</label>
            <select class="form-select @error('procedure_id') is-invalid @enderror" name="procedure_id" required>
              <option value="">{{ __('app.select_procedure') }}</option>
              @foreach ($procedures as $procedure)
                <option value="{{ $procedure->id }}" @selected(old('procedure_id') == $procedure->id)>{{ $procedure->name }}</option>
              @endforeach
            </select>
            @error('procedure_id')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('app.operation_type') }}</label>
            <select class="form-select @error('operation_type') is-invalid @enderror" name="operation_type" required>
              @foreach ($operationTypes as $type)
                <option value="{{ $type->code }}" @selected(old('operation_type', $loop->first ? $type->code : null) === $type->code)>
                  {{ $type->label }}
                </option>
              @endforeach
            </select>
            @error('operation_type')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('app.difficulty_level') }}</label>
            <input class="form-control @error('difficulty_level') is-invalid @enderror" type="number" min="1"
              max="5" name="difficulty_level" value="{{ old('difficulty_level', 3) }}" required>
            @error('difficulty_level')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('app.role_in_operation') }}</label>
            <select class="form-select @error('role') is-invalid @enderror" name="role" required>
              @foreach ($operationRoles as $role)
                <option value="{{ $role->code }}" @selected(old('role', $loop->first ? $role->code : null) === $role->code)>
                  {{ $role->code === 'primary' ? 'Operator' : $role->label }}
                </option>
              @endforeach
            </select>
            @error('role')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('app.supervisor') }}</label>
            <select class="form-select @error('supervisor_id') is-invalid @enderror" name="supervisor_id">
              <option value="">{{ __('app.auto_assign') }}</option>
              @foreach ($supervisors as $supervisor)
                <option value="{{ $supervisor->id }}" @selected(old('supervisor_id') == $supervisor->id)>{{ $supervisor->name }}</option>
              @endforeach
            </select>
            @error('supervisor_id')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('app.operation_date') }}</label>
            <input class="form-control @error('operation_date') is-invalid @enderror" type="date" name="operation_date"
              value="{{ old('operation_date', now()->toDateString()) }}" required>
            @error('operation_date')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6 d-flex align-items-end">
            <div class="form-check mb-1">
              <input class="form-check-input" type="checkbox" name="is_external" value="1" id="isExternalCase"
                @checked(old('is_external'))>
              <label class="form-check-label" for="isExternalCase">{{ __('app.external_case') }}</label>
              <div class="form-text">{{ __('app.external_case_hint') }}</div>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label">{{ __('app.note') }}</label>
            <textarea class="form-control @error('note') is-invalid @enderror" name="note" rows="3">{{ old('note') }}</textarea>
            @error('note')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary">{{ __('app.submit_for_approval') }}</button>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection
