<?php

namespace App\Models;

use App\Services\FonnteService;
use App\Services\WhatsAppGatewayService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class OtpRegisterDriver extends Model
{
    use HasFactory;

    protected $fillable = [
        'nohp',
        'otp',
        'expire_at'
    ];
    public function sendWa($nohp)
    {
        $waService = new WhatsAppGatewayService();
        $waService->sendOtp($nohp, $this->otp);
    }

    public function sendEmail($email)
    {
        Mail::to($email)->send(new \App\Mail\OtpVerification($this->otp));
    }
}
