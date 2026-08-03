<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicletype_id',
        'brand',
        'registration_number',
        'manufacture_year',
        'color',
        'stnk',
        'image',
        'vehicle_category_id'
    ];

    public $with = ['vehicletype', 'vehiclecategory'];

    public function vehicletype()
    {
        return $this->belongsTo(Vehicletype::class);
    }

    public function vehicleCategory()
    {
        return $this->belongsTo(VehicleCategory::class);
    }
}
