<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionPuchase extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $with = ['customer'];


    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getDataRequestAttribute($value)
    {
        return json_decode($value);
    }

    public function getInfoAttribute($value)
    {
        return json_decode($value);
    }
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }
}
