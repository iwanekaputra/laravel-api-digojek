<?php

namespace App\Models;

use App\Services\FonnteService;
use App\Services\WhatsAppGatewayService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;


class OtpDriver extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'otp',
        'expire_at'
    ];


    public $with = ['driver'];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function sendSMS($receiverNumber)

    {
        $message = "Login OTP is " . $this->otp;
        try {
            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_TOKEN");
            $twilio_number = getenv("TWILIO_FROM");

            $client = new Client($account_sid, $auth_token);

            $client->messages->create($receiverNumber, [
                'from' => $twilio_number,
                'body' => $message
            ]);

            info('SMS Sent Successfully.');
        } catch (Exception $e) {

            info("Error: " . $e->getMessage());
        }
    }


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
