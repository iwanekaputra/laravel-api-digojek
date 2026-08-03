<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositMerchant extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $with = ['merchant'];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function getImageTransferAttribute($value)
    {
        if ($value == "") {
            return "";
        } else {
            return asset('storage/' . $value);
        }
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }

    public function getExpireAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }
}
