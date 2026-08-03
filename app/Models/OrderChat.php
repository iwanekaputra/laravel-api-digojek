<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderChat extends Model
{
    use HasFactory;

    // Definisikan nama tabel secara eksplisit jika berbeda dari konvasi jamak Laravel
    protected $table = 'order_chats';

    // Daftarkan kolom yang boleh diisi secara massal
    protected $fillable = [
        'order_id',
        'sender_type',
        'sender_id',
        'message',
        'is_read',
    ];

    /**
     * Relasi balik ke model Order.
     * Setiap chat pasti dimiliki oleh satu Order tertentu. [cite: 8]
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Helper tambahan untuk mengecek apakah pengirimnya Customer
     */
    public function isFromCustomer(): bool
    {
        return $this->sender_type === 'customer';
    }

    /**
     * Helper tambahan untuk mengecek apakah pengirimnya Driver
     */
    public function isFromDriver(): bool
    {
        return $this->sender_type === 'driver';
    }
}
