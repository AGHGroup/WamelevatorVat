<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class VatCategoriesController extends Controller
{
    public function index()
    {
        $categories = DB::connection('oracle')->select("
            SELECT c.V_CAT_ID, c.V_CAT_NAME, c.V_CAT_TYPE, c.VAT_P,
                   COUNT(i.SERIAL) AS invoice_count
            FROM VAT_CATEGORIES c
            LEFT JOIN VAT_INVOICE i ON i.V_CAT_ID = c.V_CAT_ID AND i.DEL_FLAG = 0
            GROUP BY c.V_CAT_ID, c.V_CAT_NAME, c.V_CAT_TYPE, c.VAT_P
            ORDER BY c.V_CAT_ID
        ");

        return view('vat-categories.index', compact('categories'));
    }
}
