<?php

namespace App\Database;

use Illuminate\Database\Query\Processors\Processor;

class OracleProcessor extends Processor
{
    // Oracle returns column names in UPPERCASE — normalize to lowercase so Laravel internals work
    public function processSelect($query, $results): array
    {
        return array_map(
            fn($row) => (object) array_change_key_case((array) $row, CASE_LOWER),
            $results
        );
    }

    public function processTables($results): array
    {
        return array_map(function ($result) {
            $row = array_change_key_case((array) $result, CASE_LOWER);

            return [
                'name'      => $row['name']      ?? null,
                'schema'    => $row['schema']    ?? null,
                'size'      => isset($row['size']) ? (int) $row['size'] : null,
                'comment'   => $row['comment']   ?? null,
                'collation' => $row['collation'] ?? null,
                'engine'    => $row['engine']    ?? null,
            ];
        }, $results);
    }
}
