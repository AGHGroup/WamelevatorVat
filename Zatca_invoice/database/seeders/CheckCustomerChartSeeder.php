<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CheckCustomerChartSeeder extends Seeder
{
    public function run(): void
    {
        $oracle = DB::connection('oracle');

        foreach (['CUSTOMERS', 'CHART_OF_ACCOUNT'] as $tbl) {
            $cols = $oracle->select(
                'SELECT column_name, data_type, data_length FROM user_tab_columns WHERE table_name = ? ORDER BY column_id',
                [$tbl]
            );

            if (empty($cols)) {
                $this->command->error("{$tbl}: NOT FOUND");
                continue;
            }

            $this->command->info("=== {$tbl} ===");
            foreach ($cols as $col) {
                $c = array_values((array)$col);
                $this->command->line("  {$c[0]}  ({$c[1]}, {$c[2]})");
            }

            // Sample row
            $sample = $oracle->select(
                "SELECT * FROM (SELECT * FROM {$tbl}) WHERE ROWNUM <= 1"
            );
            if (!empty($sample)) {
                $this->command->line("  [keys: " . implode(', ', array_keys((array)$sample[0])) . "]");
            }
            $this->command->line('');
        }
    }
}
