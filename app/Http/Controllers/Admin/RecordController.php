<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Record;
use App\Models\WorkOrder;
use App\Models\FormVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RecordsExport;
use App\Constants\RecordStatus;

class RecordController extends Controller
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
    public function index(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $query = Record::where('tenant_id', $currentTenant->id)
                      ->with(['form', 'project', 'workOrder', 'submittedBy']);

        // Filter by form
        if ($request->filled('form_id')) {
            $query->where('form_id', $request->form_id);
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by status (map form string to integer)
        if ($request->filled('status')) {
            $statusMap = [
                'draft' => RecordStatus::DRAFT,
                'submitted' => RecordStatus::SUBMITTED,
                'reviewed' => RecordStatus::SUBMITTED,
                'approved' => RecordStatus::APPROVED,
                'rejected' => RecordStatus::REJECTED,
            ];
            $statusValue = $statusMap[$request->status] ?? null;
            if ($statusValue !== null) {
                $query->where('status', $statusValue);
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('submitted_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('submitted_at', '<=', $request->date_to);
        }

        $records = $query->latest('submitted_at')->paginate(15)->withQueryString();

        // Get filter options
        $forms = \App\Models\Form::where('tenant_id', $currentTenant->id)->get();
        $projects = \App\Models\Project::where('tenant_id', $currentTenant->id)->get();

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.records.index", compact('records', 'forms', 'projects'));
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

        $workOrders = WorkOrder::where('tenant_id', $currentTenant->id)->get();
        $formVersions = FormVersion::whereHas('form', function($query) use ($currentTenant) {
            $query->where('tenant_id', $currentTenant->id);
        })->get();

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.records.create", compact('workOrders', 'formVersions'));
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
            'work_order_id' => 'required|exists:work_orders,id',
            'form_version_id' => 'required|exists:form_versions,id',
            'status' => 'required|integer|in:0,1,2', // 0=draft, 1=submitted, 2=approved
        ]);

        $record = Record::create([
            'tenant_id' => $currentTenant->id,
            'work_order_id' => $request->work_order_id,
            'form_version_id' => $request->form_version_id,
            'status' => $request->status,
            'submitted_at' => $request->status == 1 ? now() : null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        if ($request->status == 1) {
            $record->load(['workOrder.project.managers', 'formVersion.form']);
            $routePrefix = $this->getRoutePrefix();
            $recordUrl = route("{$routePrefix}.records.show", $record->id);
            $formName = $record->formVersion->form->name ?? 'Form';
            $notifiedIds = [Auth::id()];
            if ($record->workOrder && $record->workOrder->assigned_to && !in_array($record->workOrder->assigned_to, $notifiedIds)) {
                Notification::create([
                    'tenant_id' => $currentTenant->id,
                    'user_id' => $record->workOrder->assigned_to,
                    'type' => 'form',
                    'title' => 'New form submission',
                    'message' => "New submission for \"{$formName}\" in work order: " . ($record->workOrder->title ?? ''),
                    'data' => ['record_id' => $record->id],
                    'action_url' => $recordUrl,
                    'created_by' => Auth::id(),
                ]);
                $notifiedIds[] = $record->workOrder->assigned_to;
            }
            if ($record->workOrder && $record->workOrder->project) {
                foreach ($record->workOrder->project->managers as $manager) {
                    if ($manager->id && !in_array($manager->id, $notifiedIds)) {
                        Notification::create([
                            'tenant_id' => $currentTenant->id,
                            'user_id' => $manager->id,
                            'type' => 'form',
                            'title' => 'New form submission',
                            'message' => "New submission for \"{$formName}\" in project: " . $record->workOrder->project->name,
                            'data' => ['record_id' => $record->id],
                            'action_url' => $recordUrl,
                            'created_by' => Auth::id(),
                        ]);
                        $notifiedIds[] = $manager->id;
                    }
                }
            }
        }

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.records.index")
                        ->with('success', 'Record created successfully.');
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

        $record = Record::where('tenant_id', $currentTenant->id)
                       ->with([
                           'form.formFields' => function($query) {
                               $query->orderBy('order', 'asc');
                           },
                           'project',
                           'workOrder',
                           'submittedBy',
                           'recordFields.formField',
                           'files',
                           'comments' => function($query) {
                               $query->whereNull('parent_id')
                                     ->with('user', 'replies.user')
                                     ->latest();
                           },
                           'activities' => function($query) {
                               $query->with('user')->latest()->limit(20);
                           },
                           'approvals' => function($query) {
                               $query->with('approver', 'requester', 'delegatedUser')
                                     ->orderBy('sequence');
                           }
                       ])
                       ->findOrFail($id);

        // Organize record field values by form field
        $fieldValues = [];
        foreach ($record->recordFields as $recordField) {
            if ($recordField->formField) {
                $value = $recordField->value_json;
                // Extract value from array if it's wrapped
                if (is_array($value) && isset($value['value'])) {
                    $value = $value['value'];
                }
                $fieldValues[$recordField->formField->name] = $value;
            }
        }

        // Get users for @mention in comments
        $users = \App\Models\User::where('tenant_id', $currentTenant->id)
                                 ->select('id', 'name', 'email')
                                 ->get();

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.records.show", compact('record', 'fieldValues', 'users'));
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

        $record = Record::where('tenant_id', $currentTenant->id)
                       ->with([
                           'form.formFields' => function($query) {
                               $query->orderBy('order', 'asc');
                           },
                           'project',
                           'workOrder',
                           'submittedBy',
                           'recordFields.formField',
                           'files'
                       ])
                       ->findOrFail($id);

        // Organize current field values by field name
        $currentValues = [];
        foreach ($record->recordFields as $recordField) {
            if ($recordField->formField) {
                $value = $recordField->value_json;
                // Extract value from array if it's wrapped
                if (is_array($value) && isset($value['value'])) {
                    $value = $value['value'];
                }
                $currentValues[$recordField->formField->name] = $value;
            }
        }

        $projects = \App\Models\Project::where('tenant_id', $currentTenant->id)->get();
        $forms = \App\Models\Form::where('tenant_id', $currentTenant->id)->get();

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.records.edit", compact('record', 'currentValues', 'projects', 'forms'));
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

        $record = Record::where('tenant_id', $currentTenant->id)
                       ->with('form.formFields')
                       ->findOrFail($id);

        // Validate the form submission against form field rules
        $validationRules = [];
        foreach ($record->form->formFields as $field) {
            $rules = [];

            if ($field->is_required) {
                $rules[] = 'required';
            } else {
                $rules[] = 'nullable';
            }

            // Add type-specific validation
            switch ($field->type) {
                case 'email':
                    $rules[] = 'email';
                    break;
                case 'number':
                case 'currency':
                case 'percentage':
                    $rules[] = 'numeric';
                    if ($field->min_value !== null) $rules[] = 'min:' . $field->min_value;
                    if ($field->max_value !== null) $rules[] = 'max:' . $field->max_value;
                    break;
                case 'url':
                    $rules[] = 'url';
                    break;
                case 'multiselect':
                    $rules[] = 'array';
                    if ($field->is_required) $rules[] = 'min:1';
                    break;
                case 'file':
                case 'photo':
                case 'video':
                case 'audio':
                    $rules[] = 'file';
                    if ($field->max_size) $rules[] = 'max:' . $field->max_size;
                    break;
            }

            if (!empty($rules)) {
                $validationRules[$field->name] = implode('|', $rules);
            }
        }

        // Validate the request
        $validatedData = $request->validate($validationRules);

        $previousStatus = $record->status;
        $newStatus = (int) ($request->status ?? $record->status);
        $record->update([
            'project_id' => $request->project_id,
            'status' => $newStatus,
            'submitted_at' => $newStatus === 1 ? ($record->submitted_at ?? now()) : $record->submitted_at,
            'updated_by' => Auth::id(),
        ]);

        if ($newStatus === 1 && $previousStatus !== 1) {
            $record->load(['workOrder.project.managers', 'form', 'formVersion.form']);
            $routePrefix = $this->getRoutePrefix();
            $recordUrl = route("{$routePrefix}.records.show", $record->id);
            $formName = $record->form->name ?? $record->formVersion->form->name ?? 'Form';
            $notifiedIds = [Auth::id()];
            if ($record->workOrder && $record->workOrder->assigned_to && !in_array($record->workOrder->assigned_to, $notifiedIds)) {
                Notification::create([
                    'tenant_id' => $currentTenant->id,
                    'user_id' => $record->workOrder->assigned_to,
                    'type' => 'form',
                    'title' => 'New form submission',
                    'message' => "New submission for \"{$formName}\" in work order: " . ($record->workOrder->title ?? ''),
                    'data' => ['record_id' => $record->id],
                    'action_url' => $recordUrl,
                    'created_by' => Auth::id(),
                ]);
                $notifiedIds[] = $record->workOrder->assigned_to;
            }
            if ($record->workOrder && $record->workOrder->project) {
                foreach ($record->workOrder->project->managers as $manager) {
                    if ($manager->id && !in_array($manager->id, $notifiedIds)) {
                        Notification::create([
                            'tenant_id' => $currentTenant->id,
                            'user_id' => $manager->id,
                            'type' => 'form',
                            'title' => 'New form submission',
                            'message' => "New submission for \"{$formName}\" in project: " . $record->workOrder->project->name,
                            'data' => ['record_id' => $record->id],
                            'action_url' => $recordUrl,
                            'created_by' => Auth::id(),
                        ]);
                        $notifiedIds[] = $manager->id;
                    }
                }
            }
        }

        // Update field values
        foreach ($record->form->formFields as $field) {
            if ($request->has($field->name)) {
                $value = $request->input($field->name);

                // Find existing record field or create new one
                $recordField = $record->recordFields()
                    ->where('form_field_id', $field->id)
                    ->first();

                $valueToStore = is_array($value) ? $value : ['value' => $value];

                if ($recordField) {
                    $recordField->update(['value_json' => $valueToStore]);
                } else {
                    $record->recordFields()->create([
                        'tenant_id' => $currentTenant->id,
                        'form_field_id' => $field->id,
                        'value_json' => $valueToStore,
                    ]);
                }
            }
        }

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.records.show", $record->id)
                        ->with('success', 'Record updated successfully.');
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

        $record = Record::where('tenant_id', $currentTenant->id)->findOrFail($id);
        $record->delete();

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.records.index")
                        ->with('success', 'Record deleted successfully.');
    }

    /**
     * Export records to Excel/CSV.
     */
    public function export(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $filters = [
            'work_order_id' => $request->input('work_order_id'),
            'form_id' => $request->input('form_id'),
            'user_id' => $request->input('user_id'),
        ];

        $format = $request->input('format', 'xlsx'); // 'xlsx' or 'csv'
        $filename = 'records_' . date('Y-m-d_His') . '.' . $format;

        return Excel::download(
            new RecordsExport($currentTenant->id, $filters),
            $filename,
            $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Bulk export selected records to XLSX
     */
    public function bulkExport(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $request->validate([
            'record_ids' => 'required|array',
            'record_ids.*' => 'required|string',
        ]);

        $first = Record::where('tenant_id', $currentTenant->id)
            ->whereIn('id', $request->record_ids)
            ->first();

        if (!$first) {
            $routePrefix = $this->getRoutePrefix();
            return redirect()->route("{$routePrefix}.records.index")
                ->with('error', 'No records found to export.');
        }

        $filters = [
            'record_ids' => $request->record_ids,
            'form_id' => $first->form_id,
        ];

        $filename = 'records_export_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new RecordsExport($currentTenant->id, $filters),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $request->validate([
            'record_ids' => 'required|array',
            'record_ids.*' => 'required|string',
            'status' => 'required|string|in:submitted,in_review,approved,rejected,pending_info',
        ]);

        $statusMap = [
            'submitted' => RecordStatus::SUBMITTED,
            'in_review' => RecordStatus::SUBMITTED,
            'approved' => RecordStatus::APPROVED,
            'rejected' => RecordStatus::REJECTED,
            'pending_info' => RecordStatus::DRAFT,
        ];
        $statusValue = $statusMap[$request->status];

        $count = Record::where('tenant_id', $currentTenant->id)
            ->whereIn('id', $request->record_ids)
            ->update([
                'status' => $statusValue,
                'updated_by' => Auth::id(),
            ]);

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.records.index")
                        ->with('success', "Status updated for {$count} record(s).");
    }

    /**
     * Bulk delete records
     */
    public function bulkDelete(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $request->validate([
            'record_ids' => 'required|array',
            'record_ids.*' => 'required|string',
        ]);

        $count = Record::where('tenant_id', $currentTenant->id)
            ->whereIn('id', $request->record_ids)
            ->delete();

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.records.index")
                        ->with('success', "{$count} record(s) deleted successfully.");
    }

    /**
     * Add comment to record
     */
    public function addComment(Request $request, string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $request->validate([
            'comment' => 'required|string',
            'parent_id' => 'nullable|exists:record_comments,id',
            'is_internal' => 'nullable|boolean',
        ]);

        $record = Record::where('tenant_id', $currentTenant->id)->findOrFail($id);

        // Extract mentions from comment (@username format)
        preg_match_all('/@(\w+)/', $request->comment, $matches);
        $mentionedUsernames = $matches[1] ?? [];

        $mentionedUserIds = [];
        if (!empty($mentionedUsernames)) {
            $mentionedUserIds = \App\Models\User::where('tenant_id', $currentTenant->id)
                ->whereIn('name', $mentionedUsernames)
                ->orWhereIn('email', array_map(fn($name) => $name . '@', $mentionedUsernames))
                ->pluck('id')
                ->toArray();
        }

        $comment = $record->comments()->create([
            'tenant_id' => $currentTenant->id,
            'user_id' => Auth::id(),
            'parent_id' => $request->parent_id,
            'comment' => $request->comment,
            'mentions' => $mentionedUserIds,
            'is_internal' => $request->is_internal ?? false,
        ]);

        // Log activity
        \App\Models\RecordActivity::create([
            'tenant_id' => $currentTenant->id,
            'record_id' => $record->id,
            'user_id' => Auth::id(),
            'action' => 'commented',
            'description' => 'Added a comment',
        ]);

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.records.show", $record->id)
                        ->with('success', 'Comment added successfully.');
    }

    /**
     * Delete comment
     */
    public function deleteComment(string $recordId, string $commentId)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $comment = \App\Models\RecordComment::where('tenant_id', $currentTenant->id)
                                           ->where('id', $commentId)
                                           ->where('record_id', $recordId)
                                           ->firstOrFail();

        // Only allow deletion by comment author or admin
        if ($comment->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized to delete this comment.');
        }

        $comment->delete();

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.records.show", $recordId)
                        ->with('success', 'Comment deleted successfully.');
    }

    /**
     * Request approval for record
     */
    public function requestApproval(Request $request, string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $request->validate([
            'approvers' => 'required|array',
            'approvers.*' => 'required|exists:users,id',
        ]);

        $record = Record::where('tenant_id', $currentTenant->id)->findOrFail($id);

        // Create approval requests for each approver
        foreach ($request->approvers as $index => $approverId) {
            $record->approvals()->create([
                'tenant_id' => $currentTenant->id,
                'approver_id' => $approverId,
                'requested_by' => Auth::id(),
                'sequence' => $index + 1,
                'status' => 'pending',
            ]);
        }

        // Log activity
        \App\Models\RecordActivity::create([
            'tenant_id' => $currentTenant->id,
            'record_id' => $record->id,
            'user_id' => Auth::id(),
            'action' => 'approval_requested',
            'description' => 'Requested approval from ' . count($request->approvers) . ' user(s)',
        ]);

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.records.show", $record->id)
                        ->with('success', 'Approval request sent successfully.');
    }

    /**
     * Approve record
     */
    public function approve(Request $request, string $id, string $approvalId)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $approval = \App\Models\RecordApproval::where('tenant_id', $currentTenant->id)
                                              ->where('id', $approvalId)
                                              ->where('record_id', $id)
                                              ->firstOrFail();

        // Check if user is the approver
        if ($approval->approver_id !== Auth::id() && $approval->delegated_to !== Auth::id()) {
            abort(403, 'You are not authorized to approve this record.');
        }

        $approval->update([
            'status' => 'approved',
            'comments' => $request->input('comments'),
            'approved_at' => now(),
        ]);

        // Log activity
        \App\Models\RecordActivity::create([
            'tenant_id' => $currentTenant->id,
            'record_id' => $id,
            'user_id' => Auth::id(),
            'action' => 'approved',
            'description' => 'Approved the record',
        ]);

        // Update record status if all approvals are complete
        $record = Record::findOrFail($id);
        $allApproved = $record->approvals()->where('status', '!=', 'approved')->count() === 0;

        if ($allApproved) {
            $record->update(['status' => 'approved']);

            \App\Models\RecordActivity::create([
                'tenant_id' => $currentTenant->id,
                'record_id' => $id,
                'user_id' => null,
                'action' => 'status_changed',
                'old_value' => $record->status,
                'new_value' => 'approved',
                'description' => 'Status automatically changed to approved',
            ]);
        }

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.records.show", $id)
                        ->with('success', 'Record approved successfully.');
    }

    /**
     * Reject record
     */
    public function reject(Request $request, string $id, string $approvalId)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $request->validate([
            'comments' => 'required|string',
        ]);

        $approval = \App\Models\RecordApproval::where('tenant_id', $currentTenant->id)
                                              ->where('id', $approvalId)
                                              ->where('record_id', $id)
                                              ->firstOrFail();

        // Check if user is the approver
        if ($approval->approver_id !== Auth::id() && $approval->delegated_to !== Auth::id()) {
            abort(403, 'You are not authorized to reject this record.');
        }

        $approval->update([
            'status' => 'rejected',
            'comments' => $request->comments,
            'rejected_at' => now(),
        ]);

        // Log activity
        \App\Models\RecordActivity::create([
            'tenant_id' => $currentTenant->id,
            'record_id' => $id,
            'user_id' => Auth::id(),
            'action' => 'rejected',
            'description' => 'Rejected the record',
        ]);

        // Update record status
        $record = Record::findOrFail($id);
        $record->update(['status' => 'rejected']);

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.records.show", $id)
                        ->with('success', 'Record rejected.');
    }

    /**
     * Assign record to user
     */
    public function assign(Request $request, string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $record = Record::where('tenant_id', $currentTenant->id)->findOrFail($id);

        $oldUserId = $record->submitted_by;
        $record->update(['submitted_by' => $request->user_id]);

        // Log activity
        $oldUser = $oldUserId ? \App\Models\User::find($oldUserId) : null;
        $newUser = \App\Models\User::find($request->user_id);

        \App\Models\RecordActivity::create([
            'tenant_id' => $currentTenant->id,
            'record_id' => $record->id,
            'user_id' => Auth::id(),
            'action' => 'assigned',
            'old_value' => $oldUser?->name ?? 'Unassigned',
            'new_value' => $newUser->name,
            'description' => "Assigned to {$newUser->name}",
        ]);

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.records.show", $record->id)
                        ->with('success', 'Record assigned successfully.');
    }
}
