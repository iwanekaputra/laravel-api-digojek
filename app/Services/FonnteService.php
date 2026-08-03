<?php

namespace App\Services;

use App\Models\FonnteSetting;
use App\Models\FontneSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected string $apiUrl = 'https://api.fonnte.com';
    protected ?string $token = null;

    public function __construct()
    {
        $this->loadToken();
    }

    /**
     * Load token from database
     */
    protected function loadToken(): void
    {
        $setting = FonnteSetting::where('is_active', true)->first();
        $this->token = $setting?->token;
    }

    /**
     * Save new token
     */
    public function saveToken(string $token, string $deviceName = null): bool
    {
        try {
            // Deactivate all existing tokens first
            FonnteSetting::query()->update(['is_active' => false]);

            // Create new token entry
            FonnteSetting::create([
                'token' => $token,
                'device_name' => $deviceName,
                'is_active' => true,
            ]);

            $this->token = $token;
            return true;
        } catch (\Exception $e) {
            Log::error('Error saving Fonnte token: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get current token
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Update existing token
     */
    public function updateToken(int $id, string $token, string $deviceName = null, bool $isActive = true): bool
    {
        try {
            $setting = FonnteSetting::findOrFail($id);

            // If setting this token as active, deactivate others
            if ($isActive) {
                FonnteSetting::where('id', '!=', $id)->update(['is_active' => false]);
            }

            $setting->update([
                'token' => $token,
                'device_name' => $deviceName,
                'is_active' => $isActive,
            ]);

            // If setting is active, update current token
            if ($isActive) {
                $this->token = $token;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error updating Fonnte token: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if service has token
     */
    public function hasToken(): bool
    {
        return !empty($this->token);
    }

    /**
     * Send message to a phone number
     */
    public function sendMessage(string $phone, string $message, array $options = []): array
    {
        if (!$this->hasToken()) {
            return [
                'status' => false,
                'message' => 'Fonnte token not configured'
            ];
        }

        $start = microtime(true);

        try {
            $response = Http::withOptions([
                'proxy' => env('HTTP_PROXY'),
            ])->withHeaders([
                'Authorization' => $this->token
            ])->post($this->apiUrl . '/send', array_merge([
                'target' => $phone,
                'message' => $message,
            ], $options));

            $duration = round((microtime(true) - $start) * 1000, 2);
            Log::channel('apifonnte')->info('External API call', [
                'url'      => $this->apiUrl . '/send',
                'payload'  => array_merge([
                    'target' => $phone,
                    'message' => $message,
                ], $options),
                'status'   => $response->status(),
                'body'     => $response->json(),
                'duration' => $duration . 'ms',
                'ip'       => request()->ip(),
            ]);

            $result = $response->json();
            return [
                'status' => $response->successful(),
                'data' => $result,
                'message' => $result['message'] ?? ($response->successful() ? 'Message sent successfully' : 'Failed to send message')
            ];
        } catch (\Exception $e) {
            Log::error('Error sending Fonnte message: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send message to multiple phone numbers
     */
    public function sendBulkMessage(array $phones, string $message, array $options = []): array
    {
        if (!$this->hasToken()) {
            return [
                'status' => false,
                'message' => 'Fonnte token not configured'
            ];
        }

        try {
            $response = Http::withOptions([
                'proxy' => env('HTTP_PROXY'),
            ])->withHeaders([
                'Authorization' => $this->token
            ])->post($this->apiUrl . '/send', array_merge([
                'target' => implode(',', $phones),
                'message' => $message,
            ], $options));

            $result = $response->json();
            return [
                'status' => $response->successful(),
                'data' => $result,
                'message' => $result['message'] ?? ($response->successful() ? 'Messages sent successfully' : 'Failed to send messages')
            ];
        } catch (\Exception $e) {
            Log::error('Error sending bulk Fonnte messages: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send a template message
     */
    public function sendTemplateMessage(string $phone, string $templateName, array $variables = [], array $options = []): array
    {
        if (!$this->hasToken()) {
            return [
                'status' => false,
                'message' => 'Fonnte token not configured'
            ];
        }

        try {
            $response = Http::withOptions([
                'proxy' => env('HTTP_PROXY'),
            ])->withHeaders([
                'Authorization' => $this->token
            ])->post($this->apiUrl . '/send', array_merge([
                'target' => $phone,
                'template' => $templateName,
                'variables' => json_encode($variables),
            ], $options));

            $result = $response->json();
            return [
                'status' => $response->successful(),
                'data' => $result,
                'message' => $result['message'] ?? ($response->successful() ? 'Template message sent successfully' : 'Failed to send template message')
            ];
        } catch (\Exception $e) {
            Log::error('Error sending Fonnte template message: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check device status
     */
    public function checkStatus(): array
    {
        if (!$this->hasToken()) {
            return [
                'status' => false,
                'message' => 'Fonnte token not configured'
            ];
        }

        try {
            $response = Http::withOptions([
                'proxy' => env('HTTP_PROXY'),
            ])->withHeaders([
                'Authorization' => $this->token
            ])->get($this->apiUrl . '/device');

            $result = $response->json();
            return [
                'status' => $response->successful(),
                'data' => $result,
                'message' => $result['message'] ?? ($response->successful() ? 'Device status retrieved' : 'Failed to get device status')
            ];
        } catch (\Exception $e) {
            Log::error('Error checking Fonnte device status: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all active tokens
     */
    public function getAllTokens()
    {
        return FonnteSetting::orderBy('is_active', 'desc')->get();
    }



    /**
     * Send price update notification
     */
    public function sendPriceUpdateNotification(array $updatedProducts, int $totalUpdated, array $options = []): array
    {
        if (!$this->hasToken()) {
            return [
                'status' => false,
                'message' => 'Fonnte token not configured'
            ];
        }

        $message = $this->buildPriceUpdateMessage($updatedProducts, $totalUpdated);

        // Default admin phone - bisa diambil dari config atau env
        $adminPhone = $options['admin_phone'] ?? env('ADMIN_PHONE', '0895404816031');

        return $this->sendMessage($adminPhone, $message, $options);
    }

    /**
     * Build price update notification message
     */
    private function buildPriceUpdateMessage(array $updatedProducts, int $totalUpdated): string
    {
        $message = "🔄 *Price Update Alert*\n\n";
        $message .= "Total products updated: *{$totalUpdated}*\n";
        $message .= "Time: " . now()->format('d/m/Y H:i:s') . "\n\n";

        if (empty($updatedProducts)) {
            $message .= "No product details available.";
            return $message;
        }

        $message .= "📊 *Details:*\n";

        // Group by reseller for better organization
        $groupedByReseller = collect($updatedProducts)->groupBy('reseller');

        foreach ($groupedByReseller as $reseller => $products) {
            $message .= "\n*{$reseller}:*\n";

            foreach ($products as $product) {
                $message .= "• {$product['product']} ({$product['code']})\n";

                foreach ($product['changes'] as $field => $change) {
                    if ($field === 'price' || $field === 'buying_price') {
                        $oldFormatted = 'Rp ' . number_format($change['old'], 0, ',', '.');
                        $newFormatted = 'Rp ' . number_format($change['new'], 0, ',', '.');
                        $message .= "  - {$field}: {$oldFormatted} → {$newFormatted}\n";
                    } else {
                        $message .= "  - {$field}: {$change['old']} → {$change['new']}\n";
                    }
                }
            }
        }

        return $message;
    }

    /**
     * Send price update to multiple admins
     */
    public function sendPriceUpdateToAdmins(array $updatedProducts, int $totalUpdated, array $adminPhones = [], array $options = []): array
    {
        if (!$this->hasToken()) {
            return [
                'status' => false,
                'message' => 'Fonnte token not configured'
            ];
        }

        // Default admin phones if not provided
        if (empty($adminPhones)) {
            $adminPhones = [
                env('ADMIN_PHONE', '0895404816031'),
                env('ADMIN_PHONE_2', ''), // Optional second admin
            ];
            $adminPhones = array_filter($adminPhones); // Remove empty values
        }

        if (empty($adminPhones)) {
            return [
                'status' => false,
                'message' => 'No admin phone numbers configured'
            ];
        }

        $message = $this->buildPriceUpdateMessage($updatedProducts, $totalUpdated);

        return $this->sendBulkMessage($adminPhones, $message, $options);
    }

    /**
     * Send price alert for critical changes
     */
    public function sendCriticalPriceAlert(array $criticalProducts, array $options = []): array
    {
        if (!$this->hasToken()) {
            return [
                'status' => false,
                'message' => 'Fonnte token not configured'
            ];
        }

        $message = "🚨 *CRITICAL PRICE ALERT*\n\n";
        $message .= "⚠️ Major price changes detected!\n";
        $message .= "Time: " . now()->format('d/m/Y H:i:s') . "\n\n";

        foreach ($criticalProducts as $product) {
            $message .= "🔥 *{$product['product']}* ({$product['code']})\n";
            $message .= "Reseller: {$product['reseller']}\n";

            foreach ($product['changes'] as $field => $change) {
                if ($field === 'price' || $field === 'buying_price') {
                    $oldFormatted = 'Rp ' . number_format($change['old'], 0, ',', '.');
                    $newFormatted = 'Rp ' . number_format($change['new'], 0, ',', '.');
                    $percentage = $change['old'] > 0 ? round((($change['new'] - $change['old']) / $change['old']) * 100, 2) : 0;
                    $message .= "💰 {$field}: {$oldFormatted} → {$newFormatted} ({$percentage}%)\n";
                }
            }
            $message .= "\n";
        }

        $adminPhone = $options['admin_phone'] ?? env('ADMIN_PHONE', '0895404816031');

        return $this->sendMessage($adminPhone, $message, $options);
    }
}
