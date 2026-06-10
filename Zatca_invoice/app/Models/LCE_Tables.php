<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LCE_Tables extends Model
{
    use HasFactory;

    protected $connection = 'oracle';

    public static function getTables(): array
    {
        return DB::connection('oracle')
            ->select('SELECT table_name FROM user_tables ORDER BY table_name');
    }

    public static function getTableColumns(string $table): array
    {
        return DB::connection('oracle')
            ->select('SELECT column_name, data_type FROM user_tab_columns WHERE table_name = ? ORDER BY column_id', [strtoupper($table)]);
    }

    public static function getTableRows(string $table, int $limit = 20): array
    {
        return DB::connection('oracle')
            ->select("SELECT * FROM {$table} WHERE ROWNUM <= {$limit}");
    }
}
