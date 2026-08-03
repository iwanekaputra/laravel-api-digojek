<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'city',
        'price',
        'status',
        'description',
        'morning_busy_start',
        'morning_busy_end',
        'morning_busy_price',
        'afternoon_busy_start',
        'afternoon_busy_end',
        'afternoon_busy_price',
        'rainy_status',
        'rainy_price',
    ];

    public function vehicleTypes()
    {
        return $this->hasMany(VehicleType::class, 'price_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
