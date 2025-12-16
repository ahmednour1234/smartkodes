@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
        <p class="mt-2 text-sm text-gray-600">Comprehensive insights into your form submissions and activities</p>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Total Submissions -->
        <div class="bg-white overflow-hidden shadow-md rounded-lg hover:shadow-lg transition-shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Submissions</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ $stats['total_records'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Forms -->
        <div class="bg-white overflow-hidden shadow-md rounded-lg hover:shadow-lg transition-shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Forms</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ $stats['active_forms'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Orders -->
        <div class="bg-white overflow-hidden shadow-md rounded-lg hover:shadow-lg transition-shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Work Orders</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ $stats['total_work_orders'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users -->
        <div class="bg-white overflow-hidden shadow-md rounded-lg hover:shadow-lg transition-shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Users</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Submissions by Status -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Submissions by Status</h3>
                <button onclick="refreshStatusChart()" class="text-indigo-600 hover:text-indigo-800 text-sm">
                    🔄 Refresh
                </button>
            </div>
            <canvas id="statusChart" height="250"></canvas>
        </div>

        <!-- Submissions Over Time -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Submissions Over Time (Last 30 Days)</h3>
                <button onclick="refreshTrendChart()" class="text-indigo-600 hover:text-indigo-800 text-sm">
                    🔄 Refresh
                </button>
            </div>
            <canvas id="trendChart" height="250"></canvas>
        </div>
    </div>

    <!-- Form Analytics -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Form Analytics</h3>
            <button onclick="refreshFormAnalytics()" class="text-indigo-600 hover:text-indigo-800 text-sm">
                🔄 Refresh
            </button>
        </div>
        <canvas id="formChart" height="100"></canvas>
    </div>

    <!-- Report Builder -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Custom Report Builder</h3>

        <form id="report-builder-form" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Report Type -->
                <div>
                    <label for="report_type" class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                    <select id="report_type" name="report_type" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="submissions">Submissions Report</option>
                        <option value="forms">Form Analytics</option>
                        <option value="projects">Project Summary</option>
                        <option value="users">User Activity</option>
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" id="date_from" name="date_from" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" id="date_to" name="date_to" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Format -->
                <div>
                    <label for="export_format" class="block text-sm font-medium text-gray-700 mb-1">Export Format</label>
                    <select id="export_format" name="export_format" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="view">View Online</option>
                        <option value="csv">CSV</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
            </div>

            <!-- Group By -->
            <div>
                <label for="group_by" class="block text-sm font-medium text-gray-700 mb-1">Group By</label>
                <div class="flex flex-wrap gap-2">
                    <label class="inline-flex items-center">
                        <input type="radio" name="group_by" value="form" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Form</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="group_by" value="project" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Project</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="group_by" value="status" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Status</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="group_by" value="user" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">User</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="group_by" value="date" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Date</span>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    📊 Generate Report
                </button>
                <button type="button" onclick="saveReportTemplate()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                    💾 Save Template
                </button>
                <button type="button" onclick="scheduleReport()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                    ⏰ Schedule Report
                </button>
            </div>
        </form>

        <!-- Report Results -->
        <div id="report-results" class="mt-6 hidden">
            <h4 class="text-md font-semibold text-gray-900 mb-3">Report Results</h4>
            <div id="report-content" class="overflow-x-auto">
                <!-- Report data will be inserted here -->
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Work Orders -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Work Orders</h3>
            @if($recentWorkOrders && $recentWorkOrders->count() > 0)
                <div class="space-y-3">
                    @foreach($recentWorkOrders as $wo)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md hover:bg-gray-100">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $wo->form->name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Assigned to: {{ $wo->assignedUser->name ?? 'Unassigned' }}
                                </div>
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ $wo->updated_at->diffForHumans() }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">No recent work orders</p>
            @endif
        </div>

        <!-- Recent Submissions -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Submissions</h3>
            @if($recentRecords && $recentRecords->count() > 0)
                <div class="space-y-3">
                    @foreach($recentRecords as $record)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md hover:bg-gray-100">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $record->form->name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    By: {{ $record->submittedBy->name ?? 'Unknown' }}
                                </div>
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ $record->submitted_at?->diffForHumans() ?? 'N/A' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">No recent submissions</p>
            @endif
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Chart instances
let statusChart, trendChart, formChart;

// Initialize charts on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeStatusChart();
    initializeTrendChart();
    initializeFormChart();
});

// Status Chart (Pie Chart)
function initializeStatusChart() {
    fetch('{{ route("admin.reports.submissions-by-status") }}')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('statusChart').getContext('2d');

            if (statusChart) {
                statusChart.destroy();
            }

            statusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: [
                            '#3B82F6', // Blue - Submitted
                            '#F59E0B', // Amber - In Review
                            '#10B981', // Green - Approved
                            '#EF4444', // Red - Rejected
                            '#8B5CF6', // Purple - Pending Info
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error loading status chart:', error));
}

// Trend Chart (Line Chart)
function initializeTrendChart() {
    fetch('{{ route("admin.reports.submissions-over-time") }}')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('trendChart').getContext('2d');

            if (trendChart) {
                trendChart.destroy();
            }

            trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Submissions',
                        data: data.values,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error loading trend chart:', error));
}

// Form Analytics Chart (Bar Chart)
function initializeFormChart() {
    fetch('{{ route("admin.reports.form-analytics") }}')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('formChart').getContext('2d');

            if (formChart) {
                formChart.destroy();
            }

            formChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Total Submissions',
                        data: data.values,
                        backgroundColor: '#3B82F6',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error loading form chart:', error));
}

// Refresh functions
function refreshStatusChart() {
    initializeStatusChart();
}

function refreshTrendChart() {
    initializeTrendChart();
}

function refreshFormAnalytics() {
    initializeFormChart();
}

// Report Builder
document.getElementById('report-builder-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const params = new URLSearchParams(formData);

    fetch(`{{ route("admin.reports.generate") }}?${params}`)
        .then(response => response.json())
        .then(data => {
            displayReportResults(data);
        })
        .catch(error => {
            console.error('Error generating report:', error);
            alert('Error generating report. Please try again.');
        });
});

function displayReportResults(data) {
    const resultsDiv = document.getElementById('report-results');
    const contentDiv = document.getElementById('report-content');

    // Build table
    let html = '<table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr>';

    // Headers
    data.headers.forEach(header => {
        html += `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${header}</th>`;
    });
    html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';

    // Rows
    data.rows.forEach(row => {
        html += '<tr>';
        row.forEach(cell => {
            html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${cell}</td>`;
        });
        html += '</tr>';
    });

    html += '</tbody></table>';

    contentDiv.innerHTML = html;
    resultsDiv.classList.remove('hidden');
}

function saveReportTemplate() {
    alert('Save report template feature coming soon!');
}

function scheduleReport() {
    alert('Schedule report feature coming soon!');
}
</script>
@endsection
