<?php

namespace App\Database;

use Illuminate\Database\Connection;

class OracleConnection extends Connection
{
    public function getDriverName(): string
    {
        return 'oracle';
    }

    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new OracleQueryGrammar());
    }

    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new OracleSchemaGrammar());
    }

    protected function getDefaultPostProcessor()
    {
        return new OracleProcessor();
    }
}
