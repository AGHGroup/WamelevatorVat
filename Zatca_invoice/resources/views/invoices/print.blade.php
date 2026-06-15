<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة ضريبية – {{ $inv['TR_NO'] ?? $inv['tr_no'] ?? '' }}</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            direction: rtl;
            text-align: right;
            margin: 20px;
            line-height: 1.8;
        }
        .header { text-align: center; margin-bottom: 30px; }
        .header img {
            width: 100%; max-width: 600px; height: auto;
            display: block; margin: 0 auto;
        }
        .details, .table {
            width: 100%; border-collapse: collapse; margin-bottom: 20px;
        }
        .details td { padding: 10px; font-size: 1.2em; }
        .table th, .table td {
            border: 1px solid black; padding: 15px;
            text-align: center; font-size: 1.2em;
        }
        .footer { text-align: center; margin-top: 30px; font-size: 1.1em; }
        .qr-section {
            display: flex; text-align: right;
            flex-direction: column; padding: 2px;
        }
        #qrcode { display: inline-block; margin: 15px 0; }
        .print-btn {
            padding: 10px 25px; font-size: 16px; cursor: pointer;
            background: #28a745; color: white; border: none;
            border-radius: 5px; margin-top: 10px;
        }
        .back-btn {
            padding: 10px 25px; font-size: 16px; cursor: pointer;
            background: #007bff; color: white; border: none;
            border-radius: 5px; margin-top: 10px; margin-right: 10px;
            text-decoration: none; display: inline-block;
        }
        @media print {
            @page { size: A4; margin: 15mm; }
            * { box-sizing: border-box; }
            body { font-size: 16pt; line-height: 1.5; margin: 0; padding: 0; }
            .header img { width: 100%; max-width: 500px; }
            .details td { padding: 8px 10px; font-size: 10pt; }
            .table th, .table td { padding: 10px 6px; font-size: 10pt; }
            .qr-section { page-break-inside: avoid; }
            #qrcode img { width: 140px; height: 140px; }
            .footer { margin-top: 15px; font-size: 12pt; }
            .print-btn, .back-btn { display: none; }
        }
    </style>
