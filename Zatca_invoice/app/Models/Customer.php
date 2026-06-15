<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $connection = 'oracle';
    protected $table      = 'CUSTOMERS';
    protected $primaryKey = 'customer_id';
    public    $timestamps = false;

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'district_id');
    }

    public function getCityAttribute()   { return $this->district?->city; }
    public function getRegionAttribute() { return $this->district?->city?->region; }

    public function getNameAttribute(): string
    {
        return (string) ($this->attributes['c_aname'] ?? $this->attributes['c_ename'] ?? '');
    }
}
