@extends('layouts.app')

@section('content')
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <span class="fw-semibold d-block mb-1">{{ __('app.pending_reviews') }}</span>
              <h3 class="card-title mb-0">{{ $pendingCount }}</h3>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time-five fs-4"></i></span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <span class="fw-semibold d-block mb-1">{{ __('app.approved_cases') }}</span>
              <h3 class="card-title mb-0">{{ $approvedCount }}</h3>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-circle fs-4"></i></span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <span class="fw-semibold d-block mb-1">{{ __('app.rejected_cases') }}</span>
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
          <h5 class="card-title m-0 me-2">{{ __('app.my_decisions_chart') }}</h5>
        </div>
        <div class="card-body">
          <div id="supervisorMonthlyChart" style="min-height: 280px;"></div>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title m-0 me-2">{{ __('app.pending_approvals_snapshot') }}</h5>
        </div>
        <div class="table-responsive text-nowrap">
          <table class="table">
            <thead class="table-light">
              <tr>
                <th>{{ __('app.col_resident') }}</th>
                <th>{{ __('app.col_procedure') }}</th>
                <th>{{ __('app.col_date') }}</th>
                <th>{{ __('app.col_status') }}</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              @forelse($pendingApprovals as $approval)
                <tr>
                  <td>{{ $approval->caseLog->resident->user->name }}</td>
                  <td>{{ $approval->caseLog->procedure->name }}</td>
                  <td>{{ $approval->caseLog->operation_date?->format('Y-m-d') }}</td>
                  <td><span class="badge bg-label-warning">{{ __('app.pending') }}</span></td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-muted">{{ __('app.no_pending_approvals') }}</td>
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
      var chartEl = document.querySelector('#supervisorMonthlyChart');
      if (!chartEl || typeof ApexCharts === 'undefined') {
        return;
      }

      var chart = new ApexCharts(chartEl, {
        chart: {
          type: 'area',
          height: 280,
          toolbar: {
            show: false
          }
        },
        series: [{
          name: 'Decisions',
          data: @json($chartSeries)
        }],
        xaxis: {
          categories: @json($chartLabels)
        },
        stroke: {
          curve: 'smooth'
        },
        dataLabels: {
          enabled: false
        },
        colors: ['#03c3ec']
      });

      chart.render();
    })();
  </script>
@endsection
