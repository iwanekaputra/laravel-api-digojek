<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public $with = ['categoryProduct', 'merchant'];

    public function getDurationInHoursAttribute()
    {
        if (!$this->duration_minutes) return null;
        return $this->duration_minutes / 60;
    }


    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function categoryProduct()
    {
        return $this->belongsTo(CategoryProduct::class);
    }

    public function galleryProducts()
    {
        return $this->hasMany(GalleryProduct::class);
    }
}
