<!doctype html>
<html lang="{{ app()->getLocale() }}" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
  data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Surgical Training Management System (STMS)</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
  @if (auth()->check())
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="{{ route('home') }}" class="app-brand-link">
              <span class="app-brand-logo demo me-2">
                <i class="bx bx-plus-medical text-primary"></i>
              </span>
              <span class="app-brand-text demo menu-text fw-semibold">STMS</span>
            </a>
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
              <i class="bx bx-chevron-left bx-sm align-middle"></i>
            </a>
          </div>

          <div class="menu-inner-shadow"></div>

          <ul class="menu-inner py-1">
            <li class="menu-header small text-uppercase">
              <span class="menu-header-text">{{ __('app.nav_header') }}</span>
            </li>

            @if (auth()->user()->role === 'resident')
              <li class="menu-item {{ request()->routeIs('resident.dashboard') ? 'active' : '' }}">
                <a href="{{ route('resident.dashboard') }}" class="menu-link">
                  <i class="menu-icon tf-icons bx bx-pie-chart-alt-2"></i>
                  <div>{{ __('app.dashboard') }}</div>
                </a>
              </li>
              <li class="menu-item {{ request()->routeIs('resident.case-logs.index') ? 'active' : '' }}">
                <a href="{{ route('resident.case-logs.index') }}" class="menu-link">
                  <i class="menu-icon tf-icons bx bx-clipboard"></i>
                  <div>{{ __('app.my_case_logs') }}</div>
                </a>
              </li>
              <li class="menu-item {{ request()->routeIs('resident.case-logs.create') ? 'active' : '' }}">
                <a href="{{ route('resident.case-logs.create') }}" class="menu-link">
                  <i class="menu-icon tf-icons bx bx-plus-circle"></i>
                  <div>{{ __('app.log_new_case') }}</div>
                </a>
              </li>
              <li class="menu-item {{ request()->routeIs('resident.peers-progress') ? 'active' : '' }}">
                <a href="{{ route('resident.peers-progress') }}" class="menu-link">
                  <i class="menu-icon tf-icons bx bx-group"></i>
                  <div>{{ __('app.peers_progress') }}</div>
                </a>
              </li>
            @elseif(auth()->user()->role === 'supervisor')
              <li class="menu-item {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
                <a href="{{ route('supervisor.dashboard') }}" class="menu-link">
                  <i class="menu-icon tf-icons bx bx-pie-chart-alt-2"></i>
                  <div>{{ __('app.dashboard') }}</div>
                </a>
              </li>
              <li class="menu-item {{ request()->routeIs('supervisor.approvals.index') ? 'active' : '' }}">
                <a href="{{ route('supervisor.approvals.index') }}" class="menu-link">
                  <i class="menu-icon tf-icons bx bx-check-shield"></i>
                  <div>{{ __('app.pending_approvals') }}</div>
                </a>
              </li>
            @elseif(auth()->user()->role === 'director')
              <li class="menu-item {{ request()->routeIs('director.dashboard') ? 'active' : '' }}">
                <a href="{{ route('director.dashboard') }}" class="menu-link">
                  <i class="menu-icon tf-icons bx bx-line-chart"></i>
                  <div>{{ __('app.director_dashboard') }}</div>
                </a>
              </li>
              <li class="menu-item {{ request()->routeIs('director.approvals.index') ? 'active' : '' }}">
                <a href="{{ route('director.approvals.index') }}" class="menu-link">
                  <i class="menu-icon tf-icons bx bx-check-shield"></i>
                  <div>{{ __('app.pending_approvals') }}</div>
                </a>
              </li>
              <li class="menu-item {{ request()->routeIs('director.residents-progress') ? 'active' : '' }}">
                <a href="{{ route('director.residents-progress') }}" class="menu-link">
                  <i class="menu-icon tf-icons bx bx-user-pin"></i>
                  <div>{{ __('app.residents_progress') }}</div>
                </a>
              </li>
            @elseif(auth()->user()->role === 'administrator')
              <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}" class="menu-link">
                  <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
                  <div>Admin Dashboard</div>
                </a>
              </li>
              <li class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <a href="{{ route('admin.users.index') }}" class="menu-link">
                  <i class="menu-icon tf-icons bx bx-user"></i>
                  <div>Manage Users</div>
                </a>
              </li>
              <li class="menu-item {{ request()->routeIs('admin.procedures.*') ? 'active' : '' }}">
                <a href="{{ route('admin.procedures.index') }}" class="menu-link">
                  <i class="menu-icon tf-icons bx bx-list-check"></i>
                  <div>Manage Procedures</div>
                </a>
              </li>
            @endif
          </ul>
        </aside>

        <div class="layout-page">
          <nav id="layout-navbar"
            class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme">
            <div class="navbar-nav-right d-flex align-items-center ms-auto" id="navbar-collapse">
              <a class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none"
                href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
              </a>

              <span class="text-muted small me-3">{{ auth()->user()->name }}
                ({{ ucfirst(auth()->user()->role) }})</span>

              {{-- Language switcher --}}
              <div class="me-3 d-flex align-items-center gap-1">
                <a href="{{ route('locale.switch', 'en') }}"
                  class="btn btn-sm {{ app()->getLocale() === 'en' ? 'btn-primary' : 'btn-outline-secondary' }}">EN</a>
                <a href="{{ route('locale.switch', 'de') }}"
                  class="btn btn-sm {{ app()->getLocale() === 'de' ? 'btn-primary' : 'btn-outline-secondary' }}">DE</a>
              </div>

              <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">{{ __('app.logout') }}</button>
              </form>

              <div class="dropdown ms-3">
                <button class="btn btn-sm btn-outline-secondary position-relative dropdown-toggle" type="button"
                  data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bx bx-bell"></i>
                  <span id="notification-count"
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-0" style="width: min(420px, 88vw);">
                  <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <strong>Notifications</strong>
                    <button id="mark-all-read" type="button" class="btn btn-sm btn-outline-primary">Mark all
                      read</button>
                  </div>
                  <div id="notification-list" class="list-group list-group-flush"
                    style="max-height: 360px; overflow:auto;">
                    <div class="px-3 py-3 text-muted small">Loading notifications...</div>
                  </div>
                </div>
              </div>
            </div>
          </nav>

          <div class="content-wrapper">
            <div class="container-xxl flex-grow-1 container-p-y">
              @if (session('status'))
                <div class="alert alert-success" role="alert">{{ session('status') }}</div>
              @endif
              @yield('content')
            </div>
          </div>
        </div>
      </div>
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
  @else
    @yield('content')
  @endif
