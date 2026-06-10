<?php

namespace App\Database;

use PDO;
use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;

class OracleConnector extends Connector implements ConnectorInterface
{
    public function connect(array $config): PDO
    {
        putenv('NLS_LANG=ARABIC_SAUDI ARABIA.AL32UTF8');

        $dsn = $this->getDsn($config);
        $options = $this->getOptions($config);
        return $this->createConnection($dsn, $config, $options);
    }

    protected function getDsn(array $config): string
    {
        $charset = $config['charset'] ?? 'AL32UTF8';

        if (!empty($config['tns'])) {
            return "oci:dbname={$config['tns']};charset={$charset}";
        }

        $host     = $config['host'];
        $port     = $config['port'] ?? 1521;
        $database = $config['database'];

        return "oci:dbname=(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST={$host})(PORT={$port}))(CONNECT_DATA=(SID={$database})));charset={$charset}";
    }
}
