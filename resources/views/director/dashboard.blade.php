@extends('layouts.app')

@section('content')
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <span class="fw-semibold d-block mb-1">{{ __('app.residents') }}</span>
              <h3 class="card-title mb-0">{{ $residentCount }}</h3>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-user-check fs-4"></i></span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <span class="fw-semibold d-block mb-1">{{ __('app.procedures') }}</span>
              <h3 class="card-title mb-0">{{ $procedureCount }}</h3>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-info"><i class="bx bx-injection fs-4"></i></span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <span class="fw-semibold d-block mb-1">{{ __('app.approved_cases') }}</span>
              <h3 class="card-title mb-0">{{ $approvedTotal }}</h3>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-circle fs-4"></i></span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <span class="fw-semibold d-block mb-1">{{ __('app.pending_cases') }}</span>
              <h3 class="card-title mb-0">{{ $pendingTotal }}</h3>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time-five fs-4"></i></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title m-0 me-2">{{ __('app.approved_trend_chart') }}</h5>
        </div>
        <div class="card-body">
          <div id="directorMonthlyChart" style="min-height: 280px;"></div>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title m-0 me-2">{{ __('app.status_mix_chart') }}</h5>
        </div>
        <div class="card-body">
          <div id="directorStatusChart" style="min-height: 280px;"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h5 class="card-title m-0 me-2">{{ __('app.director_section_title') }}</h5>
    </div>
    <div class="card-body">
      <p class="text-muted">{{ __('app.status_color_hint') }}</p>
      <form method="post" action="{{ route('director.recommendations.store') }}" class="mb-4">
        @csrf
        <div class="row g-3 align-items-end">
          <div class="col-md-8">
            <label class="form-label">{{ __('app.upcoming_procedure') }}</label>
            <select class="form-select @error('procedure_id') is-invalid @enderror" name="procedure_id" required>
              <option value="">{{ __('app.select_procedure') }}</option>
              @foreach ($procedures as $procedure)
                <option value="{{ $procedure->id }}">{{ $procedure->name }}</option>
              @endforeach
            </select>
            @error('procedure_id')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">{{ __('app.generate_recommendation') }}</button>
          </div>
        </div>
      </form>

      <div class="table-responsive text-nowrap">
        <table class="table">
          <thead class="table-light">
            <tr>
              <th>{{ __('app.col_resident') }}</th>
              <th>{{ __('app.col_year') }}</th>
              <th>{{ __('app.col_procedure') }}</th>
              <th>{{ __('app.col_expected') }}</th>
              <th>{{ __('app.col_completed') }}</th>
              <th>{{ __('app.col_progress') }}</th>
              <th>{{ __('app.col_status') }}</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($rows as $row)
              <tr>
                <td>{{ $row['resident']->user->name }}</td>
                <td>R{{ $row['resident']->training_year }}</td>
                <td>{{ $row['procedure']->name }}</td>
                <td>{{ $row['expected'] }}</td>
                <td>{{ $row['completed'] }}</td>
                <td>{{ $row['progress_percent'] }}%</td>
                <td>
                  @if ($row['status'] === 'green')
                    <span class="badge bg-label-success">{{ $row['status_label'] }}</span>
                  @elseif($row['status'] === 'yellow')
                    <span class="badge bg-label-warning">{{ $row['status_label'] }}</span>
                  @else
                    <span class="badge bg-label-danger">{{ $row['status_label'] }}</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted">{{ __('app.no_progress_rows') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script>
    (function() {
      if (typeof ApexCharts === 'undefined') {
        return;
      }

      var monthlyEl = document.querySelector('#directorMonthlyChart');
      if (monthlyEl) {
        var monthlyChart = new ApexCharts(monthlyEl, {
          chart: {
            type: 'line',
            height: 280,
            toolbar: {
              show: false
            }
          },
          series: [{
            name: 'Approved',
            data: @json($chartSeries)
          }],
          xaxis: {
            categories: @json($chartLabels)
          },
          stroke: {
            curve: 'smooth',
            width: 3
          },
          dataLabels: {
            enabled: false
          },
          colors: ['#71dd37']
        });
        monthlyChart.render();
      }

      var statusEl = document.querySelector('#directorStatusChart');
      if (statusEl) {
        var statusChart = new ApexCharts(statusEl, {
          chart: {
            type: 'donut',
            height: 280
          },
          labels: @json(array_keys($statusCounts)),
          series: @json(array_values($statusCounts)),
          colors: ['#71dd37', '#ffab00', '#ff3e1d'],
          legend: {
            position: 'bottom'
          }
        });
        statusChart.render();
      }
    })();
  </script>
@endsection
