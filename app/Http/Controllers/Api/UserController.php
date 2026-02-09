<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\User\SetPasscodeRequest;
use App\Http\Requests\Api\User\VerifyPasscodeRequest;
use App\Http\Requests\Api\User\ForgotPasswordRequest;
use App\Http\Requests\Api\User\ResetPasswordRequest;
use App\Http\Resources\Api\UserResource;
use App\Repositories\UserRepository;
use App\Helpers\EmailHelper;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends BaseApiController
{
    protected UserRepository $repository;

    public function __construct(ApiResponseService $response, UserRepository $repository)
    {
        parent::__construct($response);
        $this->repository = $repository;
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user()->load('roles', 'permissions', 'tenant');
            
            return $this->successResponse(
                new UserResource($user),
                'User retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve user: ' . $e->getMessage());
        }
    }

    /**
     * Set passcode for authenticated user
     */
    public function setPasscode(SetPasscodeRequest $request)
    {
        try {
            $user = $request->user();
            $passcode = $request->validated()['passcode'];

            // Set passcode
            $user = $this->repository->setPasscode($user->id, $passcode);

            // Send email with passcode
            EmailHelper::sendPasscode($user->email, $passcode);

            return $this->successResponse(
                new UserResource($user->fresh()),
                'Passcode set successfully. An email has been sent with your passcode.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to set passcode: ' . $e->getMessage());
        }
    }

    /**
     * Verify passcode for authenticated user
     */
    public function verifyPasscode(VerifyPasscodeRequest $request)
    {
        try {
            $user = $request->user();
            $passcode = $request->validated()['passcode'];

            // Check if user has passcode set
            if (!$this->repository->hasPasscode($user->id)) {
                return $this->errorResponse(
                    'Passcode not set. Please set a passcode first.',
                    400,
                    [],
                    'PASSCODE_NOT_SET'
                );
            }

            // Verify passcode
            $isValid = $this->repository->verifyPasscode($user->id, $passcode);

            if (!$isValid) {
                return $this->errorResponse(
                    'Invalid passcode',
                    401,
                    [],
                    'INVALID_PASSCODE'
                );
            }

            return $this->successResponse(
                ['verified' => true],
                'Passcode verified successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to verify passcode: ' . $e->getMessage());
        }
    }

    /**
     * Request password reset
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $email = $request->validated()['email'];
            
            // Find user
            $user = $this->repository->findByEmail($email);
            
            if (!$user) {
                // Don't reveal if user exists or not for security
                return $this->successResponse(
                    null,
                    'If a user with that email exists, a password reset link has been sent.'
                );
            }

            // Generate password reset token using Laravel's Password broker
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                // Get the token from password_reset_tokens table
                $passwordReset = \DB::table('password_reset_tokens')
                    ->where('email', $email)
                    ->first();
                
                if ($passwordReset) {
                    $token = $passwordReset->token;
                    $resetUrl = config('app.frontend_url', config('app.url')) . '/reset-password?token=' . $token . '&email=' . urlencode($email);
                    
                    // Send custom email with the reset link
                    EmailHelper::sendPasswordReset($user->email, $token, $resetUrl);
                }

                return $this->successResponse(
                    null,
                    'If a user with that email exists, a password reset link has been sent.'
                );
            }

            return $this->errorResponse(
                'Unable to send password reset link',
                500,
                [],
                'RESET_LINK_FAILED'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process password reset request: ' . $e->getMessage());
        }
    }

    /**
     * Reset password
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return $this->successResponse(
                    null,
                    'Password has been reset successfully'
                );
            }

            return $this->errorResponse(
                'Unable to reset password. The token may be invalid or expired.',
                400,
                [],
                'PASSWORD_RESET_FAILED'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to reset password: ' . $e->getMessage());
        }
    }

    /**
     * Get list of users (paginated)
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $filters = $request->only(['search', 'role']);

            $users = $this->repository->getWithRoles($filters, $perPage);

            return $this->paginatedResponse(
                UserResource::collection($users),
                'Users retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve users: ' . $e->getMessage());
        }
    }

    /**
     * Get single user by ID
     */
    public function show(string $id)
    {
        try {
            $user = $this->repository->with(['roles', 'permissions', 'tenant'])->find($id);
            
            return $this->successResponse(
                new UserResource($user),
                'User retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->notFoundResponse('User not found');
        }
    }
}

