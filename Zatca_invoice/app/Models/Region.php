<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $connection = 'oracle';
    protected $table      = 'REGIONS';
    protected $primaryKey = 'region_id';
    public    $timestamps = false;

    public function cities()
    {
        return $this->hasMany(City::class, 'region_id', 'region_id');
    }

    public function getNameArAttribute(): string
    {
        return (string) ($this->attributes['name_ar'] ?? '');
    }
}
