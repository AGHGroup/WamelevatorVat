@extends('layouts.guest')

@section('title', __('auth.select_system'))

@section('content')
<div class="card px-sm-6 px-0">
  <div class="card-body">

    <!-- Logo -->
    <div class="app-brand justify-content-center mb-6">
      <a href="#" class="app-brand-link gap-2">
        <span class="app-brand-logo demo">
          <i class="bx bx-grid-alt" style="font-size:2rem;color:var(--bs-primary);"></i>
        </span>
        <span class="app-brand-text demo fw-bold fs-4">{{ __('auth.choose_system') }}</span>
      </a>
    </div>

    <h4 class="mb-1 text-center">{{ __('auth.select_system_title') }}</h4>
    <p class="mb-6 text-body-secondary text-center">{{ __('auth.select_system_subtitle') }}</p>

    <div class="row g-4">

      @if(in_array('zatca', $systems))
      <div class="col-12">
        <form action="{{ route('system.choose') }}" method="POST">
          @csrf
          <input type="hidden" name="system" value="zatca" />
          <button type="submit" class="btn btn-outline-primary w-100 p-4 d-flex align-items-center gap-3" style="border-radius:12px;">
            <span style="font-size:2.5rem; line-height:1;">
              <i class="bx bx-receipt"></i>
            </span>
            <span class="text-start">
              <strong class="d-block fs-5">{{ __('auth.system_zatca') }}</strong>
              <small class="text-body-secondary">{{ __('auth.system_zatca_desc') }}</small>
            </span>
          </button>
        </form>
      </div>
      @endif

      @if(in_array('wamelevator', $systems))
      <div class="col-12">
        <form action="{{ route('system.choose') }}" method="POST">
          @csrf
          <input type="hidden" name="system" value="wamelevator" />
          <button type="submit" class="btn btn-outline-success w-100 p-4 d-flex align-items-center gap-3" style="border-radius:12px;">
            <span style="font-size:2.5rem; line-height:1;">
              <i class="bx bx-elevator"></i>
            </span>
            <span class="text-start">
              <strong class="d-block fs-5">{{ __('auth.system_wamelevator') }}</strong>
              <small class="text-body-secondary">{{ __('auth.system_wamelevator_desc') }}</small>
            </span>
          </button>
        </form>
      </div>
      @endif

    </div>

    <div class="text-center mt-5">
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-link text-body-secondary">
          <i class="bx bx-log-out me-1"></i>{{ __('auth.logout') }}
        </button>
      </form>
    </div>

  </div>
</div>
@endsection
