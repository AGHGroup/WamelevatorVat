<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CheckInvoice914Seeder extends Seeder
{
    public function run(): void
    {
        $oracle = DB::connection('oracle');

        // ── 1. Find the VAT_INVOICE row ────────────────────────────────
        $this->command->info('=== VAT_INVOICE (TR_NO LIKE 914) ===');
        $inv = $oracle->select("
            SELECT SERIAL, TR_NO, REF_NO, TRANS_DATE, REF_VAL, VAT_VAL_C, VAT_NO
            FROM VAT_INVOICE
            WHERE (UPPER(TR_NO) LIKE '%914%' OR UPPER(TR_NO) LIKE '%3230%')
              AND DEL_FLAG = 0
              AND ROWNUM <= 5
        ");
        foreach ($inv as $r) {
            $a = array_values((array)$r);
            $k = array_keys((array)$r);
            foreach ($k as $i => $key) {
                $this->command->line("  {$key}: {$a[$i]}");
            }
            $this->command->line('---');
        }

        if (empty($inv)) {
            $this->command->warn('لم يُعثر على الفاتورة في VAT_INVOICE');
            return;
        }

        $row   = (array) $inv[0];
        $refNo = $row[array_key_first(array_filter($row, fn($v,$k) => stripos($k,'REF_NO') !== false, ARRAY_FILTER_USE_BOTH))]
                 ?? array_values($row)[2]; // REF_NO is 3rd column

        // Safer: extract by key name
        foreach ($row as $k => $v) {
            if (strtoupper($k) === 'REF_NO') { $refNo = $v; break; }
        }

        $this->command->info("REF_NO = [{$refNo}]");

        // ── 2. Check CONST_ORDERS for that REF_NO ─────────────────────
        $this->command->info('=== CONST_ORDERS WHERE CONST_ID = REF_NO ===');
        $orders = $oracle->select("
            SELECT CONST_ID, CUSTOMER_ACC_NO, BASIC_PRICE, VAT_VAL, ADDRESS, NOTES
            FROM CONST_ORDERS
            WHERE TO_CHAR(CONST_ID) = TO_CHAR(:id) AND ROWNUM <= 3
        ", [':id' => $refNo]);

        if (empty($orders)) {
            $this->command->warn("لا يوجد سجل في CONST_ORDERS بـ CONST_ID = {$refNo}");

            // Try different matching
            $this->command->info('--- محاولة LIKE ---');
            $like = $oracle->select("
                SELECT CONST_ID, CUSTOMER_ACC_NO FROM CONST_ORDERS
                WHERE TO_CHAR(CONST_ID) LIKE :pat AND ROWNUM <= 5
            ", [':pat' => '%'.trim($refNo).'%']);
            foreach ($like as $r) {
                $a = array_values((array)$r);
                $this->command->line("  CONST_ID={$a[0]}  CUSTOMER_ACC_NO={$a[1]}");
            }
        } else {
            foreach ($orders as $r) {
                $a = array_values((array)$r); $k = array_keys((array)$r);
                foreach ($k as $i => $key) {
                    if ($a[$i] !== null && $a[$i] !== '') $this->command->line("  {$key}: {$a[$i]}");
                }
                $this->command->line('---');
            }

            // ── 3. Check CONST_ITEMS ────────────────────────────────────
            $this->command->info('=== CONST_ITEMS WHERE CONST_ID = REF_NO ===');
            $items = $oracle->select("
                SELECT ci.ITEM_NO, ci.QTY, ci.UNT_PRICE, ci.DEL_FLAG,
                       i.ITEM_ANAME, i.ITEM_ENAME
                FROM CONST_ITEMS ci
                LEFT JOIN ITEMS i ON i.ITEM_NO = ci.ITEM_NO
                WHERE ci.CONST_ID = :id
            ", [':id' => $refNo]);

            if (empty($items)) {
                $this->command->warn("لا توجد أصناف في CONST_ITEMS لهذا الطلب");

                // Count all items ignoring DEL_FLAG
                $cnt = $oracle->selectOne("
                    SELECT COUNT(*) AS CNT FROM CONST_ITEMS WHERE CONST_ID = :id
                ", [':id' => $refNo]);
                $c = array_values((array)$cnt)[0];
                $this->command->line("  إجمالي الصفوف في CONST_ITEMS (بدون فلتر): {$c}");
            } else {
                foreach ($items as $r) {
                    $a = array_values((array)$r); $k = array_keys((array)$r);
                    foreach ($k as $i => $key) {
                        $this->command->line("  {$key}: {$a[$i]}");
                    }
                    $this->command->line('---');
                }
            }
        }
    }
}
