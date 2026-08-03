<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndonesiaProvince extends Model
{
    use HasFactory;

    protected $table = 'indonesia_provinces';

    protected $fillable = [
        'code',
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
    public function cities()
    {
        return $this->hasMany(IndonesiaCity::class, 'province_code', 'code');
    }

    public function wajibRetribusi()
    {
        return $this->hasMany(WajibRetribusi::class, 'provinsi', 'name');
    }

    // Scopes
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    public function scopeWithCities($query)
    {
        return $query->with('cities');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}
