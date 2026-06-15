<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$pdo = DB::connection('oracle')->getPdo();

$exists = $pdo->query("SELECT COUNT(*) FROM DISTRICTS WHERE CITY_ID=1 AND NAME_AR='حي السلامة'")->fetchColumn();
if ($exists) { echo "Already exists.\n"; exit; }

$maxId = (int) $pdo->query("SELECT NVL(MAX(TO_NUMBER(DISTRICT_ID)),20000000000) FROM DISTRICTS")->fetchColumn();
$pdo->prepare("INSERT INTO DISTRICTS (DISTRICT_ID, CITY_ID, NAME_AR, NAME_EN) VALUES (:id, 1, 'حي السلامة', 'Al-Salamah Dist.')")
    ->execute([':id' => $maxId + 1]);

echo "Added حي السلامة to Jeddah. ID=" . ($maxId+1) . "\n";
