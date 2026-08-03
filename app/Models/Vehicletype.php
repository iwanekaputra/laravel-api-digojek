<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicletype extends Model
{
    use HasFactory;
    protected $fillable = [
        'image',
        'type',
        'vehicletype',
        'passenger',
        'price',
        'price_id',
        'status',
        'price_mober'
    ];

    public function getImageAttribute($value)
    {
        return asset('storage/' . $value);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'vehicletype_id');
    }

    public function price()
    {
        return $this->belongsTo(Price::class, 'price_id');
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getFormattedPriceMoberAttribute(): string
    {
        return 'Rp ' . number_format($this->price_mober ?? 0, 0, ',', '.');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCity($query, $city)
    {
        return $query->whereHas('price', function ($q) use ($city) {
            $q->where('city', $city);
        });
    }

    public function scopeByPriceId($query, $priceId)
    {
        return $query->where('price_id', $priceId);
    }
}
