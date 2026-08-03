<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'status'
    ];

    public function prices()
    {
        return $this->hasMany(CityVehiclePrice::class);
    }
}
