<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
<<<<<<< HEAD
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
=======
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',  // <-- This line was added
    apiPrefix: 'api',                    // <-- This line was added
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
>>>>>>> bb431f6 (wqeeqw)
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => \App\Http\Middleware\TenantMiddleware::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
        ]);

        // Add Sanctum middleware for API routes
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle validation exceptions
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $responseService = app(\App\Services\ApiResponseService::class);
                return $responseService->validationError(
                    $e->errors(),
                    'Validation failed'
                );
            }
        });

        // Handle authentication exceptions
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $responseService = app(\App\Services\ApiResponseService::class);
                return $responseService->unauthorized('Unauthenticated');
            }
        });

        // Handle authorization exceptions
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $responseService = app(\App\Services\ApiResponseService::class);
                return $responseService->forbidden('This action is unauthorized');
            }
        });

        // Handle model not found exceptions
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $responseService = app(\App\Services\ApiResponseService::class);
                return $responseService->notFound('Resource not found');
            }
        });

        // Handle JWT exceptions (if JWT package is installed)
        if (class_exists(\Tymon\JWTAuth\Exceptions\TokenExpiredException::class)) {
            $exceptions->render(function (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e, $request) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    $responseService = app(\App\Services\ApiResponseService::class);
                    return $responseService->unauthorized('Token has expired');
                }
            });
        }

        if (class_exists(\Tymon\JWTAuth\Exceptions\TokenInvalidException::class)) {
            $exceptions->render(function (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e, $request) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    $responseService = app(\App\Services\ApiResponseService::class);
                    return $responseService->unauthorized('Token is invalid');
                }
            });
        }

        if (class_exists(\Tymon\JWTAuth\Exceptions\JWTException::class)) {
            $exceptions->render(function (\Tymon\JWTAuth\Exceptions\JWTException $e, $request) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    $responseService = app(\App\Services\ApiResponseService::class);
                    return $responseService->unauthorized('Token error');
                }
            });
        }

        // Handle general exceptions for API requests
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $responseService = app(\App\Services\ApiResponseService::class);

                // Log the error
                \Illuminate\Support\Facades\Log::error('API Error', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // In production, don't expose error details
                if (config('app.debug')) {
                    return $responseService->serverError(
                        $e->getMessage(),
                        'SERVER_ERROR'
                    );
                }

                return $responseService->serverError(
                    'An error occurred while processing your request',
                    'SERVER_ERROR'
                );
            }
        });
    })->create();
