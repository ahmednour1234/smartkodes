<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip for super admin users (no tenant_id)
        if (!$user || $user->tenant_id === null) {
            return $next($request);
        }

        // Get tenant with subscription
        $tenant = $user->tenant()->with('subscription')->first();

        if (!$tenant) {
            return redirect()->route('login')
                ->with('error', 'Tenant account not found.');
        }

        // Check if tenant has an active subscription
        $subscription = $tenant->subscription;

        if (!$subscription) {
            // No active subscription - redirect to subscription page with warning
            if ($request->route()->getName() !== 'tenant.subscription.index') {
                return redirect()->route('tenant.subscription.index')
                    ->with('warning', 'No active subscription found. Please contact your administrator.');
            }
            return $next($request);
        }

        // Check if subscription is expired
        if ($subscription->status && $subscription->end_date->isPast()) {
            // Subscription expired - redirect to subscription page with error
            if ($request->route()->getName() !== 'tenant.subscription.index') {
                return redirect()->route('tenant.subscription.index')
                    ->with('error', 'Your subscription has expired. Please renew to continue using the system.');
            }
            return $next($request);
        }

        // Check if subscription is expiring soon (within 7 days)
        if ($subscription->status && $subscription->end_date->diffInDays(now()) <= 7 && $subscription->end_date->isFuture()) {
            // Add a warning flash message
            session()->flash('warning', 'Your subscription expires in ' . $subscription->end_date->diffInDays(now()) . ' days. Please renew soon.');
        }

        return $next($request);
    }
}
