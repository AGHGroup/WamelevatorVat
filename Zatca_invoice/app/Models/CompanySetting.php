<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class CompanySetting
{
    private static function activeSystem(): string
    {
        return session('active_system', 'zatca');
    }

    private static function filePath(): string
    {
        $system = static::activeSystem();
        return storage_path("app/company_settings_{$system}.json");
    }

    private static function defaults(): array
    {
        if (static::activeSystem() === 'wamelevator') {
            return [
                'co_name'     => 'شركة عبد الغني حسين حامد للمصاعد',
                'cr_no'       => '4650262799',
                'vat_no'      => '311744595500003',
                'region_id'   => '',
                'city_id'     => '',
                'district_id' => '',
                'street'      => 'شارع ابو إسحاق الهجري',
                'building_no' => '7359',
                'postal_code' => '42331',
                'header_path' => '',
            ];
        }

        return [
            'co_name'     => 'المؤسسة التجارية للمصاعد',
            'cr_no'       => '4650017660',
            'vat_no'      => '300453212100003',
            'region_id'   => '',
            'city_id'     => '',
            'district_id' => '',
            'street'      => 'عمير بن الحارث',
            'building_no' => '2659',
            'postal_code' => '42331',
            'header_path' => '',
        ];
    }

    // Read first row from CO_NAME and map columns to our fields
    private static function fromOracle(): array
    {
        try {
            $host = config('database.connections.oracle.host');
            \Illuminate\Support\Facades\Log::info("CompanySetting: querying CO_NAME on host={$host}, system=" . static::activeSystem());
            $row = DB::connection('oracle')->selectOne('SELECT * FROM CO_NAME WHERE ROWNUM = 1');
            if (! $row) {
                \Illuminate\Support\Facades\Log::warning('CompanySetting: CO_NAME table is empty or not found');
                return [];
            }

            $r = array_change_key_case((array) $row, CASE_UPPER);
            \Illuminate\Support\Facades\Log::info('CompanySetting: CO_NAME columns = ' . implode(', ', array_keys($r)));

            $pick = fn(array $candidates) => (string) collect($candidates)
                ->map(fn($c) => $r[$c] ?? null)
                ->first(fn($v) => $v !== null && $v !== '');

            $mapped = array_filter([
                'co_name'     => $pick(['NORMAL_NAME','CO_ANAME','ANAME','CO_NAME','NAME_AR','COMPANY_NAME']),
                'cr_no'       => $pick(['CR_NO']),
                'vat_no'      => $pick(['VAT_NO']),
                'region_id'   => $pick(['REGION_ID']),
                'city_id'     => $pick(['CITY_ID']),
                'district_id' => $pick(['DISTRICT_ID']),
                'street'      => $pick(['STREET']),
                'building_no' => $pick(['BUILDING_NO']),
                'postal_code' => $pick(['POSTAL_CODE']),
                'header_path' => $pick(['IMG_PATH','FILE_NAME']),
            ], fn($v) => $v !== null && $v !== '');

            \Illuminate\Support\Facades\Log::info('CompanySetting: mapped = ' . json_encode($mapped, JSON_UNESCAPED_UNICODE));
            return $mapped;

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('CompanySetting: fromOracle failed — ' . $e->getMessage());
            return [];
        }
    }

    public static function current(): static
    {
        \Illuminate\Support\Facades\Log::info('CompanySetting::current called, system=' . static::activeSystem());
        $data = static::defaults();

        // All company data comes from Oracle CO_NAME only
        $oracle = static::fromOracle();
        if (! empty($oracle)) {
            $data = array_merge($data, $oracle);
        }

        return static::fill(new static(), $data);
    }

    public function update(array $data): void
    {
        // Keep existing IMG_PATH if no new image provided
        $imgPath = $data['header_path'] ?? null;
        if ($imgPath === null) {
            $existing = static::fromOracle();
            $imgPath  = $existing['header_path'] ?? null;
        }

        $params = [
            ':name'     => $data['co_name']    ?? null,
            ':cr'       => $data['cr_no']       ?? null,
            ':vat'      => $data['vat_no']      ?? null,
            ':region'   => $data['region_id']   ?? null,
            ':city'     => $data['city_id']     ?? null,
            ':district' => $data['district_id'] ?? null,
            ':street'   => $data['street']      ?? null,
            ':building' => $data['building_no'] ?? null,
            ':postal'   => $data['postal_code'] ?? null,
            ':img'      => $imgPath,
        ];

        DB::connection('oracle')->statement("
            MERGE INTO CO_NAME tgt
            USING (SELECT 1 AS dummy FROM DUAL) src ON (1=1)
            WHEN MATCHED THEN UPDATE SET
                NORMAL_NAME = :name,
                CR_NO       = :cr,
                VAT_NO      = :vat,
                REGION_ID   = :region,
                CITY_ID     = :city,
                DISTRICT_ID = :district,
                STREET      = :street,
                BUILDING_NO = :building,
                POSTAL_CODE = :postal,
                IMG_PATH    = :img
            WHEN NOT MATCHED THEN INSERT
                (NORMAL_NAME, CR_NO, VAT_NO, REGION_ID, CITY_ID, DISTRICT_ID, STREET, BUILDING_NO, POSTAL_CODE, IMG_PATH)
            VALUES
                (:name, :cr, :vat, :region, :city, :district, :street, :building, :postal, :img)
        ", $params);

        $data['header_path'] = $imgPath ?? '';
        static::fill($this, array_merge(static::defaults(), $data));
    }

    private static function fill(self $obj, array $data): static
    {
        $known = ['co_name','cr_no','vat_no','region_id','city_id','district_id','street','building_no','postal_code','header_path'];
        foreach ($known as $key) {
            $obj->$key = (string) ($data[$key] ?? '');
        }
        return $obj;
    }

    public string $co_name     = '';
    public string $cr_no       = '';
    public string $vat_no      = '';
    public string $region_id   = '';
    public string $city_id     = '';
    public string $district_id = '';
    public string $street      = '';
    public string $building_no = '';
    public string $postal_code = '';
    public string $header_path = 'header.jpg';
}
