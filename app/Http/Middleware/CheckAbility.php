<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckAbility Middleware
 * 
 * Checks if the authenticated user's token has the required ability.
 * Used with Laravel Sanctum to verify token abilities.
 * 
 * Usage: ->middleware('ability:wallet:pay')
 * 
 * @package App\Http\Middleware
 */
class CheckAbility
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$abilities
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالدخول. يرجى تسجيل الدخول.',
            ], 401);
        }

        $user = auth()->user();
        
        // Check if token has at least one of the required abilities
        // Laravel Sanctum provides tokenCan() method to check abilities
        $hasAbility = false;
        foreach ($abilities as $ability) {
            if ($user->tokenCan($ability)) {
                $hasAbility = true;
                break;
            }
        }

        if (!$hasAbility) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك الصلاحية للوصول إلى هذا المورد',
            ], 403);
        }

        return $next($request);
    }
}

