<?php

namespace App\Models;

use App\Services\FonnteService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class OtpRegisterMerchant extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function sendWa($nohp)
    {
        $fonnte = new FonnteService();
        $fonnte->sendMessage($nohp,  "*" . $this->otp . "* adalah kode verifikasi anda.\n\nWASPADA PENIPUAN. Demi keamanan, jangan bagikan kode ini. berlaku 3menit.\n\DIGOJEK Call 0813");
    }

    public function sendEmail($email)
    {
        Mail::to($email)->send(new \App\Mail\OtpVerification($this->otp));
    }
}
