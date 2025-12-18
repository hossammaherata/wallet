<?php

namespace App\Services;

class ApiAuthService
{
    /**
     * Validate API token and secret
     */
    public function validateCredentials($token, $secret)
    {
        // Get valid credentials from config
        $validToken = config('api.auth_token');
        $validSecret = config('api.auth_secret');

        // Check if credentials match
        if ($token === $validToken && $secret === $validSecret) {
            return true;
        }

        return false;
    }

    /**
     * Generate a secure token (for initial setup)
     */
    public function generateToken()
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Generate a secure secret (for initial setup)
     */
    public function generateSecret()
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Validate request headers for API authentication
     */
    public function validateRequest($request)
    {
        $token = $request->header('X-API-Token');
        $secret = $request->header('X-API-Secret');

        if (!$token || !$secret) {
            return [
                'valid' => false,
                'message' => 'Missing API credentials'
            ];
        }

        if (!$this->validateCredentials($token, $secret)) {
            return [
                'valid' => false,
                'message' => 'Invalid API credentials'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Valid credentials'
        ];
    }
}
