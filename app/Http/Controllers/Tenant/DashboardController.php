<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Form;
use App\Models\WorkOrder;
use App\Models\User;
use App\Models\FormSubmission;
use App\Models\RecordActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        // Chart data for project progress (last 6 months)
        $projectChartData = $this->getProjectChartData($tenantId);

        // Chart data for manpower distribution
        $manpowerChartData = $this->getManpowerChartData($tenantId);

        // Recent activity (mock data for now - would come from activity log)
        $recentActivities = $this->getRecentActivities($tenantId);

        return view('tenant.dashboard', compact('stats', 'projectChartData', 'manpowerChartData', 'recentActivities'));
    }

    private function getProjectChartData($tenantId)
    {
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $months->push(Carbon::now()->subMonths($i)->format('M Y'));
        }

        $projectData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = Project::where('tenant_id', $tenantId)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $projectData[] = $count;
        }

        return [
            'project_labels' => $months->toArray(),
            'project_data' => $projectData,
        ];
    }

    private function getManpowerChartData($tenantId)
    {
        // Get user roles distribution using the roles relationship
        $roles = User::where('tenant_id', $tenantId)
            ->with('roles')
            ->get()
            ->pluck('roles')
            ->flatten()
            ->groupBy('name')
            ->map(function ($roleGroup) {
                return $roleGroup->count();
            });

        $labels = [];
        $data = [];

        foreach ($roles as $roleName => $count) {
            $labels[] = ucfirst($roleName);
            $data[] = $count;
        }

        // If no roles found, provide default data
        if (empty($labels)) {
            $labels = ['Field Workers', 'Managers', 'Admins'];
            $data = [0, 0, 0];
        }

        return [
            'user_labels' => $labels,
            'user_data' => $data,
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
