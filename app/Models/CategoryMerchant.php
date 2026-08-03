<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryMerchant extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function merchants()
    {
        return $this->hasMany(Merchant::class, 'category_merchant_id');
    }
}
