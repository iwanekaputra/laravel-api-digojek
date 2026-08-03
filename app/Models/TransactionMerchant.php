<?php

namespace App\Models;

use App\Livewire\Pages\Merchants\MerchantApproved;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionMerchant extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public $with = ['merchant'];


    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
