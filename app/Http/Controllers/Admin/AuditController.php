<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    /**
     * Display audit logs.
     */
    public function index(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $query = AuditLog::where('tenant_id', $currentTenant->id)
                         ->with(['user', 'auditable']);

        // Filter by event type
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by model type
        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        $auditLogs = $query->orderBy('created_at', 'desc')->paginate(25);

        // Get unique event types for filter dropdown
        $eventTypes = AuditLog::where('tenant_id', $currentTenant->id)
                             ->distinct()
                             ->pluck('event')
                             ->sort();

        // Get users for filter dropdown
        $users = \App\Models\User::where('tenant_id', $currentTenant->id)
                                ->select('id', 'name', 'email')
                                ->get();

        return view('admin.audit.index', compact('auditLogs', 'eventTypes', 'users'));
    }

    /**
     * Show detailed audit log entry.
     */
    public function show($id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $auditLog = AuditLog::where('tenant_id', $currentTenant->id)
                           ->with(['user', 'auditable'])
                           ->findOrFail($id);

        return view('admin.audit.show', compact('auditLog'));
    }

    /**
     * Export audit logs.
     */
    public function export(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $query = AuditLog::where('tenant_id', $currentTenant->id)
                         ->with(['user', 'auditable']);

        // Apply same filters as index
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        $auditLogs = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV content
        $csvContent = "Date,User,Event,Model,Changes\n";

        foreach ($auditLogs as $log) {
            $changes = '';
            if ($log->old_values || $log->new_values) {
                $changesArray = [];
                if ($log->old_values) {
                    $changesArray[] = 'Old: ' . json_encode($log->old_values);
                }
                if ($log->new_values) {
                    $changesArray[] = 'New: ' . json_encode($log->new_values);
                }
                $changes = implode('; ', $changesArray);
            }

            $csvContent .= sprintf(
                "%s,%s,%s,%s,\"%s\"\n",
                $log->created_at->format('Y-m-d H:i:s'),
                $log->user ? $log->user->name : 'System',
                $log->event,
                $log->auditable_type . ' (' . $log->auditable_id . ')',
                str_replace('"', '""', $changes)
            );
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="audit_logs_' . now()->format('Y-m-d') . '.csv"');
    }
}
