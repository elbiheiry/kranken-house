@extends('layouts.app')

@section('content')
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <span class="fw-semibold d-block mb-1">{{ __('app.submitted_cases') }}</span>
              <h3 class="card-title mb-0">{{ $submittedCount }}</h3>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-file fs-4"></i></span>
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
              <span class="fw-semibold d-block mb-1">{{ __('app.approved') }}</span>
              <h3 class="card-title mb-0">{{ $approvedCount }}</h3>
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
              <span class="fw-semibold d-block mb-1">{{ __('app.pending') }}</span>
              <h3 class="card-title mb-0">{{ $pendingCount }}</h3>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time-five fs-4"></i></span>
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
              <span class="fw-semibold d-block mb-1">{{ __('app.rejected') }}</span>
              <h3 class="card-title mb-0">{{ $rejectedCount }}</h3>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-x-circle fs-4"></i></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title m-0 me-2">{{ __('app.my_submissions_chart') }}</h5>
        </div>
        <div class="card-body">
          <div id="residentMonthlyChart" style="min-height: 280px;"></div>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title m-0 me-2">{{ __('app.my_progress_by_procedure') }}</h5>
        </div>
        <div class="table-responsive text-nowrap">
          <table class="table">
            <thead class="table-light">
              <tr>
                <th>{{ __('app.col_procedure') }}</th>
                <th>{{ __('app.col_completed') }}</th>
                <th>{{ __('app.col_expected') }}</th>
                <th>{{ __('app.col_progress') }}</th>
                <th>{{ __('app.col_status') }}</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              @forelse($progressRows as $row)
                <tr>
                  <td>{{ $row['procedure'] }}</td>
                  <td>{{ $row['completed'] }}</td>
                  <td>{{ $row['expected'] }}</td>
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
                  <td colspan="5" class="text-center text-muted">{{ __('app.no_progress_data') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script>
    (function() {
      var chartEl = document.querySelector('#residentMonthlyChart');
      if (!chartEl || typeof ApexCharts === 'undefined') {
        return;
      }

      var chart = new ApexCharts(chartEl, {
        chart: {
          type: 'bar',
          height: 280,
          toolbar: {
            show: false
          }
        },
        series: [{
          name: 'Cases',
          data: @json($chartSeries)
        }],
        xaxis: {
          categories: @json($chartLabels)
        },
        colors: ['#696cff'],
        dataLabels: {
          enabled: false
        }
      });

      chart.render();
    })();
  </script>
@endsection
