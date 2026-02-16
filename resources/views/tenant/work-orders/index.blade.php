@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-visible shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white overflow-visible">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold">Work Orders</h2>
                            <p class="text-blue-100 mt-1">Assign tasks and forms to your field teams</p>
                        </div>
                        <div class="flex flex-wrap gap-2 sm:gap-3 items-center">
                            <!-- Export Dropdown with Filters -->
                            <div class="relative inline-block text-left">
                                <button type="button"
                                        onclick="toggleWorkOrderExportMenu()"
                                        class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-medium py-2 px-4 rounded-lg transition duration-200 inline-flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Export
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div id="work-order-export-menu"
                                     class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                                    <div class="py-1" role="menu">
                                        <a href="{{ route('tenant.work-orders.export', array_merge(['format' => 'xlsx'], request()->only(['project_id', 'status', 'priority']))) }}"
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                           role="menuitem">
                                            <svg class="w-4 h-4 inline mr-2 text-green-600" fill="none"
                                                 stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Excel (.xlsx)
                                        </a>
                                        <a href="{{ route('tenant.work-orders.export', array_merge(['format' => 'csv'], request()->only(['project_id', 'status', 'priority']))) }}"
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                           role="menuitem">
                                            <svg class="w-4 h-4 inline mr-2 text-gray-600" fill="none"
                                                 stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            CSV
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('tenant.work-orders.create') }}"
                               class="bg-white text-blue-600 hover:bg-blue-50 font-bold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center whitespace-nowrap shrink-0">
                                <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Create Work Order
                            </a>
                        </div>
                    </div>
                </div>
            </div>

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

            <!-- View Tabs -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                        <a href="{{ route('tenant.work-orders.index') }}"
                           class="{{ !request('view') || request('view') === 'list' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            List View
                        </a>
                        <a href="{{ route('tenant.work-orders.index', ['view' => 'map']) }}"
                           class="{{ request('view') === 'map' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                            </svg>
                            Map View
                        </a>
                    </nav>
                </div>
            </div>

            @if(request('view') === 'map')
                <!-- Map View -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div id="workOrdersMap" style="height: 600px; border-radius: 0.5rem;"></div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        // Initialize map (default center fallback)
                        const map = L.map('workOrdersMap').setView([25.276987, 55.296249], 11);

                        // Add OpenStreetMap tile layer
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                            maxZoom: 19
                        }).addTo(map);

                        // Work orders from backend
                        const workOrders = @json($workOrders->items());

                        // Add markers for work orders that have latitude/longitude
                        workOrders.forEach(function (workOrder) {
                            if (workOrder.latitude && workOrder.longitude) {
                                const marker = L.marker([workOrder.latitude, workOrder.longitude]).addTo(map);

                                let statusClass =
                                    workOrder.status === 3 ? 'green' :
                                        workOrder.status === 2 ? 'orange' :
                                            workOrder.status === 1 ? 'blue' : 'gray';

                                let statusText =
                                    workOrder.status === 3 ? 'Completed' :
                                        workOrder.status === 2 ? 'In Progress' :
                                            workOrder.status === 1 ? 'Assigned' : 'Draft';

                                const title = workOrder.title ? workOrder.title : `Work Order #${workOrder.id}`;

                                const projectName = workOrder.project && workOrder.project.name
                                    ? workOrder.project.name
                                    : 'N/A';

                                const assignedName = workOrder.assigned_user && workOrder.assigned_user.name
                                    ? workOrder.assigned_user.name
                                    : 'Unassigned';

                                const dueDate = workOrder.due_date
                                    ? new Date(workOrder.due_date).toLocaleString()
                                    : '';

                                const popupContent = `
                                    <div style="min-width: 220px;">
                                        <h3 style="font-weight: 600; margin-bottom: 6px;">${title}</h3>
                                        <p style="margin: 2px 0; font-size: 13px;"><strong>ID:</strong> #${workOrder.id}</p>
                                        <p style="margin: 2px 0; font-size: 13px;"><strong>Project:</strong> ${projectName}</p>
                                        <p style="margin: 2px 0; font-size: 13px;"><strong>Assigned To:</strong> ${assignedName}</p>
                                        <p style="margin: 2px 0; font-size: 13px;">
                                            <strong>Status:</strong>
                                            <span style="color: ${statusClass}; font-weight: 500;">${statusText}</span>
                                        </p>
                                        ${dueDate ? `<p style="margin: 2px 0; font-size: 13px;"><strong>Due:</strong> ${dueDate}</p>` : ''}
                                        <a href="/tenant/work-orders/${workOrder.id}"
                                           style="display: inline-block; margin-top: 8px; color: #2563eb; text-decoration: underline; font-size: 13px;">
                                            View Details
                                        </a>
                                    </div>
                                `;

                                marker.bindPopup(popupContent);
                            }
                        });

                        // Fit bounds to markers if any
                        const bounds = [];
                        workOrders.forEach(function (wo) {
                            if (wo.latitude && wo.longitude) {
                                bounds.push([wo.latitude, wo.longitude]);
                            }
                        });

                        if (bounds.length > 0) {
                            map.fitBounds(bounds, {padding: [50, 50]});
                        }
                    });
                </script>
            @else
                <!-- Filters (collapsible) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <button type="button" id="work-order-filters-toggle" class="w-full px-4 py-3 flex items-center justify-between text-left text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                            aria-expanded="false" aria-controls="work-order-filters-body">
                        <span class="inline-flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filters
                            @if(request('search') || request('status') !== null || request('project_id'))
                                <span class="ml-2 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Active</span>
                            @endif
                        </span>
                        <svg id="work-order-filters-chevron" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div id="work-order-filters-body" class="border-t border-gray-200 hidden" role="region">
                        <div class="p-4">
                            <form method="GET" id="work-order-filters-form"
                                  action="{{ route('tenant.work-orders.index') }}"
                                  class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <input type="hidden" name="view" value="list">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                                    <input type="text" id="filter-search"
                                           name="search"
                                           value="{{ request('search') }}"
                                           placeholder="Search work orders..."
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select name="status" id="filter-status"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">All Statuses</option>
                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Draft</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Assigned</option>
                                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>In Progress</option>
                                        <option value="3" {{ request('status') === '3' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Project</label>
                                    <select name="project_id" id="filter-project_id"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">All Projects</option>
                                        @foreach(\App\Models\Project::where('tenant_id', session('tenant_context.current_tenant')->id ?? null)->get() as $project)
                                            <option value="{{ $project->id }}"
                                                {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                                {{ $project->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button type="submit"
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                        </svg>
                                        Apply Filters
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                (function() {
                    var STORAGE_KEY_OPEN = 'workOrdersFiltersOpen';
                    var STORAGE_KEY_VALUES = 'workOrderFilterValues';
                    var toggle = document.getElementById('work-order-filters-toggle');
                    var body = document.getElementById('work-order-filters-body');
                    var chevron = document.getElementById('work-order-filters-chevron');
                    var form = document.getElementById('work-order-filters-form');

                    function isOpen() { return !body.classList.contains('hidden'); }
                    function setOpen(open) {
                        body.classList.toggle('hidden', !open);
                        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                        chevron.style.transform = open ? 'rotate(180deg)' : '';
                        try { localStorage.setItem(STORAGE_KEY_OPEN, open ? '1' : '0'); } catch (e) {}
                    }

                    var savedOpen = false;
                    try { savedOpen = localStorage.getItem(STORAGE_KEY_OPEN) === '1'; } catch (e) {}
                    var hasParams = {{ (request('search') || request('status') !== null || request('project_id')) ? 'true' : 'false' }};
                    setOpen(hasParams || savedOpen);

                    toggle.addEventListener('click', function() { setOpen(!isOpen()); });

                    try {
                        var saved = localStorage.getItem(STORAGE_KEY_VALUES);
                        if (saved) {
                            var v = JSON.parse(saved);
                            if (!hasParams && v) {
                                if (v.search) { var el = document.getElementById('filter-search'); if (el) el.value = v.search; }
                                if (v.status !== undefined) { var el = document.getElementById('filter-status'); if (el) el.value = v.status; }
                                if (v.project_id) { var el = document.getElementById('filter-project_id'); if (el) el.value = v.project_id; }
                            }
                        }
                    } catch (e) {}

                    if (form) form.addEventListener('submit', function() {
                        try {
                            localStorage.setItem(STORAGE_KEY_VALUES, JSON.stringify({
                                search: document.getElementById('filter-search')?.value || '',
                                status: document.getElementById('filter-status')?.value ?? '',
                                project_id: document.getElementById('filter-project_id')?.value || ''
                            }));
                        } catch (e) {}
                    });
                })();
                </script>

                <!-- Work Orders Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    @forelse($workOrders as $workOrder)
                        <div
                            class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition duration-200">
                            <div class="p-6">
                                <!-- Header -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                            {{ $workOrder->title ?? 'Work Order #'.$workOrder->id }}
                                        </h3>
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                @if($workOrder->status == 0) bg-gray-100 text-gray-800
                                                @elseif($workOrder->status == 1) bg-blue-100 text-blue-800
                                                @elseif($workOrder->status == 2) bg-yellow-100 text-yellow-800
                                                @elseif($workOrder->status == 3) bg-green-100 text-green-800
                                                @else bg-red-100 text-red-800 @endif">
                                                @if($workOrder->status == 0) Draft
                                                @elseif($workOrder->status == 1) Assigned
                                                @elseif($workOrder->status == 2) In Progress
                                                @elseif($workOrder->status == 3) Completed
                                                @else Cancelled @endif
                                            </span>
                                            @if($workOrder->project)
                                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                                    {{ $workOrder->project->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Forms -->
                                @if($workOrder->forms->count() > 0)
                                    <div class="mb-4">
                                        <p class="text-sm font-medium text-gray-700 mb-2">Forms:</p>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($workOrder->forms->take(3) as $form)
                                                <span
                                                    class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full font-medium">
                                                    {{ $form->name }}
                                                </span>
                                            @endforeach
                                            @if($workOrder->forms->count() > 3)
                                                <span
                                                    class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full font-medium">
                                                    +{{ $workOrder->forms->count() - 3 }} more
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- Assigned User -->
                                @if($workOrder->assignedUser)
                                    <div class="flex items-center mb-4">
                                        <div
                                            class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold mr-3">
                                            {{ strtoupper(substr($workOrder->assignedUser->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $workOrder->assignedUser->name }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $workOrder->assignedUser->email }}
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center mb-4">
                                        <div
                                            class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mr-3">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm text-gray-500 italic">Unassigned</p>
                                    </div>
                                @endif

                                <!-- Due Date & Records -->
                                <div
                                    class="flex justify-between items-center text-sm text-gray-500 mb-4">
                                    @if($workOrder->due_date)
                                        <span>Due: {{ $workOrder->due_date->format('M d, Y') }}</span>
                                    @else
                                        <span>No due date</span>
                                    @endif
                                    <span>{{ $workOrder->records->count() }} records</span>
                                </div>

                                <!-- Actions -->
                                <div class="flex space-x-2">
                                    <a href="{{ route('tenant.work-orders.show', $workOrder) }}"
                                       class="flex-1 bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-2 rounded text-sm font-medium text-center transition duration-200">
                                        View Details
                                    </a>
                                    <a href="{{ route('tenant.work-orders.edit', $workOrder) }}"
                                       class="flex-1 bg-gray-50 text-gray-700 hover:bg-gray-100 px-3 py-2 rounded text-sm font-medium text-center transition duration-200">
                                        Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full">
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No work orders yet</h3>
                                    <p class="text-gray-500 mb-6">
                                        Create work orders to assign forms and tasks to your field teams.
                                    </p>
                                    <a href="{{ route('tenant.work-orders.create') }}"
                                       class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                        Create First Work Order
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($workOrders->hasPages())
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4">
                            {{ $workOrders->links() }}
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <script>
        function toggleWorkOrderExportMenu() {
            const menu = document.getElementById('work-order-export-menu');
            menu.classList.toggle('hidden');
        }

        // Close export menu when clicking outside
        document.addEventListener('click', function (event) {
            const menu = document.getElementById('work-order-export-menu');
            const button = event.target.closest('button[onclick="toggleWorkOrderExportMenu()"]');
            if (!button && menu && !menu.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>
@endsection
