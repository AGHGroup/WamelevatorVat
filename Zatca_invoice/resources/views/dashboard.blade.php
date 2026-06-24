@extends('layouts.app')

@section('title', __('app.dashboard'))
@section('page-title', __('app.dashboard'))

@section('content')

@php $ar = app()->getLocale() === 'ar'; @endphp

{{-- ── KPI stat row ───────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

  <div class="col-6 col-xl-3">
    <a href="{{ route('invoices.index') }}" class="text-decoration-none">
    <div class="zatca-stat-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label">{{ __('app.total_invoices') }}</span>
        <span class="zatca-stat-icon icon-blue"><i class="bx bx-receipt"></i></span>
      </div>
      <div class="stat-value">{{ number_format($totalInvoices) }}</div>
      <div class="stat-desc">{{ __('app.invoices_count') }}</div>
    </div>
    </a>
  </div>

  <div class="col-6 col-xl-3">
    <a href="{{ route('vat-categories.index') }}" class="text-decoration-none">
    <div class="zatca-stat-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label">{{ __('app.vat_categories') }}</span>
        <span class="zatca-stat-icon icon-green"><i class="bx bx-category"></i></span>
      </div>
      <div class="stat-value">{{ number_format($catCount) }}</div>
      <div class="stat-desc">{{ __('app.categories_count') }}</div>
    </div>
    </a>
  </div>

  <div class="col-6 col-xl-3">
    <a href="{{ route('vat-types.index') }}" class="text-decoration-none">
    <div class="zatca-stat-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label">{{ __('app.vat_types') }}</span>
        <span class="zatca-stat-icon icon-amber"><i class="bx bx-tag"></i></span>
      </div>
      <div class="stat-value">{{ number_format($typeCount) }}</div>
      <div class="stat-desc">{{ __('app.types_count') }}</div>
    </div>
    </a>
  </div>

  <div class="col-6 col-xl-3">
    <div class="zatca-stat-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label">{{ $ar?'صافي ض.ق.م':'Net VAT' }}</span>
        <span class="zatca-stat-icon {{ $totalVatNet >= 0 ? 'icon-green' : 'icon-red' }}">
          <i class="bx bx-trending-{{ $totalVatNet >= 0 ? 'up' : 'down' }}"></i>
        </span>
      </div>
      <div class="stat-value" style="color:{{ $totalVatNet>=0?'#16A34A':'#DC2626' }}">
        {{ number_format(abs($totalVatNet), 2) }}
      </div>
      <div class="stat-desc">{{ $ar?'إجمالي الضريبة الصافية':'total net VAT amount' }}</div>
    </div>
  </div>

</div>

{{-- ── Recent invoices strip ──────────────────────────────────── --}}
@if(!empty($recent))
<div class="card mb-4">
  <div class="card-header d-flex align-items-center justify-content-between py-2">
    <span class="fw-semibold small" style="color:var(--zatca-navy);">
      <i class="bx bx-time-five me-1 text-primary"></i>
      {{ $ar?'آخر الفواتير':'Recent Invoices' }}
    </span>
    <a href="{{ route('invoices.index') }}" class="small text-primary">{{ $ar?'عرض الكل':'View all' }} →</a>
  </div>
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0" style="font-size:.8125rem;">
      <tbody>
        @foreach($recent as $inv)
          @php $r=array_values((array)$inv); @endphp
          <tr>
            <td class="text-muted ps-3">{{ $r[0] }}</td>
            <td class="fw-semibold">{{ $r[1] }}</td>
            <td class="text-muted">{{ is_string($r[2]) ? substr($r[2],0,10) : '' }}</td>
            <td class="text-truncate" style="max-width:160px;" title="{{ $r[3] }}">{{ $r[3] }}</td>
            <td><span class="badge bg-label-primary" style="font-size:.68rem;">{{ $r[5] ?? '' }}</span></td>
            <td class="text-end pe-3 fw-semibold {{ (float)$r[4]>=0?'text-success':'text-danger' }}">
              {{ number_format(abs((float)$r[4]),2) }}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- ── VAT Activity Cards ──────────────────────────────────────── --}}
