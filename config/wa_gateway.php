<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi ini digunakan oleh WhatsAppGatewayService untuk mengirimkan
    | pesan template WhatsApp / OTP menggunakan service provider gateway.
    |
    */

    'enabled' => env('WA_GATEWAY_ENABLED', true),

    'base_url' => env('WA_GATEWAY_BASE_URL', 'https://chat.api.co.id/api/v1/public'),

    'token' => env('WA_GATEWAY_TOKEN'),

    'whatsapp_phone_number_id' => env('WA_GATEWAY_PHONE_NUMBER_ID'),

    'template_name' => env('WA_GATEWAY_TEMPLATE_NAME', 'selamat_datang_pelanggan'),

    'language_code' => env('WA_GATEWAY_LANGUAGE_CODE', 'id'),

    'timeout' => env('WA_GATEWAY_TIMEOUT', 30),

];
