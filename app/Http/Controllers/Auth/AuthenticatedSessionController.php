<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        Log::info('Login attempt started');

        $request->authenticate();

        if (Auth::check()) {
            $user = Auth::user();
            Log::info('User authenticated successfully', ['user_id' => $user->id, 'tenant_id' => $user->tenant_id]);

            // Don't regenerate session immediately to avoid auth loss
            // Just regenerate the token for CSRF protection
            session()->regenerateToken();

            // Set tenant context if user is superadmin or tenant user
            if ($user->tenant_id === null) {
                $tenant = \App\Models\Tenant::where('status', 1)->first();
                if ($tenant) {
                    session(['tenant_context.current_tenant' => $tenant]);
                    Log::info('Set tenant context for superadmin', ['tenant_id' => $tenant->id]);
                }
            } else {
                // For tenant users, load their tenant and set context
                if (!$user->relationLoaded('tenant')) {
                    $user->load('tenant');
                }
                $tenant = $user->tenant;
                if ($tenant && $tenant->status == 1) {
                    session(['tenant_context.current_tenant' => $tenant]);
                    Log::info('Set tenant context for tenant user', ['user_id' => $user->id, 'tenant_id' => $tenant->id]);
                } else {
                    Log::error('Tenant user has invalid tenant', ['user_id' => $user->id, 'tenant' => $tenant]);
                    Auth::logout();
                    session()->invalidate();
                    session()->regenerateToken();
                    return redirect('/login')->withErrors(['email' => 'Your account is not properly configured. Please contact support.']);
                }
            }

            // Redirect directly based on user type to avoid an extra redirect
            if ($user->tenant_id === null) {
                Log::info('Redirecting superadmin to admin.dashboard');
                return redirect()->route('admin.dashboard');
            }

            Log::info('Redirecting tenant user to tenant.dashboard', ['tenant_id' => $user->tenant_id]);
            return redirect()->route('tenant.dashboard');
        }

        Log::error('Authentication check failed after authenticate()');
        return back()->withErrors(['email' => 'Authentication failed.']);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
