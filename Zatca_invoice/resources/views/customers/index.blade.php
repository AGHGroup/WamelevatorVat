@extends('layouts.app')
@section('title', 'العملاء')
@section('page-title', 'العملاء')
@section('breadcrumb')
    <li class="breadcrumb-item active">العملاء</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="bx bx-group me-2 text-primary"></i>قائمة العملاء</h5>
    </div>
    <div class="card-body pb-0">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" name="search" value="{{ $search }}"
                           class="form-control" placeholder="بحث بالاسم أو رقم الهوية أو الرقم الضريبي...">
                    <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i></button>
                    @if($search)
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">✕</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>النوع</th>
                    <th>رقم الهوية / الرقم الضريبي</th>
                    <th>السجل التجاري</th>
                    <th>الهاتف</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                @php
                    $type    = (int)($c->customer_type ?? $c->CUSTOMER_TYPE ?? 0);
                    $name    = $c->c_aname ?? $c->C_ANAME ?? '';
                    $idNo    = $c->id_number  ?? $c->ID_NUMBER  ?? '';
                    $vatNo   = $c->vat_number ?? $c->VAT_NUMBER ?? '';
                    $cr      = $c->cr         ?? $c->CR         ?? '';
                    $phone   = $c->phone      ?? $c->PHONE      ?? '';
                    $mobile  = $c->mobile     ?? $c->MOBILE     ?? '';
                    $custId  = $c->customer_id ?? $c->CUSTOMER_ID ?? '';
                @endphp
                <tr>
                    <td>{{ $custId }}</td>
                    <td>{{ $name }}</td>
                    <td>
                        @if($type === 1)
                            <span class="badge bg-info">فرد</span>
                        @else
                            <span class="badge bg-primary">شركة</span>
                        @endif
                    </td>
                    <td>
                        @if($type === 1)
                            {{ $idNo ?: '—' }}
                        @else
                            {{ $vatNo ?: '—' }}
                        @endif
                    </td>
                    <td>{{ $cr ?: '—' }}</td>
                    <td>{{ $phone ?: $mobile ?: '—' }}</td>
                    <td>
                        <a href="{{ route('customers.edit', $custId) }}" class="btn btn-sm btn-warning">
                            <i class="bx bx-edit"></i> تعديل
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">لا توجد نتائج</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{-- Pagination --}}
    @if($last > 1)
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">{{ $total }} عميل — صفحة {{ $page }} من {{ $last }}</small>
        <div class="d-flex gap-1">
            @if($page > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}" class="btn btn-sm btn-outline-secondary">‹ السابق</a>
            @endif
            @if($page < $last)
                <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}" class="btn btn-sm btn-outline-secondary">التالي ›</a>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
