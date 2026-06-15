<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$db = DB::connection('oracle');

// Get invoice 24371 details
$inv = $db->selectOne("SELECT SERIAL, TR_NO, REF_NO, SUP_CUST_ACC FROM VAT_INVOICE WHERE SERIAL=24371 AND DEL_FLAG=0");
echo "Invoice: " . json_encode((array)$inv, JSON_UNESCAPED_UNICODE) . "\n\n";

$trNo  = $inv->tr_no  ?? $inv->TR_NO  ?? '';
$refNo = $inv->ref_no ?? $inv->REF_NO ?? '';

// Try link via INVOICE_NO = TR_NO
$cash1 = $db->select("SELECT SERIAL, CONTRACT_NO, ACC_NO, TO_MR, VOUCHER_NO, REF_NO, INVOICE_NO, DEL_FLAG FROM CASH WHERE INVOICE_NO=:no AND DEL_FLAG=0", [':no' => $trNo]);
echo "CASH via INVOICE_NO=$trNo:\n";
foreach ($cash1 as $r) echo "  " . json_encode((array)$r, JSON_UNESCAPED_UNICODE) . "\n";

// Try link via REF_NO
$cash2 = $db->select("SELECT SERIAL, CONTRACT_NO, ACC_NO, TO_MR, VOUCHER_NO, REF_NO, INVOICE_NO, DEL_FLAG FROM CASH WHERE REF_NO=:no AND DEL_FLAG=0", [':no' => $refNo]);
echo "\nCASH via REF_NO=$refNo:\n";
foreach ($cash2 as $r) echo "  " . json_encode((array)$r, JSON_UNESCAPED_UNICODE) . "\n";

// Also check CONST_PAYMENTS for this contract
$pays = $db->select("SELECT * FROM CONST_PAYMENTS WHERE CONSTRUCT_ID=:id", [':id' => $refNo]);
echo "\nCONST_PAYMENTS for CONSTRUCT_ID=$refNo:\n";
foreach ($pays as $r) echo "  " . json_encode((array)$r, JSON_UNESCAPED_UNICODE) . "\n";
