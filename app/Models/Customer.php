<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User;
use Laravel\Sanctum\HasApiTokens;
use App\Models\CustomerAddress;

class Customer extends User
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'image',
        'balance',
        'nohp',
        'status',
        'device_token',
        'referal',
        'pin',
        'nik',
        'customer_level_id'
    ];

    protected $appends = [
        'link_image'
    ];

    public function getLinkImageAttribute()
    {
        return asset('storage/' . $this->image);
    }

    public function level()
    {
        return $this->belongsTo(CustomerLevel::class, 'customer_level_id');
    }



    // relationship

    public function carts()
    {
        return $this->hasMany(MpCart::class, 'customer_id');
    }

    public function orders()
    {
        return $this->hasMany(MpOrder::class, 'customer_id');
    }

    public function reviews()
    {
        return $this->hasMany(MpReview::class, 'customer_id');
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }

    public function defaultAddress()
    {
        return $this->hasOne(CustomerAddress::class, 'customer_id')
            ->where('is_default', true);
    }
}