</body>

@auth
  <script>
    (() => {
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const list = document.getElementById('notification-list');
      const count = document.getElementById('notification-count');
      const markAllBtn = document.getElementById('mark-all-read');

      if (!list || !count) {
        return;
      }

      const render = (payload) => {
        const unread = Number(payload.unread_count || 0);
        count.textContent = unread;
        count.classList.toggle('d-none', unread === 0);

        const items = Array.isArray(payload.items) ? payload.items : [];

        if (items.length === 0) {
          list.innerHTML = '<div class="px-3 py-3 text-muted small">No notifications yet.</div>';

          return;
        }

        list.innerHTML = items
          .map((item) => {
            const isUnread = !item.read_at;

            return `
              <div class="list-group-item ${isUnread ? 'bg-light' : ''}">
                <div class="d-flex justify-content-between">
                  <strong class="small">${item.title}</strong>
                  <span class="small text-muted">${(item.created_at || '').slice(0, 16).replace('T', ' ')}</span>
                </div>
                <div class="small text-muted mt-1">${item.message}</div>
              </div>
            `;
          })
          .join('');
      };

      const loadNotifications = () => {
        fetch('{{ route('notifications.poll') }}', {
            method: 'GET',
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
          })
          .then((response) => response.json())
          .then((payload) => {
            render(payload || {});
          })
          .catch(() => {
            list.innerHTML = '<div class="px-3 py-3 text-danger small">Failed to load notifications.</div>';
          });
      };

      markAllBtn?.addEventListener('click', () => {
        fetch('{{ route('notifications.read-all') }}', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token,
          },
          body: JSON.stringify({}),
          credentials: 'same-origin',
        }).then(() => {
          loadNotifications();
        });
      });

      loadNotifications();
      setInterval(loadNotifications, 5000);
    })
    ();
  </script>
@endauth

</html>
