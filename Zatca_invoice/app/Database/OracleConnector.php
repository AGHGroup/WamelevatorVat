<?php

namespace App\Database;

use PDO;
use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;

class OracleConnector extends Connector implements ConnectorInterface
{
    public function connect(array $config): PDO
    {
        $dsn = $this->getDsn($config);
        $options = $this->getOptions($config);
        return $this->createConnection($dsn, $config, $options);
    }

    protected function getDsn(array $config): string
    {
        if (!empty($config['tns'])) {
            return 'oci:dbname=' . $config['tns'];
        }

        $host = $config['host'];
        $port = $config['port'] ?? 1521;
        $database = $config['database'];

        return "oci:dbname=(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST={$host})(PORT={$port}))(CONNECT_DATA=(SID={$database})))";
    }
}
