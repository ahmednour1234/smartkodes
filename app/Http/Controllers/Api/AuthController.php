<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseApiController
{
    /**
     * Handle login request
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

        if (!Auth::attempt($credentials)) {
            return $this->unauthorizedResponse('Invalid credentials');
        }

        $user = Auth::user();

        // Check tenant status
        if ($user->tenant_id && $user->tenant && $user->tenant->status !== 1) {
            return $this->forbiddenResponse('Your tenant account has been suspended');
        }

        // Delete existing tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tenant_id' => $user->tenant_id,
            ],
            'token' => $token,
            'token_type' => 'bearer',
        ], 'Login successful');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            Auth::logout();

            return $this->successResponse(null, 'Logged out successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to logout', 500);
        }
    }

    /**
     * Get current user
     */
    public function user(Request $request)
    {
        try {
            $user = $request->user();

            return $this->successResponse([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tenant_id' => $user->tenant_id,
                'roles' => $user->roles->pluck('name'),
            ], 'User retrieved successfully');
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('User not authenticated');
        }
    }
}
