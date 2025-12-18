<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckUserStatus Middleware
 * 
 * Checks if the authenticated user's account is active (not suspended).
 * If the user is suspended, revokes all tokens and returns 403 error.
 * 
 * This middleware ensures that if a user's account is blocked while they
 * are logged in, they will be immediately logged out on their next request.
 * 
 * @package App\Http\Middleware
 */
class CheckUserStatus
{
    /**
     * Handle an incoming request.
     * 
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Check if user account is suspended
        if (!$user->isActive()) {
            // Revoke all tokens to force logout
            $user->tokens()->delete();

            return response()->json([
                'success' => false,
                'message' => 'تم تعطيل حسابك. يرجى التواصل مع الدعم',
            ], 403);
        }

        return $next($request);
    }
}

