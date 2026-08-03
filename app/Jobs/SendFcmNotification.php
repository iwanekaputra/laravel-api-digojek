<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendFcmNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $notifications;

    public function __construct(array $notifications)
    {
        $this->notifications = $notifications;
    }

    public function handle(): void
    {
        // ⬇️ HARUS PALING ATAS
        putenv(
            'GOOGLE_APPLICATION_CREDENTIALS=' .
                storage_path('app/firebase/firebase_credential.json')
        );

        $accessToken = Cache::get('google_access_token');

        if (!$accessToken) {
            $client = new \Google_Client();
            $client->useApplicationDefaultCredentials();
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

            $client->fetchAccessTokenWithAssertion();
            $tokenData = $client->getAccessToken();

            $accessToken = $tokenData['access_token'];

            // Simpan 55 menit (aman sebelum expired)
            Cache::put('google_access_token', $accessToken, 55);
        }

        foreach ($this->notifications as $notification) {
            $response = Http::withToken($accessToken)
                ->post('https://fcm.googleapis.com/v1/projects/gpar-ddefa/messages:send', [
                    'message' => [
                        'token' => $notification['token'],
                        'notification' => [
                            'title' => $notification['title'],
                            'body'  => $notification['body'],
                        ],
                        'android' => [
                            'priority' => 'HIGH',
                        ],
                    ],
                ]);

            if ($response->failed()) {
                Log::error('FCM send failed', [
                    'response' => $response->json(),
                    'token' => $notification['token'],
                ]);
            }
        }
    }
}
