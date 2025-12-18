<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * NotificationController
 * 
 * Handles all notification-related API endpoints for mobile applications:
 * - Get all notifications with pagination
 * - Get unread notifications count
 * - Mark notification as read
 * - Mark all notifications as read
 * 
 * All endpoints require authentication (Bearer token).
 * All responses use ApiResponse trait for consistent format.
 * All error messages are in Arabic.
 * 
 * @package App\Http\Controllers\Api
 */
class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * NotificationService instance for business logic.
     * 
     * @var NotificationService
     */
    protected NotificationService $notificationService;

    /**
     * Create a new NotificationController instance.
     * 
     * @param NotificationService $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all notifications for authenticated user.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 15);
        $read = $request->get('read'); // null, true, or false

        $query = Notification::where('user_id', $user->id);

        if ($read !== null) {
            $query->where('read', filter_var($read, FILTER_VALIDATE_BOOLEAN));
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->successResponse([
            'notifications' => NotificationResource::collection($notifications->items()),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ], 'تم جلب الإشعارات بنجاح');
    }

    /**
     * Get unread notifications count.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = $this->notificationService->getUnreadCount($user);

        return $this->successResponse([
            'unread_count' => $count,
        ], 'تم جلب عدد الإشعارات غير المقروءة بنجاح');
    }

    /**
     * Mark notification as read.
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $success = $this->notificationService->markAsRead($id, $user->id);

        if (!$success) {
            return $this->errorResponse('الإشعار غير موجود', null, 404);
        }

        return $this->successResponse(null, 'تم تحديد الإشعار كمقروء');
    }

    /**
     * Mark all notifications as read.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = $this->notificationService->markAllAsRead($user);

        return $this->successResponse([
            'marked_count' => $count,
        ], "تم تحديد {$count} إشعار كمقروء");
    }
}

