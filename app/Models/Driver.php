<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

use function PHPSTORM_META\map;

class Driver extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;


    protected $fillable = [
        'name',
        'birthday',
        'gender',
        'email',
        'image',
        'balance',
        'city',
        'province',
        'address',
        'status',
        'referal',
        'vehicle_id',
        'agreement',
        'nohp',
        'ktp',
        'sim',
        'skck',
        'status_driver',
        'latitude',
        'longitude',
        'is_delivering',
        'is_mober',
        'code_referal',
        'referal',
        'device_token'
    ];

    public $with = ['vehicle'];

    protected $appends = [
        'link_image'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }



    public function getLinkImageAttribute()
    {
        return asset('storage/' . $this->image);
    }

    public function incomingOrders()
    {
        return $this->hasMany(DriverOrderRequest::class);
    }
}
