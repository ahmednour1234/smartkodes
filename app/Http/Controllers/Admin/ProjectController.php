<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProjectsExport;

class ProjectController extends Controller
{
    /**
     * Get the view prefix based on current route
     */
    private function getViewPrefix()
    {
        $prefix = request()->route()->getPrefix();
        return str_contains($prefix, 'tenant') ? 'tenant' : 'admin';
    }

    /**
     * Get the route prefix based on current route
     */
    private function getRoutePrefix()
    {
        $prefix = request()->route()->getPrefix();
        return str_contains($prefix, 'tenant') ? 'tenant' : 'admin';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $projects = Project::where('tenant_id', $currentTenant->id)->paginate(10);
        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.projects.index", compact('projects'));
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

        // Get available users for assignment (only users in current tenant)
        $users = \App\Models\User::where('tenant_id', $currentTenant->id)
                    ->orderBy('name')
                    ->get();

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.projects.create", compact('users'));
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

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:projects,code',
            'description' => 'nullable|string',
            'status' => 'required|integer|in:0,1,2',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'area' => 'nullable|string|max:100',
            'client_name' => 'nullable|string|max:255',
            'managers' => 'nullable|array',
            'managers.*' => 'exists:users,id',
            'field_users' => 'nullable|array',
            'field_users.*' => 'exists:users,id',
        ]);

        // Create project
        $project = Project::create([
            'tenant_id' => $currentTenant->id,
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'area' => $validated['area'] ?? null,
            'client_name' => $validated['client_name'] ?? null,
            'created_by' => Auth::id(),
        ]);

        // Attach managers
        if ($request->filled('managers')) {
            foreach ($request->managers as $managerId) {
                $project->members()->attach($managerId, [
                    'role' => 'manager',
                    'assigned_by' => Auth::id(),
                ]);
            }
        }

        // Attach field users
        if ($request->filled('field_users')) {
            foreach ($request->field_users as $userId) {
                $project->members()->attach($userId, [
                    'role' => 'field_user',
                    'assigned_by' => Auth::id(),
                ]);
            }
        }

        // Log audit trail
        \App\Models\AuditLog::create([
            'tenant_id' => $currentTenant->id,
            'user_id' => Auth::id(),
            'event' => 'created',
            'auditable_type' => Project::class,
            'auditable_id' => $project->id,
            'old_values' => null,
            'new_values' => $project->toArray(),
        ]);

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.projects.show", $project)->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $this->authorizeTenant($project);

        // Eager load relationships
        $project->load(['managers', 'fieldUsers', 'creator', 'updater']);

        // Calculate project metrics
        $totalWorkOrders = $project->workOrders()->count();
        $openWorkOrders = $project->workOrders()->whereIn('status', [0, 1, 2])->count();
        $completedWorkOrders = $project->workOrders()->where('status', 3)->count();
        $overdueWorkOrders = $project->workOrders()
            ->whereIn('status', [0, 1, 2])
            ->where('due_date', '<', now())
            ->count();

        // Calculate on-time completion percentage
        $completedOnTime = $project->workOrders()
            ->where('status', 3)
            ->whereColumn('updated_at', '<=', 'due_date')
            ->count();
        $onTimePercentage = $completedWorkOrders > 0
            ? round(($completedOnTime / $completedWorkOrders) * 100, 1)
            : 0;

        // Calculate average resolution time (in days)
        $avgResolutionTime = $project->workOrders()
            ->where('status', 3)
            ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
            ->value('avg_days');
        $avgResolutionTime = $avgResolutionTime ? round($avgResolutionTime, 1) : 0;

        $metrics = [
            'total_work_orders' => $totalWorkOrders,
            'open_work_orders' => $openWorkOrders,
            'completed_work_orders' => $completedWorkOrders,
            'overdue_work_orders' => $overdueWorkOrders,
            'on_time_percentage' => $onTimePercentage,
            'avg_resolution_days' => $avgResolutionTime,
        ];

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.projects.show", compact('project', 'metrics'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $this->authorizeTenant($project);

        $currentTenant = session('tenant_context.current_tenant');

        // Get available users for assignment
        $users = \App\Models\User::where('tenant_id', $currentTenant->id)
                    ->orderBy('name')
                    ->get();

        // Load current members
        $project->load(['managers', 'fieldUsers']);
        $managers = $project->managers;
        $fieldUsers = $project->fieldUsers;

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.projects.edit", compact('project', 'users', 'managers', 'fieldUsers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorizeTenant($project);

        $oldValues = $project->toArray();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:projects,code,' . $project->id,
            'description' => 'nullable|string',
            'status' => 'required|integer|in:0,1,2',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'area' => 'nullable|string|max:100',
            'client_name' => 'nullable|string|max:255',
            'managers' => 'nullable|array',
            'managers.*' => 'exists:users,id',
            'field_users' => 'nullable|array',
            'field_users.*' => 'exists:users,id',
        ]);

        // Update project
        $project->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'area' => $validated['area'] ?? null,
            'client_name' => $validated['client_name'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        // Sync managers
        $managerIds = $request->managers ?? [];
        $project->members()->wherePivot('role', 'manager')->detach();
        foreach ($managerIds as $managerId) {
            $project->members()->attach($managerId, [
                'role' => 'manager',
                'assigned_by' => Auth::id(),
            ]);
        }

        // Sync field users
        $fieldUserIds = $request->field_users ?? [];
        $project->members()->wherePivot('role', 'field_user')->detach();
        foreach ($fieldUserIds as $userId) {
            $project->members()->attach($userId, [
                'role' => 'field_user',
                'assigned_by' => Auth::id(),
            ]);
        }

        // Log audit trail
        \App\Models\AuditLog::create([
            'tenant_id' => $project->tenant_id,
            'user_id' => Auth::id(),
            'event' => 'updated',
            'auditable_type' => Project::class,
            'auditable_id' => $project->id,
            'old_values' => $oldValues,
            'new_values' => $project->fresh()->toArray(),
        ]);

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.projects.show", $project)->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorizeTenant($project);
        $project->delete();
        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.projects.index")->with('success', 'Project deleted successfully.');
    }

    /**
     * Export projects to Excel/CSV.
     */
    public function export(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $format = $request->input('format', 'xlsx'); // 'xlsx' or 'csv'
        $filename = 'projects_' . date('Y-m-d_His') . '.' . $format;

        return Excel::download(
            new ProjectsExport($currentTenant->id),
            $filename,
            $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
        );
    }

    private function authorizeTenant(Project $project)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant || $project->tenant_id !== $currentTenant->id) {
            abort(403, 'Unauthorized');
        }
    }
}
