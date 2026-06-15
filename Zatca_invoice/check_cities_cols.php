<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cols = DB::connection('oracle')->select(
    "SELECT COLUMN_NAME, DATA_TYPE FROM ALL_TAB_COLUMNS WHERE TABLE_NAME='CITIES' ORDER BY COLUMN_ID"
);
foreach ($cols as $c) {
    echo ($c->column_name ?? $c->COLUMN_NAME) . " - " . ($c->data_type ?? $c->DATA_TYPE) . "\n";
}
