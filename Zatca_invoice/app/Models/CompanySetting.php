<?php

namespace App\Models;

class CompanySetting
{
    private static string $path = '';

    private static function filePath(): string
    {
        if (static::$path === '') {
            static::$path = storage_path('app/company_settings.json');
        }
        return static::$path;
    }

    private static function defaults(): array
    {
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
            'header_path' => 'header.jpg',
        ];
    }

    public static function current(): static
    {
        $data = static::defaults();

        if (file_exists(static::filePath())) {
            $json = json_decode(file_get_contents(static::filePath()), true);
            if (is_array($json)) {
                $data = array_merge($data, $json);
            }
        }

        $obj = new static();
        foreach ($data as $key => $value) {
            $obj->$key = $value;
        }
        return $obj;
    }

    public function update(array $data): void
    {
        $existing = static::defaults();
        if (file_exists(static::filePath())) {
            $json = json_decode(file_get_contents(static::filePath()), true);
            if (is_array($json)) {
                $existing = array_merge($existing, $json);
            }
        }

        $merged = array_merge($existing, $data);
        file_put_contents(static::filePath(), json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        foreach ($merged as $key => $value) {
            $this->$key = $value;
        }
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
