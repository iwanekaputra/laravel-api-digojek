<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionPurchaseDriver extends Model
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

    public function getDataRequestAttribute($value)
    {
        return json_decode($value);
    }

    public function getInfoAttribute($value)
    {
        return json_decode($value);
    }
}
