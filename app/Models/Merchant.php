<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;


use function PHPSTORM_META\map;

class Merchant extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;


    protected $guarded = [
        'id'
    ];


    public $with = [
        'categoryMerchant'
    ];

    protected $appends = [
        'link_image'
    ];


    public function categoryMerchant()
    {
        return $this->belongsTo(CategoryMerchant::class, 'category_merchant_id');
    }

    public function MerchantOperationalHours()
    {
        return $this->hasMany(MerchantOperationalHours::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getLinkImageAttribute()
    {
        return asset('storage/' . $this->image);
    }
}
