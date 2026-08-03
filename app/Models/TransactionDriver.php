<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDriver extends Model
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
}
