@extends('layouts.guest')

@section('title', __('auth.login'))

@section('content')
<div class="card px-sm-6 px-0">
  <div class="card-body">

    <!-- Logo -->
    <div class="app-brand justify-content-center mb-6">
      <a href="#" class="app-brand-link gap-2">
        <span class="app-brand-logo demo">
          <i class="bx bx-receipt" style="font-size:2rem;color:var(--bs-primary);"></i>
        </span>
        <span class="app-brand-text demo fw-bold fs-4">ZATCA</span>
      </a>
    </div>

    <h4 class="mb-1">{{ __('auth.welcome') }} 👋</h4>
    <p class="mb-6 text-body-secondary">{{ __('auth.sign_in_prompt') }}</p>

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible mb-4" role="alert">
      {{ $errors->first() }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('login') }}" method="POST">
      @csrf

      <div class="mb-4">
        <label class="form-label" for="user_id">{{ __('auth.user_id') }}</label>
        <input
          type="text"
          id="user_id"
          name="user_id"
          class="form-control @error('user_id') is-invalid @enderror"
          value="{{ old('user_id') }}"
          placeholder="{{ __('auth.user_id_placeholder') }}"
          autocomplete="username"
          autofocus
          required
        />
      </div>

      <div class="mb-4">
        <label class="form-label" for="password">{{ __('auth.password') }}</label>
        <div class="input-group input-group-merge">
          <input
            type="password"
            id="password"
            name="password"
            class="form-control"
            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
            autocomplete="current-password"
            required
          />
          <span class="input-group-text cursor-pointer" id="toggle-password">
            <i class="bx bx-hide"></i>
          </span>
        </div>
      </div>

      <div class="mb-4">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="remember" name="remember" />
          <label class="form-check-label" for="remember">{{ __('auth.remember_me') }}</label>
        </div>
      </div>

      <button type="submit" class="btn btn-primary d-grid w-100">
        {{ __('auth.sign_in') }}
      </button>
    </form>

  </div>
</div>
@endsection

@push('scripts')
<script>
  document.getElementById('toggle-password').addEventListener('click', function () {
    const pwd  = document.getElementById('password');
    const icon = this.querySelector('i');
    if (pwd.type === 'password') {
      pwd.type = 'text';
      icon.classList.replace('bx-hide', 'bx-show');
    } else {
      pwd.type = 'password';
      icon.classList.replace('bx-show', 'bx-hide');
    }
  });
</script>
@endpush
