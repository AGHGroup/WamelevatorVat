@extends('layouts.app')
@section('title', 'الأحياء')
@section('page-title', 'الأحياء')
@section('breadcrumb')
    <li class="breadcrumb-item active">الأحياء</li>
@endsection

@section('content')
<div class="row g-4">
    {{-- إضافة حي --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-plus-circle me-1 text-primary"></i>إضافة حي</h6></div>
            <div class="card-body">
                @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
                <form method="POST" action="{{ route('locations.districts.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">المدينة <span class="text-danger">*</span></label>
                        <select name="city_id" class="form-select @error('city_id') is-invalid @enderror" id="add_city_id">
                            <option value="">— اختر المدينة —</option>
                            @foreach($cities as $c)
                                @php $cid = $c->city_id ?? $c->CITY_ID ?? ''; @endphp
                                <option value="{{ $cid }}" {{ old('city_id', $cityId) == $cid ? 'selected' : '' }}>
                                    {{ $c->city_name ?? $c->CITY_NAME ?? '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('city_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">الاسم بالعربية <span class="text-danger">*</span></label>
                        <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror"
                               value="{{ old('name_ar') }}" placeholder="مثال: حي النزهة">
                        @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">الاسم بالإنجليزية</label>
                        <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}" placeholder="Al-Nuzhah Dist.">
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-save me-1"></i>حفظ</button>
                </form>
            </div>
        </div>
    </div>

    {{-- قائمة الأحياء --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="bx bx-map-pin me-1 text-primary"></i>الأحياء ({{ $total }})</h6>
            </div>
            <div class="card-body pb-0">
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-5">
                        <select name="city_id" class="form-select" onchange="this.form.submit()">
                            <option value="">— كل المدن —</option>
                            @foreach($cities as $c)
                                @php $cid = $c->city_id ?? $c->CITY_ID ?? ''; @endphp
                                <option value="{{ $cid }}" {{ $cid == $cityId ? 'selected' : '' }}>
                                    {{ $c->city_name ?? $c->CITY_NAME ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="بحث...">
                            <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i></button>
                            @if($search || $cityId)
                                <a href="{{ route('locations.districts') }}" class="btn btn-outline-secondary">✕</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr><th>#</th><th>الحي</th><th>المدينة</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($districts as $d)
                        @php
                            $did   = $d->district_id ?? $d->DISTRICT_ID ?? '';
                            $dname = $d->name_ar     ?? $d->NAME_AR     ?? '';
                            $cname = $d->city_name   ?? $d->CITY_NAME   ?? '—';
                        @endphp
                        <tr>
                            <td><small class="text-muted">{{ $did }}</small></td>
                            <td>{{ $dname }}</td>
                            <td>{{ $cname }}</td>
                            <td>
                                <form method="POST" action="{{ route('locations.districts.destroy', $did) }}"
                                      onsubmit="return confirm('حذف هذا الحي؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">لا توجد نتائج</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($last > 1)
            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">{{ $total }} حي — صفحة {{ $page }} من {{ $last }}</small>
                <div class="d-flex gap-1">
                    @if($page > 1)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}" class="btn btn-sm btn-outline-secondary">‹</a>
                    @endif
                    @if($page < $last)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}" class="btn btn-sm btn-outline-secondary">›</a>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
new TomSelect('#add_city_id', { allowEmptyOption: true, placeholder: '— اختر المدينة —', direction: 'down' });
</script>
@endpush
