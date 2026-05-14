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
              <th>{{ __('app.col_completed') }}</th>
              <th>{{ __('app.col_expected') }}</th>
              <th>{{ __('app.col_progress') }}</th>
              <th>{{ __('app.col_status') }}</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($peerRows as $row)
              <tr data-year="{{ $row['training_year'] }}" data-status="{{ $row['status'] }}">
                <td>{{ $row['resident_name'] }}</td>
                <td>R{{ $row['training_year'] }}</td>
                <td>{{ $row['completed_total'] }}</td>
                <td>{{ $row['expected_total'] }}</td>
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
                <td colspan="6" class="text-center text-muted">{{ __('app.no_peer_progress') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <p class="text-muted mt-3 mb-0 d-none" id="peerFilterNoResults">{{ __('app.no_filter_results') }}</p>
    </div>
  </div>

  <script>
    (function() {
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
    })();
  </script>
@endsection
