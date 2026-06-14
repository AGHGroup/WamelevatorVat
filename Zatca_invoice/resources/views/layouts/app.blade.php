@php
  $locale = app()->getLocale();
  $isRtl  = ($locale === 'ar');
  $dir    = $isRtl ? 'rtl' : 'ltr';
  $other  = $isRtl ? 'en' : 'ar';
  $otherLabel = $isRtl ? 'EN' : 'ع';
@endphp
<!doctype html>
<html lang="{{ $locale }}" dir="{{ $dir }}"
      class="layout-menu-fixed layout-compact"
      data-assets-path="{{ asset('sneat') }}/"
      data-template="vertical-menu-template-free">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', __('app.dashboard')) {{ __('app.page_title_suffix') }}</title>

  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  @if ($isRtl)
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>:root { --bs-body-font-family: 'Cairo', sans-serif; } body { font-family: 'Cairo', sans-serif; }</style>
  @else
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>:root { --bs-body-font-family: 'IBM Plex Sans', sans-serif; } body { font-family: 'IBM Plex Sans', sans-serif; }</style>
  @endif

  <link rel="stylesheet" href="{{ asset('sneat/vendor/fonts/iconify-icons.css') }}" />
  <link rel="stylesheet" href="{{ asset('sneat/vendor/css/core.css') }}" />
  <link rel="stylesheet" href="{{ asset('sneat/css/demo.css') }}" />
  @if ($isRtl)
    <link rel="stylesheet" href="{{ asset('sneat/css/rtl.css') }}" />
  @endif
  <link rel="stylesheet" href="{{ asset('sneat/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
  <link rel="stylesheet" href="{{ asset('sneat/css/zatca-theme.css') }}" />

  @stack('styles')

  <script src="{{ asset('sneat/vendor/js/helpers.js') }}"></script>
  <script src="{{ asset('sneat/js/config.js') }}"></script>
</head>

