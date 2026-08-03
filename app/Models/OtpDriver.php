<?php

namespace App\Models;

use App\Services\FonnteService;
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

        $fonnte = new FonnteService();
        $fonnte->sendMessage($nohp,  "*" . $this->otp . "* adalah kode verifikasi anda.\n\nWASPADA PENIPUAN. Demi keamanan, jangan bagikan kode ini. berlaku 3menit.\n\DIGOJEK Call 0813");
    }

    public function sendEmail($email)
    {
        Mail::to($email)->send(new \App\Mail\OtpVerification($this->otp));
    }
}
