@extends('tenant.layouts.app')

@section('content')
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center">
                <h1 class="text-4xl font-bold mb-4">Welcome to Smart Site</h1>
                <p class="text-xl opacity-90 mb-8">Manage your organization's projects, forms, and field operations with ease.</p>
                <div class="flex justify-center space-x-4">
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg px-4 py-2">
                        <span class="text-sm font-medium">{{ Auth::user()->name }}</span>
                    </div>
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg px-4 py-2">
                        <span class="text-sm font-medium">{{ session('tenant_context.current_tenant')->name ?? 'Organization' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Projects</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_projects'] ?? 0 }}</p>
                        <a href="{{ route('tenant.projects.create') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Create Project</a>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active Forms</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['active_forms'] ?? 0 }}</p>
                        <a href="{{ route('tenant.forms.create') }}" class="text-xs text-green-600 hover:text-green-800 font-medium">Create Form</a>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Work Orders</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_work_orders'] ?? 0 }}</p>
                        <a href="{{ route('tenant.work-orders.create') }}" class="text-xs text-yellow-600 hover:text-yellow-800 font-medium">Create Work Order</a>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Team Members</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] ?? 0 }}</p>
                        <a href="{{ route('tenant.users.index') }}" class="text-xs text-purple-600 hover:text-purple-800 font-medium">Manage Users</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        @php
            $projectIds = $projectChartData['project_ids'] ?? [];
            $projectLabels = $projectChartData['project_labels'] ?? [];
            $projectProgressData = $projectChartData['project_progress_data'] ?? [];
            $projectWorkOrdersData = $projectChartData['project_work_orders_data'] ?? [];
            $projectSubmittedRecordsData = $projectChartData['project_submitted_records_data'] ?? [];

            $manpowerProjectLabels = $manpowerChartData['project_labels'] ?? [];
            $manpowerProjectData = $manpowerChartData['project_manpower_data'] ?? [];

            $hasProjectChartData = count($projectLabels) > 0;
            $hasManpowerChartData = count($manpowerProjectLabels) > 0;
        @endphp
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Project Progress Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Project Progress (%)</h3>
                    @if($hasProjectChartData)
                        <select id="projectProgressFilter" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="all">All Projects</option>
                            @foreach($projectLabels as $index => $projectLabel)
                                <option value="{{ $projectIds[$index] ?? '' }}">{{ $projectLabel }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                @if($hasProjectChartData)
                    <p id="selectedProjectProgressText" class="text-sm text-gray-600 mb-3">Showing progress for all projects.</p>
                    <canvas id="projectProgressChart" width="400" height="200"></canvas>
                @else
                    <p class="text-gray-500 text-center py-8">Data will appear here once projects and work orders are created.</p>
                @endif
            </div>

            <!-- Manpower Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Manpower Distribution By Project</h3>
                @if($hasManpowerChartData)
                    <canvas id="manpowerChart" width="400" height="200"></canvas>
                @else
                    <p class="text-gray-500 text-center py-8">Data will appear here once users are assigned to projects.</p>
                @endif
            </div>
        </div>

        <!-- Form Progress Stats -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Form Submission Statistics</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['total_submissions'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Total Submissions</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $stats['completed_forms'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Completed Forms</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending_reviews'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Pending Reviews</div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
            <div class="space-y-4">
                @forelse($recentActivities ?? [] as $activity)
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">{{ $activity['description'] }}</p>
                            <p class="text-xs text-gray-500">{{ $activity['timestamp'] }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No recent activity</p>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('tenant.projects.create') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200">
                    <svg class="h-6 w-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span class="text-sm font-medium text-blue-700">Create Project</span>
                </a>

                <a href="{{ route('tenant.forms.create') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition duration-200">
                    <svg class="h-6 w-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-sm font-medium text-green-700">Create Form</span>
                </a>

                <a href="{{ route('tenant.work-orders.create') }}" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition duration-200">
                    <svg class="h-6 w-6 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="text-sm font-medium text-yellow-700">Assign Work</span>
                </a>

                <a href="{{ route('tenant.reports.index') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition duration-200">
                    <svg class="h-6 w-6 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span class="text-sm font-medium text-purple-700">View Reports</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        @if($hasProjectChartData)
        const projectChartSource = {
            projectIds: @json($projectIds),
            labels: @json($projectLabels),
            progress: @json($projectProgressData),
            assignedWorkOrders: @json($projectWorkOrdersData),
            submittedRecords: @json($projectSubmittedRecordsData),
        };

        const projectCtx = document.getElementById('projectProgressChart').getContext('2d');

        const buildProjectChartDataset = (selectedProjectId = 'all') => {
            if (selectedProjectId === 'all') {
                return {
                    labels: projectChartSource.labels,
                    progress: projectChartSource.progress,
                    assignedWorkOrders: projectChartSource.assignedWorkOrders,
                    submittedRecords: projectChartSource.submittedRecords,
                };
            }

            const selectedIndex = projectChartSource.projectIds.findIndex((id) => id === selectedProjectId);
            if (selectedIndex === -1) {
                return {
                    labels: [],
                    progress: [],
                    assignedWorkOrders: [],
                    submittedRecords: [],
                };
            }

            return {
                labels: [projectChartSource.labels[selectedIndex]],
                progress: [projectChartSource.progress[selectedIndex]],
                assignedWorkOrders: [projectChartSource.assignedWorkOrders[selectedIndex]],
                submittedRecords: [projectChartSource.submittedRecords[selectedIndex]],
            };
        };

        const initialProjectDataset = buildProjectChartDataset('all');

        const projectChart = new Chart(projectCtx, {
            type: 'bar',
            data: {
                labels: initialProjectDataset.labels,
                datasets: [{
                    label: 'Progress %',
                    data: initialProjectDataset.progress,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.35)',
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: (value) => `${value}%`
                        }
                    }
                },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const index = context.dataIndex;
                                const progress = initialProjectDataset.progress[index] ?? 0;
                                const submitted = initialProjectDataset.submittedRecords[index] ?? 0;
                                const assigned = initialProjectDataset.assignedWorkOrders[index] ?? 0;
                                return `Progress: ${progress}% (Submitted Records: ${submitted}, Assigned Work Orders: ${assigned})`;
                            }
                        }
                    }
                }
            }
        });

        const projectFilter = document.getElementById('projectProgressFilter');
        const selectedProjectProgressText = document.getElementById('selectedProjectProgressText');

        if (projectFilter) {
            projectFilter.addEventListener('change', (event) => {
                const selectedProjectId = event.target.value;
                const nextDataset = buildProjectChartDataset(selectedProjectId);

                projectChart.data.labels = nextDataset.labels;
                projectChart.data.datasets[0].data = nextDataset.progress;
                projectChart.options.plugins.tooltip.callbacks.label = function(context) {
                    const index = context.dataIndex;
                    const progress = nextDataset.progress[index] ?? 0;
                    const submitted = nextDataset.submittedRecords[index] ?? 0;
                    const assigned = nextDataset.assignedWorkOrders[index] ?? 0;
                    return `Progress: ${progress}% (Submitted Records: ${submitted}, Assigned Work Orders: ${assigned})`;
                };
                projectChart.update();

                if (selectedProjectProgressText) {
                    if (selectedProjectId === 'all') {
                        selectedProjectProgressText.textContent = 'Showing progress for all projects.';
                    } else {
                        selectedProjectProgressText.textContent = `Showing progress for: ${nextDataset.labels[0] ?? 'Selected Project'}`;
                    }
                }
            });
        }
        @endif

        @if($hasManpowerChartData)
        const manpowerCtx = document.getElementById('manpowerChart').getContext('2d');
        new Chart(manpowerCtx, {
            type: 'bar',
            data: {
                labels: @json($manpowerProjectLabels),
                datasets: [{
                    label: 'Assigned Manpower',
                    data: @json($manpowerProjectData),
                    backgroundColor: 'rgba(16, 185, 129, 0.4)',
                    borderColor: 'rgb(5, 150, 105)',
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
        @endif
    </script>
@endsection
