<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WalletWebhookService
 * 
 * Handles sending webhooks to Wallet System.
 * Sends notifications when they are created in Midan.
 * 
 * @package App\Services
 */
class WalletWebhookService
{
    protected string $walletApiUrl;
    protected string $walletApiKey;
    protected bool $enabled;

    public function __construct()
    {
        $this->walletApiUrl = config('wallet.api_url', env('WALLET_API_URL', 'http://46.62.241.66'));
        $this->walletApiKey = config('wallet.api_key', env('WALLET_API_KEY', 'mdn-wallet-9088db076fcd7f0fad256821f8a3c3c9cba4cdcab915559e7c894d5c4e7bc564'));
        $this->enabled = config('wallet.webhook_enabled', env('WALLET_WEBHOOK_ENABLED', true));
    }

    /**
     * Send notification to Wallet System.
     * 
     * @param Notification $notification
     * @return bool Success status
     */
    public function sendNotification(Notification $notification): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $user = $notification->user;
            
            // Prepare notification data
            $data = [
                'user_id' => $user->external_refs ?? $user->id,
                'email' => $user->email,
                'phone' => $user->phone,
                'title' => $this->translateToEnglish($notification->title) ?? $notification->title,
                'title_ar' => $notification->title,
                'body' => $this->translateToEnglish($notification->body) ?? $notification->body,
                'body_ar' => $notification->body,
                'notification_type' => $notification->type,
                'external_id' => 'midan-notif-' . $notification->id,
                'metadata' => $notification->data ?? [],
            ];

            // Remove null values
            $data = array_filter($data, function ($value) {
                return $value !== null;
            });

            $url = rtrim($this->walletApiUrl, '/') . '/api/wallet/webhook/notification/';

            $response = Http::timeout(10)
                ->withHeaders([
                    'X-API-Key' => $this->walletApiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $data);
                        // dd($response->body());
            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['success']) && $responseData['success']) {
                    Log::info('Notification sent to Wallet System', [
                        'notification_id' => $notification->id,
                        'user_id' => $user->id,
                    ]);
                    return true;
                }
            }

            Log::warning('Failed to send notification to Wallet System', [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Exception while sending notification to Wallet System', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Simple translation helper.
     * Since we only store Arabic, we provide English placeholders.
     * 
     * @param string $arabicText
     * @return string
     */
    protected function translateToEnglish(string $arabicText): string
    {
        // Simple mapping for common notification types
        $translations = [
            'تم استلام تحويل' => 'Transfer Received',
            'تم إرسال تحويل' => 'Transfer Sent',
            'تم استلام دفعة' => 'Payment Received',
            'تم الدفع بنجاح' => 'Payment Successful',
            'تم تسجيل دفعة خارجية' => 'External Payment Recorded',
        ];

        // Check if we have a direct translation
        if (isset($translations[$arabicText])) {
            return $translations[$arabicText];
        }

        // For body text, try to extract amount and create generic message
        if (preg_match('/مبلغ\s+([\d.]+)\s+نقطة/', $arabicText, $matches)) {
            $amount = $matches[1];
            if (strpos($arabicText, 'استلام') !== false) {
                return "You received {$amount} points";
            } elseif (strpos($arabicText, 'إرسال') !== false) {
                return "You sent {$amount} points";
            } elseif (strpos($arabicText, 'دفع') !== false) {
                return "You paid {$amount} points";
            }
        }

        // Default: return Arabic text (better than nothing)
        return $arabicText;
    }
}


