<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantOperationalHours extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $with = ['merchant'];



    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }



    // Metode untuk menghitung status toko

}
