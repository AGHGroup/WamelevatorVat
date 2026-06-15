<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class VatTypesController extends Controller
{
    public function index()
    {
        $types = DB::connection('oracle')->select("
            SELECT t.VAT_ID, t.VAT_NAME,
                   COUNT(i.SERIAL)                          AS invoice_count,
                   NVL(SUM(i.VAT_VAL_C - i.VAT_VAL_D), 0) AS vat_net
            FROM VAT_TYPES t
            LEFT JOIN VAT_INVOICE i ON i.VAT_ID = t.VAT_ID AND i.DEL_FLAG = 0
            GROUP BY t.VAT_ID, t.VAT_NAME
            ORDER BY t.VAT_ID
        ");

        return view('vat-types.index', compact('types'));
    }
}
