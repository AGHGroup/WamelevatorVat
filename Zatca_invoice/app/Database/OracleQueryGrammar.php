<?php

namespace App\Database;

use Illuminate\Database\Query\Grammars\Grammar;

class OracleQueryGrammar extends Grammar
{
    // Don't quote identifiers — Oracle is case-insensitive for unquoted names
    protected string $wrapper = '%s';

    protected function wrapValue($value): string
    {
        return $value !== '*' ? $value : $value;
    }

    // Disable the default LIMIT/OFFSET compilers; we handle them in compileComponents
    public function compileLimit($query, $limit): string  { return ''; }
    public function compileOffset($query, $offset): string { return ''; }

    /**
     * Wrap the full query in Oracle 10g-compatible ROWNUM pagination.
     *
     * Oracle 10g does NOT support FETCH FIRST … ROWS ONLY (requires 12c+).
     * Use the classic double-subquery ROWNUM trick instead.
     */
    protected function compileComponents($query): array
    {
        $sql = parent::compileComponents($query);

        $limit  = $query->limit  ?? null;
        $offset = $query->offset ?? null;

        // Remove empty strings so they don't produce extra spaces
        $sql = array_filter($sql, fn($v) => $v !== '');

        if (! is_null($offset)) {
            // Wrap for offset + limit: SELECT * FROM (SELECT a.*, ROWNUM rn FROM (...) a WHERE ROWNUM <= ?) WHERE rn > ?
            $inner     = implode(' ', $sql);
            $maxRow    = $offset + ($limit ?? PHP_INT_MAX);
            $sql       = ['select' => "SELECT * FROM (SELECT a.*, ROWNUM rn FROM ({$inner}) a WHERE ROWNUM <= {$maxRow}) WHERE rn > {$offset}"];
        } elseif (! is_null($limit)) {
            // Wrap for limit only: SELECT * FROM (...) WHERE ROWNUM <= ?
            $inner = implode(' ', $sql);
            $sql   = ['select' => "SELECT * FROM ({$inner}) WHERE ROWNUM <= {$limit}"];
        }

        return $sql;
    }
}
