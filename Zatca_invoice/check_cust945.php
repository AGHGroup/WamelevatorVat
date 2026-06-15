<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$pdo = DB::connection('oracle')->getPdo();

$r = $pdo->query("SELECT CUSTOMER_ID, C_ANAME, DISTRICT_ID, STREET_NAME, BUILDING_NO, POSTAL_CODE, ADDRESS FROM CUSTOMERS WHERE CUSTOMER_ID=945")->fetch(PDO::FETCH_ASSOC);
echo json_encode($r, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
