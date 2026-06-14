@php $isRtl = app()->getLocale() === 'ar'; @endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
      class="layout-wide customizer-hide"
      data-assets-path="{{ asset('sneat') }}/"
      data-template="vertical-menu-template-free">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', 'ZATCA') — نظام الفواتير</title>

  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  @if ($isRtl)
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>body { font-family: 'Cairo', sans-serif; }</style>
  @else
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>body { font-family: 'IBM Plex Sans', sans-serif; }</style>
  @endif

  <link rel="stylesheet" href="{{ asset('sneat/vendor/fonts/iconify-icons.css') }}" />
  <link rel="stylesheet" href="{{ asset('sneat/vendor/css/core.css') }}" />
  <link rel="stylesheet" href="{{ asset('sneat/css/demo.css') }}" />
  @if ($isRtl)
  <link rel="stylesheet" href="{{ asset('sneat/css/rtl.css') }}" />
  @endif
  <link rel="stylesheet" href="{{ asset('sneat/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
  <link rel="stylesheet" href="{{ asset('sneat/vendor/css/pages/page-auth.css') }}" />
  <link rel="stylesheet" href="{{ asset('sneat/css/zatca-theme.css') }}" />

  @stack('styles')

  <script src="{{ asset('sneat/vendor/js/helpers.js') }}"></script>
  <script src="{{ asset('sneat/js/config.js') }}"></script>
  <script src="{{ asset('sneat/js/rtl-switcher.js') }}"></script>
</head>

<body>
  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner">
        @yield('content')
      </div>
    </div>
  </div>

  <script src="{{ asset('sneat/vendor/libs/jquery/jquery.js') }}"></script>
  <script src="{{ asset('sneat/vendor/libs/popper/popper.js') }}"></script>
  <script src="{{ asset('sneat/vendor/js/bootstrap.js') }}"></script>
  <script src="{{ asset('sneat/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
  <script src="{{ asset('sneat/vendor/js/menu.js') }}"></script>
  <script src="{{ asset('sneat/js/main.js') }}"></script>

  @stack('scripts')
</body>
</html>
