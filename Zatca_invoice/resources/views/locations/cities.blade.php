@extends('layouts.app')
@section('title', 'المدن')
@section('page-title', 'المدن')
@section('breadcrumb')
    <li class="breadcrumb-item active">المدن</li>
@endsection

@section('content')
<div class="row g-4">
    {{-- إضافة مدينة --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-plus-circle me-1 text-primary"></i>إضافة مدينة</h6></div>
            <div class="card-body">
                @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
                <form method="POST" action="{{ route('locations.cities.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">اسم المدينة <span class="text-danger">*</span></label>
                        <input type="text" name="city_name" class="form-control @error('city_name') is-invalid @enderror"
                               value="{{ old('city_name') }}" placeholder="مثال: جدة">
                        @error('city_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-save me-1"></i>حفظ</button>
                </form>
            </div>
        </div>
    </div>

    {{-- قائمة المدن --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-buildings me-1 text-primary"></i>المدن ({{ count($cities) }})</h6></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr><th>#</th><th>اسم المدينة</th><th>الحالة</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($cities as $c)
                        @php
                            $cid  = $c->city_id   ?? $c->CITY_ID   ?? '';
                            $name = $c->city_name  ?? $c->CITY_NAME ?? '';
                            $flag = $c->del_flag   ?? $c->DEL_FLAG  ?? 0;
                        @endphp
                        <tr>
                            <td>{{ $cid }}</td>
                            <td>{{ $name }}</td>
                            <td>
                                @if((int)$flag === 0)
                                    <span class="badge bg-success">نشطة</span>
                                @else
                                    <span class="badge bg-secondary">محذوفة</span>
                                @endif
                            </td>
                            <td>
                                @if((int)$flag === 0)
                                <form method="POST" action="{{ route('locations.cities.destroy', $cid) }}"
                                      onsubmit="return confirm('حذف هذه المدينة؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">لا توجد بيانات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