</head>
<body>
@php
    // Normalise keys — pdo_oci may return uppercase or lowercase
    $inv = array_change_key_case((array)$inv, CASE_UPPER);

    $trNo       = $inv['TR_NO']            ?? '';
    $deptId     = $inv['DEPARTMENT_ID']   ?? '';
    $serial     = $inv['SERIAL']          ?? '';
    $invoiceNo  = $deptId ? "{$deptId} - {$serial}" : $serial;
    $transDate  = $inv['TRANS_DATE']      ?? '';
    $refDateRaw = $inv['REF_DATE']        ?? '';
    $refNo      = $inv['REF_NO']          ?? '';
    $refVal     = (float)($inv['REF_VAL'] ?? 0);
    $vatVal     = (float)($inv['VAT_VAL_C'] ?? 0);
    $grandTotal = $refVal + $vatVal;
    $vatPct     = $refVal > 0 ? round($vatVal / $refVal * 100) : 15;
    $custName      = $inv['CUSTOMER_NAME']    ?? '';
    $custAcc       = $inv['CUSTOMER_ACC']     ?? $inv['SUP_CUST_ACC'] ?? '';
    $vatNo         = $inv['VAT_NO']           ?? '';
    $custPhone     = $inv['CUST_PHONE']       ?? '';
    $custType      = (int)($inv['CUST_TYPE']  ?? 0);
    $custIdNumber  = $inv['CUST_ID_NUMBER']   ?? '';
    $custVatNumber = $inv['CUST_VAT_NUMBER']  ?? '';
    $custCr        = $inv['CUST_CR']          ?? '';
    $isSimplified  = $custType === 1;
    $custBldNo     = $inv['CUST_BUILDING_NO'] ?? '';
    $custStreet    = $inv['CUST_STREET']      ?? '';
    $custPostal    = $inv['CUST_POSTAL']      ?? '';
    $custDistrictId= $inv['CUST_DISTRICT_ID'] ?? '';

    // Resolve district → city → region via single JOIN query
    $custDistrictName = '';
    $custCityName     = '';
    $custRegionName   = '';
    if ($custDistrictId) {
        $loc = \Illuminate\Support\Facades\DB::connection('oracle')->selectOne(
            "SELECT d.NAME_AR AS dist_name, c.CITY_NAME, r.NAME_AR AS reg_name
             FROM DISTRICTS d
             LEFT JOIN CITIES  c ON c.CITY_ID   = d.CITY_ID
             LEFT JOIN REGIONS r ON r.REGION_ID = c.REGION_ID
             WHERE d.DISTRICT_ID = :id",
            [':id' => $custDistrictId]
        );
        if ($loc) {
            $custDistrictName = $loc->dist_name  ?? $loc->DIST_NAME  ?? '';
            $custCityName     = $loc->city_name  ?? $loc->CITY_NAME  ?? '';
            $custRegionName   = $loc->reg_name   ?? $loc->REG_NAME   ?? '';
        }
    }

    // Build full customer address from structured fields
    $custAddr = trim(implode(' - ', array_filter([
        $custRegionName,
        $custCityName,
        $custDistrictName,
        $custStreet,
        $custBldNo  ? 'مبنى ' . $custBldNo  : '',
        $custPostal ? 'ر.ب '  . $custPostal : '',
    ])));
    // Fallback to raw ADDRESS if no structured data
    if (!$custAddr) {
        $raw = trim($inv['CUSTOMER_ADDRESS'] ?? '');
        if ($raw) $custAddr = $raw;
    }

    $description= $inv['DESCRIPTION']     ?? '';
    $notes      = $inv['NOTES']           ?? '';

    // Date formatting
    $now = new \DateTime();

    // C_DATE (creation time) — resolve first so it's available for displayDate
    $cDateRaw = $inv['C_DATE'] ?? '';
    try {
        $cDt = new \DateTime($cDateRaw ?: 'now');
    } catch (\Exception $e) {
        $cDt = $now;
    }

    if ($transDate instanceof \DateTime) {
        $dt = $transDate;
    } elseif (is_string($transDate) && strlen($transDate) >= 10) {
        try { $dt = new \DateTime($transDate); } catch (\Exception $e) { $dt = $now; }
    } else {
        $dt = $now;
    }
    $displayDate = $dt->format('d-m-Y') . ' ' . $cDt->format('H:i:s');
    $isoDate     = $dt->format('Y-m-d') . 'T' . $cDt->format('H:i:s');

    // Supply date (REF_DATE)
    if ($refDateRaw instanceof \DateTime) {
        $supplyDate = $refDateRaw->format('d-m-Y');
    } elseif (is_string($refDateRaw) && strlen($refDateRaw) >= 6) {
        try { $supplyDate = (new \DateTime($refDateRaw))->format('d-m-Y'); }
        catch (\Exception $e) { $supplyDate = $displayDate; }
    } else {
        $supplyDate = $displayDate;
    }

    $items = collect($items)->map(fn($i) => array_change_key_case((array)$i, CASE_UPPER));

    // Company settings from JSON file
    $co = \App\Models\CompanySetting::current();
    $coName    = $co->co_name    ?: '';
    $coCrNo    = $co->cr_no      ?: '';
    $coVatNo   = $co->vat_no     ?: '';
    $coBldNo   = $co->building_no ?: '';
    $coPostal  = $co->postal_code ?: '';
    $coHeader  = $co->header_path ?: 'header.jpg';

    // Resolve district name from Oracle
    $coDistrictName = '';
    if ($co->district_id) {
        $dist = \Illuminate\Support\Facades\DB::connection('oracle')
            ->selectOne("SELECT NAME_AR FROM DISTRICTS WHERE DISTRICT_ID = :id", [':id' => $co->district_id]);
        $coDistrictName = $dist ? ($dist->NAME_AR ?? $dist->name_ar ?? '') : '';
    }

    $coFullAddr = trim(implode(' - ', array_filter([
        $coDistrictName,
        $co->street      ?: '',
        $coBldNo   ? 'مبنى رقم ' . $coBldNo  : '',
        $coPostal  ? 'الرمز البريدي ' . $coPostal : '',
    ])));
