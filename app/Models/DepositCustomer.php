<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositCustomer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'paid_at' => 'datetime',
        'expire_at' => 'datetime',
        'bank_confirmed_at' => 'datetime'
    ];

    public $with = ['customer'];

    // public $timestamps = false;

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getImageTransferAttribute($value)
    {
        if ($value == "") {
            return "";
        } else {
            return asset('storage/' . $value);
        }
    }


    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }

    public function getExpireAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }


    public function isManual(): bool
    {
        return $this->payment_method === 'manual';
    }

    public function isVa(): bool
    {
        return $this->payment_method === 'va';
    }


    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function paymentBank()
    {
        return $this->belongsTo(PaymentBank::class, 'bank_id');
    }

    public function scopeLunas($query)
    {
        return $query->where('status', 'lunas');
    }
}
