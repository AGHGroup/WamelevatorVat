@extends('layouts.app')
@section('title', 'تعديل بيانات العميل')
@section('page-title', 'تعديل بيانات العميل')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">العملاء</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')
@php
    $c       = (array) $customer;
    $c       = array_change_key_case($c, CASE_LOWER);
    $type    = (int)($c['customer_type'] ?? 0);
@endphp
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <i class="bx bx-user me-2 text-primary fs-5"></i>
                <h5 class="mb-0">{{ $c['c_aname'] ?? '' }}</h5>
                <span class="badge ms-2 {{ $type === 1 ? 'bg-info' : 'bg-primary' }}">
                    {{ $type === 1 ? 'فرد' : 'شركة' }}
                </span>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('customers.update', $c['customer_id']) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- نوع العميل --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">نوع العميل</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="customer_type"
                                       id="type1" value="1" {{ old('customer_type', $type) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="type1">فرد (فاتورة مبسطة)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="customer_type"
                                       id="type2" value="2" {{ old('customer_type', $type) != 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="type2">شركة / منشأة (فاتورة ضريبية)</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4" id="section-individual" style="{{ $type != 1 ? 'display:none' : '' }}">
                        {{-- رقم الهوية --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="id_number">رقم الهوية</label>
                            <input type="text" id="id_number" name="id_number"
                                   class="form-control @error('id_number') is-invalid @enderror"
                                   value="{{ old('id_number', $c['id_number'] ?? '') }}">
                            @error('id_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4" id="section-company" style="{{ $type == 1 ? 'display:none' : '' }}">
                        {{-- الرقم الضريبي --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="vat_number">الرقم الضريبي</label>
                            <input type="text" id="vat_number" name="vat_number"
                                   class="form-control @error('vat_number') is-invalid @enderror"
                                   value="{{ old('vat_number', $c['vat_number'] ?? '') }}">
                            @error('vat_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        {{-- السجل التجاري --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="cr">السجل التجاري</label>
                            <input type="text" id="cr" name="cr"
                                   class="form-control @error('cr') is-invalid @enderror"
                                   value="{{ old('cr', $c['cr'] ?? '') }}">
                            @error('cr')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        {{-- الهاتف --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="phone">هاتف</label>
                            <input type="text" id="phone" name="phone"
                                   class="form-control"
                                   value="{{ old('phone', $c['phone'] ?? '') }}">
                        </div>
                        {{-- الجوال --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="mobile">جوال</label>
                            <input type="text" id="mobile" name="mobile"
                                   class="form-control"
                                   value="{{ old('mobile', $c['mobile'] ?? '') }}">
                        </div>
                    </div>

                    {{-- العنوان --}}
                    <div class="card bg-light border-0 p-3 mb-4">
                        <h6 class="mb-3 text-muted"><i class="bx bx-map me-1"></i> عنوان العميل</h6>
                        <div class="row g-3">
                            {{-- المنطقة (UI فقط) --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="region_id">المنطقة</label>
                                <select id="region_id" class="form-select">
                                    <option value="">— اختر المنطقة —</option>
                                    @foreach($regions as $r)
                                        @php $rid = $r->region_id ?? $r->REGION_ID ?? ''; @endphp
                                        <option value="{{ $rid }}" {{ $rid == ($regionId ?? '') ? 'selected' : '' }}>{{ $r->name_ar ?? $r->NAME_AR ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- المدينة --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="city_id">المدينة</label>
                                <select id="city_id" class="form-select">
                                    <option value="">— اختر المدينة —</option>
                                    @foreach($cities as $c2)
                                        @php $cid = $c2->city_id ?? $c2->CITY_ID ?? ''; @endphp
                                        <option value="{{ $cid }}" {{ $cid == $cityId ? 'selected' : '' }}>
                                            {{ $c2->city_name ?? $c2->CITY_NAME ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- الحي (يُحفظ) --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="district_id">الحي</label>
                                <select id="district_id" name="district_id" class="form-select">
                                    <option value="">— اختر الحي —</option>
                                    @foreach($districts as $d)
                                        @php $did = $d->district_id ?? $d->DISTRICT_ID ?? ''; @endphp
                                        <option value="{{ $did }}"
                                            {{ old('district_id', $c['district_id'] ?? '') == $did ? 'selected' : '' }}>
                                            {{ $d->name_ar ?? $d->NAME_AR ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="street_name">الشارع</label>
                                <input type="text" id="street_name" name="street_name"
                                       class="form-control"
                                       value="{{ old('street_name', $c['street_name'] ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold" for="building_no">رقم المبنى</label>
                                <input type="text" id="building_no" name="building_no"
                                       class="form-control"
                                       value="{{ old('building_no', $c['building_no'] ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold" for="postal_code">الرمز البريدي</label>
                                <input type="text" id="postal_code" name="postal_code"
                                       class="form-control"
                                       value="{{ old('postal_code', $c['postal_code'] ?? '') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold" for="address">العنوان التفصيلي</label>
                                <input type="text" id="address" name="address"
                                       class="form-control"
                                       value="{{ old('address', $c['address'] ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i>حفظ
                        </button>
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">إلغاء</a>
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
// Toggle individual / company sections
document.querySelectorAll('input[name="customer_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        const isIndividual = this.value === '1';
        document.getElementById('section-individual').style.display = isIndividual ? '' : 'none';
        document.getElementById('section-company').style.display    = isIndividual ? 'none' : '';
    });
});

// Cascading district selects
(function () {
    const savedDistrict  = @json((string)($c['district_id'] ?? ''));
    const initCityId     = @json((string)($cityId ?? ''));
    const districtsUrl   = '{{ url("/company/districts") }}';
    const districtSelect = document.getElementById('district_id');
    let   tsDistrict     = null;

    function initDistrictTs(selectVal) {
        if (tsDistrict) { tsDistrict.destroy(); tsDistrict = null; }
        tsDistrict = new TomSelect(districtSelect, { allowEmptyOption: true, placeholder: '— اختر الحي —', direction: 'down' });
        if (selectVal) tsDistrict.setValue(selectVal, true);
    }

    function loadDistricts(cityId, selectVal) {
        if (tsDistrict) { tsDistrict.destroy(); tsDistrict = null; }
        districtSelect.innerHTML = '<option value="">— جاري التحميل... —</option>';
        if (!cityId) {
            districtSelect.innerHTML = '<option value="">— اختر الحي —</option>';
            initDistrictTs('');
            return;
        }
        fetch(districtsUrl + '/' + encodeURIComponent(cityId), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                districtSelect.innerHTML = '<option value="">— اختر الحي —</option>';
                data.forEach(d => {
                    const o = document.createElement('option');
                    o.value = d.id; o.textContent = d.name;
                    districtSelect.appendChild(o);
                });
                initDistrictTs(selectVal);
            })
            .catch(() => { districtSelect.innerHTML = '<option value="">— خطأ —</option>'; initDistrictTs(''); });
    }

    new TomSelect('#region_id', { allowEmptyOption: true, placeholder: '— اختر المنطقة —', direction: 'down' });
    const tsCity = new TomSelect('#city_id', { allowEmptyOption: true, placeholder: '— اختر المدينة —', direction: 'down' });

    tsCity.on('change', val => loadDistricts(val, ''));

    if (initCityId) {
        loadDistricts(initCityId, savedDistrict);
    } else {
        initDistrictTs(savedDistrict);
    }
})();
</script>
@endpush