<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

      <!-- ===== Sidebar ===== -->
      <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
        <div class="app-brand demo">
          <a href="{{ route('dashboard') }}" class="app-brand-link d-flex align-items-center gap-2 text-decoration-none">
            <span class="zatca-brand-icon">
              <i class="bx bx-receipt"></i>
            </span>
            <span class="app-brand-text demo fw-bold" style="font-size:1.0625rem;color:#F8FAFC;letter-spacing:.02em;">
              ZATCA
            </span>
          </a>

          <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="bx bx-chevron-left d-block d-xl-none align-middle" style="color:#94A3B8;"></i>
          </a>
        </div>

        <div class="menu-divider mt-0"></div>
        <div class="menu-inner-shadow"></div>

        <ul class="menu-inner py-1">

          <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
              <i class="menu-icon tf-icons bx bx-home-smile"></i>
              <div class="text-truncate">{{ __('nav.dashboard') }}</div>
            </a>
          </li>

          <li class="menu-header small text-uppercase">
            <span class="menu-header-text">{{ __('nav.section_zatca') }}</span>
          </li>

          <li class="menu-item {{ request()->routeIs('invoices.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
              <i class="menu-icon tf-icons bx bx-receipt"></i>
              <div class="text-truncate">{{ __('nav.invoices') }}</div>
            </a>
            <ul class="menu-sub">
              <li class="menu-item {{ request()->routeIs('invoices.index') ? 'active' : '' }}">
                <a href="{{ route('invoices.index') }}" class="menu-link">
                  <div class="text-truncate">{{ __('nav.all_invoices') }}</div>
                </a>
              </li>
              <li class="menu-item {{ request()->routeIs('invoices.create') ? 'active' : '' }}">
                <a href="{{ route('invoices.create') }}" class="menu-link">
                  <div class="text-truncate">{{ __('nav.new_invoice') }}</div>
                </a>
              </li>
            </ul>
          </li>

          <li class="menu-item {{ request()->routeIs('vat-categories.*') ? 'active' : '' }}">
            <a href="{{ route('vat-categories.index') }}" class="menu-link">
              <i class="menu-icon tf-icons bx bx-category"></i>
              <div class="text-truncate">{{ __('nav.vat_categories') }}</div>
            </a>
          </li>

          <li class="menu-item {{ request()->routeIs('vat-types.*') ? 'active' : '' }}">
            <a href="{{ route('vat-types.index') }}" class="menu-link">
              <i class="menu-icon tf-icons bx bx-tag"></i>
              <div class="text-truncate">{{ __('nav.vat_types') }}</div>
            </a>
          </li>

          <li class="menu-header small text-uppercase">
            <span class="menu-header-text">{{ __('nav.section_database') }}</span>
          </li>

          <li class="menu-item {{ request()->routeIs('oracle.*') ? 'active' : '' }}">
            <a href="{{ route('oracle.tables') }}" class="menu-link">
              <i class="menu-icon tf-icons bx bx-table"></i>
              <div class="text-truncate">{{ __('nav.oracle_tables') }}</div>
            </a>
          </li>

        </ul>
      </aside>
      <!-- / Sidebar -->

      <!-- ===== Layout Page ===== -->
      <div class="layout-page">

        <!-- Navbar -->
        <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
          <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
              <i class="icon-base bx bx-menu icon-md"></i>
            </a>
          </div>

          <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
            <!-- Search -->
            <div class="navbar-nav align-items-center me-auto">
              <div class="nav-item d-flex align-items-center">
                <i class="icon-base bx bx-search icon-md"></i>
                <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2"
                       placeholder="{{ __('app.search') }}" aria-label="Search" />
              </div>
            </div>

            <ul class="navbar-nav flex-row align-items-center ms-md-auto">

              <!-- Language toggle -->
              <li class="nav-item me-2 me-xl-0">
                <a href="{{ route('locale.switch', $other) }}"
                   class="nav-link zatca-lang-btn"
                   title="{{ $isRtl ? 'Switch to English' : 'التبديل إلى العربية' }}">
                  {{ $otherLabel }}
                </a>
              </li>

              <!-- User dropdown -->
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                  <div class="avatar avatar-online">
                    <span class="avatar-initial rounded-circle bg-label-primary">
                      {{ Auth::check() ? strtoupper(substr(Auth::user()->name, 0, 1)) : 'A' }}
                    </span>
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="#">
                      <div class="d-flex">
                        <div class="flex-grow-1">
                          <h6 class="mb-0">{{ Auth::check() ? Auth::user()->name : __('app.admin') }}</h6>
                          <small class="text-body-secondary">{{ Auth::check() ? Auth::user()->email : 'admin' }}</small>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li><div class="dropdown-divider my-1"></div></li>
                  @auth
                  <li>
                    <a class="dropdown-item" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                      <i class="icon-base bx bx-power-off icon-md me-3 text-danger"></i>
                      <span>{{ __('app.logout') }}</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                  </li>
                  @endauth
                </ul>
              </li>

            </ul>
          </div>
        </nav>
        <!-- / Navbar -->

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">

            <!-- Page header -->
            @hasSection('page-title')
            <div class="zatca-page-header d-flex align-items-start justify-content-between">
              <div>
                <h4>@yield('page-title')</h4>
                @hasSection('breadcrumb')
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb mb-0" style="font-size:.8125rem;">
                    <li class="breadcrumb-item">
                      <a href="{{ route('dashboard') }}">{{ __('app.home') }}</a>
                    </li>
                    @yield('breadcrumb')
                  </ol>
                </nav>
                @endif
              </div>
              @hasSection('page-actions')
              <div class="mt-1">@yield('page-actions')</div>
              @endif
            </div>
            @endif

            <!-- Flash messages -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible mb-4" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible mb-4" role="alert">
              {{ session('error') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @yield('content')

          </div>

          <!-- Footer -->
          <footer class="content-footer footer bg-footer-theme">
            <div class="container-xxl">
              <div class="footer-container d-flex align-items-center justify-content-between py-3 flex-md-row flex-column">
                <div class="text-body">
                  &copy; {{ date('Y') }}
                  <a href="#" class="footer-link fw-medium">{{ __('app.footer_copy') }}</a>
                </div>
                <div class="d-flex gap-2 mt-2 mt-md-0">
                  @foreach (config('app.supported_locales', ['ar','en']) as $loc)
                    <a href="{{ route('locale.switch', $loc) }}"
                       class="badge {{ $locale === $loc ? 'bg-primary' : 'bg-label-secondary text-body' }} text-decoration-none">
                      {{ strtoupper($loc) }}
                    </a>
                  @endforeach
                </div>
              </div>
            </div>
          </footer>
        </div>
        <!-- / Content wrapper -->

      </div>
      <!-- / Layout page -->

    </div>

    <div class="layout-overlay layout-menu-toggle"></div>
  </div>

  <!-- Core JS -->
  <script src="{{ asset('sneat/vendor/libs/jquery/jquery.js') }}"></script>
  <script src="{{ asset('sneat/vendor/libs/popper/popper.js') }}"></script>
  <script src="{{ asset('sneat/vendor/js/bootstrap.js') }}"></script>
  <script src="{{ asset('sneat/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
  <script src="{{ asset('sneat/vendor/js/menu.js') }}"></script>
  <script src="{{ asset('sneat/js/main.js') }}"></script>

  @stack('scripts')
</body>
</html>
