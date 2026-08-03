<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionCustomer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $with = ['customer'];


    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactionPuchase()
    {
        return $this->belongsTo(TransactionPuchase::class);
    }
}
