<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * AuthController
 * 
 * Handles authentication endpoints for mobile applications.
 * 
 * IMPORTANT: This API is ONLY for 'user' and 'store' types.
 * Admins CANNOT use this API and must use the web admin panel instead.
 * 
 * Features:
 * - User registration (creates wallet automatically, type='user' only)
 * - User/Store login (generates Sanctum token with appropriate abilities)
 * 
 * Token abilities are assigned based on user type:
 * - Users: wallet:read, wallet:pay, wallet:transfer
 * - Stores: wallet:read, wallet:receive
 * 
 * All responses use ApiResponse trait for consistent format.
 * All error messages are in Arabic.
 * 
 * @package App\Http\Controllers\Api
 */
class AuthController extends Controller
{
    use ApiResponse;

    /**
     * UserService instance for business logic.
     * 
     * @var UserService
     */
    protected UserService $userService;

    /**
     * Create a new AuthController instance.
     * 
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Register a new user (Mobile only).
     * 
     * Only creates regular users (type='user').
     * Stores must be created through the admin panel.
     * Admins cannot be created through this endpoint.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->register($request->validated());

            // Create token with 1 year expiration
            $token = $user->createToken(
                'mobile-app',
                ['wallet:read', 'wallet:pay', 'wallet:transfer'],
                now()->addYear() // Token expires after 1 year
            )->plainTextToken;

            return $this->successResponse([
                'user' => new UserResource($user),
                'token' => $token,
            ], 'تم التسجيل بنجاح', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('فشل التسجيل: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Login for User / Store only.
     * 
     * Admins cannot login through this API endpoint.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->userService->authenticate(
            $request->identifier,
            $request->password ?? null // Password optional (required for stores, not for users)
        );

        if (!$user) {
            return $this->errorResponse('بيانات الدخول غير صحيحة أو الحساب معطل', null, 401);
        }

        // Double check: Ensure user is not admin (API is only for users and stores)
        if ($user->isAdmin()) {
            return $this->errorResponse('لا يمكن للمديرين تسجيل الدخول من خلال التطبيق', null, 403);
        }

        // Check if user is active (not suspended)
        if (!$user->isActive()) {
            return $this->errorResponse('تم تعطيل حسابك. يرجى التواصل مع الدعم', null, 403);
        }

        // Determine abilities based on user type
        $abilities = [];
        if ($user->isRegularUser()) {
            $abilities = ['wallet:read', 'wallet:pay', 'wallet:transfer'];
        } elseif ($user->isStore()) {
            $abilities = ['wallet:read', 'wallet:receive'];
        }

        // Create token with 1 year expiration
        $token = $user->createToken(
            'mobile-app',
            $abilities,
            now()->addYear() // Token expires after 1 year
        )->plainTextToken;

        return $this->successResponse([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'تم تسجيل الدخول بنجاح');
    }
}

