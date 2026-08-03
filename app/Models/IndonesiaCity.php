<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndonesiaCity extends Model
{
    use HasFactory;

    protected $table = 'indonesia_cities';

    protected $fillable = [
        'code',
        'province_code',
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
    public function province()
    {
        return $this->belongsTo(IndonesiaProvince::class, 'province_code', 'code');
    }

    public function districts()
    {
        return $this->hasMany(IndonesiaDistrict::class, 'city_code', 'code');
    }

    public function wajibRetribusi()
    {
        return $this->hasMany(WajibRetribusi::class, 'kota', 'name');
    }

    // Scopes
    public function scopeByProvince($query, $provinceCode)
    {
        return $query->where('province_code', $provinceCode);
    }

    public function scopeByProvinceName($query, $provinceName)
    {
        return $query->whereHas('province', function ($q) use ($provinceName) {
            $q->where('name', $provinceName);
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
        return $this->name . ', ' . $this->province->name;
    }
}
