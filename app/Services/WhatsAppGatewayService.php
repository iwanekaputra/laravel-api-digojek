<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class WhatsAppGatewayService
{
    public function sendOtp(string $phoneNumber, string $otp, ?string $displayName = null): array
    {
        if (! config('wa_gateway.enabled')) {
            return ['skipped' => true, 'reason' => 'disabled'];
        }

        $token = config('wa_gateway.token');
        if (! $token) {
            throw new RuntimeException('WA gateway token belum dikonfigurasi.');
        }

        $payload = [
            'phone_number' => $this->normalizePhone($phoneNumber),
            'channel' => 'whatsapp',
            'message_type' => 'template',
            'whatsapp_phone_number_id' => '',
            'template' => [
                'name' => config('wa_gateway.template_name'),
                'language' => ['code' => config('wa_gateway.language_code')],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $otp],
                        ],
                    ],
                    [
                        'type' => 'button',
                        'sub_type' => 'url',
                        'index' => 0,
                        'parameters' => [
                            ['type' => 'text', 'text' => $otp],
                        ],
                    ],
                ],
            ],
        ];

        if (config('wa_gateway.whatsapp_phone_number_id')) {
            $payload['whatsapp_phone_number_id'] = config('wa_gateway.whatsapp_phone_number_id');
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->timeout(config('wa_gateway.timeout'))
                ->post(rtrim(config('wa_gateway.base_url'), '/') . '/messages/send', $payload);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('WA OTP gateway connection failed', [
                'phone' => $payload['phone_number'],
                'message' => $e->getMessage(),
            ]);
            throw new RuntimeException('Gagal mengirim OTP WhatsApp: tidak dapat terhubung ke gateway (' . $e->getMessage() . ').');
        }

        if (! $response->successful()) {
            Log::warning('WA OTP gateway failed', [
                'phone' => $payload['phone_number'],
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException(sprintf(
                'Gagal mengirim OTP WhatsApp: HTTP %d - %s',
                $response->status(),
                $response->body() ?: '(respons kosong)'
            ));
        }

        return $response->json() ?? ['status' => $response->status()];
    }
    public function sendPengumuman(string $phoneNumber, string $otp, ?string $displayName = null): array
    {
        if (! config('wa_gateway.enabled')) {
            return ['skipped' => true, 'reason' => 'disabled'];
        }

        $token = config('wa_gateway.token');
        if (! $token) {
            throw new RuntimeException('WA gateway token belum dikonfigurasi.');
        }

        $payload = [
            'phone_number' => $this->normalizePhone($phoneNumber),
            'channel' => 'whatsapp',
            'message_type' => 'template',
            'whatsapp_phone_number_id' => '',
            'template' => [
                'name' => config('wa_gateway.template_name'),
                'language' => ['code' => config('wa_gateway.language_code')],
                'components' => [[
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $displayName ?: 'Pengguna Rojek'],
                        ['type' => 'text', 'text' => $otp],
                        ['type' => 'text', 'text' => 'Kode Login Rojek'],
                    ],
                ]],
            ],
        ];

        if (config('wa_gateway.whatsapp_phone_number_id')) {
            $payload['whatsapp_phone_number_id'] = config('wa_gateway.whatsapp_phone_number_id');
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->timeout(config('wa_gateway.timeout'))
                ->post(rtrim(config('wa_gateway.base_url'), '/') . '/messages/send', $payload);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('WA OTP gateway connection failed', [
                'phone' => $payload['phone_number'],
                'message' => $e->getMessage(),
            ]);
            throw new RuntimeException('Gagal mengirim OTP WhatsApp: tidak dapat terhubung ke gateway (' . $e->getMessage() . ').');
        }

        if (! $response->successful()) {
            Log::warning('WA OTP gateway failed', [
                'phone' => $payload['phone_number'],
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException(sprintf(
                'Gagal mengirim OTP WhatsApp: HTTP %d - %s',
                $response->status(),
                $response->body() ?: '(respons kosong)'
            ));
        }

        return $response->json() ?? ['status' => $response->status()];
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '62' . substr($phone, 1);
        }

        if (str_starts_with($phone, '62')) {
            return $phone;
        }

        return '62' . $phone;
    }
}
