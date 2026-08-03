<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPackage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $with = ['order'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
