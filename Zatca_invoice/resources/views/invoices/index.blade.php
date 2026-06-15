@extends('layouts.app')

@section('title', __('nav.invoices'))
@section('page-title', __('nav.all_invoices'))
@section('breadcrumb')
  <li class="breadcrumb-item active">{{ __('nav.invoices') }}</li>
@endsection

@php
  $ar   = app()->getLocale() === 'ar';
  $flip = fn(string $col) => $sortCol === $col ? ($sortDir === 'ASC' ? 'DESC' : 'ASC') : 'ASC';
  $sort = fn(string $col) => request()->fullUrlWithQuery(['sort'=>$col,'dir'=>$flip($col),'page'=>1]);
  $icon = fn(string $col) => $sortCol===$col
    ? '<i class="bx bx-sort-'.($sortDir==='ASC'?'up':'down').'" style="color:var(--bs-primary);"></i>'
    : '<i class="bx bx-sort" style="opacity:.35;"></i>';
@endphp

@section('page-actions')
  <a href="{{ route('invoices.export', request()->query()) }}"
     class="btn btn-sm btn-outline-success">
    <i class="bx bx-download me-1"></i>
    {{ $ar ? 'تصدير CSV' : 'Export CSV' }}
  </a>
  <button onclick="window.print()" class="btn btn-sm btn-outline-secondary ms-1">
    <i class="bx bx-printer me-1"></i>
    {{ $ar ? 'طباعة' : 'Print' }}
  </button>
@endsection

@push('styles')
<style>
  @media print {
    .layout-menu, .layout-navbar, .card-footer,
    form, .btn, nav[aria-label] { display:none!important; }
    .card { box-shadow:none!important; }
    table { font-size:.75rem; }
  }
  .sort-link { color:inherit; text-decoration:none; display:inline-flex; align-items:center; gap:.25rem; }
  .sort-link:hover { color:var(--bs-primary); }
  .skeleton { background:linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);
              background-size:200% 100%; animation:shimmer 1.5s infinite; border-radius:4px; }
  @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
</style>
@endpush

@section('content')

