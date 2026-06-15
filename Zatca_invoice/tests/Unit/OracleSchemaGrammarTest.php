<?php

namespace Tests\Unit;

use App\Database\OracleSchemaGrammar;
use PHPUnit\Framework\TestCase;

class OracleSchemaGrammarTest extends TestCase
{
    public function test_compile_tables_uses_safe_aliases_for_oracle(): void
    {
        $grammar = new OracleSchemaGrammar();

        $sql = $grammar->compileTables();

        $this->assertStringContainsString('AS "name"', $sql);
        $this->assertStringContainsString('AS "schema"', $sql);
        $this->assertStringContainsString('AS "size"', $sql);
        $this->assertStringContainsString('AS "comment"', $sql);
        $this->assertStringContainsString('AS "collation"', $sql);
        $this->assertStringContainsString('AS "engine"', $sql);
    }

    public function test_compile_columns_uses_safe_alias_for_default(): void
    {
        $grammar = new OracleSchemaGrammar();

        $sql = $grammar->compileColumns('SCHEMA', 'orders');

        $this->assertStringContainsString('AS "default"', $sql);
    }
}
