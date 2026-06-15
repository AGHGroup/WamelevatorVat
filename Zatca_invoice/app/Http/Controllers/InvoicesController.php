<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoicesController extends Controller
{
    private function buildQuery(Request $request): array
    {
        $vatId   = $request->input('vat_id');
        $search  = trim($request->input('search', ''));
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $sortCol  = $request->input('sort', 'SERIAL');
        $sortDir  = strtoupper($request->input('dir', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $allowed = ['SERIAL','TR_NO','TRANS_DATE','VAT_NAME','REF_NO','REF_VAL','VAT_VAL_D','VAT_VAL_C','VAT_NET'];
        if (! in_array(strtoupper($sortCol), $allowed)) $sortCol = 'SERIAL';

        $where  = "i.DEL_FLAG = 0 AND NVL(i.TRANS_DATE, i.C_DATE) < TO_DATE('2024-10-01','YYYY-MM-DD')";
        $params = [];

        if ($vatId) {
            $where .= " AND i.VAT_ID = :vat_id";
            $params[':vat_id'] = $vatId;
        }
        if ($search) {
            $like = '%' . strtoupper($search) . '%';
            $where .= " AND (UPPER(i.TR_NO) LIKE :s1 OR UPPER(i.REF_NO) LIKE :s2 OR UPPER(i.DESCRIPTION) LIKE :s3)";
            $params[':s1'] = $like;
            $params[':s2'] = $like;
            $params[':s3'] = $like;
        }
        if ($dateFrom) {
            $where .= " AND i.TRANS_DATE >= TO_DATE(:df, 'YYYY-MM-DD')";
            $params[':df'] = $dateFrom;
        }
        if ($dateTo) {
            $where .= " AND i.TRANS_DATE <= TO_DATE(:dt, 'YYYY-MM-DD')";
            $params[':dt'] = $dateTo;
        }

        return compact('where','params','sortCol','sortDir','vatId','search','dateFrom','dateTo');
    }

    public function index(Request $request)
    {
        $request->validate([
            'per_page'  => 'nullable|integer|in:10,25,50,100',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to'   => 'nullable|date_format:Y-m-d',
            'vat_id'    => 'nullable|integer',
        ]);

        $q       = $this->buildQuery($request);
        $perPage = (int) $request->input('per_page', 25);
        $perPage = in_array($perPage, [10,25,50,100]) ? $perPage : 25;

        $total = (int)(DB::connection('oracle')
            ->selectOne("SELECT COUNT(*) AS CNT FROM VAT_INVOICE i WHERE {$q['where']}", $q['params'])
            ->cnt ?? 0);

        $page   = max(1, (int) $request->input('page', 1));
        $last   = max(1, (int) ceil($total / $perPage));
        $page   = min($page, $last);
        $offset = ($page - 1) * $perPage;

        $rows = DB::connection('oracle')->select("
            SELECT * FROM (
                SELECT a.*, ROWNUM rn FROM (
                    SELECT i.SERIAL, i.TR_NO,
                           TO_CHAR(NVL(i.TRANS_DATE, i.C_DATE), 'DD/MM/YYYY') AS TRANS_DATE,
                           i.REF_NO, TO_CHAR(i.REF_DATE, 'DD/MM/YYYY') AS REF_DATE,
                           i.DESCRIPTION, i.REF_VAL, i.VAT_VAL_D, i.VAT_VAL_C,
                           i.VAT_VAL_C - i.VAT_VAL_D AS VAT_NET,
                           i.VAT_ID, t.VAT_NAME, i.SUP_CUST_ACC, i.VAT_NO
                    FROM VAT_INVOICE i
                    LEFT JOIN VAT_TYPES t ON t.VAT_ID = i.VAT_ID
                    WHERE {$q['where']}
                    ORDER BY NVL(i.TRANS_DATE, i.C_DATE) {$q['sortDir']}
                ) a WHERE ROWNUM <= :max_row
            ) WHERE rn > :offset
        ", array_merge($q['params'], [':max_row' => $offset + $perPage, ':offset' => $offset]));

        $vatTypes = DB::connection('oracle')
            ->select("SELECT VAT_ID, VAT_NAME FROM VAT_TYPES ORDER BY VAT_ID");

        $currentType = $q['vatId']
            ? collect($vatTypes)->first(fn($t) => ((array)$t)[array_key_first((array)$t)] == $q['vatId'])
            : null;

        return view('invoices.index', array_merge(compact(
            'rows','vatTypes','perPage','total','page','last','currentType'
        ), [
            'vatId'    => $q['vatId'],
            'search'   => $q['search'],
            'dateFrom' => $q['dateFrom'],
            'dateTo'   => $q['dateTo'],
            'sortCol'  => $q['sortCol'],
            'sortDir'  => $q['sortDir'],
        ]));
    }

    public function show(int $serial)
    {
        $oracle = DB::connection('oracle');

        // Invoice + customer info via CHART_OF_ACCOUNT → CUSTOMERS
        $invoice = $oracle->selectOne("
            SELECT
                v.SERIAL, v.TR_NO,
                TO_CHAR(NVL(v.TRANS_DATE, v.C_DATE), 'YYYY-MM-DD')    AS TRANS_DATE,
                TO_CHAR(v.REF_DATE,                  'YYYY-MM-DD')    AS REF_DATE,
                TO_CHAR(v.C_DATE, 'YYYY-MM-DD') || 'T' || TO_CHAR(v.C_DATE, 'HH24:MI:SS') AS C_DATE,
                v.REF_NO, v.DESCRIPTION, v.REF_VAL, v.VAT_VAL_C,
                v.SUP_CUST_ACC, v.C_USER, v.DEPARTMENT_ID,
                o.DISCOUNT, o.DIS_VAL, o.NOTES, o.CONSTRUCT_ID,
                NVL(c.ACC_ANAME, d.ACC_ANAME)              AS CUSTOMER_NAME,
                NVL(c.ACC_ENAME, d.ACC_ENAME)              AS CUSTOMER_ENAME,
                NVL(c.ACC_NO,    d.ACC_NO)                 AS CUSTOMER_ACC,
                NVL(cu.VAT_NO,      cu2.VAT_NO)             AS VAT_NO,
                NVL(cu.ADDRESS,     cu2.ADDRESS)            AS CUSTOMER_ADDRESS,
                NVL(cu.BUILDING_NO, cu2.BUILDING_NO)        AS CUST_BUILDING_NO,
                NVL(cu.STREET_NAME, cu2.STREET_NAME)        AS CUST_STREET,
                NVL(cu.POSTAL_CODE, cu2.POSTAL_CODE)        AS CUST_POSTAL,
                NVL(cu.DISTRICT_ID, cu2.DISTRICT_ID)        AS CUST_DISTRICT_ID,
                NVL(cu.PHONE,       cu2.PHONE)              AS CUST_PHONE,
                NVL(cu.CUSTOMER_TYPE, cu2.CUSTOMER_TYPE)    AS CUST_TYPE,
                NVL(cu.ID_NUMBER,   cu2.ID_NUMBER)          AS CUST_ID_NUMBER,
                NVL(cu.VAT_NUMBER,  cu2.VAT_NUMBER)         AS CUST_VAT_NUMBER,
                NVL(cu.CR,          cu2.CR)                 AS CUST_CR
            FROM VAT_INVOICE v
            LEFT JOIN CONST_ORDERS     o   ON TO_CHAR(o.CONSTRUCT_ID) = TO_CHAR(v.REF_NO)
            LEFT JOIN CHART_OF_ACCOUNT c   ON c.ACC_NO  = o.CUSTOMER_ACC_NO
            LEFT JOIN CHART_OF_ACCOUNT d   ON d.ACC_NO  = v.SUP_CUST_ACC
            LEFT JOIN CUSTOMERS        cu  ON cu.CUSTOMER_ID = c.CUSTOMER_ID
            LEFT JOIN CUSTOMERS        cu2 ON cu2.CUSTOMER_ID = d.CUSTOMER_ID
            WHERE v.SERIAL = :serial AND v.DEL_FLAG = 0
        ", [':serial' => $serial]);

        abort_if(! $invoice, 404);

        $inv        = (array) $invoice;
        $constId    = $inv['CONSTRUCT_ID'] ?? null;

        // Line items
        $items = [];
        if ($constId) {
            $items = $oracle->select("
                SELECT
                    ci.ITEM_NO, ci.QTY, ci.UNT_PRICE,
                    ci.QTY * ci.UNT_PRICE   AS LINE_TOTAL,
                    itm.ITEM_ANAME, itm.ITEM_ENAME
                FROM CONST_ITEMS ci
                LEFT JOIN ITEMS itm ON itm.ITEM_NO = ci.ITEM_NO
                WHERE ci.CONSTRUCT_ID = :cid AND ci.DEL_FLAG = 0
            ", [':cid' => $constId]);
        }

        return view('invoices.print', compact('inv', 'items'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to'   => 'nullable|date_format:Y-m-d',
            'vat_id'    => 'nullable|integer',
        ]);

        $q = $this->buildQuery($request);

        $rows = DB::connection('oracle')->select("
            SELECT i.SERIAL, i.TR_NO, i.TRANS_DATE, i.REF_NO,
                   i.DESCRIPTION, i.REF_VAL, i.VAT_VAL_D, i.VAT_VAL_C,
                   i.VAT_VAL_C - i.VAT_VAL_D AS VAT_NET,
                   t.VAT_NAME, i.VAT_NO
            FROM VAT_INVOICE i
            LEFT JOIN VAT_TYPES t ON t.VAT_ID = i.VAT_ID
            WHERE {$q['where']}
            ORDER BY i.SERIAL DESC
        ", $q['params']);

        $filename = 'vat_invoices_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM so Excel opens Arabic correctly
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['#','رقم القيد','التاريخ','المرجع','الوصف','قيمة المرجع','مدين','دائن','الصافي','النشاط','رقم الضريبة']);
            foreach ($rows as $r) {
                $row = (array) $r;
                $keys = array_keys($row);
                fputcsv($out, [
                    $row[$keys[0]],  // SERIAL
                    $row[$keys[1]],  // TR_NO
                    $row[$keys[2]],  // TRANS_DATE
                    $row[$keys[3]],  // REF_NO
                    $row[$keys[4]],  // DESCRIPTION
                    number_format((float)($row[$keys[5]] ?? 0), 2),
                    number_format((float)($row[$keys[6]] ?? 0), 2),
                    number_format((float)($row[$keys[7]] ?? 0), 2),
                    number_format((float)($row[$keys[8]] ?? 0), 2),
                    $row[$keys[9]],  // VAT_NAME
                    $row[$keys[10]], // VAT_NO
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
