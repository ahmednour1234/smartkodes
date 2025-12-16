<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply tenant logic to authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Check if user is super admin (no tenant_id)
        if ($user->tenant_id === null) {
            $this->handleSuperAdminTenant($request);
        } else {
            // Tenant users cannot access admin routes
            if ($request->is('admin') || $request->is('admin/*')) {
                Log::warning('TenantMiddleware: Tenant user attempted to access admin route', [
                    'user_id' => $user->id,
                    'tenant_id' => $user->tenant_id,
                    'route' => $request->path()
                ]);

                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Unauthorized. You do not have access to this area.'], 403);
                }

                return redirect()->route('tenant.dashboard')->with('error', 'You do not have access to the admin area.');
            }

            $result = $this->handleRegularUserTenant($user, $request);
            if ($result instanceof Response) {
                return $result;
            }
        }

        return $next($request);
    }

    /**
     * Handle tenant context for super admin users
     */
    private function handleSuperAdminTenant(Request $request): void
    {
        // Superadmin can access all tenants, so don't set a specific tenant context
        // Controllers can check if session('tenant_context.current_tenant') is null to show all data
    }

    /**
     * Handle tenant context for regular tenant users
     */
    private function handleRegularUserTenant($user, Request $request)
    {
        Log::info('TenantMiddleware: Handling regular user tenant', ['user_id' => $user->id, 'tenant_id' => $user->tenant_id]);

        // Load tenant relationship if not already loaded
        if (!$user->relationLoaded('tenant')) {
            $user->load('tenant');
        }

        $tenant = $user->tenant;
        Log::info('TenantMiddleware: Tenant loaded', ['tenant' => $tenant ? $tenant->toArray() : null]);

        if ($tenant && $tenant->status == 1) { // Only allow active tenants
            session(['tenant_context.current_tenant' => $tenant]);
            Log::info('TenantMiddleware: Tenant context set successfully', ['tenant_id' => $tenant->id]);
        } elseif ($tenant && $tenant->status != 1) {
            // Tenant exists but is not active
            Log::info('TenantMiddleware: Tenant inactive, logging out user', ['tenant_status' => $tenant->status]);
            return $this->handleInactiveTenant($request);
        } else {
            // User has no tenant assigned
            Log::info('TenantMiddleware: No tenant assigned, logging out user');
            return $this->handleMissingTenant($request);
        }
    }

    /**
     * Handle case where user's tenant is inactive
     */
    private function handleInactiveTenant(Request $request): Response
    {
        Auth::logout();

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Your tenant account has been suspended. Please contact support.'], 403);
        }

        session()->invalidate();
        session()->regenerateToken();

        return redirect('/login')->with('error', 'Your tenant account has been suspended. Please contact support.');
    }

    /**
     * Handle case where user has no tenant assigned
     */
    private function handleMissingTenant(Request $request): Response
    {
        Auth::logout();

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Your account is not associated with any tenant. Please contact support.'], 403);
        }

        session()->invalidate();
        session()->regenerateToken();

        return redirect('/login')->with('error', 'Your account is not associated with any tenant. Please contact support.');
    }
}
