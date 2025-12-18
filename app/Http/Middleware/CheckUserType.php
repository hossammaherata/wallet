<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckUserType Middleware
 * 
 * Ensures that only 'user' and 'store' types can access API endpoints.
 * Admins are blocked from accessing mobile API routes.
 * 
 * @package App\Http\Middleware
 */
class CheckUserType
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
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = auth()->user();

        // Block admins from accessing API
        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admins cannot access the API',
            ], 403);
        }

        // Only allow 'user' and 'store' types
        if (!$user->isRegularUser() ) {
            return response()->json([
                'success' => false,
                'message' => 'User type not supported',
            ], 403);
        }

        return $next($request);
    }
}

