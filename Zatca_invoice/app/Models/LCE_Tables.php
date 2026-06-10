<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LCE_Tables extends Model
{
    use HasFactory;

    protected $connection = 'oracle';

    public static function getTables()
    {
        $all = DB::connection('oracle')
            ->select('SELECT table_name FROM user_tables ORDER BY table_name');

        return new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($all, (request()->get('page', 1) - 1) * 30, 30),
            count($all),
            30,
            request()->get('page', 1),
            ['path' => request()->url()]
        );
    }

    public static function getTableColumns(string $table): array
    {
        return DB::connection('oracle')
            ->select('SELECT column_name, data_type FROM user_tab_columns WHERE table_name = ? ORDER BY column_id', [strtoupper($table)]);
    }

    public static function getTableRows(string $table)
    {
        $perPage = 20;
        $page    = request()->get('page', 1);
        $offset  = ($page - 1) * $perPage;

        $total = DB::connection('oracle')
            ->select("SELECT COUNT(*) AS CNT FROM {$table}")[0]->CNT;

        $rows = DB::connection('oracle')
            ->select("SELECT * FROM (SELECT a.*, ROWNUM rn FROM (SELECT * FROM {$table}) a WHERE ROWNUM <= ?) WHERE rn > ?", [$offset + $perPage, $offset]);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $rows,
            $total,
            $perPage,
            $page,
            ['path' => request()->url()]
        );
    }
}
