<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Services\WalletWebhookService;
use Illuminate\Support\Facades\Log;

/**
 * NotificationService
 * 
 * Handles all notification-related operations including:
 * - Creating and storing notifications in database
 * - Managing notification read/unread status
 * - Sending webhooks to Wallet System
 * 
 * @package App\Services
 */
class NotificationService
{
    protected WalletWebhookService $webhookService;

    public function __construct(WalletWebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Send a notification to a user.
     * 
     * Creates notification in database and sends webhook to Wallet System.
     * 
     * @param User $user
     * @param string $type Notification type
     * @param string $title Notification title (Arabic)
     * @param string $body Notification body (Arabic)
     * @param array|null $data Additional data
     * @return Notification
     */
    public function send(User $user, string $type, string $title, string $body, ?array $data = null): Notification
    {
        // Create notification in database
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'read' => false,
        ]);

        // Send webhook to Wallet System (async, don't wait for response)
        try {
            $this->webhookService->sendNotification($notification);
        } catch (\Exception $e) {
            // Log error but don't fail notification creation
            Log::error('Failed to send notification webhook', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $notification;
    }

    /**
     * Mark notification as read.
     * 
     * @param int $notificationId
     * @param int $userId
     * @return bool
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if (!$notification) {
            return false;
        }

        return $notification->markAsRead();
    }

    /**
     * Mark all notifications as read for a user.
     * 
     * @param User $user
     * @return int Number of notifications marked as read
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Get unread notifications count for a user.
     * 
     * @param User $user
     * @return int
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotificationsCount();
    }

    /**
     * Send transaction received notification.
     * 
     * @param User $user
     * @param float $amount
     * @param string $fromUserName
     * @param string $referenceId
     * @return Notification
     */
    public function sendTransactionReceived(User $user, float $amount, string $fromUserName, string $referenceId): Notification
    {
        return $this->send(
            $user,
            'transaction_received',
            'تم استلام تحويل',
            "تم استلام مبلغ {$amount} نقطة من {$fromUserName}",
            [
                'transaction_type' => 'transfer',
                'amount' => $amount,
                'from_user' => $fromUserName,
                'reference_id' => $referenceId,
            ]
        );
    }

    /**
     * Send transaction sent notification.
     * 
     * @param User $user
     * @param float $amount
     * @param string $toUserName
     * @param string $referenceId
     * @return Notification
     */
    public function sendTransactionSent(User $user, float $amount, string $toUserName, string $referenceId): Notification
    {
        return $this->send(
            $user,
            'transaction_sent',
            'تم إرسال تحويل',
            "تم إرسال مبلغ {$amount} نقطة إلى {$toUserName}",
            [
                'transaction_type' => 'transfer',
                'amount' => $amount,
                'to_user' => $toUserName,
                'reference_id' => $referenceId,
            ]
        );
    }

    /**
     * Send payment received notification (for stores).
     * 
     * @param User $store
     * @param float $amount
     * @param string $fromUserName
     * @param string $referenceId
     * @return Notification
     */
    public function sendPaymentReceived(User $store, float $amount, string $fromUserName, string $referenceId): Notification
    {
        return $this->send(
            $store,
            'payment_received',
            'تم استلام دفعة',
            "تم استلام مبلغ {$amount} نقطة من {$fromUserName}",
            [
                'transaction_type' => 'purchase',
                'amount' => $amount,
                'from_user' => $fromUserName,
                'reference_id' => $referenceId,
            ]
        );
    }

    /**
     * Send payment sent notification (for users).
     * 
     * @param User $user
     * @param float $amount
     * @param string $storeName
     * @param string $referenceId
     * @return Notification
     */
    public function sendPaymentSent(User $user, float $amount, string $storeName, string $referenceId): Notification
    {
        return $this->send(
            $user,
            'payment_sent',
            'تم الدفع بنجاح',
            "تم دفع مبلغ {$amount} نقطة إلى {$storeName}",
            [
                'transaction_type' => 'purchase',
                'amount' => $amount,
                'store_name' => $storeName,
                'reference_id' => $referenceId,
            ]
        );
    }

    /**
     * Send external payment received notification (for stores).
     * 
     * @param User $store
     * @param float $amount
     * @param string $referenceId
     * @param string|null $note
     * @return Notification
     */
    public function sendExternalPaymentReceived(User $store, float $amount, string $referenceId, ?string $note = null): Notification
    {
        $body = $note 
            ? "تم تسجيل دفعة خارجية بقيمة {$amount} نقطة. ملاحظة: {$note}"
            : "تم تسجيل دفعة خارجية بقيمة {$amount} نقطة من قبل الإدارة";

        return $this->send(
            $store,
            'external_payment_received',
            'تم تسجيل دفعة خارجية',
            $body,
            [
                'transaction_type' => 'debit',
                'amount' => $amount,
                'reference_id' => $referenceId,
                'note' => $note,
            ]
        );
    }
}

