@extends('layouts.app')
@section('title', 'المناطق')
@section('page-title', 'المناطق')
@section('breadcrumb')
    <li class="breadcrumb-item active">المناطق</li>
@endsection

@section('content')
<div class="row g-4">
    {{-- إضافة منطقة --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-plus-circle me-1 text-primary"></i>إضافة منطقة</h6></div>
            <div class="card-body">
                @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
                <form method="POST" action="{{ route('locations.regions.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">الاسم بالعربية <span class="text-danger">*</span></label>
                        <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror"
                               value="{{ old('name_ar') }}" placeholder="مثال: منطقة الرياض">
                        @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">الاسم بالإنجليزية</label>
                        <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}" placeholder="Riyadh Region">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">الرمز</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="RD" maxlength="10">
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-save me-1"></i>حفظ</button>
                </form>
            </div>
        </div>
    </div>

    {{-- قائمة المناطق --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-map me-1 text-primary"></i>المناطق ({{ count($regions) }})</h6></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr><th>#</th><th>الاسم بالعربية</th><th>الاسم بالإنجليزية</th><th>الرمز</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($regions as $r)
                        @php $rid = $r->region_id ?? $r->REGION_ID ?? ''; @endphp
                        <tr>
                            <td>{{ $rid }}</td>
                            <td>{{ $r->name_ar ?? $r->NAME_AR ?? '' }}</td>
                            <td>{{ $r->name_en ?? $r->NAME_EN ?? '—' }}</td>
                            <td>{{ $r->code    ?? $r->CODE    ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('locations.regions.destroy', $rid) }}"
                                      onsubmit="return confirm('حذف هذه المنطقة؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">لا توجد بيانات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
