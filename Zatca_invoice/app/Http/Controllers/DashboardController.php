<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $oracle = DB::connection('oracle');

        // KPI counts
        $totalInvoices = (int) ($oracle->selectOne("SELECT COUNT(*) AS CNT FROM VAT_INVOICE WHERE DEL_FLAG = 0")->cnt ?? 0);
        $totalVatDr    = (float)($oracle->selectOne("SELECT NVL(SUM(VAT_VAL_D),0) AS S FROM VAT_INVOICE WHERE DEL_FLAG=0")->s ?? 0);
        $totalVatCr    = (float)($oracle->selectOne("SELECT NVL(SUM(VAT_VAL_C),0) AS S FROM VAT_INVOICE WHERE DEL_FLAG=0")->s ?? 0);
        $totalVatNet   = $totalVatCr - $totalVatDr;
        $catCount      = (int) ($oracle->selectOne("SELECT COUNT(*) AS CNT FROM VAT_CATEGORIES")->cnt ?? 0);
        $typeCount     = (int) ($oracle->selectOne("SELECT COUNT(*) AS CNT FROM VAT_TYPES")->cnt ?? 0);

        // VAT types with invoice counts and totals
        $vatTypes = $oracle->select("
            SELECT
                t.VAT_ID,
                t.VAT_NAME,
                COUNT(i.SERIAL)                          AS invoice_count,
                NVL(SUM(i.VAT_VAL_C - i.VAT_VAL_D), 0) AS vat_total
            FROM VAT_TYPES t
            LEFT JOIN VAT_INVOICE i ON i.VAT_ID = t.VAT_ID AND i.DEL_FLAG = 0
            GROUP BY t.VAT_ID, t.VAT_NAME
            ORDER BY t.VAT_ID
        ");

        // Recent 5 invoices
        $recent = $oracle->select("
            SELECT * FROM (
                SELECT i.SERIAL, i.TR_NO, i.TRANS_DATE, i.DESCRIPTION,
                       i.VAT_VAL_C - i.VAT_VAL_D AS VAT_NET, t.VAT_NAME
                FROM VAT_INVOICE i
                LEFT JOIN VAT_TYPES t ON t.VAT_ID = i.VAT_ID
                WHERE i.DEL_FLAG = 0
                ORDER BY i.SERIAL DESC
            ) WHERE ROWNUM <= 5
        ");

        return view('dashboard', compact(
            'vatTypes', 'recent',
            'totalInvoices', 'totalVatDr', 'totalVatCr', 'totalVatNet',
            'catCount', 'typeCount'
        ));
    }
}
