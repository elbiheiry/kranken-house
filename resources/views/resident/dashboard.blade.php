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
        <div class="card-body">
          <div id="residentProgressChart" style="min-height: 320px;"></div>

          <div class="row g-3 mt-1">
            <div class="col-sm-4">
              <div class="border rounded-3 p-3 h-100">
                <span class="text-muted d-block mb-1">{{ __('app.col_completed') }}</span>
                <h5 class="mb-0">{{ $totalCompletedCases }}</h5>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="border rounded-3 p-3 h-100">
                <span class="text-muted d-block mb-1">{{ __('app.col_expected') }}</span>
                <h5 class="mb-0">{{ $totalExpectedCases }}</h5>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="border rounded-3 p-3 h-100">
                <span class="text-muted d-block mb-1">{{ __('app.col_progress') }}</span>
                <h5 class="mb-0">{{ $overallProgressPercent }}%</h5>
              </div>
            </div>
          </div>

          @if (empty($progressChartLabels))
            <p class="text-center text-muted mt-3 mb-0">{{ __('app.no_progress_data') }}</p>
          @else
            <p class="text-muted mt-3 mb-0">{{ __('app.procedures') }}: {{ $totalCompletedProcedures }}</p>
            <div class="border rounded-3 p-3 mt-3" id="procedureProgressDetails">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0" id="procedureDetailName">-</h6>
                <span class="badge bg-label-primary">{{ __('app.col_year') }} {{ $residentTrainingYear }}</span>
              </div>
              <div class="row g-3">
                <div class="col-sm-4">
                  <div class="small text-muted">{{ __('app.max_required_by_year') }}</div>
                  <div class="fw-semibold" id="procedureDetailExpected">0</div>
                </div>
                <div class="col-sm-4">
                  <div class="small text-muted">{{ __('app.performed_by_resident') }}</div>
                  <div class="fw-semibold" id="procedureDetailCompleted">0</div>
                </div>
                <div class="col-sm-4">
                  <div class="small text-muted">{{ __('app.col_progress') }}</div>
                  <div class="fw-semibold" id="procedureDetailPercent">0%</div>
                </div>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script>
    (function() {
      if (typeof ApexCharts === 'undefined') {
        return;
      }

      var chartEl = document.querySelector('#residentMonthlyChart');
      if (chartEl) {
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
      }

      var progressEl = document.querySelector('#residentProgressChart');
      if (progressEl) {
        var progressDetails = @json($progressChartDetails);
        var detailNameEl = document.querySelector('#procedureDetailName');
        var detailExpectedEl = document.querySelector('#procedureDetailExpected');
        var detailCompletedEl = document.querySelector('#procedureDetailCompleted');
        var detailPercentEl = document.querySelector('#procedureDetailPercent');

        var renderProcedureDetails = function(dataPointIndex) {
          if (!Array.isArray(progressDetails) || progressDetails.length === 0) {
            return;
          }

          var row = progressDetails[dataPointIndex] || progressDetails[0];
          if (!row) {
            return;
          }

          if (detailNameEl) {
            detailNameEl.textContent = row.procedure || '-';
          }

          if (detailExpectedEl) {
            detailExpectedEl.textContent = String(row.expected ?? 0);
          }

          if (detailCompletedEl) {
            detailCompletedEl.textContent = String(row.completed ?? 0);
          }

          if (detailPercentEl) {
            detailPercentEl.textContent = String(row.progress_percent ?? 0) + '%';
          }
        };

        var progressChart = new ApexCharts(progressEl, {
          chart: {
            type: 'pie',
            height: 320,
            events: {
              dataPointSelection: function(event, chartContext, config) {
                renderProcedureDetails(config.dataPointIndex);
              },
              legendClick: function(chartContext, seriesIndex) {
                renderProcedureDetails(seriesIndex);
              }
            }
          },
          labels: @json($progressChartLabels),
          series: @json($progressChartSeries),
          colors: @json($progressChartColors),
          legend: {
            position: 'bottom'
          },
          stroke: {
            width: 0
          },
          noData: {
            text: @json(__('app.no_progress_data'))
          },
          dataLabels: {
            enabled: false
          }
        });

        progressChart.render();
        renderProcedureDetails(0);
      }
    })();
  </script>
@endsection
