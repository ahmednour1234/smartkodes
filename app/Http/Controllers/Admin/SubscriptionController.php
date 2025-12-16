<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::with(['tenant', 'plan'])
            ->latest()
            ->paginate(15);

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        $tenants = Tenant::where('status', 1)->orderBy('name')->get();
        $plans = Plan::where('status', 1)->orderBy('name')->get();

        return view('admin.subscriptions.create', compact('tenants', 'plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'plan_id' => 'required|exists:plans,id',
            'status' => 'required|boolean',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $validated['created_by'] = $request->user()->id ?? null;

        // Deactivate any existing active subscription for this tenant
        if ($validated['status'] == 1) {
            Subscription::where('tenant_id', $validated['tenant_id'])
                ->where('status', 1)
                ->update(['status' => 0]);
        }

        $subscription = Subscription::create($validated);

        // Update tenant's plan
        Tenant::where('id', $validated['tenant_id'])
            ->update(['plan_id' => $validated['plan_id']]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription created successfully.');
    }

    public function show(Subscription $subscription)
    {
        $subscription->load(['tenant', 'plan', 'creator', 'payments']);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function edit(Subscription $subscription)
    {
        $tenants = Tenant::where('status', 1)->orderBy('name')->get();
        $plans = Plan::where('status', 1)->orderBy('name')->get();

        return view('admin.subscriptions.edit', compact('subscription', 'tenants', 'plans'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'plan_id' => 'required|exists:plans,id',
            'status' => 'required|boolean',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        // If activating this subscription, deactivate others for this tenant
        if ($validated['status'] == 1 && $subscription->status != 1) {
            Subscription::where('tenant_id', $validated['tenant_id'])
                ->where('id', '!=', $subscription->id)
                ->where('status', 1)
                ->update(['status' => 0]);
        }

        $subscription->update($validated);

        // Update tenant's plan if subscription is active
        if ($validated['status'] == 1) {
            Tenant::where('id', $validated['tenant_id'])
                ->update(['plan_id' => $validated['plan_id']]);
        }

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription updated successfully.');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription deleted successfully.');
    }

    public function renew(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'duration_months' => 'required|integer|min:1|max:24',
        ]);

        $newEndDate = $subscription->end_date->addMonths($validated['duration_months']);

        $subscription->update([
            'end_date' => $newEndDate,
            'status' => 1,
        ]);

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('success', 'Subscription renewed successfully.');
    }

    public function cancel(Subscription $subscription)
    {
        $subscription->update([
            'status' => 0,
            'end_date' => now(),
        ]);

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('success', 'Subscription cancelled successfully.');
    }
}
