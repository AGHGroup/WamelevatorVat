<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LCETablesSeeder extends Seeder
{
    public function run(): void
    {
        // Step 1: fetch actual Oracle columns for each table
        $tables     = ['regions', 'cities', 'districts'];
        $oracleCols = [];

        $this->command->info('=== Oracle table columns ===');
        foreach ($tables as $tbl) {
            $rows = DB::connection('oracle')
                ->select(
                    'SELECT column_name FROM user_tab_columns WHERE table_name = ? ORDER BY column_id',
                    [strtoupper($tbl)]
                );

            if (empty($rows)) {
                $this->command->error("  {$tbl}: table NOT FOUND — skipping.");
                $oracleCols[$tbl] = null;
            } else {
                $first = (array) $rows[0];
                $prop  = array_key_first($first); // pdo_oci returns uppercase props
                $cols  = array_map(fn($r) => strtolower(((array)$r)[$prop]), $rows);
                $oracleCols[$tbl] = $cols;
                $this->command->info("  {$tbl}: " . implode(', ', $cols));
            }
        }
        $this->command->info('============================');

        // Step 2: insert data, using only columns that exist in Oracle
        $sqlDir = 'C:\\Users\\AGH-DEV\\Desktop\\location';
        $files  = [
            'regions'   => $sqlDir . '\\regions_lite.sql',
            'cities'    => $sqlDir . '\\cities_lite.sql',
            'districts' => $sqlDir . '\\districts_lite.sql',
        ];

        foreach ($files as $targetTable => $file) {
            $actualCols = $oracleCols[$targetTable] ?? null;
            if ($actualCols === null) {
                $this->command->warn("Skipping {$file} — no Oracle table found.");
                continue;
            }

            $raw = file_get_contents($file);
            $raw = str_replace("\\'", "''", $raw); // MySQL \' → Oracle ''

            // Parse the file character-by-character to extract every
            // "into TABLE (cols) values (vals)" block, respecting quoted strings
            // so parentheses inside string literals don't confuse the parser.
            $rows = $this->parseInsertAll($raw);

            $count = 0;
            foreach ($rows as $row) {
                // Keep only columns that actually exist in the Oracle table
                $keepIdx  = [];
                $keepCols = [];
                foreach ($row['cols'] as $i => $col) {
                    if (in_array(strtolower($col), $actualCols, true)) {
                        $keepIdx[]  = $i;
                        $keepCols[] = strtoupper($col);
                    }
                }

                if (empty($keepCols)) {
                    continue;
                }

                if (count($keepIdx) === count($row['cols'])) {
                    $filteredVals = '(' . implode(', ', $row['vals']) . ')';
                } else {
                    $picked       = array_map(fn($i) => $row['vals'][$i] ?? 'NULL', $keepIdx);
                    $filteredVals = '(' . implode(', ', $picked) . ')';
                }

                $colList = '(' . implode(', ', $keepCols) . ')';
                $plsql   = "BEGIN INSERT INTO {$targetTable} {$colList} VALUES {$filteredVals}; "
                         . "EXCEPTION WHEN DUP_VAL_ON_INDEX THEN NULL; END;";

                DB::connection('oracle')->unprepared($plsql);
                $count++;
            }

            $this->command->info("Seeded {$count} rows into {$targetTable}.");
        }
    }

    /**
     * Parse an INSERT ALL block and return an array of rows, each with:
     *   ['cols' => [...], 'vals' => [...]]
     *
     * Uses a character-by-character scan so parentheses and commas inside
     * single-quoted strings are never mistaken for delimiters.
     */
    private function parseInsertAll(string $sql): array
    {
        $rows = [];
        // Use byte-length only — Arabic UTF-8 bytes are all >= 0x80,
        // so ASCII delimiters ('  ( ) ,) never appear inside multi-byte chars.
        $len  = strlen($sql);
        $i    = 0;

        while ($i < $len) {
            // Look for "into" keyword (byte-safe, case-insensitive)
            if (strtolower(substr($sql, $i, 4)) === 'into') {
                $i += 4;

                // Skip whitespace + table name
                while ($i < $len && ctype_space($sql[$i])) $i++;
                while ($i < $len && !ctype_space($sql[$i]) && $sql[$i] !== '(') $i++;

                // Extract column list (...)
                $cols = $this->extractParenBlock($sql, $i, $len);
                if ($cols === null) { $i++; continue; }
                $i = $cols['end'];

                // Skip "values" keyword and whitespace
                while ($i < $len && ctype_space($sql[$i])) $i++;
                if (strtolower(substr($sql, $i, 6)) === 'values') $i += 6;
                while ($i < $len && ctype_space($sql[$i])) $i++;

                // Extract values list (...)
                $vals = $this->extractParenBlock($sql, $i, $len);
                if ($vals === null) { $i++; continue; }
                $i = $vals['end'];

                $rows[] = [
                    'cols' => array_map('trim', $this->splitTokens($cols['inner'])),
                    'vals' => $this->splitTokens($vals['inner']),
                ];
            } else {
                $i++;
            }
        }

        return $rows;
    }

    /**
     * Extract the content of a parenthesised block starting at $pos,
     * respecting nested parens and single-quoted strings.
     * Returns ['inner' => '...', 'end' => <position after closing paren>] or null.
     */
    private function extractParenBlock(string $sql, int $pos, int $len): ?array
    {
        while ($pos < $len && $sql[$pos] !== '(') $pos++;
        if ($pos >= $len) return null;

        $pos++;   // skip opening '('
        $depth   = 1;
        $inner   = '';
        $inQuote = false;

        while ($pos < $len && $depth > 0) {
            $ch = $sql[$pos];

            if ($inQuote) {
                $inner .= $ch;
                if ($ch === "'") {
                    // Oracle '' escape
                    if ($pos + 1 < $len && $sql[$pos + 1] === "'") {
                        $inner .= $sql[++$pos];
                    } else {
                        $inQuote = false;
                    }
                }
            } else {
                if ($ch === "'") {
                    $inQuote = true;
                    $inner  .= $ch;
                } elseif ($ch === '(') {
                    $depth++;
                    $inner .= $ch;
                } elseif ($ch === ')') {
                    $depth--;
                    if ($depth > 0) $inner .= $ch;
                } else {
                    $inner .= $ch;
                }
            }

            $pos++;
        }

        return ['inner' => $inner, 'end' => $pos];
    }

    /**
     * Split a comma-separated token string respecting single-quoted strings
     * and nested parentheses.
     */
    private function splitTokens(string $inner): array
    {
        $tokens  = [];
        $current = '';
        $inQuote = false;
        $depth   = 0;
        $len     = strlen($inner);

        for ($i = 0; $i < $len; $i++) {
            $ch = $inner[$i];

            if ($inQuote) {
                $current .= $ch;
                if ($ch === "'") {
                    if ($i + 1 < $len && $inner[$i + 1] === "'") {
                        $current .= $inner[++$i];
                    } else {
                        $inQuote = false;
                    }
                }
            } elseif ($ch === "'") {
                $inQuote  = true;
                $current .= $ch;
            } elseif ($ch === '(') {
                $depth++;
                $current .= $ch;
            } elseif ($ch === ')') {
                $depth--;
                $current .= $ch;
            } elseif ($ch === ',' && $depth === 0) {
                $tokens[]  = trim($current);
                $current   = '';
            } else {
                $current .= $ch;
            }
        }

        if (trim($current) !== '') {
            $tokens[] = trim($current);
        }

        return $tokens;
    }
}
