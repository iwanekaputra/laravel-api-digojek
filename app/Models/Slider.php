<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;
    protected $fillable = [
        'image',
        'sort',
        'description',
        'status',
    ];

    protected $appends = [
        'link_image'
    ];


    public function getLinkImageAttribute()
    {
        return 'https://admin.digojek.com/storage/images/slider/' . $this->image;
    }
}
