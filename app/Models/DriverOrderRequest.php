<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverOrderRequest extends Model
{
    protected $fillable = [
        'order_id',
        'driver_id',
        'status',
        'sent_at',
        'responded_at',
        'expired_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'responded_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
