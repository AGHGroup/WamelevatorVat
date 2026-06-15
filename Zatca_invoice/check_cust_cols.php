<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$pdo = DB::connection('oracle')->getPdo();
$cols = $pdo->query("SELECT column_name FROM user_tab_columns WHERE table_name='CUSTOMERS' ORDER BY column_id")->fetchAll(PDO::FETCH_COLUMN);
echo implode(', ', $cols) . "\n";
