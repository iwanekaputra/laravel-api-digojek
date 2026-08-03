<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $with = ['driver'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function orderCustomers()
    {
        return $this->hasMany(OrderCustomer::class);
    }

    public function orderMerchants()
    {
        return $this->hasMany(OrderMerchant::class);
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function orderPackages()
    {
        return $this->hasMany(OrderPackage::class);
    }

    public function requests()
    {
        return $this->hasMany(DriverOrderRequest::class);
    }

    /**
     * Relasi ke data kargo ship (1 Order memiliki 1 detail kargo ship)
     */
    public function shipCargo(): HasOne
    {
        return $this->hasOne(OrderShipCargo::class, 'order_id');
    }


    public function chats(): HasMany
    {
        return $this->hasMany(OrderChat::class, 'order_id')->orderBy('created_at', 'asc');
    }
}
