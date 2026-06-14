@extends('layouts.app')

@section('title', __('app.dashboard'))
@section('page-title', __('app.dashboard'))

@section('content')

{{-- ── KPI stat row ───────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

  <div class="col-6 col-xl-3">
    <div class="zatca-stat-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label">{{ __('app.total_invoices') }}</span>
        <span class="zatca-stat-icon icon-blue">
          <i class="bx bx-receipt"></i>
        </span>
      </div>
      <div class="stat-value">—</div>
      <div class="stat-desc">{{ __('app.invoices_count') }}</div>
    </div>
  </div>

  <div class="col-6 col-xl-3">
    <div class="zatca-stat-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label">{{ __('app.vat_categories') }}</span>
        <span class="zatca-stat-icon icon-green">
          <i class="bx bx-category"></i>
        </span>
      </div>
      <div class="stat-value">—</div>
      <div class="stat-desc">{{ __('app.categories_count') }}</div>
    </div>
  </div>

  <div class="col-6 col-xl-3">
    <div class="zatca-stat-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label">{{ __('app.vat_types') }}</span>
        <span class="zatca-stat-icon icon-amber">
          <i class="bx bx-tag"></i>
        </span>
      </div>
      <div class="stat-value">—</div>
      <div class="stat-desc">{{ __('app.types_count') }}</div>
    </div>
  </div>

  <div class="col-6 col-xl-3">
    <div class="zatca-stat-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label">{{ __('app.oracle_tables') }}</span>
        <span class="zatca-stat-icon icon-cyan">
          <i class="bx bx-data"></i>
        </span>
      </div>
      <div class="stat-value">—</div>
      <div class="stat-desc">{{ __('app.tables_count') }}</div>
    </div>
  </div>

</div>

{{-- ── Invoice status strip ───────────────────────────────────── --}}
<div class="card mb-4">
  <div class="card-body py-3">
    <div class="d-flex flex-wrap align-items-center gap-3">
      <span class="fw-semibold text-muted small me-2">
        @if(app()->getLocale() === 'ar') حالة الفواتير @else Invoice Status @endif
      </span>
      <span class="status-badge status-draft">
        @if(app()->getLocale() === 'ar') مسودة @else Draft @endif
        &nbsp;<strong>0</strong>
      </span>
      <span class="status-badge status-sent">
        @if(app()->getLocale() === 'ar') مُرسلة @else Sent @endif
        &nbsp;<strong>0</strong>
      </span>
      <span class="status-badge status-paid">
        @if(app()->getLocale() === 'ar') مدفوعة @else Paid @endif
        &nbsp;<strong>0</strong>
      </span>
      <span class="status-badge status-overdue">
        @if(app()->getLocale() === 'ar') متأخرة @else Overdue @endif
        &nbsp;<strong>0</strong>
      </span>
    </div>
  </div>
</div>

{{-- ── Quick actions + About ──────────────────────────────────── --}}
<div class="row g-4">

  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="mb-0 fw-semibold" style="color:var(--zatca-navy);">
          <i class="bx bx-bolt-circle me-2 text-primary"></i>
          {{ __('app.quick_actions') }}
        </h6>
      </div>
      <div class="card-body d-flex flex-column gap-2 py-3">

        <a href="{{ route('oracle.tables') }}" class="zatca-action-item">
          <span class="action-icon" style="background:#E0F2FE;color:#0369A1;">
            <i class="bx bx-table"></i>
          </span>
          <span>{{ __('nav.oracle_tables') }}</span>
          <i class="bx bx-chevron-right ms-auto" style="color:var(--zatca-muted);"></i>
        </a>

        <a href="{{ route('invoices.create') }}" class="zatca-action-item">
          <span class="action-icon" style="background:#DCFCE7;color:#16A34A;">
            <i class="bx bx-plus-circle"></i>
          </span>
          <span>{{ __('nav.new_invoice') }}</span>
          <i class="bx bx-chevron-right ms-auto" style="color:var(--zatca-muted);"></i>
        </a>

        <a href="{{ route('invoices.index') }}" class="zatca-action-item">
          <span class="action-icon" style="background:#F1F5F9;color:#475569;">
            <i class="bx bx-list-ul"></i>
          </span>
          <span>{{ __('nav.all_invoices') }}</span>
          <i class="bx bx-chevron-right ms-auto" style="color:var(--zatca-muted);"></i>
        </a>

      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="mb-0 fw-semibold" style="color:var(--zatca-navy);">
          <i class="bx bx-info-circle me-2 text-primary"></i>
          {{ __('app.about_system') }}
        </h6>
      </div>
      <div class="card-body py-3">
        <div class="d-flex align-items-center gap-3 mb-3">
          <span class="zatca-brand-icon" style="width:3rem;height:3rem;font-size:1.375rem;">
            <i class="bx bx-receipt"></i>
          </span>
          <div>
            <div class="fw-bold" style="color:var(--zatca-navy);font-size:1rem;">
              {{ __('app.app_name') }}
            </div>
            <div class="small" style="color:var(--zatca-muted);">
              @if(app()->getLocale() === 'ar') منصة الفواتير الإلكترونية — هيئة الزكاة والضريبة
              @else ZATCA E-Invoice Platform
              @endif
            </div>
          </div>
        </div>

        <p class="small mb-3" style="color:var(--zatca-muted);line-height:1.65;">
          {{ __('app.system_desc') }}
        </p>

        <div class="d-flex flex-wrap gap-2">
          <span class="badge" style="background:#E0F2FE;color:#0369A1;font-weight:500;">
            <i class="bx bx-check-shield me-1"></i>
            @if(app()->getLocale() === 'ar') ZATCA معتمد @else ZATCA Compliant @endif
          </span>
          <span class="badge" style="background:#DCFCE7;color:#16A34A;font-weight:500;">
            <i class="bx bx-globe me-1"></i>
            @if(app()->getLocale() === 'ar') عربي / إنجليزي @else AR / EN @endif
          </span>
          <span class="badge" style="background:#FEF3C7;color:#D97706;font-weight:500;">
            <i class="bx bx-data me-1"></i>
            Oracle DB
          </span>
        </div>
      </div>
    </div>
  </div>

</div>

@endsection
