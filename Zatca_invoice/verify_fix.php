<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pdo = DB::connection('oracle')->getPdo();

echo "=== MIGRATIONS table columns ===\n";
$cols = $pdo->query("SELECT column_name FROM user_tab_columns WHERE table_name='MIGRATIONS' ORDER BY column_id")->fetchAll(PDO::FETCH_COLUMN);
foreach ($cols as $c) echo "  $c\n";

echo "\n=== Rows ===\n";
$rows = $pdo->query("SELECT ID, MIGRATION, BATCH FROM MIGRATIONS ORDER BY ID")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  {$r['ID']} | {$r['MIGRATION']} | batch={$r['BATCH']}\n";

// Drop and recreate CLEAN if columns are wrong
$colNames = implode(',', $cols);
if (!in_array('MIGRATION', $cols) || !in_array('BATCH', $cols)) {
    echo "\n=== Fixing table (wrong columns: $colNames) ===\n";
    try { $pdo->exec("DROP TABLE MIGRATIONS"); } catch(\Exception $e){}

    $pdo->exec("CREATE TABLE MIGRATIONS (
        ID        NUMBER(10)    NOT NULL,
        MIGRATION VARCHAR2(255) NOT NULL,
        BATCH     NUMBER(10)    NOT NULL
    )");
    $fakes = [
        '2014_10_12_000000_create_users_table',
        '2014_10_12_100000_create_password_reset_tokens_table',
        '2019_08_19_000000_create_failed_jobs_table',
        '2019_12_14_000001_create_personal_access_tokens_table',
    ];
    $i = 1;
    foreach ($fakes as $m) {
        $pdo->exec("INSERT INTO MIGRATIONS (ID,MIGRATION,BATCH) VALUES ($i,'$m',1)");
        $i++;
    }
    echo "Fixed!\n";
} else {
    echo "\nTable looks CORRECT ($colNames)\n";
}
