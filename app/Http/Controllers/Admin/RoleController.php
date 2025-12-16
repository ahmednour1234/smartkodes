<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $roles = Role::where('tenant_id', $currentTenant->id)
                    ->with('permissions')
                    ->paginate(15);

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $permissions = Permission::where('tenant_id', $currentTenant->id)->get();
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'id' => (string) Str::ulid(),
            'tenant_id' => $currentTenant->id,
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'status' => 1, // Active by default
            'created_by' => Auth::id(),
        ]);

        if ($request->permission_ids) {
            $role->permissions()->attach($request->permission_ids);
        }

        return redirect()->route('admin.roles.show', $role)
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $this->authorizeTenant($role);
        $role->load('permissions');
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $this->authorizeTenant($role);

        $currentTenant = session('tenant_context.current_tenant');
        $permissions = Permission::where('tenant_id', $currentTenant->id)->get();

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $this->authorizeTenant($role);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'updated_by' => Auth::id(),
        ]);

        if ($request->permission_ids !== null) {
            $role->permissions()->sync($request->permission_ids);
        }

        return redirect()->route('admin.roles.show', $role)
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $this->authorizeTenant($role);
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    private function authorizeTenant(Role $role)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant || $role->tenant_id !== $currentTenant->id) {
            abort(403, 'Unauthorized');
        }
    }
}
