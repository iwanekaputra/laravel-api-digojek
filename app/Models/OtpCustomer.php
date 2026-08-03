<?php

namespace App\Models;

use App\Services\FonnteService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;


class OtpCustomer extends Model
{
    use HasFactory;


    protected $fillable = [
        'customer_id',
        'otp',
        'expire_at'
    ];

    public $with = ['customer'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
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
        $fonnte->sendMessage(
            $nohp,
            "*" . $this->otp . "* adalah kode verifikasi anda.\n\n" .
                "WASPADA PENIPUAN. Demi keamanan, jangan bagikan kode ini. berlaku 3menit.\n\n" .
                "*UPDATE* terbaru aplikasi DIGOJEK, mohon untuk mengirimkan NIK KTP anda ke Chat ini untuk aktifasi kembali akun anda.\n" .
                "Mohon di isi\n" .
                "NIK KTP:\n\n" .
                "DIGOJEK Call 0813"
        );
    }

    public function sendEmail($email)
    {
        Mail::to($email)->send(new \App\Mail\OtpVerification($this->otp));
    }
}