{{-- ── Filter bar ────────────────────────────────────────────── --}}
<div class="card mb-4">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('invoices.index') }}" id="filter-form">
      <div class="row g-2 align-items-end">

        <div class="col-12 col-sm-6 col-lg-3">
          <label class="form-label small mb-1">{{ $ar?'النشاط':'Activity' }}</label>
          <select name="vat_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">{{ $ar?'— كل الأنشطة —':'— All Activities —' }}</option>
            @foreach($vatTypes as $vt)
              @php $vtA=array_values((array)$vt); @endphp
              <option value="{{ $vtA[0] }}" {{ (string)$vatId===(string)$vtA[0]?'selected':'' }}>{{ $vtA[1] }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-6 col-lg-2">
          <label class="form-label small mb-1">{{ $ar?'من تاريخ':'Date from' }}</label>
          <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
        </div>

        <div class="col-6 col-lg-2">
          <label class="form-label small mb-1">{{ $ar?'إلى تاريخ':'Date to' }}</label>
          <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
        </div>

        <div class="col-12 col-lg-3">
          <label class="form-label small mb-1">{{ $ar?'بحث':'Search' }}</label>
          <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="{{ $ar?'رقم القيد، المرجع، الوصف':'TR No, Ref No, Description' }}"
                   value="{{ $search }}">
            <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i></button>
            @if($search || $vatId || $dateFrom || $dateTo)
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary" title="{{ $ar?'مسح':'Clear' }}">
              <i class="bx bx-x"></i>
            </a>
            @endif
          </div>
        </div>

        <div class="col-auto ms-auto">
          <label class="form-label small mb-1">{{ $ar?'لكل صفحة':'Per page' }}</label>
          <select name="per_page" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            @foreach([10,25,50,100] as $n)
              <option value="{{ $n }}" {{ $perPage==$n?'selected':'' }}>{{ $n }}</option>
            @endforeach
          </select>
        </div>

      </div>
      <input type="hidden" name="page" value="1">
      <input type="hidden" name="sort" value="{{ $sortCol }}">
      <input type="hidden" name="dir"  value="{{ $sortDir }}">
    </form>
  </div>
</div>

{{-- ── Active filter badge ─────────────────────────────────── --}}
<div class="d-flex align-items-center gap-2 flex-wrap mb-3">
  @if($currentType)
    @php $ct=array_values((array)$currentType); @endphp
    <span class="badge bg-primary px-3 py-2"><i class="bx bx-filter-alt me-1"></i>{{ $ct[1] }}</span>
  @endif
  @if($dateFrom || $dateTo)
    <span class="badge bg-label-secondary text-body px-3 py-2">
      <i class="bx bx-calendar me-1"></i>{{ $dateFrom ?: '…' }} → {{ $dateTo ?: '…' }}
    </span>
  @endif
  <span class="text-muted small ms-auto">
    {{ number_format($total) }} {{ $ar?'سجل':'records' }}
  </span>
</div>

{{-- ── Table ──────────────────────────────────────────────── --}}
<div class="card" id="invoice-table">
  <div class="table-responsive">
    <table class="table table-hover table-sm align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th></th>
          <th><a class="sort-link" href="{{ $sort('SERIAL') }}">#  {!! $icon('SERIAL') !!}</a></th>
          <th><a class="sort-link" href="{{ $sort('TR_NO') }}">{{ $ar?'رقم القيد':'TR No' }} {!! $icon('TR_NO') !!}</a></th>
          <th><a class="sort-link" href="{{ $sort('TRANS_DATE') }}">{{ $ar?'التاريخ':'Date' }} {!! $icon('TRANS_DATE') !!}</a></th>
          <th>{{ $ar?'النشاط':'Activity' }}</th>
          <th>{{ $ar?'المرجع':'Ref No' }}</th>
          <th>{{ $ar?'الوصف':'Description' }}</th>
          <th class="text-end"><a class="sort-link" href="{{ $sort('REF_VAL') }}">{{ $ar?'قيمة المرجع':'Ref Val' }} {!! $icon('REF_VAL') !!}</a></th>
          <th class="text-end"><a class="sort-link" href="{{ $sort('VAT_VAL_D') }}">{{ $ar?'مدين':'Dr' }} {!! $icon('VAT_VAL_D') !!}</a></th>
          <th class="text-end"><a class="sort-link" href="{{ $sort('VAT_VAL_C') }}">{{ $ar?'دائن':'Cr' }} {!! $icon('VAT_VAL_C') !!}</a></th>
          <th class="text-end"><a class="sort-link" href="{{ $sort('VAT_NET') }}">{{ $ar?'الصافي':'Net' }} {!! $icon('VAT_NET') !!}</a></th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $row)
          @php
            $r    = (array)$row;
            $k    = array_keys($r);
            $net  = (float)($r['VAT_NET']  ?? $r['vat_net']  ?? 0);
            $date = $r['TRANS_DATE'] ?? $r['trans_date'] ?? '';
            if ($date instanceof \DateTime) $date = $date->format('Y-m-d');
          @endphp
          <tr>
            <td>
              <a href="{{ route('invoices.print', $r['SERIAL'] ?? $r['serial'] ?? 0) }}"
                 target="_blank" title="{{ $ar?'طباعة الفاتورة':'Print Invoice' }}"
                 class="btn btn-sm btn-outline-primary p-1">
                <i class="bx bx-printer"></i>
              </a>
            </td>
            <td class="text-muted small">{{ $r['SERIAL']      ?? $r['serial']      ?? '' }}</td>
            <td class="fw-semibold">      {{ $r['TR_NO']       ?? $r['tr_no']       ?? '' }}</td>
            <td class="small text-nowrap">{{ is_string($date) ? substr($date,0,10) : '' }}</td>
            <td><span class="badge bg-label-primary" style="font-size:.7rem;">{{ $r['VAT_NAME'] ?? $r['vat_name'] ?? '' }}</span></td>
            <td class="small">            {{ $r['REF_NO']      ?? $r['ref_no']      ?? '' }}</td>
            <td class="small" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                title="{{ $r['DESCRIPTION'] ?? $r['description'] ?? '' }}">
              {{ $r['DESCRIPTION'] ?? $r['description'] ?? '' }}
            </td>
            <td class="text-end small">{{ number_format((float)($r['REF_VAL']  ?? 0),2) }}</td>
            <td class="text-end small text-danger">{{ number_format((float)($r['VAT_VAL_D']??0),2) }}</td>
            <td class="text-end small text-success">{{ number_format((float)($r['VAT_VAL_C']??0),2) }}</td>
            <td class="text-end small fw-semibold {{ $net>=0?'text-success':'text-danger' }}">
              {{ number_format(abs($net),2) }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="11" class="text-center py-5">
              <div style="opacity:.45;">
                <i class="bx bx-receipt" style="font-size:3rem;display:block;margin-bottom:.5rem;"></i>
                <div class="fw-semibold">{{ $ar?'لا توجد فواتير':'No invoices found' }}</div>
                <div class="small text-muted mt-1">{{ $ar?'جرّب تغيير معايير البحث أو الفلتر':'Try adjusting your search or filter criteria' }}</div>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($last > 1)
  <div class="card-footer d-flex align-items-center justify-content-between py-2">
    <span class="text-muted small">
      @php $from=($page-1)*$perPage+1; $to=min($page*$perPage,$total); @endphp
      {{ $ar?"عرض ".number_format($from)." – ".number_format($to)." من ".number_format($total)
            :"Showing ".number_format($from)."–".number_format($to)." of ".number_format($total) }}
    </span>
    <nav>
      <ul class="pagination pagination-sm mb-0">
        <li class="page-item {{ $page<=1?'disabled':'' }}">
          <a class="page-link" href="{{ request()->fullUrlWithQuery(['page'=>$page-1]) }}">
            <i class="bx bx-chevron-{{ $ar?'right':'left' }}"></i>
          </a>
        </li>
        @php $start=max(1,$page-2); $end=min($last,$page+2); @endphp
        @if($start>1) <li class="page-item disabled"><span class="page-link">…</span></li> @endif
        @for($p=$start;$p<=$end;$p++)
          <li class="page-item {{ $p===$page?'active':'' }}">
            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page'=>$p]) }}">{{ $p }}</a>
          </li>
        @endfor
        @if($end<$last) <li class="page-item disabled"><span class="page-link">…</span></li> @endif
        <li class="page-item {{ $page>=$last?'disabled':'' }}">
          <a class="page-link" href="{{ request()->fullUrlWithQuery(['page'=>$page+1]) }}">
            <i class="bx bx-chevron-{{ $ar?'left':'right' }}"></i>
          </a>
        </li>
      </ul>
    </nav>
  </div>
  @endif
</div>

@endsection
