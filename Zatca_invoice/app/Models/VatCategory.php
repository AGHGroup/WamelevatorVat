<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VatCategory extends Model
{
    protected $connection = 'oracle';
    protected $table = 'VAT_CATEGORIES';
    protected $primaryKey = 'V_CAT_ID';
    public $timestamps = false;

    protected $fillable = [
        'V_CAT_NAME', 'V_CAT_TYPE', 'TRANS', 'CALC_VAT', 'VAT_P',
    ];
}
