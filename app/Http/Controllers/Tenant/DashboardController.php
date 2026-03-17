<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Form;
use App\Models\WorkOrder;
use App\Models\User;
use App\Models\FormSubmission;
use App\Models\RecordActivity;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $tenant = session('tenant_context.current_tenant');

        if (!$tenant) {
            Auth::logout();
            return redirect('/login')->with('error', 'Your session has expired. Please log in again.');
        }

        $tenantId = $tenant->id;

        // Get basic stats
        $stats = [
            'total_projects' => Project::where('tenant_id', $tenantId)->count(),
            'active_forms' => Form::where('tenant_id', $tenantId)->where('status', 1)->count(),
            'total_work_orders' => WorkOrder::where('tenant_id', $tenantId)->count(),
            'total_users' => User::where('tenant_id', $tenantId)->count(),
            'total_submissions' => FormSubmission::where('tenant_id', $tenantId)->count(),
            'completed_forms' => FormSubmission::where('tenant_id', $tenantId)->where('status', 'completed')->count(),
            'pending_reviews' => FormSubmission::where('tenant_id', $tenantId)->where('status', 'pending_review')->count(),
        ];

        // Chart data: progress percentage by project
        $projectChartData = $this->getProjectChartData($tenantId);

        // Chart data: manpower assigned to each project
        $manpowerChartData = $this->getManpowerChartData($tenantId);

        // Recent activity (mock data for now - would come from activity log)
        $recentActivities = $this->getRecentActivities($tenantId);

        return view('tenant.dashboard', compact('stats', 'projectChartData', 'manpowerChartData', 'recentActivities'));
    }

    private function getProjectChartData($tenantId)
    {
        $projects = Project::where('tenant_id', $tenantId)
            ->select('id', 'name')
            ->withCount([
                'workOrders as assigned_work_orders_count',
                'records as submitted_records_count' => function ($query) {
                    $query->whereNotNull('submitted_at');
                },
            ])
            ->orderBy('name')
            ->get();

        $projectIds = [];
        $labels = [];
        $progressData = [];
        $workOrdersData = [];
        $submittedRecordsData = [];

        foreach ($projects as $project) {
            $assignedWorkOrders = (int) $project->assigned_work_orders_count;
            $submittedRecords = (int) $project->submitted_records_count;
            $progressPercent = $assignedWorkOrders > 0
                ? min(round(($submittedRecords / $assignedWorkOrders) * 100, 2), 100)
                : 0;

            $projectIds[] = $project->id;
            $labels[] = $project->name;
            $progressData[] = $progressPercent;
            $workOrdersData[] = $assignedWorkOrders;
            $submittedRecordsData[] = $submittedRecords;
        }

        return [
            'project_ids' => $projectIds,
            'project_labels' => $labels,
            'project_progress_data' => $progressData,
            'project_work_orders_data' => $workOrdersData,
            'project_submitted_records_data' => $submittedRecordsData,
        ];
    }

    private function getManpowerChartData($tenantId)
    {
        $projects = Project::where('tenant_id', $tenantId)
            ->select('id', 'name')
            ->withCount('members')
            ->orderBy('name')
            ->get();

        return [
            'project_labels' => $projects->pluck('name')->toArray(),
            'project_manpower_data' => $projects->pluck('members_count')->map(fn ($count) => (int) $count)->toArray(),
        ];
    }

    private function getRecentActivities($tenantId)
    {
        return RecordActivity::where('tenant_id', $tenantId)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->map(function (RecordActivity $a) {
                $by = $a->user?->name ?? 'Someone';
                $desc = $a->description ?: ($a->action_name . ' by ' . $by);
                return [
                    'description' => $desc,
                    'timestamp' => $a->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }
}
