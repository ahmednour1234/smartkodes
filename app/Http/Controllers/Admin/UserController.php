<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;

class UserController extends Controller
{
    private function isAdminContext(): bool
    {
        $routeName = request()->route()->getName();
        return str_starts_with($routeName, 'admin.');
    }
    /**
     * Get the view prefix based on the current route.
     */
    private function getViewPrefix()
    {
        $routeName = request()->route()->getName();
        return str_contains($routeName, 'tenant') ? 'tenant' : 'admin';
    }

    /**
     * Get the route prefix based on the current route.
     */
    private function getRoutePrefix()
    {
        $routeName = request()->route()->getName();
        return str_contains($routeName, 'tenant') ? 'tenant' : 'admin';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if ($this->isAdminContext()) {
            // Super Admin: manage admin users (no tenant)
            $users = User::whereNull('tenant_id')->with('roles')->paginate(15);
        } else {
            $currentTenant = session('tenant_context.current_tenant');
            if (!$currentTenant) {
                abort(403, 'No tenant context available.');
            }
            $users = User::where('tenant_id', $currentTenant->id)->with('roles')->paginate(15);
        }

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.users.index", compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if ($this->isAdminContext()) {
            $roles = Role::whereNull('tenant_id')->get();
        } else {
            $currentTenant = session('tenant_context.current_tenant');
            if (!$currentTenant) {
                abort(403, 'No tenant context available.');
            }
            $roles = Role::where('tenant_id', $currentTenant->id)->get();
        }
        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.users.create", compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $emailRule = Rule::unique('users', 'email')->whereNull('deleted_at');
        if (!$this->isAdminContext()) {
            $currentTenant = session('tenant_context.current_tenant');
            if (!$currentTenant) {
                abort(403, 'No tenant context available.');
            }
            $emailRule->where('tenant_id', $currentTenant->id);
        } else {
            $emailRule->whereNull('tenant_id');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', $emailRule],
            'password' => 'required|string|min:8|confirmed',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        if ($this->isAdminContext()) {
            $user = User::create([
                'id' => (string) Str::ulid(),
                'tenant_id' => null,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
                'created_by' => Auth::id(),
            ]);
        } else {
            $currentTenant = session('tenant_context.current_tenant');
            if (!$currentTenant) {
                abort(403, 'No tenant context available.');
            }
            $user = User::create([
                'id' => (string) Str::ulid(),
                'tenant_id' => $currentTenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
                'created_by' => Auth::id(),
            ]);
        }

        if ($request->role_ids) {
            $user->roles()->attach($request->role_ids);
        }

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.users.show", $user)
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorizeTenant($user);
        $user->load('roles');
        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.users.show", compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorizeTenant($user);

        if ($this->isAdminContext()) {
            $roles = Role::whereNull('tenant_id')->get();
        } else {
            $currentTenant = session('tenant_context.current_tenant');
            $roles = Role::where('tenant_id', $currentTenant->id)->get();
        }

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.users.edit", compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorizeTenant($user);

        $emailRule = Rule::unique('users', 'email')->ignore($user->id)->whereNull('deleted_at');
        if (!$this->isAdminContext()) {
            $currentTenant = session('tenant_context.current_tenant');
            if ($currentTenant) {
                $emailRule->where('tenant_id', $currentTenant->id);
            }
        } else {
            $emailRule->whereNull('tenant_id');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', $emailRule],
            'password' => 'nullable|string|min:8|confirmed',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->boolean('status') ? 1 : 0,
            'updated_by' => Auth::id(),
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        if ($request->role_ids !== null) {
            $user->roles()->sync($request->role_ids);
        }

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.users.show", $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorizeTenant($user);

        // Prevent deleting self
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.users.index")
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Export users to Excel/CSV.
     */
    public function export(Request $request)
    {
        if ($this->isAdminContext()) {
            $tenantId = null; // Export admin users
        } else {
            $currentTenant = session('tenant_context.current_tenant');
            if (!$currentTenant) {
                abort(403, 'No tenant context available.');
            }
            $tenantId = $currentTenant->id;
        }

        $format = $request->input('format', 'xlsx'); // 'xlsx' or 'csv'
        $filename = 'users_' . date('Y-m-d_His') . '.' . $format;

        return Excel::download(
            new UsersExport($tenantId),
            $filename,
            $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
        );
    }

    private function authorizeTenant(User $user)
    {
        if ($this->isAdminContext()) {
            // Super Admin can manage users with tenant_id null
            if (!is_null($user->tenant_id)) {
                abort(403, 'Unauthorized');
            }
            return;
        }
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant || $user->tenant_id !== $currentTenant->id) {
            abort(403, 'Unauthorized');
        }
    }
}
