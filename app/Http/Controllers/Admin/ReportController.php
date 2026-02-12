<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\WorkOrder;
use App\Models\Record;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
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
     * Display a listing of reports.
     */
    public function index()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        // Get summary statistics (all from DB)
        $stats = [
            'total_projects' => Project::where('tenant_id', $currentTenant->id)->count(),
            'archived_projects' => Project::where('tenant_id', $currentTenant->id)->where('status', 0)->count(),
            'total_submissions' => Record::where('tenant_id', $currentTenant->id)->count(),
            'active_work_orders' => WorkOrder::where('tenant_id', $currentTenant->id)->whereIn('status', [1, 2])->count(),
            'total_forms' => Form::where('tenant_id', $currentTenant->id)->count(),
            'active_forms' => Form::where('tenant_id', $currentTenant->id)->where('status', 1)->count(),
            'total_work_orders' => WorkOrder::where('tenant_id', $currentTenant->id)->count(),
            'completed_work_orders' => WorkOrder::where('tenant_id', $currentTenant->id)->where('status', 3)->count(),
            'total_records' => Record::where('tenant_id', $currentTenant->id)->count(),
            'submitted_records' => Record::where('tenant_id', $currentTenant->id)->whereNotNull('submitted_at')->count(),
            'total_users' => User::where('tenant_id', $currentTenant->id)->count(),
        ];

        // Get project performance data
        $projectPerformance = Project::where('tenant_id', $currentTenant->id)
            ->select('id', 'name')
            ->limit(5)
            ->get()
            ->map(function ($project) {
                $totalWorkOrders = $project->workOrders()->count();
                $completedWorkOrders = $project->workOrders()->where('status', 3)->count();
                $completionPercentage = $totalWorkOrders > 0 ? round(($completedWorkOrders / $totalWorkOrders) * 100) : 0;

                return [
                    'name' => $project->name,
                    'completion_percentage' => $completionPercentage,
                ];
            });

        // Get team productivity data
        $teamProductivity = User::where('tenant_id', $currentTenant->id)
            ->withCount('submittedRecords')
            ->orderBy('submitted_records_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'submissions_count' => $user->submitted_records_count,
                    'performance' => min(100, $user->submitted_records_count * 10), // Simple performance metric
                ];
            });

        // Chart data: project status (1=Active, 2=Paused, 3=Draft, 0=Archived)
        $chartData = [
            'project_status' => [
                Project::where('tenant_id', $currentTenant->id)->where('status', 1)->count(),
                Project::where('tenant_id', $currentTenant->id)->where('status', 2)->count(),
                Project::where('tenant_id', $currentTenant->id)->where('status', 3)->count(),
                Project::where('tenant_id', $currentTenant->id)->where('status', 0)->count(),
            ],
            'months' => collect(range(5, 0))->map(fn($i) => now()->subMonths($i)->format('M Y'))->values()->all(),
            'monthly_submissions' => collect(range(5, 0))->map(function ($i) use ($currentTenant) {
                $date = now()->subMonths($i);
                return Record::where('tenant_id', $currentTenant->id)
                    ->whereYear('submitted_at', $date->year)
                    ->whereMonth('submitted_at', $date->month)
                    ->count();
            })->values()->all(),
        ];

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.reports.index", compact('stats', 'projectPerformance', 'teamProductivity', 'chartData'));
    }

    /**
     * Get submissions by status for chart
     */
    public function submissionsByStatus()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $statusCounts = Record::where('tenant_id', $currentTenant->id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $statusLabels = [
            'submitted' => 'Submitted',
            'in_review' => 'In Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'pending_info' => 'Pending Information',
        ];

        $labels = [];
        $values = [];

        foreach ($statusCounts as $item) {
            $labels[] = $statusLabels[$item->status] ?? ucfirst($item->status);
            $values[] = $item->count;
        }

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }

    /**
     * Get submissions over time for trend chart
     */
    public function submissionsOverTime(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $days = $request->input('days', 30);
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        $submissions = Record::where('tenant_id', $currentTenant->id)
            ->where('submitted_at', '>=', $startDate)
            ->select(DB::raw('DATE(submitted_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        // Fill in missing dates with zero
        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->format('M d');
            $values[] = $submissions[$date] ?? 0;
        }

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }

    /**
     * Get form analytics for chart
     */
    public function formAnalytics()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $formStats = Form::where('tenant_id', $currentTenant->id)
            ->leftJoin('records', 'forms.id', '=', 'records.form_id')
            ->select('forms.name', DB::raw('count(records.id) as submission_count'))
            ->groupBy('forms.id', 'forms.name')
            ->orderBy('submission_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'labels' => $formStats->pluck('name')->toArray(),
            'values' => $formStats->pluck('submission_count')->toArray(),
        ]);
    }

    /**
     * Generate custom report
     */
    public function generate(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $reportType = $request->input('report_type', 'form_submissions');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $groupBy = $request->input('group_by', 'form');
        $exportFormat = $request->input('export_format', 'view');

        $query = Record::where('tenant_id', $currentTenant->id);
        if ($dateFrom) {
            $query->whereDate('submitted_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('submitted_at', '<=', $dateTo);
        }

        $normalized = match ($reportType) {
            'project_summary' => 'projects',
            'form_submissions' => 'submissions',
            'team_performance' => 'users',
            'work_order_status' => 'work_order_status',
            default => $reportType,
        };

        switch ($normalized) {
            case 'submissions':
                $data = $this->generateSubmissionsReport($query, $groupBy);
                break;
            case 'forms':
                $data = $this->generateFormsReport($currentTenant->id, $dateFrom, $dateTo);
                break;
            case 'projects':
                $data = $this->generateProjectsReport($currentTenant->id, $dateFrom, $dateTo);
                break;
            case 'users':
                $data = $this->generateUsersReport($currentTenant->id, $dateFrom, $dateTo);
                break;
            case 'work_order_status':
                $data = $this->generateWorkOrderStatusReport($currentTenant->id);
                break;
            default:
                $data = ['headers' => [], 'rows' => []];
        }

        // Handle export format
        if ($exportFormat === 'csv') {
            return $this->exportToCsv($data, $reportType);
        } elseif ($exportFormat === 'pdf') {
            return $this->exportToPdf($data, $reportType);
        }

        return response()->json($data);
    }

    /**
     * Generate submissions report
     */
    private function generateSubmissionsReport($query, $groupBy)
    {
        switch ($groupBy) {
            case 'form':
                $query->select('forms.name as group_name', DB::raw('count(records.id) as count'))
                    ->join('forms', 'records.form_id', '=', 'forms.id')
                    ->groupBy('forms.id', 'forms.name');
                $headers = ['Form', 'Submissions'];
                break;

            case 'project':
                $query->select('projects.name as group_name', DB::raw('count(records.id) as count'))
                    ->leftJoin('projects', 'records.project_id', '=', 'projects.id')
                    ->groupBy('projects.id', 'projects.name');
                $headers = ['Project', 'Submissions'];
                break;

            case 'status':
                $query->select('records.status as group_name', DB::raw('count(records.id) as count'))
                    ->groupBy('records.status');
                $headers = ['Status', 'Submissions'];
                break;

            case 'user':
                $query->select('users.name as group_name', DB::raw('count(records.id) as count'))
                    ->leftJoin('users', 'records.submitted_by', '=', 'users.id')
                    ->groupBy('users.id', 'users.name');
                $headers = ['User', 'Submissions'];
                break;

            case 'date':
                $query->select(DB::raw('DATE(records.submitted_at) as group_name'), DB::raw('count(records.id) as count'))
                    ->groupBy(DB::raw('DATE(records.submitted_at)'))
                    ->orderBy('group_name', 'desc');
                $headers = ['Date', 'Submissions'];
                break;

            default:
                $headers = ['Group', 'Count'];
        }

        $results = $query->get();
        $rows = $results->map(function ($item) {
            return [
                $item->group_name ?? 'N/A',
                $item->count
            ];
        })->toArray();

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Generate forms report
     */
    private function generateFormsReport($tenantId, $dateFrom, $dateTo)
    {
        $query = Form::where('tenant_id', $tenantId)
            ->leftJoin('records', function($join) use ($dateFrom, $dateTo) {
                $join->on('forms.id', '=', 'records.form_id');
                if ($dateFrom) {
                    $join->where('records.submitted_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $join->where('records.submitted_at', '<=', $dateTo);
                }
            })
            ->select(
                'forms.name',
                DB::raw('count(records.id) as total_submissions'),
                DB::raw('sum(case when records.status = "approved" then 1 else 0 end) as approved'),
                DB::raw('sum(case when records.status = "rejected" then 1 else 0 end) as rejected')
            )
            ->groupBy('forms.id', 'forms.name')
            ->get();

        $headers = ['Form Name', 'Total Submissions', 'Approved', 'Rejected'];
        $rows = $query->map(function ($item) {
            return [
                $item->name,
                $item->total_submissions,
                $item->approved,
                $item->rejected
            ];
        })->toArray();

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Generate projects report
     */
    private function generateProjectsReport($tenantId, $dateFrom, $dateTo)
    {
        $query = Project::where('tenant_id', $tenantId)
            ->leftJoin('records', function($join) use ($dateFrom, $dateTo) {
                $join->on('projects.id', '=', 'records.project_id');
                if ($dateFrom) {
                    $join->where('records.submitted_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $join->where('records.submitted_at', '<=', $dateTo);
                }
            })
            ->select(
                'projects.name',
                'projects.area',
                DB::raw('count(records.id) as total_submissions')
            )
            ->groupBy('projects.id', 'projects.name', 'projects.area')
            ->get();

        $headers = ['Project Name', 'Area', 'Total Submissions'];
        $rows = $query->map(function ($item) {
            return [
                $item->name,
                $item->area ?? 'N/A',
                $item->total_submissions
            ];
        })->toArray();

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Generate users report
     */
    private function generateUsersReport($tenantId, $dateFrom, $dateTo)
    {
        $query = User::where('tenant_id', $tenantId)
            ->leftJoin('records', function($join) use ($dateFrom, $dateTo) {
                $join->on('users.id', '=', 'records.submitted_by');
                if ($dateFrom) {
                    $join->where('records.submitted_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $join->where('records.submitted_at', '<=', $dateTo);
                }
            })
            ->select(
                'users.name',
                'users.email',
                DB::raw('count(records.id) as total_submissions')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_submissions', 'desc')
            ->get();

        $headers = ['User Name', 'Email', 'Total Submissions'];
        $rows = $query->map(function ($item) {
            return [
                $item->name,
                $item->email,
                $item->total_submissions
            ];
        })->toArray();

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Export report to CSV
     */
    private function exportToCsv($data, $reportType)
    {
        $filename = $reportType . '_report_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'r+');

        // Write headers
        fputcsv($handle, $data['headers']);

        // Write rows
        foreach ($data['rows'] as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export report to PDF (placeholder)
     */
    private function exportToPdf($data, $reportType)
    {
        // TODO: Implement PDF export using a library like DomPDF or TCPDF
        return response()->json([
            'message' => 'PDF export coming soon',
            'data' => $data
        ]);
    }

    /**
     * Generate work order status report (for Custom Report form).
     */
    private function generateWorkOrderStatusReport($tenantId): array
    {
        $statuses = [0 => 'Draft', 1 => 'Assigned', 2 => 'In Progress', 3 => 'Completed'];
        $counts = WorkOrder::where('tenant_id', $tenantId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $headers = ['Status', 'Count'];
        $rows = [];
        foreach ($statuses as $key => $label) {
            $rows[] = [$label, $counts[$key] ?? 0];
        }
        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Get work order status counts (API/chart).
     */
    public function workOrderStatus(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $statusCounts = WorkOrder::where('tenant_id', $currentTenant->id)
                                ->select('status', DB::raw('count(*) as count'))
                                ->groupBy('status')
                                ->get()
                                ->pluck('count', 'status')
                                ->toArray();

        $statuses = [
            0 => 'Draft',
            1 => 'Assigned',
            2 => 'In Progress',
            3 => 'Completed'
        ];

        $data = [];
        foreach ($statuses as $key => $label) {
            $data[] = [
                'status' => $label,
                'count' => $statusCounts[$key] ?? 0
            ];
        }

        return response()->json($data);
    }

    /**
     * Generate form usage report.
     */
    public function formUsage(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $formUsage = Form::where('tenant_id', $currentTenant->id)
                        ->withCount('workOrders')
                        ->withCount(['workOrders as completed_work_orders_count' => function ($query) {
                            $query->where('status', 3);
                        }])
                        ->get();

        return response()->json($formUsage);
    }

    /**
     * Generate user activity report.
     */
    public function userActivity(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $userActivity = User::where('tenant_id', $currentTenant->id)
                           ->withCount('workOrders')
                           ->withCount(['workOrders as completed_work_orders_count' => function ($query) {
                               $query->where('status', 3);
                           }])
                           ->withCount('records')
                           ->get();

        return response()->json($userActivity);
    }
}
