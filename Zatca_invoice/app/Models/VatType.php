<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VatType extends Model
{
    protected $connection = 'oracle';
    protected $table = 'VAT_TYPES';
    protected $primaryKey = 'VAT_ID';
    public $timestamps = false;

    protected $fillable = ['VAT_NAME'];
}
