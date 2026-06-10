<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VatInvoice extends Model
{
    protected $connection = 'oracle';
    protected $table = 'VAT_INVOICE';
    protected $primaryKey = 'SERIAL';
    public $timestamps = false;

    protected $fillable = [
        'DEPARTMENT_ID', 'TR_NO', 'TRANS_DATE', 'V_CAT_ID', 'V_CAT_TYPE',
        'VAT_ID', 'SUP_CUST_ACC', 'REF_NO', 'REF_DATE', 'DESCRIPTION',
        'REF_VAL', 'VAT_VAL_D', 'VAT_VAL_C', 'DEL_FLAG',
        'C_USER', 'C_DATE', 'F_ACC_NO', 'VAT_NO',
    ];

    public function vatCategory()
    {
        return $this->belongsTo(VatCategory::class, 'V_CAT_ID', 'V_CAT_ID');
    }

    public function vatType()
    {
        return $this->belongsTo(VatType::class, 'VAT_ID', 'VAT_ID');
    }
}
