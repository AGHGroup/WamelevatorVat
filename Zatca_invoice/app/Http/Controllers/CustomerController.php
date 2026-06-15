<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search  = trim($request->input('search', ''));
        $perPage = 25;
        $page    = max(1, (int) $request->input('page', 1));
        $offset  = ($page - 1) * $perPage;

        $where  = "DEL_FLAG = 0";
        $params = [];

        if ($search) {
            $like = '%' . strtoupper($search) . '%';
            $where .= " AND (UPPER(C_ANAME) LIKE :s1 OR UPPER(C_ENAME) LIKE :s2 OR ID_NUMBER LIKE :s3 OR VAT_NUMBER LIKE :s4)";
            $params[':s1'] = $like;
            $params[':s2'] = $like;
            $params[':s3'] = '%' . $search . '%';
            $params[':s4'] = '%' . $search . '%';
        }

        $countRow = DB::connection('oracle')
            ->selectOne("SELECT COUNT(*) AS CNT FROM CUSTOMERS WHERE $where", $params);
        $total = (int) ($countRow->cnt ?? $countRow->CNT ?? 0);

        $last = max(1, (int) ceil($total / $perPage));
        $page = min($page, $last);

        $customers = DB::connection('oracle')->select("
            SELECT * FROM (
                SELECT a.*, ROWNUM rn FROM (
                    SELECT CUSTOMER_ID, C_ANAME, C_ENAME, CUSTOMER_TYPE,
                           ID_NUMBER, VAT_NUMBER, CR, PHONE, MOBILE
                    FROM CUSTOMERS
                    WHERE $where
                    ORDER BY C_ANAME
                ) a WHERE ROWNUM <= :max_row
            ) WHERE rn > :offset
        ", array_merge($params, [':max_row' => $offset + $perPage, ':offset' => $offset]));

        return view('customers.index', compact('customers', 'search', 'total', 'page', 'last', 'perPage'));
    }

    public function edit($id)
    {
        $customer = DB::connection('oracle')
            ->selectOne("SELECT * FROM CUSTOMERS WHERE CUSTOMER_ID = :id AND DEL_FLAG = 0", [':id' => $id]);

        abort_if(!$customer, 404);

        $c      = array_change_key_case((array) $customer, CASE_LOWER);
        $oracle = DB::connection('oracle');

        $regions = $oracle->select("SELECT REGION_ID, NAME_AR FROM REGIONS ORDER BY NAME_AR");
        $cities  = $oracle->select("SELECT CITY_ID, CITY_NAME FROM CITIES WHERE DEL_FLAG=0 AND CITY_NAME IS NOT NULL ORDER BY CITY_NAME");

        // Resolve city_id and region_id from the saved district_id
        $districtId = $c['district_id'] ?? '';
        $cityId     = '';
        $regionId   = '';
        $districts  = [];
        if ($districtId) {
            $distRow = $oracle->selectOne(
                "SELECT d.CITY_ID, c.REGION_ID FROM DISTRICTS d LEFT JOIN CITIES c ON c.CITY_ID = d.CITY_ID WHERE d.DISTRICT_ID = :id",
                [':id' => $districtId]
            );
            if ($distRow) {
                $cityId   = $distRow->city_id   ?? $distRow->CITY_ID   ?? '';
                $regionId = $distRow->region_id ?? $distRow->REGION_ID ?? '';
            }
        }
        if ($cityId) {
            $districts = $oracle->select("SELECT DISTRICT_ID, NAME_AR FROM DISTRICTS WHERE CITY_ID = :cid ORDER BY NAME_AR", [':cid' => $cityId]);
        }

        return view('customers.edit', compact('customer', 'regions', 'cities', 'districts', 'cityId', 'regionId'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_type' => 'required|in:1,2',
            'id_number'     => 'nullable|string|max:20',
            'vat_number'    => 'nullable|string|max:15',
            'cr'            => 'nullable|string|max:20',
            'phone'         => 'nullable|string|max:20',
            'mobile'        => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:255',
            'building_no'   => 'nullable|string|max:10',
            'street_name'   => 'nullable|string|max:255',
            'region_id'     => 'nullable|string|max:50',
            'city_id'       => 'nullable|string|max:50',
            'district_id'   => 'nullable|string|max:50',
            'postal_code'   => 'nullable|string|max:10',
        ]);

        DB::connection('oracle')->statement("
            UPDATE CUSTOMERS SET
                CUSTOMER_TYPE = :ctype,
                ID_NUMBER     = :idnum,
                VAT_NUMBER    = :vatnum,
                CR            = :cr,
                PHONE         = :phone,
                MOBILE        = :mobile,
                ADDRESS       = :addr,
                BUILDING_NO   = :bldno,
                STREET_NAME   = :street,
                DISTRICT_ID   = :dist,
                POSTAL_CODE   = :postal,
                L_U_USER      = :luuser,
                L_U_DATE      = SYSDATE
            WHERE CUSTOMER_ID = :custid AND DEL_FLAG = 0
        ", [
            ':ctype'  => $request->input('customer_type'),
            ':idnum'  => $request->input('id_number')   ?: null,
            ':vatnum' => $request->input('vat_number')  ?: null,
            ':cr'     => $request->input('cr')          ?: null,
            ':phone'  => $request->input('phone')       ?: null,
            ':mobile' => $request->input('mobile')      ?: null,
            ':addr'   => $request->input('address')     ?: null,
            ':bldno'  => $request->input('building_no') ?: null,
            ':street' => $request->input('street_name') ?: null,
            ':dist'   => $request->input('district_id') ?: null,
            ':postal' => $request->input('postal_code') ?: null,
            ':luuser' => auth()->user()->user_id ?? auth()->id(),
            ':custid' => $id,
        ]);
        // region_id and city_id are UI-only helpers — not stored in CUSTOMERS

        return redirect()->route('customers.index')
            ->with('success', 'تم تحديث بيانات العميل بنجاح');
    }
}