@if(!empty($vatTypes))
<div class="mb-4">
  <h6 class="fw-semibold mb-3" style="color:var(--zatca-navy);">
    <i class="bx bx-category me-2 text-primary"></i>
    @if(app()->getLocale()==='ar') أنشطة ضريبة القيمة المضافة @else VAT Activities @endif
  </h6>
  <div class="row g-3">
    @php
      $typeIcons = [
        1 => ['icon'=>'bx-wrench',        'bg'=>'#EDE9FE','color'=>'#7C3AED'],
        2 => ['icon'=>'bx-cog',           'bg'=>'#DBEAFE','color'=>'#2563EB'],
        3 => ['icon'=>'bx-file',          'bg'=>'#FEF3C7','color'=>'#D97706'],
        4 => ['icon'=>'bx-cart',          'bg'=>'#DCFCE7','color'=>'#16A34A'],
        5 => ['icon'=>'bx-store',         'bg'=>'#FCE7F3','color'=>'#BE185D'],
        6 => ['icon'=>'bx-globe',         'bg'=>'#E0F2FE','color'=>'#0369A1'],
        7 => ['icon'=>'bx-package',       'bg'=>'#FEE2E2','color'=>'#DC2626'],
      ];
    @endphp

    @foreach($vatTypes as $type)
      @php
        $row    = (array) $type;
        $keys   = array_keys($row);
        $id     = (int) $row[$keys[0]];
        $name   = $row[$keys[1]];
        $count  = (int) $row[$keys[2]];
        $total  = (float) $row[$keys[3]];
        $style  = $typeIcons[$id] ?? ['icon'=>'bx-tag','bg'=>'#F1F5F9','color'=>'#475569'];
      @endphp
      <div class="col-6 col-md-4 col-xl-3">
        <a href="{{ route('invoices.index', ['vat_id' => $id]) }}" class="text-decoration-none">
        <div class="card h-100 border-0 shadow-sm" style="border-radius:12px;transition:transform .15s,box-shadow .15s;"
             onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.12)'"
             onmouseout="this.style.transform='';this.style.boxShadow=''">
          <div class="card-body p-3">
            <div class="d-flex align-items-center gap-3 mb-3">
              <span class="d-flex align-items-center justify-content-center rounded-circle"
                    style="width:2.5rem;height:2.5rem;background:{{ $style['bg'] }};flex-shrink:0;">
                <i class="bx {{ $style['icon'] }}" style="font-size:1.15rem;color:{{ $style['color'] }};"></i>
              </span>
              <div class="fw-semibold lh-sm" style="color:var(--zatca-navy);font-size:.875rem;">
                {{ $name }}
              </div>
            </div>
            <div class="d-flex align-items-end justify-content-between">
              <div>
                <div class="fs-4 fw-bold" style="color:var(--zatca-navy);">{{ number_format($count) }}</div>
                <div class="small" style="color:var(--zatca-muted);">
                  @if(app()->getLocale()==='ar') فاتورة @else invoices @endif
                </div>
              </div>
              @if($total != 0)
              <div class="text-end">
                <div class="small fw-semibold" style="color:{{ $total >= 0 ? '#16A34A' : '#DC2626' }};">
                  {{ number_format(abs($total), 2) }}
                </div>
                <div class="small" style="color:var(--zatca-muted);">
                  @if(app()->getLocale()==='ar') إجمالي @else total @endif
                </div>
              </div>
              @endif
            </div>
          </div>
          <div class="card-footer border-0 py-2 px-3 d-flex align-items-center justify-content-between"
               style="background:{{ $style['bg'] }}20;border-radius:0 0 12px 12px;">
            <span class="badge rounded-pill" style="background:{{ $style['bg'] }};color:{{ $style['color'] }};font-size:.7rem;">
              {{ __('app.vat_type') }} #{{ $id }}
            </span>
            <i class="bx bx-chevron-left" style="color:{{ $style['color'] }};font-size:1rem;"></i>
          </div>
        </div>
        </a>
      </div>
    @endforeach
  </div>
</div>
@endif

{{-- ── About ──────────────────────────────────── --}}
<div class="row g-4">

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
