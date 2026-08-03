<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CityVehiclePrice extends Model
{
    protected $fillable = [
        'city',
        'vehicle_category_id',
        'price_per_km',
        'minimum_km',
        'minimum_price',
        'is_active',
        'driver_cut_percentage',
        'minimum_driver_balance',
        'service_fee',
    ];

    public function vehicle()
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }
}
