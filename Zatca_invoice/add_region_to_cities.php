<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$db = DB::connection('oracle');

// 1. Add REGION_ID column
try {
    $db->statement("ALTER TABLE CITIES ADD REGION_ID NUMBER");
    echo "Added REGION_ID column to CITIES\n";
} catch (\Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// 2. Show existing regions
$regions = $db->select("SELECT REGION_ID, NAME_AR FROM REGIONS ORDER BY NAME_AR");
echo "\nRegions:\n";
foreach ($regions as $r) {
    echo "  " . ($r->region_id ?? $r->REGION_ID) . " - " . ($r->name_ar ?? $r->NAME_AR) . "\n";
}

// 3. Show existing cities
$cities = $db->select("SELECT CITY_ID, CITY_NAME FROM CITIES WHERE DEL_FLAG=0 ORDER BY CITY_NAME");
echo "\nCities:\n";
foreach ($cities as $c) {
    echo "  " . ($c->city_id ?? $c->CITY_ID) . " - " . ($c->city_name ?? $c->CITY_NAME) . "\n";
}
