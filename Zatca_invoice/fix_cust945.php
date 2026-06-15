<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// First check current data
$c = DB::connection('oracle')->selectOne(
    "SELECT CUSTOMER_ID, C_ANAME, DISTRICT_ID, ADDRESS, STREET_NAME, BUILDING_NO, POSTAL_CODE FROM CUSTOMERS WHERE CUSTOMER_ID = :id",
    [':id' => 945]
);
echo "Before:\n";
echo json_encode((array)$c, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) . "\n\n";

// Find حي السلامة district_id (city_id=1 = Jeddah)
$dist = DB::connection('oracle')->selectOne(
    "SELECT DISTRICT_ID, NAME_AR, CITY_ID FROM DISTRICTS WHERE NAME_AR LIKE '%السلامة%' AND CITY_ID = 1"
);
echo "District found: ";
echo json_encode((array)$dist, JSON_UNESCAPED_UNICODE) . "\n\n";

if ($dist) {
    $distId = $dist->district_id ?? $dist->DISTRICT_ID;
    DB::connection('oracle')->statement(
        "UPDATE CUSTOMERS SET DISTRICT_ID = :dist, L_U_DATE = SYSDATE WHERE CUSTOMER_ID = :cid AND DEL_FLAG = 0",
        [':dist' => $distId, ':cid' => 945]
    );
    echo "Updated customer 945 with DISTRICT_ID = $distId\n";
} else {
    echo "District not found! List available districts for Jeddah:\n";
    $rows = DB::connection('oracle')->select(
        "SELECT DISTRICT_ID, NAME_AR FROM DISTRICTS WHERE CITY_ID = 1 AND NAME_AR LIKE '%سلام%'"
    );
    foreach ($rows as $r) {
        echo "  " . ($r->district_id ?? $r->DISTRICT_ID) . " - " . ($r->name_ar ?? $r->NAME_AR) . "\n";
    }
}
