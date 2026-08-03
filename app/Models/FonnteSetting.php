<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FonnteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'is_active',
        'device_name'
    ];
}
