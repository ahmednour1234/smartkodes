<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TenantsExport;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Only super admins can access tenant management
        $user = Auth::user();
        if (!$user || $user->tenant_id !== null) {
            abort(403, 'Access denied. Super admin required.');
        }

                $tenants = Tenant::with(['plan', 'users', 'subscription'])->paginate(15);
        return view('admin.tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $plans = Plan::all();
        return view('admin.tenants.create', compact('plans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255|unique:tenants',
            'plan_id' => 'required|exists:plans,id',
            'status' => 'required|integer|in:0,1,2', // 0=draft, 1=active, 2=suspended
            'storage_quota' => 'nullable|integer|min:0',
            'api_rate_limit' => 'nullable|integer|min:0',
        ]);

        $tenant = Tenant::create([
            'id' => (string) Str::ulid(),
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'domain' => $request->domain,
            'plan_id' => $request->plan_id,
            'status' => $request->status,
            'storage_quota' => $request->storage_quota ?? 1000, // MB
            'api_rate_limit' => $request->api_rate_limit ?? 1000, // requests per hour
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', 'Tenant created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant)
    {
        $tenant->load(['plan', 'users']);
        return view('admin.tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        $plans = Plan::all();
        return view('admin.tenants.edit', compact('tenant', 'plans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255|unique:tenants,domain,' . $tenant->id,
            'plan_id' => 'required|exists:plans,id',
            'status' => 'required|integer|in:0,1,2',
            'storage_quota' => 'nullable|integer|min:0',
            'api_rate_limit' => 'nullable|integer|min:0',
        ]);

        $tenant->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'domain' => $request->domain,
            'plan_id' => $request->plan_id,
            'status' => $request->status,
            'storage_quota' => $request->storage_quota,
            'api_rate_limit' => $request->api_rate_limit,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', 'Tenant updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        // Soft delete the tenant
        $tenant->delete();

        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant deleted successfully.');
    }

    /**
     * Impersonate a tenant user (for support)
     */
    public function impersonate(Tenant $tenant)
    {
        // Set the tenant as current tenant for impersonation
        session(['impersonating_tenant' => $tenant->id]);
        session(['current_tenant' => $tenant]);

        return redirect()->route('admin.dashboard')
            ->with('info', "Now impersonating tenant: {$tenant->name}");
    }

    /**
     * Stop impersonation
     */
    public function stopImpersonation()
    {
        session()->forget(['impersonating_tenant', 'current_tenant']);
        return redirect()->route('admin.tenants.index')
            ->with('success', 'Stopped impersonation.');
    }

    /**
     * Export tenants to Excel/CSV (System Admin only).
     */
    public function export(Request $request)
    {
        // Only super admins can access tenant management
        $user = Auth::user();
        if (!$user || $user->tenant_id !== null) {
            abort(403, 'Access denied. Super admin required.');
        }

        $format = $request->input('format', 'xlsx'); // 'xlsx' or 'csv'
        $filename = 'tenants_' . date('Y-m-d_His') . '.' . $format;

        return Excel::download(
            new TenantsExport(),
            $filename,
            $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
        );
    }
}
