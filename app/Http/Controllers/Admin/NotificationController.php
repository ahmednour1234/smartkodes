<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get the view prefix based on current route.
     */
    private function getViewPrefix(): string
    {
        $routeName = request()->route()->getName();
        return str_contains($routeName, 'tenant') ? 'tenant' : 'admin';
    }

    /**
     * Get the route prefix based on current route.
     */
    private function getRoutePrefix(): string
    {
        $routeName = request()->route()->getName();
        return str_contains($routeName, 'tenant') ? 'tenant' : 'admin';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $routeName = request()->route()->getName();
        if (str_starts_with($routeName, 'admin.')) {
            // Global notifications view for super admin (show all or latest N)
            $notifications = Notification::with(['tenant', 'user', 'creator'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            $currentTenant = session('tenant_context.current_tenant');
            if (!$currentTenant) {
                abort(403, 'No tenant context available.');
            }
            $notifications = Notification::where('tenant_id', $currentTenant->id)
                ->with(['user', 'creator'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.notifications.index", compact('notifications'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $routeName = request()->route()->getName();
        if (str_starts_with($routeName, 'admin.')) {
            // Admin global broadcast form does not need users listing
            $users = collect();
        } else {
            $currentTenant = session('tenant_context.current_tenant');
            if (!$currentTenant) {
                abort(403, 'No tenant context available.');
            }
            $users = User::where('tenant_id', $currentTenant->id)->get();
        }
        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.notifications.create", compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $routeName = request()->route()->getName();
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'tenant_id' => 'nullable|exists:tenants,id',
            'type' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'data' => 'nullable|json',
        ]);

        if (str_starts_with($routeName, 'admin.')) {
            // Global broadcast options
            // 1) Broadcast to all users of all tenants (no tenant_id/user_id provided)
            // 2) Broadcast to all users of a specific tenant (tenant_id provided)
            // 3) Send to a single user (user_id provided)
            if ($request->filled('user_id')) {
                $targetUsers = User::where('id', $request->user_id)->get();
                $tenantIdForUser = optional($targetUsers->first())->tenant_id;
                foreach ($targetUsers as $user) {
                    Notification::create([
                        'tenant_id' => $tenantIdForUser,
                        'user_id' => $user->id,
                        'type' => $request->type,
                        'title' => $request->title,
                        'message' => $request->message,
                        'data' => $request->data ? json_decode($request->data, true) : null,
                        'created_by' => Auth::id(),
                    ]);
                }
            } elseif ($request->filled('tenant_id')) {
                $users = User::where('tenant_id', $request->tenant_id)->get();
                foreach ($users as $user) {
                    Notification::create([
                        'tenant_id' => $request->tenant_id,
                        'user_id' => $user->id,
                        'type' => $request->type,
                        'title' => $request->title,
                        'message' => $request->message,
                        'data' => $request->data ? json_decode($request->data, true) : null,
                        'created_by' => Auth::id(),
                    ]);
                }
            } else {
                // Global broadcast to all users across all tenants
                $users = User::whereNotNull('tenant_id')->get();
                foreach ($users as $user) {
                    Notification::create([
                        'tenant_id' => $user->tenant_id,
                        'user_id' => $user->id,
                        'type' => $request->type,
                        'title' => $request->title,
                        'message' => $request->message,
                        'data' => $request->data ? json_decode($request->data, true) : null,
                        'created_by' => Auth::id(),
                    ]);
                }
            }
        } else {
            // Tenant-scoped: existing behavior
            $currentTenant = session('tenant_context.current_tenant');
            if (!$currentTenant) {
                abort(403, 'No tenant context available.');
            }
            if (!$request->user_id) {
                $users = User::where('tenant_id', $currentTenant->id)->get();
                foreach ($users as $user) {
                    Notification::create([
                        'tenant_id' => $currentTenant->id,
                        'user_id' => $user->id,
                        'type' => $request->type,
                        'title' => $request->title,
                        'message' => $request->message,
                        'data' => $request->data ? json_decode($request->data, true) : null,
                        'created_by' => Auth::id(),
                    ]);
                }
            } else {
                Notification::create([
                    'tenant_id' => $currentTenant->id,
                    'user_id' => $request->user_id,
                    'type' => $request->type,
                    'title' => $request->title,
                    'message' => $request->message,
                    'data' => $request->data ? json_decode($request->data, true) : null,
                    'created_by' => Auth::id(),
                ]);
            }
        }

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.notifications.index")
                        ->with('success', 'Notification sent successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $notification = Notification::where('tenant_id', $currentTenant->id)
                                   ->with(['user', 'creator'])
                                   ->findOrFail($id);

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.notifications.show", compact('notification'));
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $notification = Notification::where('tenant_id', $currentTenant->id)->findOrFail($id);
        $notification->markAsRead();

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        Notification::where('tenant_id', $currentTenant->id)
                   ->whereNull('read_at')
                   ->update(['read_at' => now()]);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $notification = Notification::where('tenant_id', $currentTenant->id)->findOrFail($id);
        $notification->delete();

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.notifications.index")
                        ->with('success', 'Notification deleted successfully.');
    }
}
