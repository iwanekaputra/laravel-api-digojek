<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndonesiaVillage extends Model
{
    use HasFactory;

    protected $table = 'indonesia_villages';

    protected $fillable = [
        'code',
        'district_code',
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
    public function district()
    {
        return $this->belongsTo(IndonesiaDistrict::class, 'district_code', 'code');
    }

    public function wajibRetribusi()
    {
        return $this->hasMany(WajibRetribusi::class, 'kelurahan', 'name');
    }

    // Scopes
    public function scopeByDistrict($query, $districtCode)
    {
        return $query->where('district_code', $districtCode);
    }

    public function scopeByDistrictName($query, $districtName)
    {
        return $query->whereHas('district', function ($q) use ($districtName) {
            $q->where('name', $districtName);
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
    public function getFullAddressAttribute()
    {
        return $this->name . ', ' . $this->district->name . ', ' . $this->district->city->name . ', ' . $this->district->city->province->name;
    }
}
