@extends('layouts.app')

@section('title', __('nav.company_settings'))
@section('page-title', __('nav.company_settings'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('nav.company_settings') }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <i class="bx bx-building me-2 text-primary fs-5"></i>
                <h5 class="mb-0">{{ __('nav.company_settings') }}</h5>
            </div>
            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('company.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- اسم الشركة --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="co_name">{{ __('company.co_name') }}</label>
                        <input type="text" id="co_name" name="co_name"
                               class="form-control @error('co_name') is-invalid @enderror"
                               value="{{ old('co_name', $setting->co_name) }}">
                        @error('co_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-4">
                        {{-- السجل التجاري --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="cr_no">{{ __('company.cr_no') }}</label>
                            <input type="text" id="cr_no" name="cr_no"
                                   class="form-control @error('cr_no') is-invalid @enderror"
                                   value="{{ old('cr_no', $setting->cr_no) }}">
                            @error('cr_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        {{-- الرقم الضريبي --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="vat_no">{{ __('company.vat_no') }}</label>
                            <input type="text" id="vat_no" name="vat_no"
                                   class="form-control @error('vat_no') is-invalid @enderror"
                                   value="{{ old('vat_no', $setting->vat_no) }}">
                            @error('vat_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- الموقع: منطقة / مدينة / حي --}}
                    <div class="card bg-light border-0 mb-4 p-3">
                        <h6 class="mb-3 text-muted"><i class="bx bx-map me-1"></i> موقع الشركة</h6>
                        <div class="row g-3">

                            {{-- المنطقة --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="region_id">المنطقة</label>
                                <select id="region_id" name="region_id"
                                        class="form-select @error('region_id') is-invalid @enderror">
                                    <option value="">— اختر المنطقة —</option>
                                    @foreach($regions as $r)
                                        @php $rid = $r->region_id ?? $r->REGION_ID ?? ''; @endphp
                                        <option value="{{ $rid }}"
                                            {{ old('region_id', $setting->region_id) == $rid ? 'selected' : '' }}>
                                            {{ $r->name_ar ?? $r->NAME_AR ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('region_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- المدينة --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="city_id">المدينة</label>
                                <select id="city_id" name="city_id"
                                        class="form-select @error('city_id') is-invalid @enderror">
                                    <option value="">— اختر المدينة —</option>
                                    @foreach($cities as $c)
                                        @php $cid = $c->city_id ?? $c->CITY_ID ?? ''; @endphp
                                        <option value="{{ $cid }}"
                                            {{ old('city_id', $setting->city_id) == $cid ? 'selected' : '' }}>
                                            {{ $c->city_name ?? $c->CITY_NAME ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('city_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- الحي --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="district_id">الحي</label>
                                <select id="district_id" name="district_id"
                                        class="form-select @error('district_id') is-invalid @enderror">
                                    <option value="">— اختر الحي —</option>
                                    @foreach($districts as $d)
                                        @php $did = $d->district_id ?? $d->DISTRICT_ID ?? ''; @endphp
                                        <option value="{{ $did }}"
                                            {{ old('district_id', $setting->district_id) == $did ? 'selected' : '' }}>
                                            {{ $d->name_ar ?? $d->NAME_AR ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('district_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                        </div>
                    </div>

                    {{-- الشارع --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="street">{{ __('company.street') }}</label>
                        <input type="text" id="street" name="street"
                               class="form-control @error('street') is-invalid @enderror"
                               value="{{ old('street', $setting->street) }}">
                        @error('street')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-4">
                        {{-- رقم المبنى --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="building_no">{{ __('company.building_no') }}</label>
                            <input type="text" id="building_no" name="building_no"
                                   class="form-control @error('building_no') is-invalid @enderror"
                                   value="{{ old('building_no', $setting->building_no) }}">
                            @error('building_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        {{-- الرمز البريدي --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="postal_code">{{ __('company.postal_code') }}</label>
                            <input type="text" id="postal_code" name="postal_code"
                                   class="form-control @error('postal_code') is-invalid @enderror"
                                   value="{{ old('postal_code', $setting->postal_code) }}">
                            @error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- مسار الترويسة --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="header_image">{{ __('company.header_path') }}</label>
                        @if($setting->header_path)
                        <div class="mb-2">
                            <img src="{{ asset($setting->header_path) }}"
                                 alt="{{ __('company.header_preview') }}"
                                 class="img-thumbnail"
                                 style="max-height:100px; max-width:400px;">
                            <small class="d-block text-muted mt-1">
                                {{ __('company.header_current') }}: <code>{{ $setting->header_path }}</code>
                            </small>
                        </div>
                        @endif
                        <input type="file" id="header_image" name="header_image"
                               class="form-control @error('header_image') is-invalid @enderror"
                               accept="image/jpeg,image/png">
                        <div class="form-text">{{ __('company.header_hint') }}</div>
                        @error('header_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i>{{ __('app.save') }}
                        </button>
                    </div>
                </form>
            </div>
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
(function () {
    const savedDistrict  = @json((string)($setting->district_id ?? ''));
    const districtsUrl   = '{{ url("/company/districts") }}';
    const districtSelect = document.getElementById('district_id');
    let   tsDistrict     = null;

    function initDistrictTs(selectVal) {
        if (tsDistrict) { tsDistrict.destroy(); tsDistrict = null; }
        tsDistrict = new TomSelect(districtSelect, {
            allowEmptyOption: true,
            placeholder: '— اختر الحي —',
            direction: 'down',
        });
        if (selectVal) tsDistrict.setValue(selectVal, true);
    }

    function loadDistricts(cityId, selectVal) {
        // Reset to empty while loading
        if (tsDistrict) { tsDistrict.destroy(); tsDistrict = null; }
        districtSelect.innerHTML = '<option value="">— جاري التحميل... —</option>';

        if (!cityId) {
            districtSelect.innerHTML = '<option value="">— اختر الحي —</option>';
            initDistrictTs('');
            return;
        }

        fetch(districtsUrl + '/' + encodeURIComponent(cityId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            districtSelect.innerHTML = '<option value="">— اختر الحي —</option>';
            data.forEach(function (d) {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.name;
                districtSelect.appendChild(opt);
            });
            initDistrictTs(selectVal);
        })
        .catch(function () {
            districtSelect.innerHTML = '<option value="">— خطأ في التحميل —</option>';
            initDistrictTs('');
        });
    }

    // Init region & city as searchable
    new TomSelect('#region_id', { allowEmptyOption: true, placeholder: '— اختر المنطقة —', direction: 'down' });
    const tsCity = new TomSelect('#city_id', { allowEmptyOption: true, placeholder: '— اختر المدينة —', direction: 'down' });

    // On city change → reload districts
    tsCity.on('change', function (val) {
        loadDistricts(val, '');
    });

    // On page load
    const initCity = districtSelect.dataset.city || '{{ $setting->city_id ?? "" }}';
    if (initCity) {
        loadDistricts(initCity, savedDistrict);
    } else {
        initDistrictTs('');
    }
})();
</script>
@endpush
