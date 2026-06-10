<?php

namespace App\Database;

use Illuminate\Database\Query\Grammars\Grammar;

class OracleQueryGrammar extends Grammar
{
    protected string $wrapper = '%s';

    public function compileLimit($query, $limit): string
    {
        return '';
    }

    public function compileOffset($query, $offset): string
    {
        return '';
    }

    protected function compileComponents($query): array
    {
        $sql = parent::compileComponents($query);

        $limit = $query->limit ?? null;
        $offset = $query->offset ?? null;

        if (!is_null($offset)) {
            $sql['limit'] = "offset {$offset} rows fetch next " . ($limit ?? 10) . " rows only";
        } elseif (!is_null($limit)) {
            $sql['limit'] = "fetch first {$limit} rows only";
        }

        return $sql;
    }
}