@endphp

{{-- ── Header ───────────────────────────────────────────────── --}}
<div class="header">
    <table class="details">
        <tr>
            <td colspan="3" style="text-align:center;border:none;padding:0;">
                <img src="{{ asset($coHeader) }}" alt="Header">
            </td>
        </tr>
        <tr>
            <td colspan="3" style="text-align:center;border:none;padding:6px 0;">
                <strong style="font-size:1.4em;">
                    {{ $isSimplified ? 'فاتورة مبسطة' : 'فاتورة ضريبية' }}
                </strong>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center;">
                <p>رقم السجل التجاري: {{ $coCrNo }}</p>
            </td>
            <td>
                <p>رقم ضريبة القيمة المضافة: {{ $coVatNo }}</p>
            </td>
        </tr>
    </table>
</div>

{{-- ── Invoice meta ─────────────────────────────────────────── --}}
<table class="details">
    <tr>
        <td>المورد: {{ $coName }}</td>
        <td>العنوان: {{ $coFullAddr }}</td>
    </tr>
    <tr>
        <td id="invoice_id">رقم الفاتورة: {{ $invoiceNo }}</td>
        <td>&ensp;</td>
        <td id="date">تاريخ الفاتورة: {{ $displayDate }}</td>
    </tr>
    <tr>
        <td>طريقة الدفع: نقداً</td>
        <td>&ensp;</td>
        <td>تاريخ التوريد: {{ $supplyDate }}</td>
    </tr>
</table>

{{-- ── QR code ──────────────────────────────────────────────── --}}
<div class="qr-section">
    <div id="qrcode"></div>
</div>

{{-- ── Customer info ────────────────────────────────────────── --}}
<table class="details">
    <tr>
        <td id="customer_name">العميل: {{ $custName }}</td>
        @if($isSimplified)
            @if($custIdNumber)
            <td>رقم الهوية: {{ $custIdNumber }}</td>
            @else
            <td>&ensp;</td>
            @endif
        @else
            @if($custVatNumber)
            <td id="customer_vat_number">الرقم الضريبي: {{ $custVatNumber }}</td>
            @else
            <td>&ensp;</td>
            @endif
        @endif
    </tr>
    @if(!$isSimplified && $custCr)
    <tr>
        <td>السجل التجاري: {{ $custCr }}</td>
        <td>&ensp;</td>
    </tr>
    @endif
</table>
@if($custAddr)
<table class="details">
    <tr>
        <td id="customer_address">العنوان: {{ $custAddr }}</td>
    </tr>
</table>
@endif

