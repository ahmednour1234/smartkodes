<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends BaseApiController
{
    /**
     * Handle login request with JWT
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse(
                $validator->errors()->toArray(),
                'Validation failed'
            );
        }

        $credentials = $request->only('email', 'password');

        try {
            // Attempt to create JWT token
            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->unauthorizedResponse('Invalid credentials');
            }
        } catch (JWTException $e) {
            return $this->errorResponse('Could not create token', 500);
        }

        $user = Auth::user();

        // Check tenant status
        if ($user->tenant_id && $user->tenant && $user->tenant->status !== 1) {
            JWTAuth::invalidate($token);
            return $this->forbiddenResponse('Your tenant account has been suspended');
        }

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'country' => $user->country,
                'tenant_id' => $user->tenant_id,
            ],
            'token' => $token,
            'token_type' => 'jwt',
            'expires_in' => JWTAuth::factory()->getTTL() * 60, // seconds
        ], 'Login successful');
    }

    /**
     * Handle logout request - invalidate JWT token
     */
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return $this->successResponse(null, 'Logged out successfully');
        } catch (JWTException $e) {
            return $this->errorResponse('Failed to logout', 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function user(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not found');
            }

            return $this->successResponse([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'country' => $user->country,
                'tenant_id' => $user->tenant_id,
                'roles' => $user->roles->pluck('name'),
            ], 'User retrieved successfully');
        } catch (JWTException $e) {
            return $this->unauthorizedResponse('User not authenticated');
        }
    }

    /**
     * Refresh JWT token
     */
    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());

            return $this->successResponse([
                'token' => $token,
                'token_type' => 'jwt',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ], 'Token refreshed successfully');
        } catch (JWTException $e) {
            return $this->unauthorizedResponse('Could not refresh token');
        }
    }
}
