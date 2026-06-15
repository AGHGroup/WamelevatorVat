<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    // ── Regions ──────────────────────────────────────────────────────────────

    public function regions()
    {
        $regions = DB::connection('oracle')->select("SELECT REGION_ID, NAME_AR, NAME_EN, CODE FROM REGIONS ORDER BY NAME_AR");
        return view('locations.regions', compact('regions'));
    }

    public function storeRegion(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:100',
            'name_en' => 'nullable|string|max:100',
            'code'    => 'nullable|string|max:10',
        ]);

        $pdo   = DB::connection('oracle')->getPdo();
        $maxId = (int) $pdo->query("SELECT NVL(MAX(REGION_ID),0) FROM REGIONS")->fetchColumn();

        DB::connection('oracle')->statement(
            "INSERT INTO REGIONS (REGION_ID, NAME_AR, NAME_EN, CODE) VALUES (:id, :ar, :en, :code)",
            [':id' => $maxId + 1, ':ar' => $request->name_ar, ':en' => $request->name_en ?: null, ':code' => $request->code ?: null]
        );

        return back()->with('success', 'تمت إضافة المنطقة');
    }

    public function destroyRegion($id)
    {
        DB::connection('oracle')->statement("DELETE FROM REGIONS WHERE REGION_ID = :id", [':id' => $id]);
        return back()->with('success', 'تم حذف المنطقة');
    }

    // ── Cities ────────────────────────────────────────────────────────────────

    public function cities()
    {
        $cities = DB::connection('oracle')->select(
            "SELECT CITY_ID, CITY_NAME, DEL_FLAG FROM CITIES WHERE CITY_NAME IS NOT NULL ORDER BY CITY_NAME"
        );
        return view('locations.cities', compact('cities'));
    }

    public function storeCity(Request $request)
    {
        $request->validate(['city_name' => 'required|string|max:100']);

        $pdo   = DB::connection('oracle')->getPdo();
        $maxId = (int) $pdo->query("SELECT NVL(MAX(CITY_ID),0) FROM CITIES")->fetchColumn();

        DB::connection('oracle')->statement(
            "INSERT INTO CITIES (CITY_ID, CITY_NAME, DEL_FLAG) VALUES (:id, :name, 0)",
            [':id' => $maxId + 1, ':name' => $request->city_name]
        );

        return back()->with('success', 'تمت إضافة المدينة');
    }

    public function destroyCity($id)
    {
        DB::connection('oracle')->statement(
            "UPDATE CITIES SET DEL_FLAG = 1 WHERE CITY_ID = :id",
            [':id' => $id]
        );
        return back()->with('success', 'تم حذف المدينة');
    }

    // ── Districts ─────────────────────────────────────────────────────────────

    public function districts(Request $request)
    {
        $cityId = $request->input('city_id', '');
        $search = trim($request->input('search', ''));

        $cities = DB::connection('oracle')->select(
            "SELECT CITY_ID, CITY_NAME FROM CITIES WHERE DEL_FLAG=0 AND CITY_NAME IS NOT NULL ORDER BY CITY_NAME"
        );

        $where  = '1=1';
        $params = [];
        if ($cityId) {
            $where .= ' AND CITY_ID = :cid';
            $params[':cid'] = $cityId;
        }
        if ($search) {
            $where .= ' AND UPPER(NAME_AR) LIKE :s';
            $params[':s'] = '%' . strtoupper($search) . '%';
        }

        $perPage = 30;
        $page    = max(1, (int) $request->input('page', 1));
        $offset  = ($page - 1) * $perPage;

        $countRow = DB::connection('oracle')->selectOne("SELECT COUNT(*) AS CNT FROM DISTRICTS WHERE $where", $params);
        $total    = (int) ($countRow->cnt ?? $countRow->CNT ?? 0);
        $last     = max(1, (int) ceil($total / $perPage));
        $page     = min($page, $last);

        $districts = DB::connection('oracle')->select("
            SELECT * FROM (
                SELECT a.*, ROWNUM rn FROM (
                    SELECT d.DISTRICT_ID, d.NAME_AR, d.NAME_EN, d.CITY_ID, c.CITY_NAME
                    FROM DISTRICTS d
                    LEFT JOIN CITIES c ON c.CITY_ID = d.CITY_ID
                    WHERE $where
                    ORDER BY d.NAME_AR
                ) a WHERE ROWNUM <= :mx
            ) WHERE rn > :off
        ", array_merge($params, [':mx' => $offset + $perPage, ':off' => $offset]));

        return view('locations.districts', compact('districts', 'cities', 'cityId', 'search', 'total', 'page', 'last'));
    }

    public function storeDistrict(Request $request)
    {
        $request->validate([
            'city_id'   => 'required|string',
            'name_ar'   => 'required|string|max:100',
            'name_en'   => 'nullable|string|max:100',
        ]);

        $pdo   = DB::connection('oracle')->getPdo();
        $maxId = (int) $pdo->query("SELECT NVL(MAX(TO_NUMBER(DISTRICT_ID)),20000000000) FROM DISTRICTS")->fetchColumn();

        DB::connection('oracle')->statement(
            "INSERT INTO DISTRICTS (DISTRICT_ID, CITY_ID, NAME_AR, NAME_EN) VALUES (:id, :cid, :ar, :en)",
            [':id' => $maxId + 1, ':cid' => $request->city_id, ':ar' => $request->name_ar, ':en' => $request->name_en ?: null]
        );

        return back()->with('success', 'تمت إضافة الحي');
    }

    public function destroyDistrict($id)
    {
        DB::connection('oracle')->statement("DELETE FROM DISTRICTS WHERE DISTRICT_ID = :id", [':id' => $id]);
        return back()->with('success', 'تم حذف الحي');
    }
}
