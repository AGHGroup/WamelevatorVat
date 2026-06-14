<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CheckCustomersSeeder extends Seeder
{
    public function run(): void
    {
        $oracle = DB::connection('oracle');

        // Columns of CUSTOMERS table
        $cols = $oracle->select(
            'SELECT column_name, data_type, data_length, nullable FROM user_tab_columns WHERE table_name = ? ORDER BY column_id',
            ['CUSTOMERS']
        );

        if (empty($cols)) {
            $this->command->error('Table CUSTOMERS not found in Oracle schema.');
            return;
        }

        $first = array_keys((array) $cols[0]);
        [$cName, $cType, $cLen, $cNull] = $first;

        $this->command->info(str_pad('COLUMN', 30) . str_pad('TYPE', 20) . str_pad('LENGTH', 10) . 'NULLABLE');
        $this->command->info(str_repeat('-', 70));
        foreach ($cols as $col) {
            $row = (array) $col;
            $this->command->info(
                str_pad($row[$cName], 30) .
                str_pad($row[$cType], 20) .
                str_pad($row[$cLen],  10) .
                $row[$cNull]
            );
        }

        // Check which of our new columns already exist
        $existing  = array_map(fn($c) => strtolower(((array)$c)[$cName]), $cols);
        $newFields = ['postal_code', 'building_no', 'district_id', 'vat_number', 'id_number', 'street_name'];

        $this->command->info('');
        $this->command->info('=== New field availability ===');
        foreach ($newFields as $f) {
            if (in_array($f, $existing, true)) {
                $this->command->warn("  {$f}: ALREADY EXISTS — ALTER will be skipped.");
            } else {
                $this->command->info("  {$f}: available (not yet added).");
            }
        }

        // Also check FK target: districts table
        $dist = $oracle->select("SELECT COUNT(*) AS CNT FROM user_tables WHERE table_name = 'DISTRICTS'");
        $distExists = ((array) $dist[0])[array_key_first((array) $dist[0])] > 0;
        $this->command->info('');
        $this->command->info('  districts table: ' . ($distExists ? 'EXISTS ✓' : 'NOT FOUND — FK will fail'));
    }
}
