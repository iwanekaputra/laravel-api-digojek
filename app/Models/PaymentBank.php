<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentBank extends Model
{
    use HasFactory;

    protected $table = 'payment_banks';

    protected $fillable = [
        'payment_method_id',
        'bank_name',
        'account_number',
        'account_name',
        'is_active',
    ];

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
