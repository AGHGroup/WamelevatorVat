@extends('layouts.app')

@section('title', __('lce.page_title'))
@section('page-title', __('lce.page_title'))

@section('breadcrumb')
  <li class="breadcrumb-item active">{{ __('lce.breadcrumb') }}</li>
@endsection

@section('content')

  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">{{ __('lce.card_header') }}</h5>
      <small class="text-body-secondary">
        {{ __('lce.table_summary', [
            'total'   => $tables->total(),
            'current' => $tables->currentPage(),
            'last'    => $tables->lastPage(),
        ]) }}
      </small>
    </div>
    <div class="card-body">
      <div class="row g-3">
        @foreach ($tables as $t)
          <div class="col-6 col-sm-4 col-md-3 col-xl-2">
            <a href="{{ route('oracle.table.show', $t->TABLE_NAME) }}"
               class="sneat-table-card card text-center text-decoration-none h-100
                      d-flex align-items-center justify-content-center p-3
                      border rounded-2 text-body-secondary fw-medium small">
              <i class="bx bx-table d-block mb-1 text-primary"></i>
              {{ $t->TABLE_NAME }}
            </a>
          </div>
        @endforeach
      </div>
    </div>
    <div class="card-footer">
      {{ $tables->links() }}
    </div>
  </div>

@endsection

@push('styles')
<style>
  .sneat-table-card {
    transition: border-color .15s, box-shadow .15s, transform .15s;
    font-size: .8125rem;
    border-color: var(--zatca-border) !important;
    color: var(--zatca-muted) !important;
  }
  .sneat-table-card:hover {
    border-color: var(--zatca-blue) !important;
    color: var(--zatca-blue) !important;
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgb(3 105 161 / .12);
    background: #EFF8FF;
  }
  .sneat-table-card i { font-size: 1.375rem; margin-bottom: .25rem; }
</style>
@endpush
