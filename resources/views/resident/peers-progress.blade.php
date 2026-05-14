@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header">
      <h5 class="card-title m-0 me-2">{{ __('app.peers_progress_title') }}</h5>
    </div>
    <div class="card-body">
      <p class="text-muted mb-3">{{ __('app.peers_progress_hint') }}</p>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label for="peerYearFilter" class="form-label">{{ __('app.filter_training_year') }}</label>
          <select id="peerYearFilter" class="form-select">
            <option value="">{{ __('app.all_years') }}</option>
            @foreach (collect($peerRows)->pluck('training_year')->unique()->sort()->values() as $year)
              <option value="{{ $year }}">R{{ $year }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label for="peerStatusFilter" class="form-label">{{ __('app.filter_status') }}</label>
          <select id="peerStatusFilter" class="form-select">
            <option value="">{{ __('app.all_statuses') }}</option>
            <option value="green">{{ __('app.status_on_track') }}</option>
            <option value="yellow">{{ __('app.status_at_risk') }}</option>
            <option value="red">{{ __('app.status_behind') }}</option>
          </select>
        </div>
      </div>

      <div class="table-responsive text-nowrap">
        <table class="table" id="peersProgressTable">
          <thead class="table-light">
            <tr>
              <th>{{ __('app.col_resident') }}</th>
              <th>{{ __('app.col_year') }}</th>
              <th>{{ __('app.col_status') }}</th>
              <th>{{ __('app.col_action') }}</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($peerRows as $row)
              <tr data-year="{{ $row['training_year'] }}" data-status="{{ $row['status'] }}">
                <td>{{ $row['resident_name'] }}</td>
                <td>R{{ $row['training_year'] }}</td>
                <td>
                  @if ($row['status'] === 'green')
                    <span class="badge bg-label-success">{{ $row['status_label'] }}</span>
                  @elseif($row['status'] === 'yellow')
                    <span class="badge bg-label-warning">{{ $row['status_label'] }}</span>
                  @else
                    <span class="badge bg-label-danger">{{ $row['status_label'] }}</span>
                  @endif
                </td>
                <td>
                  <button type="button" class="btn btn-sm btn-outline-primary js-open-progress-modal"
                    data-bs-toggle="modal" data-bs-target="#peerProgressModal"
                    data-resident-name="{{ $row['resident_name'] }}" data-training-year="{{ $row['training_year'] }}"
                    data-completed-total="{{ $row['completed_total'] }}"
                    data-expected-total="{{ $row['expected_total'] }}"
                    data-progress-percent="{{ $row['progress_percent'] }}"
                    data-status-label="{{ $row['status_label'] }}" data-procedure-details='@json($row['procedure_details'])'>
                    {{ __('app.view_total_progress') }}
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted">{{ __('app.no_peer_progress') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <p class="text-muted mt-3 mb-0 d-none" id="peerFilterNoResults">{{ __('app.no_filter_results') }}</p>
    </div>
  </div>

  <div class="modal fade" id="peerProgressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('app.total_progress') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2"><strong>{{ __('app.col_resident') }}:</strong> <span id="peerModalResidentName">-</span>
          </div>
          <div class="mb-2"><strong>{{ __('app.col_year') }}:</strong> <span id="peerModalResidentYear">-</span></div>
          <div class="mb-2"><strong>{{ __('app.col_completed') }}:</strong> <span id="peerModalCompleted">-</span>
          </div>
          <div class="mb-2"><strong>{{ __('app.col_expected') }}:</strong> <span id="peerModalExpected">-</span></div>
          <div class="mb-2"><strong>{{ __('app.total_progress') }}:</strong> <span id="peerModalProgress">-</span>
          </div>
          <div class="mb-3"><strong>{{ __('app.col_status') }}:</strong> <span id="peerModalStatus">-</span></div>

          <h6 class="mb-2">{{ __('app.procedure_details') }}</h6>
          <div class="table-responsive text-nowrap">
            <table class="table table-sm">
              <thead class="table-light">
                <tr>
                  <th>{{ __('app.col_procedure') }}</th>
                  <th>{{ __('app.col_completed') }}</th>
                  <th>{{ __('app.col_expected') }}</th>
                  <th>{{ __('app.col_progress') }}</th>
                  <th>{{ __('app.col_status') }}</th>
                </tr>
              </thead>
              <tbody id="peerModalProcedureDetailsBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    (function() {
      var modalEl = document.getElementById('peerProgressModal');
      var buttons = document.querySelectorAll('.js-open-progress-modal');
      var yearFilter = document.getElementById('peerYearFilter');
      var statusFilter = document.getElementById('peerStatusFilter');
      var table = document.getElementById('peersProgressTable');
      var noResults = document.getElementById('peerFilterNoResults');

      if (!yearFilter || !statusFilter || !table || !noResults) {
        return;
      }

      var rows = Array.prototype.slice.call(table.querySelectorAll('tbody tr[data-year]'));

      function applyFilters() {
        var selectedYear = yearFilter.value;
        var selectedStatus = statusFilter.value;
        var visibleCount = 0;

        rows.forEach(function(row) {
          var matchesYear = !selectedYear || row.getAttribute('data-year') === selectedYear;
          var matchesStatus = !selectedStatus || row.getAttribute('data-status') === selectedStatus;
          var visible = matchesYear && matchesStatus;

          row.classList.toggle('d-none', !visible);
          if (visible) {
            visibleCount += 1;
          }
        });

        noResults.classList.toggle('d-none', visibleCount > 0 || rows.length === 0);
      }

      yearFilter.addEventListener('change', applyFilters);
      statusFilter.addEventListener('change', applyFilters);

      buttons.forEach(function(button) {
        button.addEventListener('click', function() {
          var residentName = button.getAttribute('data-resident-name') || '-';
          var trainingYear = button.getAttribute('data-training-year') || '';
          var completedTotal = button.getAttribute('data-completed-total') || '-';
          var expectedTotal = button.getAttribute('data-expected-total') || '-';
          var progressPercent = button.getAttribute('data-progress-percent') || '-';
          var statusLabel = button.getAttribute('data-status-label') || '-';
          var procedureDetailsRaw = button.getAttribute('data-procedure-details') || '[]';
          var procedureDetails = [];

          try {
            procedureDetails = JSON.parse(procedureDetailsRaw);
          } catch (error) {
            procedureDetails = [];
          }

          document.getElementById('peerModalResidentName').textContent = residentName;
          document.getElementById('peerModalResidentYear').textContent = trainingYear ? 'R' + trainingYear :
          '-';
          document.getElementById('peerModalCompleted').textContent = completedTotal;
          document.getElementById('peerModalExpected').textContent = expectedTotal;
          document.getElementById('peerModalProgress').textContent = progressPercent + '%';
          document.getElementById('peerModalStatus').textContent = statusLabel;

          var detailsBody = document.getElementById('peerModalProcedureDetailsBody');
          if (detailsBody) {
            detailsBody.innerHTML = '';

            if (procedureDetails.length === 0) {
              var emptyRow = document.createElement('tr');
              emptyRow.innerHTML =
                '<td colspan="5" class="text-center text-muted">{{ __('app.no_progress_data') }}</td>';
              detailsBody.appendChild(emptyRow);
            } else {
              procedureDetails.forEach(function(detail) {
                var tr = document.createElement('tr');
                var badgeClass = 'bg-label-danger';

                if (detail.status === 'green') {
                  badgeClass = 'bg-label-success';
                } else if (detail.status === 'yellow') {
                  badgeClass = 'bg-label-warning';
                }

                tr.innerHTML =
                  '<td>' + (detail.procedure_name || '-') + '</td>' +
                  '<td>' + (detail.completed ?? '-') + '</td>' +
                  '<td>' + (detail.expected ?? '-') + '</td>' +
                  '<td>' + (detail.progress_percent ?? '-') + '%</td>' +
                  '<td><span class="badge ' + badgeClass + '">' + (detail.status_label || '-') +
                  '</span></td>';

                detailsBody.appendChild(tr);
              });
            }
          }

          if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(modalEl);
          }
        });
      });
    })();
  </script>
@endsection
