<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'code',
        'icon',
        'name',
        'type',
        'channel_type',
        'is_active',
        'company_code',
        'sub_code',
        'price_admin',
        'description'
    ];

    protected $appends = ['icon_url'];

    public function getIconUrlAttribute()
    {
        return $this->icon
            ? "https://admin.digojek.com/storage/images/banks/" . $this->icon
            : null;
    }


    public function banks()
    {
        return $this->hasMany(PaymentBank::class);
    }


    public function isManual(): bool
    {
        return $this->type === 'manual';
    }

    public function isVa(): bool
    {
        return $this->type === 'va';
    }
}
