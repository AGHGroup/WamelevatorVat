@php
    $printUser     = auth()->user();
    $printUserName = $printUser->user_aname ?? $printUser->user_ename ?? '';
    $isMalek       = str_contains($printUserName, 'مالك');
    $isIbrahim     = str_contains($printUserName, 'الحمصي');
    $showWatermark = $isMalek || $isIbrahim;
    $invTrNo       = $inv['TR_NO'] ?? $inv['tr_no'] ?? '';
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة ضريبية – {{ $invTrNo }}{{ $showWatermark ? ' – مراجعة وتدقيق' : '' }}</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            direction: rtl;
            text-align: right;
            margin: 0;
            padding: 24px;
            background: #e8eaf0;
            color: #1a1a2e;
            line-height: 1.7;
        }

        /* ── Invoice wrapper ── */
        .invoice-wrapper {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border: 2px solid #1a3c5e;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        /* ── Top accent bar ── */
        .accent-bar {
            height: 4px;
            background: #bbb;
        }

        /* ── Sections ── */
        .invoice-header {
            padding: 20px 28px 10px;
            border-bottom: 1px solid #ccc;
        }
        .invoice-header img {
            width: 100%; max-width: 580px;
            display: block; margin: 0 auto 12px;
        }
        .invoice-type-badge {
            text-align: center;
            margin: 8px 0;
        }
        .invoice-type-badge span {
            display: inline-block;
            background: #fff;
            color: #222;
            font-size: 1.2em;
            font-weight: bold;
            padding: 6px 32px;
            border-radius: 20px;
            border: 2px solid #999;
            letter-spacing: 1px;
        }
        .reg-row {
            display: flex;
            justify-content: space-between;
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px 16px;
            margin-top: 10px;
            font-size: 0.95em;
        }

        /* ── Info sections ── */
        .section {
            padding: 14px 28px;
            border-bottom: 1px solid #ddd;
        }
        .section-title {
            font-size: 0.82em;
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            border-right: 4px solid #aaa;
            padding-right: 8px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 24px;
        }
        .info-grid.three { grid-template-columns: 1fr 1fr 1fr; }
        .info-item {
            display: flex;
            gap: 6px;
            font-size: 0.97em;
        }
        .info-item .lbl {
            color: #666;
            white-space: nowrap;
        }
        .info-item .val {
            font-weight: bold;
            color: #111;
        }

        /* ── QR ── */
        .qr-section { padding: 6px 28px 4px; }
        #qrcode { display: inline-block; }
        #qrcode img { width: 110px; height: 110px; }

        /* ── Items table ── */
        .section-table { padding: 0 28px 16px; }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95em;
            margin-top: 8px;
        }
        .items-table thead tr {
            background: #f0f0f0;
            color: #222;
        }
        .items-table thead th {
            padding: 10px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #ccc;
        }
        .items-table tbody tr:nth-child(even) { background: #fafafa; }
        .items-table tbody tr:hover { background: #f5f5f5; }
        .items-table tbody td {
            padding: 9px 10px;
            text-align: center;
            border: 1px solid #ddd;
            color: #222;
        }
        .items-table tbody td:nth-child(2) { text-align: right; }
        .items-table tfoot tr.sum-row td {
            background: #f0f0f0;
            font-weight: bold;
            border: 1px solid #ccc;
            padding: 9px 10px;
            text-align: center;
            color: #222;
        }

        /* ── Totals box ── */
        .totals-section {
            padding: 12px 28px 20px;
            display: flex;
            justify-content: flex-end;
        }
        .totals-table {
            border-collapse: collapse;
            width: 52%;
            font-size: 0.97em;
            border: 1.5px solid #bbb;
            border-radius: 6px;
            overflow: hidden;
        }
        .totals-table td {
            padding: 9px 14px;
            border: 1px solid #ddd;
            color: #222;
        }
        .totals-table .lbl {
            background: #fafafa;
            font-weight: bold;
            color: #222;
            width: 58%;
        }
        .totals-table .val {
            text-align: center;
            font-weight: bold;
            background: #fff;
            width: 42%;
        }
        .totals-table .dis-row .lbl { background: #fafafa; }
        .totals-table .dis-row .val { background: #fff; }
        .totals-table .grand-row td {
            background: #f0f0f0;
            color: #222;
            font-size: 1.05em;
            font-weight: bold;
            border-top: 2px solid #bbb;
        }

        /* ── Notes ── */
        .notes-section {
            margin: 0 28px 16px;
            padding: 10px 14px;
            background: #fafafa;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 0.95em;
        }

        /* ── Footer ── */
        .invoice-footer {
            background: #f2f2f2;
            border-top: 1px solid #ccc;
            padding: 12px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.88em;
            color: #777;
        }
        .print-btn {
            padding: 9px 22px; font-size: 14px; cursor: pointer;
            background: #28a745; color: white; border: none;
            border-radius: 5px;
        }
        .back-btn {
            padding: 9px 22px; font-size: 14px; cursor: pointer;
            background: #888; color: white; border: none;
            border-radius: 5px; text-decoration: none; display: inline-block;
            margin-right: 8px;
        }
        .bottom-bar {
            height: 4px;
            background: #bbb;
        }
        .watermark {
            position: fixed;
            left: 50%;
            transform: translateX(-50%) rotate(-35deg);
            font-weight: bold;
            white-space: nowrap;
            pointer-events: none;
            z-index: 9999;
            user-select: none;
        }
        .watermark-1 {
            top: 35%;
            font-size: 90px;
            color: rgba(200, 0, 0, 0.12);
        }
        .watermark-2 {
            top: 60%;
            font-size: 60px;
            color: rgba(180, 100, 0, 0.12);
        }
        @media print {
            .watermark {
                position: fixed;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .watermark-1 { color: rgba(200, 0, 0, 0.15) !important; }
            .watermark-2 { color: rgba(180, 100, 0, 0.15) !important; }
        }

        /* ── Print ── */
        @media print {
            @page { size: A4; margin: 6mm; }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            body {
                background: #fff !important;
                padding: 0 !important;
            }

            .invoice-wrapper {
                border: 1px solid #ccc !important;
                border-radius: 6px !important;
                box-shadow: none !important;
                max-width: 100% !important;
            }

            .accent-bar, .bottom-bar {
                background: #ccc !important;
                -webkit-print-color-adjust: exact !important;
            }

            .invoice-header img { max-width: 480px; }

            .invoice-type-badge span {
                background: #fff !important;
                color: #222 !important;
                border: 2px solid #999 !important;
            }

            .reg-row {
                background: #fafafa !important;
                border: 1px solid #ddd !important;
                font-size: 8.5pt;
            }

            .section-title {
                color: #333 !important;
                border-right: 4px solid #bbb !important;
                font-size: 8pt;
            }

            .section { padding: 8px 16px; }
            .section-table { padding: 0 16px 10px; }
            .info-item { font-size: 8.5pt; }

            .items-table thead tr { background: #f0f0f0 !important; }
            .items-table thead th {
                color: #222 !important;
                background: #f0f0f0 !important;
                font-size: 8.5pt;
                padding: 6px 5px;
                border: 1px solid #ccc !important;
            }
            .items-table tbody td { font-size: 8.5pt; padding: 5px; border: 1px solid #ddd !important; color: #222 !important; }
            .items-table tbody tr:nth-child(even) { background: #fafafa !important; }
            .items-table tfoot tr.sum-row td {
                background: #f0f0f0 !important;
                color: #222 !important;
                font-size: 8.5pt;
                padding: 5px;
                border: 1px solid #ccc !important;
            }

            .totals-section { padding: 6px 16px 12px; }
            .totals-table { font-size: 8.5pt; border: 1px solid #ccc !important; }
            .totals-table td { padding: 5px 10px; border: 1px solid #ddd !important; color: #222 !important; }
            .totals-table .lbl { background: #fafafa !important; color: #222 !important; }
            .totals-table .val { background: #fff !important; color: #222 !important; }
            .totals-table .grand-row td {
                background: #f0f0f0 !important;
                color: #222 !important;
                border-top: 2px solid #bbb !important;
            }

            .notes-section {
                background: #fafafa !important;
                border: 1px solid #ddd !important;
                font-size: 8.5pt;
            }

            .invoice-footer {
                background: #fafafa !important;
                border-top: 1px solid #ddd !important;
                padding: 6px 16px;
                font-size: 8pt;
            }

            .print-btn, .back-btn { display: none !important; }
            #qrcode img { width: 85px !important; height: 85px !important; }
        }
    </style>
</head>
<body>
@php
    $inv = array_change_key_case((array)$inv, CASE_UPPER);

    $trNo          = $inv['TR_NO']            ?? '';
    $deptId        = $inv['DEPARTMENT_ID']    ?? '';
    $serial        = $inv['SERIAL']           ?? '';
    $invoiceNo     = $deptId ? "{$deptId} - {$serial}" : $serial;
    $transDate     = $inv['TRANS_DATE']       ?? '';
    $refDateRaw    = $inv['REF_DATE']         ?? '';
    $refNo         = $inv['REF_NO']           ?? '';
    $refVal        = (float)($inv['REF_VAL']  ?? 0);
    $vatVal        = (float)($inv['VAT_VAL_C']?? 0);
    $disPct        = (float)($inv['DISCOUNT'] ?? 0);
    $disVal        = (float)($inv['DIS_VAL']  ?? 0);
    $grandTotal    = $refVal + $vatVal;
    $vatPct        = $refVal > 0 ? round($vatVal / $refVal * 100) : 15;
    $custName      = $inv['CUSTOMER_NAME']    ?? '';
    $custType      = (int)($inv['CUST_TYPE']  ?? 0);
    $custIdNumber  = $inv['CUST_ID_NUMBER']   ?? '';
    $custVatNumber = $inv['CUST_VAT_NUMBER']  ?? '';
    $custCr        = $inv['CUST_CR']          ?? '';
    $custPhone     = $inv['CUST_PHONE']       ?? '';
    $isSimplified  = $custType === 1;
    $custBldNo     = $inv['CUST_BUILDING_NO'] ?? '';
    $custStreet    = $inv['CUST_STREET']      ?? '';
    $custPostal    = $inv['CUST_POSTAL']      ?? '';
    $custDistrictId= $inv['CUST_DISTRICT_ID'] ?? '';

    $custDistrictName = ''; $custCityName = ''; $custRegionName = '';
    if ($custDistrictId) {
        $loc = \Illuminate\Support\Facades\DB::connection('oracle')->selectOne(
            "SELECT d.NAME_AR AS dist_name, c.CITY_NAME, r.NAME_AR AS reg_name
             FROM DISTRICTS d
             LEFT JOIN CITIES  c ON c.CITY_ID   = d.CITY_ID
             LEFT JOIN REGIONS r ON r.REGION_ID = c.REGION_ID
             WHERE d.DISTRICT_ID = :id", [':id' => $custDistrictId]
        );
        if ($loc) {
            $custDistrictName = $loc->dist_name ?? $loc->DIST_NAME ?? '';
            $custCityName     = $loc->city_name ?? $loc->CITY_NAME ?? '';
            $custRegionName   = $loc->reg_name  ?? $loc->REG_NAME  ?? '';
        }
    }
    $custAddr = trim(implode(' - ', array_filter([
        $custRegionName, $custCityName, $custDistrictName, $custStreet,
        $custBldNo  ? 'مبنى ' . $custBldNo  : '',
        $custPostal ? 'ر.ب '  . $custPostal : '',
    ])));
    if (!$custAddr) $custAddr = trim($inv['CUSTOMER_ADDRESS'] ?? '');

    $description = $inv['DESCRIPTION'] ?? '';
    $notes       = $inv['NOTES']       ?? '';

    $now = new \DateTime();
    $cDateRaw = $inv['C_DATE'] ?? '';
    try { $cDt = new \DateTime($cDateRaw ?: 'now'); } catch (\Exception $e) { $cDt = $now; }

    if ($transDate instanceof \DateTime) {
        $dt = $transDate;
    } elseif (is_string($transDate) && strlen($transDate) >= 10) {
        try { $dt = new \DateTime($transDate); } catch (\Exception $e) { $dt = $now; }
    } else { $dt = $now; }

    $displayDate = $dt->format('d-m-Y') . ' ' . $cDt->format('H:i:s');
    $isoDate     = $dt->format('Y-m-d') . 'T' . $cDt->format('H:i:s');

    if ($refDateRaw instanceof \DateTime) {
        $supplyDate = $refDateRaw->format('d-m-Y');
    } elseif (is_string($refDateRaw) && strlen($refDateRaw) >= 6) {
        try { $supplyDate = (new \DateTime($refDateRaw))->format('d-m-Y'); }
        catch (\Exception $e) { $supplyDate = $displayDate; }
    } else { $supplyDate = $displayDate; }

    $items = collect($items)->map(fn($i) => array_change_key_case((array)$i, CASE_UPPER));

    $co = \App\Models\CompanySetting::current();
    abort_if(empty($co->co_name), 503, 'بيانات الشركة غير متوفرة — تحقق من اتصال قاعدة البيانات.');
    $coName   = $co->co_name     ?: '';
    $coCrNo   = $co->cr_no       ?: '';
    $coVatNo  = $co->vat_no      ?: '';
    $coBldNo  = $co->building_no ?: '';
    $coPostal = $co->postal_code ?: '';
    $coHeader = $co->header_path ?: '';

    $coDistrictName = '';
    if ($co->district_id) {
        $dist = \Illuminate\Support\Facades\DB::connection('oracle')
            ->selectOne("SELECT NAME_AR FROM DISTRICTS WHERE DISTRICT_ID = :id", [':id' => $co->district_id]);
        $coDistrictName = $dist ? ($dist->NAME_AR ?? $dist->name_ar ?? '') : '';
    }
    $coFullAddr = trim(implode(' - ', array_filter([
        $coDistrictName, $co->street ?: '',
        $coBldNo  ? 'مبنى رقم ' . $coBldNo  : '',
        $coPostal ? 'الرمز البريدي ' . $coPostal : '',
    ])));
@endphp

@if($showWatermark)
<div class="watermark watermark-1">غير رسمية</div>
<div class="watermark watermark-2">للمراجعة والتدقيق</div>
@endif

<div class="invoice-wrapper">
    <div class="accent-bar"></div>

    {{-- ── Header ── --}}
    <div class="invoice-header">
        @if($coHeader)
        <img src="{{ asset($coHeader) }}?v={{ filemtime(public_path($coHeader)) ?: time() }}" alt="Header">
        @endif
        <div class="invoice-type-badge">
            <span>{{ $isSimplified ? 'فاتورة ضريبية مبسطة' : 'فاتورة ضريبية' }}</span>
        </div>
        <div class="reg-row">
            <span>رقم السجل التجاري: <strong>{{ $coCrNo }}</strong></span>
            <span>الرقم الضريبي للمورد: <strong>{{ $coVatNo }}</strong></span>
        </div>
    </div>

    {{-- ── Supplier + Invoice info ── --}}
    <div class="section">
        <div class="section-title">بيانات الفاتورة</div>
        <div class="info-grid three">
            <div class="info-item"><span class="lbl">المورد:</span><span class="val">{{ $coName }}</span></div>
            <div class="info-item"><span class="lbl">رقم الفاتورة:</span><span class="val" id="invoice_id">{{ $invoiceNo }}</span></div>
            <div class="info-item"><span class="lbl">تاريخ الفاتورة:</span><span class="val" id="date">{{ $displayDate }}</span></div>
            <div class="info-item"><span class="lbl">العنوان:</span><span class="val">{{ $coFullAddr }}</span></div>
            <div class="info-item"><span class="lbl">طريقة الدفع:</span><span class="val">نقداً</span></div>
            <div class="info-item"><span class="lbl">تاريخ التوريد:</span><span class="val">{{ $supplyDate }}</span></div>
        </div>
    </div>

    {{-- ── Customer info ── --}}
    <div class="section">
        <div class="section-title">بيانات العميل</div>
        <div class="info-grid">
            <div class="info-item"><span class="lbl">العميل:</span><span class="val" id="customer_name">{{ $custName }}</span></div>
            @if($custIdNumber)
            <div class="info-item"><span class="lbl">رقم الهوية:</span><span class="val">{{ $custIdNumber }}</span></div>
            @endif
            @if(!$isSimplified && $custVatNumber)
            <div class="info-item"><span class="lbl">الرقم الضريبي:</span><span class="val" id="customer_vat_number">{{ $custVatNumber }}</span></div>
            @endif
            @if(!$isSimplified && $custCr)
            <div class="info-item"><span class="lbl">السجل التجاري:</span><span class="val">{{ $custCr }}</span></div>
            @endif
            @if($custPhone)
            <div class="info-item"><span class="lbl">الهاتف:</span><span class="val">{{ $custPhone }}</span></div>
            @endif
            @if($custAddr)
            <div class="info-item" style="grid-column: span 2;"><span class="lbl">العنوان:</span><span class="val" id="customer_address">{{ $custAddr }}</span></div>
            @endif
        </div>
    </div>

    {{-- ── QR code ── --}}
    <div class="qr-section">
        <div id="qrcode"></div>
    </div>

    {{-- ── Items table ── --}}
    <div class="section-table">
        <div class="section-title">بنود الفاتورة</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:4%">#</th>
                    <th style="width:32%">الصنف / الخدمة</th>
                    <th>الكمية</th>
                    <th>سعر الوحدة</th>
                    <th>المبلغ</th>
                    <th>{{ $vatPct }}% ضريبة ق.م</th>
                    <th>الإجمالي مع الضريبة</th>
                </tr>
            </thead>
            <tbody>
            @if($items->isNotEmpty())
                @php $sumNet = 0; $sumVat = 0; $sumGross = 0; @endphp
                @foreach($items as $idx => $item)
                @php
                    $qty       = (float)($item['QTY']       ?? 1);
                    $price     = (float)($item['UNT_PRICE']  ?? 0);
                    $lineNet   = round((float)($item['LINE_TOTAL'] ?? $qty * $price), 2);
                    $lineVat   = round($lineNet * $vatPct / 100, 2);
                    $lineGross = round($lineNet + $lineVat, 2);
                    $itemName  = $item['ITEM_ANAME'] ?? $item['ITEM_ENAME'] ?? ('صنف '.($idx+1));
                    $sumNet    = round($sumNet  + $lineNet,   2);
                    $sumVat    = round($sumVat  + $lineVat,   2);
                    $sumGross  = round($sumGross+ $lineGross, 2);
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
                @php
                    $sumVatFull   = round($sumNet * $vatPct / 100, 2);
                    $sumGrossFull = round($sumNet + $sumVatFull, 2);
                    $sumDis       = round($sumNet * $disPct / 100, 2);
                    $sumAfterDis  = round($sumNet - $sumDis, 2);
                    $sumVatNet    = round($sumAfterDis * $vatPct / 100, 2);
                    $sumGrossNet  = round($sumAfterDis + $sumVatNet, 2);
                @endphp
            @else
                @php
                    $sumNet       = $refVal;
                    $sumVatFull   = $vatVal;
                    $sumGrossFull = $grandTotal;
                    $sumDis       = $disVal ?: round($refVal * $disPct / 100, 2);
                    $sumAfterDis  = round($sumNet - $sumDis, 2);
                    $sumVatNet    = round($sumAfterDis * $vatPct / 100, 2);
                    $sumGrossNet  = round($sumAfterDis + $sumVatNet, 2);
                @endphp
                <tr>
                    <td>1</td>
                    <td>{{ $description ?: 'خدمات' }}</td>
                    <td>1</td>
                    <td>{{ number_format($refVal, 2) }}</td>
                    <td>{{ number_format($refVal, 2) }}</td>
                    <td>{{ number_format($vatVal, 2) }}</td>
                    <td>{{ number_format($grandTotal, 2) }}</td>
                </tr>
            @endif
            </tbody>
            <tfoot>
                <tr class="sum-row">
                    <td colspan="3" style="text-align:right; padding-right:12px;">المجموع</td>
                    <td></td>
                    <td>{{ number_format($sumNet, 2) }}</td>
                    <td>{{ number_format($sumVatFull, 2) }}</td>
                    <td>{{ number_format($sumGrossFull, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- ── Totals ── --}}
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="lbl">الإجمالي قبل الخصم</td>
                <td class="val" id="total_amount">{{ number_format($sumNet, 2) }} ر.س</td>
            </tr>
            @if($sumDis > 0)
            <tr class="dis-row">
                <td class="lbl">الخصم</td>
                <td class="val">- {{ number_format($sumDis, 2) }} ر.س</td>
            </tr>
            @endif
            <tr>
                <td class="lbl">الإجمالي بعد الخصم</td>
                <td class="val">{{ number_format($sumAfterDis, 2) }} ر.س</td>
            </tr>
            <tr>
                <td class="lbl" id="vat_amount">ضريبة القيمة المضافة ({{ $vatPct }}%)</td>
                <td class="val">{{ number_format($sumVatNet, 2) }} ر.س</td>
            </tr>
            <tr class="grand-row">
                <td class="lbl" id="grand_total">الإجمالي مع الضريبة</td>
                <td class="val">{{ number_format($sumGrossNet, 2) }} ر.س</td>
            </tr>
        </table>
    </div>

    {{-- ── Notes ── --}}
    @if($notes)
    <div class="notes-section">
        <strong>ملاحظات:</strong> {{ $notes }}
    </div>
    @endif

    {{-- ── Footer ── --}}
    <div class="invoice-footer">
        <div>
            <div>طُبعت بواسطة: <strong>{{ $showWatermark ? 'للمراجعة والتدقيق - غير رسمية' : ($printUser->user_aname ?? $printUser->user_ename ?? $printUser->user_id ?? '') }}</strong></div>
            <div>{{ now()->format('Y-m-d') }} — {{ now()->format('H:i:s') }}</div>
        </div>
        <div>
            <a href="{{ route('invoices.index') }}" class="back-btn">↩ العودة</a>
            <button class="print-btn" onclick="window.print()">🖨️ طباعة</button>
        </div>
    </div>
    <div class="bottom-bar"></div>
</div>

{{-- ── ZATCA QR ── --}}
@php
    $qrTotal = number_format($sumGrossNet, 2, '.', '');
    $qrVat   = number_format($sumVatNet,   2, '.', '');
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
    const parts = [tlv(1,SELLER_NAME),tlv(2,SELLER_VAT),tlv(3,ISO_DATE),tlv(4,TOTAL),tlv(5,VAT_AMOUNT)];
    const total = parts.reduce((s,a) => s+a.length, 0);
    const buf   = new Uint8Array(total);
    let off = 0;
    parts.forEach(a => { buf.set(a, off); off += a.length; });
    const b64 = btoa(String.fromCharCode(...buf));
    const el = document.getElementById('qrcode');
    const qr = qrcode(0, 'L');
    qr.addData(b64);
    qr.make();
    el.innerHTML = qr.createImgTag(3);
})();
</script>
</body>
</html>
