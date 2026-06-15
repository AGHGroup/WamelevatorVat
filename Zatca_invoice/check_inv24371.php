<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$row = DB::connection('oracle')->selectOne("
SELECT v.SERIAL, v.SUP_CUST_ACC, v.REF_NO,
  c.CUSTOMER_ID AS c_cust_id,
  d.CUSTOMER_ID AS d_cust_id,
  NVL(cu.CUSTOMER_ID, cu2.CUSTOMER_ID) AS final_cust_id,
  NVL(cu.DISTRICT_ID, cu2.DISTRICT_ID) AS district_id,
  NVL(cu.ADDRESS, cu2.ADDRESS) AS address
FROM VAT_INVOICE v
LEFT JOIN CONST_ORDERS o ON TO_CHAR(o.CONSTRUCT_ID)=TO_CHAR(v.REF_NO)
LEFT JOIN CHART_OF_ACCOUNT c ON c.ACC_NO=o.CUSTOMER_ACC_NO
LEFT JOIN CHART_OF_ACCOUNT d ON d.ACC_NO=v.SUP_CUST_ACC
LEFT JOIN CUSTOMERS cu ON cu.CUSTOMER_ID=c.CUSTOMER_ID
LEFT JOIN CUSTOMERS cu2 ON cu2.CUSTOMER_ID=d.CUSTOMER_ID
WHERE v.SERIAL=24371 AND v.DEL_FLAG=0
");
echo json_encode((array)$row, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
