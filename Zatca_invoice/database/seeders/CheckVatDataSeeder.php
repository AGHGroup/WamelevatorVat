<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CheckVatDataSeeder extends Seeder
{
    public function run(): void
    {
        $oracle = DB::connection('oracle');

        // ── 1. Sample VAT_INVOICE with all VAT-related fields ─────────────
        $this->command->info('=== VAT_INVOICE sample (VAT fields) ===');
        $inv = $oracle->select("
            SELECT SERIAL, TR_NO, TRANS_DATE, REF_NO, VAT_NO,
                   SUP_CUST_ACC, F_ACC_NO, REF_VAL, VAT_VAL_C, DESCRIPTION
            FROM (SELECT * FROM VAT_INVOICE WHERE DEL_FLAG = 0 AND REF_NO IS NOT NULL)
            WHERE ROWNUM <= 3
        ");
        foreach ($inv as $row) {
            foreach ((array)$row as $k => $v) {
                if ($v !== null && $v !== '') $this->command->line("  {$k}: {$v}");
            }
            $this->command->line('---');
        }

        // ── 2. CONST_ORDERS linked via REF_NO → check VAT fields + address ─
        $this->command->info('=== CONST_ORDERS VAT-related fields ===');
        if (!empty($inv)) {
            $refNo = array_values((array)$inv[0])[ array_search('REF_NO', array_keys((array)$inv[0])) ];
            $order = $oracle->select("
                SELECT CONST_ID, CUSTOMER_ACC_NO, ADDRESS, BASIC_PRICE,
                       VAT_VAL, VAT, DISCOUNT, DIS_VAL, NOTES
                FROM CONST_ORDERS WHERE CONST_ID = :id AND ROWNUM <= 1
            ", [':id' => $refNo]);
            if (!empty($order)) {
                foreach ((array)$order[0] as $k => $v) {
                    if ($v !== null && $v !== '') $this->command->line("  {$k}: {$v}");
                }
            } else {
                $this->command->warn("  No CONST_ORDERS row found for REF_NO={$refNo}");
            }
        }
        $this->command->line('');

        // ── 3. CHART_OF_ACCOUNT VAT fields for customer ────────────────────
        $this->command->info('=== CHART_OF_ACCOUNT — VAT-related columns ===');
        $coa = $oracle->select("
            SELECT column_name, data_type
            FROM user_tab_columns
            WHERE table_name = 'CHART_OF_ACCOUNT'
              AND (UPPER(column_name) LIKE '%VAT%'
                OR UPPER(column_name) LIKE '%TAX%'
                OR UPPER(column_name) LIKE '%CR%'
                OR UPPER(column_name) LIKE '%REG%'
                OR UPPER(column_name) LIKE '%NAME%'
                OR UPPER(column_name) LIKE '%ANAME%'
                OR UPPER(column_name) LIKE '%ENAME%')
            ORDER BY column_id
        ");
        foreach ($coa as $col) {
            $c = array_values((array)$col);
            $this->command->line("  {$c[0]}  ({$c[1]})");
        }
        $this->command->line('');

        // ── 4. Check CONST_ITEMS + ITEMS for a sample order ────────────────
        $this->command->info('=== CONST_ITEMS + ITEMS sample ===');
        if (!empty($inv)) {
            $refNo = array_values((array)$inv[0])[ array_search('REF_NO', array_keys((array)$inv[0])) ];
            $items = $oracle->select("
                SELECT ci.ITEM_NO, ci.QTY, ci.UNT_PRICE,
                       i.ITEM_ANAME, i.ITEM_ENAME, i.SALE_PRICE
                FROM CONST_ITEMS ci
                LEFT JOIN ITEMS i ON i.ITEM_NO = ci.ITEM_NO
                WHERE ci.CONST_ID = :id AND ci.DEL_FLAG = 0
                  AND ROWNUM <= 5
            ", [':id' => $refNo]);
            if (empty($items)) {
                $this->command->warn("  No items found for CONST_ID={$refNo}");
            }
            foreach ($items as $item) {
                foreach ((array)$item as $k => $v) {
                    if ($v !== null && $v !== '') $this->command->line("  {$k}: {$v}");
                }
                $this->command->line('---');
            }
        }

        // ── 5. Verify ZATCA Phase 1 QR fields availability ─────────────────
        $this->command->info('=== ZATCA Phase 1 QR fields check ===');
        $this->command->line('  Tag 1 - Seller Name    : شركة عبد الغني حسين حامد للمصاعد (ثابت)');
        $this->command->line('  Tag 2 - Seller VAT No  : 311744595500003 (ثابت)');
        $this->command->line('  Tag 3 - Invoice Date   : VAT_INVOICE.TRANS_DATE');
        $this->command->line('  Tag 4 - Total with VAT : VAT_INVOICE.REF_VAL + VAT_VAL_C');
        $this->command->line('  Tag 5 - VAT Amount     : VAT_INVOICE.VAT_VAL_C');
        $this->command->line('');
        $this->command->line('  Customer VAT No (للعرض فقط): VAT_INVOICE.VAT_NO');
    }
}