{{-- ── Items table ──────────────────────────────────────────── --}}
<table class="table">
    <thead>
        <tr>
            <th>#</th>
            <th>الصنف / الخدمة</th>
            <th>الكمية</th>
            <th>سعر الوحدة</th>
            <th>المبلغ</th>
            <th>{{ $vatPct }}% ضريبة ق.م</th>
            <th>الإجمالي مع الضريبة</th>
        </tr>
    </thead>
    <tbody>
        @if($items->isNotEmpty())
            @foreach($items as $idx => $item)
            @php
                $qty      = (float)($item['QTY']       ?? 1);
                $price    = (float)($item['UNT_PRICE']  ?? 0);
                $lineNet  = (float)($item['LINE_TOTAL'] ?? $qty * $price);
                $lineVat  = round($lineNet * $vatPct / 100, 2);
                $lineGross= $lineNet + $lineVat;
                $itemName = $item['ITEM_ANAME'] ?? $item['ITEM_ENAME'] ?? ('صنف '.($idx+1));
            @endphp
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $itemName }}</td>
                <td>{{ number_format($qty, 2) }}</td>
                <td>{{ number_format($price, 2) }}</td>
                <td>{{ number_format($lineNet, 2) }}</td>
                <td>{{ number_format($lineVat, 2) }}</td>
                <td>{{ number_format($lineGross, 2) }}</td>
            </tr>
            @endforeach
        @else
            {{-- Fallback: one row from invoice totals when no items found --}}
            @php
                $lineVat   = $vatVal;
                $lineGross = $grandTotal;
            @endphp
            <tr>
                <td>1</td>
                <td>{{ $description ?: 'خدمات' }}</td>
                <td>1</td>
                <td>{{ number_format($refVal, 2) }}</td>
                <td>{{ number_format($refVal, 2) }}</td>
                <td>{{ number_format($lineVat, 2) }}</td>
                <td>{{ number_format($lineGross, 2) }}</td>
            </tr>
        @endif
    </tbody>
</table>

{{-- ── Totals ───────────────────────────────────────────────── --}}
<table class="details">
    <tr>
        <td id="total_amount">الإجمالي: {{ number_format($refVal, 2) }}</td>
        <td id="vat_amount">{{ $vatPct }}% ضريبة القيمة المضافة: {{ number_format($vatVal, 2) }}</td>
        <td id="grand_total">الإجمالي مع الضريبة: {{ number_format($grandTotal, 2) }}</td>
    </tr>
</table>

@if($notes)
<table class="details">
    <tr><td>ملاحظات: {{ $notes }}</td></tr>
</table>
@endif

{{-- ── Footer ───────────────────────────────────────────────── --}}
<div class="footer">
    @php
    $printUser = auth()->user();
    $printName = $printUser->user_aname ?? $printUser->user_ename ?? $printUser->user_id ?? '';
@endphp
<p>تمت طباعة الفاتورة بواسطة {{ $printName }}</p>
    <p>{{ now()->format('Y-m-d H:i:s') }}</p>
    <button class="print-btn" onclick="window.print()">🖨️ طباعة الفاتورة</button>
    <a href="{{ route('invoices.index') }}" class="back-btn">↩ العودة للقائمة</a>
</div>

{{-- ── ZATCA Phase 1 QR (TLV → Base64) ────────────────────── --}}
@php
    $qrTotal = number_format($grandTotal, 2, '.', '');
    $qrVat   = number_format($vatVal, 2, '.', '');
@endphp
<script>
(function () {
    const SELLER_NAME = @json($coName);
    const SELLER_VAT  = @json($coVatNo);
    const ISO_DATE    = @json($isoDate);
    const TOTAL       = @json($qrTotal);
    const VAT_AMOUNT  = @json($qrVat);

    function tlv(tag, value) {
        const bytes = new TextEncoder().encode(value);
        return new Uint8Array([tag, bytes.length, ...bytes]);
    }

    const parts = [
        tlv(1, SELLER_NAME),
        tlv(2, SELLER_VAT),
        tlv(3, ISO_DATE),
        tlv(4, TOTAL),
        tlv(5, VAT_AMOUNT),
    ];

    const total = parts.reduce((s, a) => s + a.length, 0);
    const buf   = new Uint8Array(total);
    let off = 0;
    parts.forEach(a => { buf.set(a, off); off += a.length; });

    const b64 = btoa(String.fromCharCode(...buf));

    const el = document.getElementById('qrcode');
    const qr = qrcode(0, 'L');
    qr.addData(b64);
    qr.make();
    el.innerHTML = qr.createImgTag(4);
})();
</script>
</body>
</html>
