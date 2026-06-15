<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $connection = 'oracle';
    protected $table      = 'CITIES';
    protected $primaryKey = 'city_id';
    public    $timestamps = false;

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'region_id');
    }

    public function districts()
    {
        return $this->hasMany(District::class, 'city_id', 'city_id');
    }

    public function getCityNameAttribute(): string
    {
        return (string) ($this->attributes['city_name'] ?? '');
    }
}
