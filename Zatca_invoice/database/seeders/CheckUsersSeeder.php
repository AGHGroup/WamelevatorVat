<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CheckUsersSeeder extends Seeder
{
    public function run(): void
    {
        $oracle = DB::connection('oracle');

        // Find any table that looks like a users/operators table
        $tables = $oracle->select(
            "SELECT table_name FROM user_tables WHERE table_name LIKE '%USER%' OR table_name LIKE '%OPER%' OR table_name LIKE '%LOGIN%' OR table_name LIKE '%STAFF%' OR table_name LIKE '%EMPLOYEE%' ORDER BY table_name"
        );

        $prop = array_key_first((array) $tables[0] ?? ['TABLE_NAME' => null]);

        $this->command->info('=== Tables matching user/operator/login ===');
        foreach ($tables as $t) {
            $name = ((array)$t)[$prop];
            $this->command->info("  {$name}");

            // Show columns for each
            $cols = $oracle->select(
                'SELECT column_name, data_type, data_length FROM user_tab_columns WHERE table_name = ? ORDER BY column_id',
                [$name]
            );
            $cprop = array_key_first((array) ($cols[0] ?? new \stdClass()));
            $tprop = array_keys((array) ($cols[0] ?? new \stdClass()))[1] ?? $cprop;
            foreach ($cols as $col) {
                $c = (array) $col;
                $keys = array_keys($c);
                $this->command->line("       ↳ {$c[$keys[0]]}  ({$c[$keys[1]]})");
            }
        }
    }
}
