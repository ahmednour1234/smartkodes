<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantSubscriptionController extends Controller
{
    public function index()
    {
        $tenant = Tenant::with(['plan', 'subscription.plan'])
            ->findOrFail(session('tenant_id'));

        $subscriptions = $tenant->subscriptions()
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view($this->getViewPrefix() . '.subscription.index', compact('tenant', 'subscriptions'));
    }

    public function show($id)
    {
        $tenant = Tenant::findOrFail(session('tenant_id'));
        $subscription = $tenant->subscriptions()
            ->with(['plan', 'payments'])
            ->findOrFail($id);

        return view($this->getViewPrefix() . '.subscription.show', compact('subscription'));
    }

    protected function getViewPrefix(): string
    {
        $routeName = request()->route()->getName() ?? '';
        return str_contains($routeName, 'tenant') ? 'tenant' : 'admin';
    }
}
