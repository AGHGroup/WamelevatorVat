<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $connection = 'oracle';
    protected $table      = 'DISTRICTS';
    protected $primaryKey = 'district_id';
    public    $timestamps = false;

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }

    // Shortcut: $district->region
    public function getRegionAttribute()
    {
        return $this->city?->region;
    }

    public function getNameArAttribute(): string
    {
        return (string) ($this->attributes['name_ar'] ?? '');
    }
}
