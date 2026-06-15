<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CheckInvoiceTablesSeeder extends Seeder
{
    public function run(): void
    {
        $oracle = DB::connection('oracle');
        $tables = ['CONST_ORDERS', 'CONST_ITEMS', 'ITEMS', 'VAT_INVOICE'];

        foreach ($tables as $tbl) {
            $cols = $oracle->select(
                'SELECT column_name, data_type, data_length FROM user_tab_columns WHERE table_name = ? ORDER BY column_id',
                [$tbl]
            );

            if (empty($cols)) {
                $this->command->error("{$tbl}: NOT FOUND");
                continue;
            }

            $prop  = array_key_first((array)$cols[0]);
            $tprop = array_keys((array)$cols[0])[1];
            $lprop = array_keys((array)$cols[0])[2];

            $this->command->info("=== {$tbl} ===");
            foreach ($cols as $col) {
                $c = (array)$col;
                $k = array_keys($c);
                $this->command->line("  {$c[$k[0]]}  ({$c[$k[1]]}, {$c[$k[2]]})");
            }

            // Sample row to see actual data shape
            $sample = $oracle->select(
                "SELECT * FROM (SELECT * FROM {$tbl}) WHERE ROWNUM <= 1"
            );
            if (!empty($sample)) {
                $this->command->line("  [sample row keys: " . implode(', ', array_keys((array)$sample[0])) . "]");
            }
            $this->command->line('');
        }

        // Also check FK relationships
        $this->command->info('=== Foreign Keys ===');
        $fks = $oracle->select("
            SELECT a.table_name, a.constraint_name, b.table_name AS ref_table
            FROM user_constraints a
            JOIN user_constraints b ON b.constraint_name = a.r_constraint_name
            WHERE a.constraint_type = 'R'
              AND a.table_name IN ('CONST_ORDERS','CONST_ITEMS','ITEMS','VAT_INVOICE')
            ORDER BY a.table_name
        ");
        foreach ($fks as $fk) {
            $f = array_values((array)$fk);
            $this->command->line("  {$f[0]} → {$f[2]} (via {$f[1]})");
        }
    }
}
