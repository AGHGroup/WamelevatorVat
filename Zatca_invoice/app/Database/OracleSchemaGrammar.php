<?php

namespace App\Database;

use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Support\Fluent;

class OracleSchemaGrammar extends Grammar
{
    protected $modifiers = ['Nullable', 'Default'];

    // ── Introspection ─────────────────────────────────────────────────────────

    public function compileTables(string $schema = ''): string
    {
        return "SELECT LOWER(table_name) AS name, NULL AS schema, NULL AS size,
                       NULL AS comment, NULL AS collation, NULL AS engine
                FROM user_tables ORDER BY table_name";
    }

    public function compileColumns(string $schema, string $table): string
    {
        return "SELECT LOWER(column_name) AS name, data_type AS type_name,
                       data_length AS length, data_precision AS precision,
                       data_scale AS places, nullable AS nullable,
                       data_default AS default
                FROM user_tab_columns
                WHERE table_name = '" . strtoupper($table) . "'
                ORDER BY column_id";
    }

    public function compileIndexes(string $schema, string $table): string
    {
        return "SELECT LOWER(i.index_name) AS name,
                       LOWER(LISTAGG(ic.column_name, ',') WITHIN GROUP (ORDER BY ic.column_position)) AS columns,
                       CASE i.uniqueness WHEN 'UNIQUE' THEN 1 ELSE 0 END AS unique,
                       CASE WHEN c.constraint_type = 'P' THEN 1 ELSE 0 END AS primary
                FROM user_indexes i
                JOIN user_ind_columns ic ON ic.index_name = i.index_name
                LEFT JOIN user_constraints c ON c.index_name = i.index_name AND c.constraint_type = 'P'
                WHERE i.table_name = '" . strtoupper($table) . "'
                GROUP BY i.index_name, i.uniqueness, c.constraint_type";
    }

    public function compileForeignKeys(string $schema, string $table): string
    {
        return "SELECT LOWER(a.constraint_name) AS name,
                       LOWER(LISTAGG(ac.column_name, ',') WITHIN GROUP (ORDER BY ac.position)) AS columns,
                       LOWER(b.table_name) AS foreign_table,
                       LOWER(LISTAGG(bc.column_name, ',') WITHIN GROUP (ORDER BY bc.position)) AS foreign_columns,
                       'NO ACTION' AS on_update, 'NO ACTION' AS on_delete
                FROM user_constraints a
                JOIN user_cons_columns ac ON ac.constraint_name = a.constraint_name
                JOIN user_constraints b  ON b.constraint_name  = a.r_constraint_name
                JOIN user_cons_columns bc ON bc.constraint_name = b.constraint_name
                WHERE a.constraint_type = 'R'
                  AND a.table_name = '" . strtoupper($table) . "'
                GROUP BY a.constraint_name, b.table_name";
    }

    // ── DDL ───────────────────────────────────────────────────────────────────

    public function compileCreate(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $command): string
    {
        $columns = implode(', ', $this->getColumns($blueprint));
        return "CREATE TABLE {$blueprint->getTable()} ({$columns})";
    }

    public function compileAdd(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $command): string
    {
        $columns = implode(', ', $this->prefixArray('ADD', $this->getColumns($blueprint)));
        return "ALTER TABLE {$blueprint->getTable()} {$columns}";
    }

    public function compilePrimary(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $command): string
    {
        return "ALTER TABLE {$blueprint->getTable()} ADD PRIMARY KEY ({$this->columnize($command->columns)})";
    }

    public function compileUnique(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $command): string
    {
        return "ALTER TABLE {$blueprint->getTable()} ADD CONSTRAINT {$command->index} UNIQUE ({$this->columnize($command->columns)})";
    }

    public function compileIndex(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $command): string
    {
        return "CREATE INDEX {$command->index} ON {$blueprint->getTable()} ({$this->columnize($command->columns)})";
    }

    public function compileDrop(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $command): string
    {
        return "DROP TABLE {$blueprint->getTable()}";
    }

    public function compileDropIfExists(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $command): string
    {
        return "BEGIN EXECUTE IMMEDIATE 'DROP TABLE {$blueprint->getTable()}'; EXCEPTION WHEN OTHERS THEN NULL; END;";
    }

    public function compileDropColumn(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $command): string
    {
        return "ALTER TABLE {$blueprint->getTable()} DROP COLUMN {$this->columnize($command->columns)}";
    }

    public function compileTableExists(): string
    {
        return "SELECT COUNT(*) FROM user_tables WHERE table_name = UPPER(?)";
    }

    public function compileColumnListing(): string
    {
        return "SELECT LOWER(column_name) AS column_name FROM user_tab_columns WHERE table_name = UPPER(?)";
    }

    // ── Column type mapping ────────────────────────────────────────────────────

    protected function typeChar(Fluent $column): string       { return "VARCHAR2({$column->length})"; }
    protected function typeString(Fluent $column): string     { return "VARCHAR2({$column->length})"; }
    protected function typeText(Fluent $column): string       { return 'CLOB'; }
    protected function typeMediumText(Fluent $column): string { return 'CLOB'; }
    protected function typeLongText(Fluent $column): string   { return 'CLOB'; }
    protected function typeInteger(Fluent $column): string    { return 'NUMBER(10)'; }
    protected function typeBigInteger(Fluent $column): string { return 'NUMBER(19)'; }
    protected function typeSmallInteger(Fluent $column): string { return 'NUMBER(5)'; }
    protected function typeTinyInteger(Fluent $column): string  { return 'NUMBER(3)'; }
    protected function typeFloat(Fluent $column): string      { return 'FLOAT'; }
    protected function typeDouble(Fluent $column): string     { return 'FLOAT'; }
    protected function typeDecimal(Fluent $column): string    { return "NUMBER({$column->total},{$column->places})"; }
    protected function typeBoolean(Fluent $column): string    { return 'NUMBER(1)'; }
    protected function typeDate(Fluent $column): string       { return 'DATE'; }
    protected function typeDateTime(Fluent $column): string   { return 'TIMESTAMP'; }
    protected function typeTimestamp(Fluent $column): string  { return 'TIMESTAMP'; }
    protected function typeTime(Fluent $column): string       { return 'VARCHAR2(8)'; }
    protected function typeJson(Fluent $column): string       { return 'CLOB'; }
    protected function typeJsonb(Fluent $column): string      { return 'CLOB'; }
    protected function typeBinary(Fluent $column): string     { return 'BLOB'; }
    protected function typeUuid(Fluent $column): string       { return 'VARCHAR2(36)'; }
    protected function typeEnum(Fluent $column): string
    {
        $allowed = implode("', '", $column->allowed);
        return "VARCHAR2(255) CHECK ({$column->name} IN ('{$allowed}'))";
    }

    // ── Column modifiers ──────────────────────────────────────────────────────

    protected function modifyNullable(
        \Illuminate\Database\Schema\Blueprint $blueprint,
        Fluent $column
    ): string {
        return $column->nullable ? ' NULL' : ' NOT NULL';
    }

    protected function modifyDefault(
        \Illuminate\Database\Schema\Blueprint $blueprint,
        Fluent $column
    ): string {
        return ! is_null($column->default)
            ? " DEFAULT {$this->getDefaultValue($column->default)}"
            : '';
    }
}
