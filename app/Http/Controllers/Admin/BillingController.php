<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Project;
use App\Models\User;
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

        $subscription = Subscription::where('tenant_id', $currentTenant->id)
                                  ->with(['plan', 'payments'])
                                  ->latest()
                                  ->first();

        $plan = $subscription?->plan ?? Plan::first();
        $features = $plan?->features ?? [];
        $currentPlan = [
            'name' => $plan?->name ?? 'Professional',
            'price' => $plan?->price ?? 99,
            'projects_limit' => $features['projects_limit'] ?? 'Unlimited',
            'users_limit' => $features['users_limit'] ?? 10,
            'forms_limit' => $features['forms_limit'] ?? 'Unlimited',
        ];

        $projectsLimit = is_numeric($currentPlan['projects_limit']) ? (int) $currentPlan['projects_limit'] : 999;
        $usersLimit = (int) $currentPlan['users_limit'];
        $usage = [
            'projects_used' => Project::where('tenant_id', $currentTenant->id)->count(),
            'projects_limit' => $projectsLimit,
            'users_used' => User::where('tenant_id', $currentTenant->id)->count(),
            'users_limit' => $usersLimit,
        ];

        $payments = Payment::where('tenant_id', $currentTenant->id)
                          ->with('subscription.plan')
                          ->orderBy('created_at', 'desc')
                          ->paginate(10);

        $invoices = $payments->getCollection()->map(function ($p, $i) {
            return [
                'number' => $p->id ? substr($p->id, -8) : ($i + 1),
                'description' => $p->subscription?->plan?->name ?? 'Subscription',
                'date' => $p->created_at->format('M d, Y'),
                'amount' => (float) $p->amount,
                'status' => $p->status === 1 ? 'paid' : ($p->status === 0 ? 'pending' : 'failed'),
            ];
        });
        $payments->setCollection($invoices->values());
        $invoices = $payments;

        $subscriptionStatus = $subscription ? ($subscription->status === 1 ? 'Active' : ($subscription->status === 0 ? 'Pending' : 'Cancelled')) : 'No subscription';
        $billingFrequency = 'Monthly';
        $nextRenewalDate = $subscription?->end_date?->format('M d, Y');

        $paymentMethod = null;
        $plans = Plan::all();

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.billing.index", compact('subscription', 'payments', 'plans', 'currentPlan', 'usage', 'invoices', 'paymentMethod', 'subscriptionStatus', 'billingFrequency', 'nextRenewalDate'));
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
