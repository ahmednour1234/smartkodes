<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    /**
     * Get the view prefix based on current route.
     */
    private function getViewPrefix(): string
    {
        $prefix = request()->route()->getPrefix();
        return str_contains($prefix, 'tenant') ? 'tenant' : 'admin';
    }

    /**
     * Get the route prefix based on current route.
     */
    private function getRoutePrefix(): string
    {
        $prefix = request()->route()->getPrefix();
        return str_contains($prefix, 'tenant') ? 'tenant' : 'admin';
    }

    /**
     * Display billing information.
     */
    public function index()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        // Get current subscription
        $subscription = Subscription::where('tenant_id', $currentTenant->id)
                                  ->with(['plan', 'payments'])
                                  ->latest()
                                  ->first();

        // Get billing history
        $payments = Payment::where('tenant_id', $currentTenant->id)
                          ->with('subscription.plan')
                          ->orderBy('created_at', 'desc')
                          ->paginate(10);

        // Get available plans for upgrade/downgrade
        $plans = Plan::all();

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.billing.index", compact('subscription', 'payments', 'plans'));
    }

    /**
     * Show subscription details.
     */
    public function subscription()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $subscription = Subscription::where('tenant_id', $currentTenant->id)
                                  ->with(['plan', 'payments'])
                                  ->latest()
                                  ->first();

        if (!$subscription) {
            $routePrefix = $this->getRoutePrefix();
            return redirect()->route("{$routePrefix}.billing.index")
                           ->with('error', 'No active subscription found.');
        }

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.billing.subscription", compact('subscription'));
    }

    /**
     * Show payment history.
     */
    public function payments()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $payments = Payment::where('tenant_id', $currentTenant->id)
                          ->with('subscription.plan')
                          ->orderBy('created_at', 'desc')
                          ->paginate(15);

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.billing.payments", compact('payments'));
    }

    /**
     * Show available plans.
     */
    public function plans()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $plans = Plan::all();
        $currentSubscription = Subscription::where('tenant_id', $currentTenant->id)
                                         ->with('plan')
                                         ->latest()
                                         ->first();

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.billing.plans", compact('plans', 'currentSubscription'));
    }

    /**
     * Process plan change.
     */
    public function changePlan(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        // Here you would typically integrate with a payment processor
        // For now, we'll just create a new subscription

        $subscription = Subscription::create([
            'tenant_id' => $currentTenant->id,
            'plan_id' => $plan->id,
            'status' => 1, // Active
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'created_by' => Auth::id(),
        ]);

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.billing.index")
                        ->with('success', 'Plan changed successfully. New billing cycle starts now.');
    }
}
