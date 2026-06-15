<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanySettingController extends Controller
{
    public function edit()
    {
        $setting = CompanySetting::current();
        $oracle  = DB::connection('oracle');

        $regions = $oracle->select(
            "SELECT REGION_ID, NAME_AR FROM REGIONS ORDER BY NAME_AR"
        );
        $cities = $oracle->select(
            "SELECT CITY_ID, CITY_NAME FROM CITIES WHERE DEL_FLAG=0 AND CITY_NAME IS NOT NULL ORDER BY CITY_NAME"
        );
        $districts = $setting->city_id
            ? $oracle->select(
                "SELECT DISTRICT_ID, NAME_AR FROM DISTRICTS WHERE CITY_ID = :cid ORDER BY NAME_AR",
                [':cid' => $setting->city_id]
              )
            : [];

        return view('company.settings', compact('setting', 'regions', 'cities', 'districts'));
    }

    public function districts(Request $request, $cityId)
    {
        $rows = DB::connection('oracle')->select(
            "SELECT DISTRICT_ID, NAME_AR FROM DISTRICTS WHERE CITY_ID = :cid ORDER BY NAME_AR",
            [':cid' => $cityId]
        );

        return response()->json(
            array_map(fn($r) => [
                'id'   => $r->district_id ?? $r->DISTRICT_ID,
                'name' => $r->name_ar     ?? $r->NAME_AR,
            ], $rows)
        );
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'co_name'     => 'required|string|max:255',
            'cr_no'       => 'required|string|max:50',
            'vat_no'      => 'required|string|max:50',
            'region_id'   => 'nullable|string|max:50',
            'city_id'     => 'nullable|string|max:50',
            'district_id' => 'nullable|string|max:50',
            'street'      => 'required|string|max:255',
            'building_no' => 'required|string|max:20',
            'postal_code' => 'required|string|max:20',
            'header_image'=> 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        $setting = CompanySetting::current();

        if ($request->hasFile('header_image')) {
            $file     = $request->file('header_image');
            $filename = 'header.' . $file->getClientOriginalExtension();
            $file->move(public_path(), $filename);
            $validated['header_path'] = $filename;
        }

        unset($validated['header_image']);
        $setting->update($validated);

        return redirect()->route('company.settings.edit')
            ->with('success', __('app.settings_saved'));
    }
}
