<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$r = DB::connection('oracle')->selectOne('SELECT CITY_ID, CITY_NAME, REGION_ID FROM CITIES WHERE CITY_ID=1');
echo json_encode((array)$r, JSON_UNESCAPED_UNICODE) . "\n";

// Also check what regions exist
$regions = DB::connection('oracle')->select('SELECT REGION_ID, NAME_AR FROM REGIONS ORDER BY NAME_AR');
echo "Regions:\n";
foreach ($regions as $reg) {
    echo "  " . json_encode((array)$reg, JSON_UNESCAPED_UNICODE) . "\n";
}
