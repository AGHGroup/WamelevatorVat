@extends('layouts.app')
@section('title', __('nav.vat_categories'))
@section('page-title', __('nav.vat_categories'))
@section('breadcrumb')
  <li class="breadcrumb-item active">{{ __('nav.vat_categories') }}</li>
@endsection

@php $ar = app()->getLocale() === 'ar'; @endphp

@section('content')
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-semibold">{{ $ar?'فئات ضريبة القيمة المضافة':'VAT Categories' }}</h6>
    <span class="badge bg-label-primary">{{ count($categories) }} {{ $ar?'فئة':'categories' }}</span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>{{ $ar?'الكود':'ID' }}</th>
          <th>{{ $ar?'اسم الفئة':'Category Name' }}</th>
          <th>{{ $ar?'النوع':'Type' }}</th>
          <th class="text-end">{{ $ar?'نسبة الضريبة %':'VAT %' }}</th>
          <th class="text-end">{{ $ar?'عدد الفواتير':'Invoices' }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse($categories as $cat)
          @php $c = array_values((array)$cat); @endphp
          <tr>
            <td class="text-muted small">{{ $c[0] }}</td>
            <td class="fw-semibold">{{ $c[1] }}</td>
            <td><span class="badge bg-label-secondary">{{ $c[2] ?? '—' }}</span></td>
            <td class="text-end">{{ number_format((float)($c[3] ?? 0), 2) }}%</td>
            <td class="text-end">
              @if((int)$c[4] > 0)
                <a href="{{ route('invoices.index') }}" class="badge bg-label-primary text-decoration-none">
                  {{ number_format($c[4]) }}
                </a>
              @else
                <span class="text-muted">0</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center py-5 text-muted">
              <i class="bx bx-category" style="font-size:2.5rem;display:block;opacity:.4;"></i>
              {{ $ar?'لا توجد فئات':'No categories found' }}
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
