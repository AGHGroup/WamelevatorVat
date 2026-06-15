<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = DB::connection('oracle')->select(
    "SELECT USER_ID, USER_ANAME, STATUS, LOCKED, DEL_FLAG FROM USERS WHERE DEL_FLAG=0 OR DEL_FLAG IS NULL ORDER BY USER_ID"
);
foreach ($users as $u) {
    echo sprintf("%-15s %-20s STATUS=%-4s LOCKED=%-4s DEL_FLAG=%s\n",
        $u->user_id ?? '',
        $u->user_aname ?? '',
        $u->status  ?? 'NULL',
        $u->locked  ?? 'NULL',
        $u->del_flag ?? 'NULL'
    );
}
