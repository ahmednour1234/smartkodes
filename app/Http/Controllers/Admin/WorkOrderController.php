<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Project;
use App\Models\Form;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WorkOrdersExport;

class WorkOrderController extends Controller
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
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $workOrders = WorkOrder::where('tenant_id', $currentTenant->id)
                              ->with(['project', 'forms', 'assignedUser', 'creator'])
                              ->paginate(15);

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.work-orders.index", compact('workOrders'));
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

        $projects = Project::where('tenant_id', $currentTenant->id)->get();
        $forms = Form::where('tenant_id', $currentTenant->id)->where('status', 1)->get();
        $users = User::where('tenant_id', $currentTenant->id)->get();

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.work-orders.create", compact('projects', 'forms', 'users'));
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
        'title'        => ['required', 'string', 'max:255'],

        'project_id'   => ['required', 'exists:projects,id'],
        'form_ids'     => ['required', 'array', 'min:1'],
        'form_ids.*'   => ['exists:forms,id'],

        'assigned_to'  => ['nullable', 'exists:users,id'],

        'status'       => ['required', 'integer', 'in:0,1,2,3'], // 0=draft, 1=assigned, 2=in_progress, 3=completed
        'importance_level' => ['nullable', 'string', 'in:low,medium,high,critical'],
        'due_date'     => ['nullable', 'date', 'after:today'],

        'priority_value' => ['nullable', 'integer', 'min:1', 'required_with:priority_unit'],
        'priority_unit'  => ['nullable', 'in:hour,day,week,month', 'required_with:priority_value'],

        'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
        'longitude'   => ['nullable', 'numeric', 'between:-180,180'],

        'description' => ['nullable', 'string'],
    ]);

    $workOrder = WorkOrder::create([
        'tenant_id'       => $currentTenant->id,
        'title'           => $request->title,
        'project_id'      => $request->project_id,
        'assigned_to'     => $request->assigned_to,
        'status'          => $request->status,
        'importance_level' => $request->importance_level,
        'due_date'        => $request->due_date,
        'priority_value'  => $request->priority_value,
        'priority_unit'   => $request->priority_unit,
        'latitude'        => $request->latitude,
        'longitude'       => $request->longitude,
        'description'     => $request->description,
        'created_by'      => Auth::id(),
        'updated_by'      => Auth::id(),
    ]);

    // Attach forms with order
    $formData = [];
    foreach ($request->form_ids as $index => $formId) {
        $formData[$formId] = [
            'id'    => Str::ulid(),
            'order' => $index,
        ];
    }
    $workOrder->forms()->attach($formData);

    $routePrefix = $this->getRoutePrefix();

    return redirect()
        ->route("{$routePrefix}.work-orders.index")
        ->with(
            'success',
            'Work order created successfully with ' . count($request->form_ids) . ' form(s).'
        );
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

        $workOrder = WorkOrder::where('tenant_id', $currentTenant->id)
                             ->with(['project', 'forms', 'assignedUser', 'creator', 'records'])
                             ->findOrFail($id);

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.work-orders.show", compact('workOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $workOrder = WorkOrder::where('tenant_id', $currentTenant->id)->findOrFail($id);
        $projects = Project::where('tenant_id', $currentTenant->id)->get();
        $forms = Form::where('tenant_id', $currentTenant->id)->where('status', 1)->get();
        $users = User::where('tenant_id', $currentTenant->id)->get();

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.work-orders.edit", compact('workOrder', 'projects', 'forms', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, string $id)
{
    $currentTenant = session('tenant_context.current_tenant');
    if (!$currentTenant) {
        abort(403, 'No tenant context available.');
    }

    /** @var \App\Models\WorkOrder $workOrder */
    $workOrder = WorkOrder::where('tenant_id', $currentTenant->id)->findOrFail($id);

    $request->validate([
        'title'        => ['required', 'string', 'max:255'],

        'project_id'   => ['required', 'exists:projects,id'],
        'form_ids'     => ['required', 'array', 'min:1'],
        'form_ids.*'   => ['exists:forms,id'],

        'assigned_to'  => ['nullable', 'exists:users,id'],

        'status'       => ['required', 'integer', 'in:0,1,2,3'],
        'importance_level' => ['nullable', 'string', 'in:low,medium,high,critical'],
        'due_date'     => ['nullable', 'date'], // you can change to 'after:today' if you want same as store

        'priority_value' => ['nullable', 'integer', 'min:1', 'required_with:priority_unit'],
        'priority_unit'  => ['nullable', 'in:hour,day,week,month', 'required_with:priority_value'],

        'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
        'longitude'   => ['nullable', 'numeric', 'between:-180,180'],

        'description' => ['nullable', 'string'],
    ]);

    $workOrder->update([
        'title'          => $request->title,
        'project_id'     => $request->project_id,
        'assigned_to'    => $request->assigned_to,
        'status'         => $request->status,
        'importance_level' => $request->importance_level,
        'due_date'       => $request->due_date,
        'priority_value' => $request->priority_value,
        'priority_unit'  => $request->priority_unit,
        'latitude'       => $request->latitude,
        'longitude'      => $request->longitude,
        'description'    => $request->description,
        'updated_by'     => Auth::id(),
    ]);

    // Sync forms with order
    $formData = [];
    foreach ($request->form_ids as $index => $formId) {
        $formData[$formId] = [
            'id'    => Str::ulid(),
            'order' => $index,
        ];
    }
    $workOrder->forms()->sync($formData);

    $routePrefix = $this->getRoutePrefix();

    return redirect()
        ->route("{$routePrefix}.work-orders.index")
        ->with('success', 'Work order updated successfully.');
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

        $workOrder = WorkOrder::where('tenant_id', $currentTenant->id)->findOrFail($id);
        $workOrder->delete();

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.work-orders.index")
                        ->with('success', 'Work order deleted successfully.');
    }

    /**
     * Export work orders to Excel/CSV.
     */
    public function export(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $filters = [
            'project_id' => $request->input('project_id'),
            'status' => $request->input('status'),
            'priority' => $request->input('priority'),
        ];

        $format = $request->input('format', 'xlsx'); // 'xlsx' or 'csv'
        $filename = 'work_orders_' . date('Y-m-d_His') . '.' . $format;

        return Excel::download(
            new WorkOrdersExport($currentTenant->id, $filters),
            $filename,
            $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
        );
    }
}
