<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$r = DB::connection('oracle')->selectOne(
    'SELECT CUSTOMER_ID, DISTRICT_ID, STREET_NAME, BUILDING_NO, POSTAL_CODE FROM CUSTOMERS WHERE CUSTOMER_ID=945'
);
echo json_encode((array)$r, JSON_UNESCAPED_UNICODE) . "\n";

// Test the JOIN query used in print.blade
if ($r && ($r->district_id ?? $r->DISTRICT_ID)) {
    $distId = $r->district_id ?? $r->DISTRICT_ID;
    $loc = DB::connection('oracle')->selectOne(
        "SELECT d.NAME_AR AS dist_name, c.CITY_NAME, r.NAME_AR AS reg_name
         FROM DISTRICTS d
         LEFT JOIN CITIES  c ON c.CITY_ID   = d.CITY_ID
         LEFT JOIN REGIONS r ON r.REGION_ID = c.REGION_ID
         WHERE d.DISTRICT_ID = :id",
        [':id' => $distId]
    );
    echo "Location: " . json_encode((array)$loc, JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo "DISTRICT_ID is NULL!\n";
}
