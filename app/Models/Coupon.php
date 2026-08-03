<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'discount',
        'type',
        'coupon_type',
        'description',
        'start_at',
        'expires_at',
        'status',
    ];

    public function getStartAtAttribute($value)
    {
        $dt = Carbon::create($value);
        return $dt->format('Y-m-d');
    }

    public function getExpiresAtAttribute($value)
    {
        $dt = Carbon::create($value);
        return $dt->format('Y-m-d');
    }
}
