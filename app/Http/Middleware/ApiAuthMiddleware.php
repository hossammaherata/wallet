<?php

namespace App\Http\Middleware;

use App\Services\ApiAuthService;
use Closure;
use Illuminate\Http\Request;

class ApiAuthMiddleware
{
    protected $apiAuthService;

    public function __construct(ApiAuthService $apiAuthService)
    {
        $this->apiAuthService = $apiAuthService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $validation = $this->apiAuthService->validateRequest($request);

        if (!$validation['valid']) {
            return response()->json([
                'status' => 'error',
                'message' => $validation['message']
            ], 401);
        }

        return $next($request);
    }
}
