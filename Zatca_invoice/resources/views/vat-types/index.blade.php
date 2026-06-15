@extends('layouts.app')
@section('title', __('nav.vat_types'))
@section('page-title', __('nav.vat_types'))
@section('breadcrumb')
  <li class="breadcrumb-item active">{{ __('nav.vat_types') }}</li>
@endsection

@php $ar = app()->getLocale() === 'ar'; @endphp

@php
  $typeIcons = [
    1=>['icon'=>'bx-wrench','bg'=>'#EDE9FE','color'=>'#7C3AED'],
    2=>['icon'=>'bx-cog','bg'=>'#DBEAFE','color'=>'#2563EB'],
    3=>['icon'=>'bx-file','bg'=>'#FEF3C7','color'=>'#D97706'],
    4=>['icon'=>'bx-cart','bg'=>'#DCFCE7','color'=>'#16A34A'],
    5=>['icon'=>'bx-store','bg'=>'#FCE7F3','color'=>'#BE185D'],
    6=>['icon'=>'bx-globe','bg'=>'#E0F2FE','color'=>'#0369A1'],
    7=>['icon'=>'bx-package','bg'=>'#FEE2E2','color'=>'#DC2626'],
  ];
@endphp

@section('content')
<div class="row g-3">
  @forelse($types as $type)
    @php
      $t     = array_values((array)$type);
      $id    = (int)$t[0];
      $name  = $t[1];
      $count = (int)$t[2];
      $net   = (float)$t[3];
      $s     = $typeIcons[$id] ?? ['icon'=>'bx-tag','bg'=>'#F1F5F9','color'=>'#475569'];
    @endphp
    <div class="col-12 col-md-6 col-xl-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:12px;">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 mb-3">
            <span class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                  style="width:3rem;height:3rem;background:{{ $s['bg'] }};">
              <i class="bx {{ $s['icon'] }}" style="font-size:1.3rem;color:{{ $s['color'] }};"></i>
            </span>
            <div>
              <div class="fw-bold" style="color:var(--zatca-navy);">{{ $name }}</div>
              <div class="small text-muted">{{ $ar?'النشاط':'Activity' }} #{{ $id }}</div>
            </div>
          </div>
          <div class="row text-center g-0 border-top pt-3">
            <div class="col border-end">
              <div class="fs-5 fw-bold" style="color:var(--zatca-navy);">{{ number_format($count) }}</div>
              <div class="small text-muted">{{ $ar?'فاتورة':'invoices' }}</div>
            </div>
            <div class="col">
              <div class="fs-5 fw-bold {{ $net>=0?'text-success':'text-danger' }}">{{ number_format(abs($net),2) }}</div>
              <div class="small text-muted">{{ $ar?'صافي الضريبة':'net VAT' }}</div>
            </div>
          </div>
        </div>
        <div class="card-footer border-0 py-2 px-3" style="background:{{ $s['bg'] }}20;border-radius:0 0 12px 12px;">
          <a href="{{ route('invoices.index', ['vat_id'=>$id]) }}"
             class="btn btn-sm w-100" style="background:{{ $s['bg'] }};color:{{ $s['color'] }};border:none;">
            <i class="bx bx-list-ul me-1"></i>
            {{ $ar?'عرض الفواتير':'View Invoices' }}
          </a>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12 text-center py-5 text-muted">
      <i class="bx bx-tag" style="font-size:3rem;opacity:.4;display:block;"></i>
      {{ $ar?'لا توجد أنواع':'No types found' }}
    </div>
  @endforelse
</div>
@endsection
