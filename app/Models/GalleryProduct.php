<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryProduct extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    protected $appends = [
        'link_image'
    ];

    public function getLinkImageAttribute()
    {
        return asset('storage/' . $this->image);
    }

    public $with = ['product'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
