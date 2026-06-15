<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get a recent invoice serial to test
$pdo = DB::connection('oracle')->getPdo();
$serial = $argv[1] ?? $pdo->query("SELECT MAX(SERIAL) FROM VAT_INVOICE WHERE DEL_FLAG=0")->fetchColumn();
echo "Testing serial: $serial\n\n";

$row = DB::connection('oracle')->selectOne("
    SELECT
        NVL(cu.DISTRICT_ID, cu2.DISTRICT_ID) AS CUST_DISTRICT_ID,
        NVL(cu.STREET_NAME, cu2.STREET_NAME) AS CUST_STREET,
        NVL(cu.BUILDING_NO, cu2.BUILDING_NO) AS CUST_BUILDING_NO,
        NVL(cu.POSTAL_CODE, cu2.POSTAL_CODE) AS CUST_POSTAL,
        NVL(cu.ADDRESS,     cu2.ADDRESS)      AS CUSTOMER_ADDRESS,
        NVL(c.ACC_ANAME,    d.ACC_ANAME)      AS CUSTOMER_NAME,
        NVL(c.CUSTOMER_ID,  d.CUSTOMER_ID)    AS CUST_ID,
        cu.CUSTOMER_ID AS CU_ID, cu2.CUSTOMER_ID AS CU2_ID
    FROM VAT_INVOICE v
    LEFT JOIN CONST_ORDERS     o   ON TO_CHAR(o.CONSTRUCT_ID) = TO_CHAR(v.REF_NO)
    LEFT JOIN CHART_OF_ACCOUNT c   ON c.ACC_NO = o.CUSTOMER_ACC_NO
    LEFT JOIN CHART_OF_ACCOUNT d   ON d.ACC_NO = v.SUP_CUST_ACC
    LEFT JOIN CUSTOMERS        cu  ON cu.CUSTOMER_ID = c.CUSTOMER_ID
    LEFT JOIN CUSTOMERS        cu2 ON cu2.CUSTOMER_ID = d.CUSTOMER_ID
    WHERE v.SERIAL = :serial AND v.DEL_FLAG = 0
", [':serial' => $serial]);

$r = array_change_key_case((array)$row, CASE_LOWER);
echo "customer_name:    " . $r['customer_name']    . "\n";
echo "cust_id:          " . $r['cust_id']           . "\n";
echo "cu_id:            " . $r['cu_id']             . "\n";
echo "cu2_id:           " . $r['cu2_id']            . "\n";
echo "cust_district_id: " . $r['cust_district_id']  . "\n";
echo "cust_street:      " . $r['cust_street']        . "\n";
echo "cust_building_no: " . $r['cust_building_no']   . "\n";
echo "cust_postal:      " . $r['cust_postal']         . "\n";
echo "customer_address: " . $r['customer_address']    . "\n";
