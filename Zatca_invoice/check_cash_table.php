<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$db = DB::connection('oracle');

// Find cash-related tables
$tables = $db->select("SELECT TABLE_NAME FROM ALL_TABLES WHERE TABLE_NAME LIKE '%CASH%' OR TABLE_NAME LIKE '%RECEIPT%' OR TABLE_NAME LIKE '%PAYMENT%' ORDER BY TABLE_NAME");
echo "Tables:\n";
foreach ($tables as $t) echo "  " . ($t->table_name ?? $t->TABLE_NAME) . "\n";

echo "\n";

// Check columns of first cash table found
foreach ($tables as $t) {
    $name = $t->table_name ?? $t->TABLE_NAME;
    echo "=== $name columns ===\n";
    $cols = $db->select("SELECT COLUMN_NAME, DATA_TYPE FROM ALL_TAB_COLUMNS WHERE TABLE_NAME=:t ORDER BY COLUMN_ID", [':t' => $name]);
    foreach ($cols as $c) echo "  " . ($c->column_name ?? $c->COLUMN_NAME) . " (" . ($c->data_type ?? $c->DATA_TYPE) . ")\n";
    echo "\n";
}
