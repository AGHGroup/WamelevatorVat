<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$u = DB::connection('oracle')->selectOne(
    "SELECT USER_ID, USER_ANAME, USER_PASSWORD, STATUS, LOCKED, DEL_FLAG FROM USERS WHERE UPPER(USER_ID) = 'ACC_AUDIT3'"
);
if (!$u) {
    echo "User ACC_AUDIT3 not found\n";
    // Try partial match
    $rows = DB::connection('oracle')->select(
        "SELECT USER_ID, USER_ANAME, USER_PASSWORD, STATUS, LOCKED, DEL_FLAG FROM USERS WHERE UPPER(USER_ID) LIKE '%AUDIT%'"
    );
    foreach ($rows as $r) {
        echo json_encode((array)$r, JSON_UNESCAPED_UNICODE) . "\n";
    }
} else {
    echo json_encode((array)$u, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) . "\n";
}
