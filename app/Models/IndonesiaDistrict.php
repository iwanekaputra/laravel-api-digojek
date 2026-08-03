<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndonesiaDistrict extends Model
{
    use HasFactory;

    protected $table = 'indonesia_districts';

    protected $fillable = [
        'code',
        'city_code',
        'name',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    // Relationships
    public function city()
    {
        return $this->belongsTo(IndonesiaCity::class, 'city_code', 'code');
    }

    public function villages()
    {
        return $this->hasMany(IndonesiaVillage::class, 'district_code', 'code');
    }

    public function wajibRetribusi()
    {
        return $this->hasMany(WajibRetribusi::class, 'kecamatan', 'name');
    }

    // Scopes
    public function scopeByCity($query, $cityCode)
    {
        return $query->where('city_code', $cityCode);
    }

    public function scopeByCityName($query, $cityName)
    {
        return $query->whereHas('city', function ($q) use ($cityName) {
            $q->where('name', $cityName);
        });
    }

    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->name . ', ' . $this->city->name . ', ' . $this->city->province->name;
    }
}
