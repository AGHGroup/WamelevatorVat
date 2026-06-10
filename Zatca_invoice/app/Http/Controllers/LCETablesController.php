<?php

namespace App\Http\Controllers;

use App\Models\LCE_Tables;
use Illuminate\Http\Request;

class LCETablesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tables = LCE_Tables::getTables();
        return view('lce.tables', compact('tables'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $table)
    {
        $columns = LCE_Tables::getTableColumns($table);
        $rows    = LCE_Tables::getTableRows($table, 20);
        return view('lce.table-data', compact('table', 'columns', 'rows'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LCE_Tables $lCE_Tables)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LCE_Tables $lCE_Tables)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LCE_Tables $lCE_Tables)
    {
        //
    }
}
