<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CheckChartSeeder extends Seeder
{
    public function run(): void
    {
        $oracle = DB::connection('oracle');

        $cols = $oracle->select(
            'SELECT column_name, data_type FROM user_tab_columns WHERE table_name = ? ORDER BY column_id',
            ['CHART_OF_ACCOUNT']
        );

        if (empty($cols)) {
            $this->command->error('CHART_OF_ACCOUNT: NOT FOUND');
            return;
        }

        $this->command->info('=== CHART_OF_ACCOUNT ===');
        foreach ($cols as $col) {
            $c = array_values((array)$col);
            $this->command->line("  {$c[0]}  ({$c[1]})");
        }

        // Sample row to see real values
        $sample = $oracle->select(
            "SELECT * FROM (SELECT * FROM CHART_OF_ACCOUNT) WHERE ROWNUM <= 2"
        );
        $this->command->line('');
        $this->command->info('Sample rows:');
        foreach ($sample as $row) {
            $r = array_values((array)$row);
            $k = array_keys((array)$row);
            foreach ($k as $i => $key) {
                if ($r[$i]) $this->command->line("  {$key}: {$r[$i]}");
            }
            $this->command->line('---');
        }
    }
}
