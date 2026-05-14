<!doctype html>
<html lang="{{ app()->getLocale() }}" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
  data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
            <li class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
              <a href="{{ route('home') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>{{ __('app.home') }}</div>
              </a>
            </li>

            @if (auth()->user()->role->value === 'resident')
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
            @elseif(auth()->user()->role->value === 'supervisor')
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
            @elseif(auth()->user()->role->value === 'director')
              <li class="menu-item {{ request()->routeIs('director.dashboard') ? 'active' : '' }}">
                <a href="{{ route('director.dashboard') }}" class="menu-link">
                  <i class="menu-icon tf-icons bx bx-line-chart"></i>
                  <div>{{ __('app.director_dashboard') }}</div>
                </a>
              </li>
              <li class="menu-item {{ request()->routeIs('director.residents-progress') ? 'active' : '' }}">
                <a href="{{ route('director.residents-progress') }}" class="menu-link">
                  <i class="menu-icon tf-icons bx bx-user-pin"></i>
                  <div>{{ __('app.residents_progress') }}</div>
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
                ({{ ucfirst(auth()->user()->role->value) }})</span>

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

</html>
