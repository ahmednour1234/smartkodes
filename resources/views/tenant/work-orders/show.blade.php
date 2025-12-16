@extends('tenant.layouts.app')

@section('title', 'Work Order Details')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center text-sm text-gray-600 mb-2">
            <a href="{{ route('tenant.work-orders.index') }}" class="hover:text-blue-600">Work Orders</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <span class="text-gray-900 font-medium">Details</span>
        </div>
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Work Order Details</h1>
                <p class="text-gray-600 mt-1">View and manage work order information</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('tenant.work-orders.edit', $workOrder->id) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('tenant.work-orders.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Work Order Info -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Work Order Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Project -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Project</label>
                        <a href="{{ route('tenant.projects.show', $workOrder->project->id) }}"
                           class="text-lg font-semibold text-blue-600 hover:text-blue-800 flex items-center">
                            {{ $workOrder->project->name }}
                            <i class="fas fa-external-link-alt ml-2 text-xs"></i>
                        </a>
                        @if($workOrder->project->description)
                            <p class="text-sm text-gray-600 mt-1">{{ Str::limit($workOrder->project->description, 100) }}</p>
                        @endif
                    </div>

                    <!-- Form Templates -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-2">
                            Assigned Form Templates ({{ $workOrder->forms->count() }})
                        </label>
                        @if($workOrder->forms->count() > 0)
                            <div class="space-y-2">
                                @foreach($workOrder->forms as $form)
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                        <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold mr-3">
                                            {{ $loop->iteration }}
                                        </span>
                                        <div class="flex-1">
                                            <a href="{{ route('tenant.forms.show', $form->id) }}"
                                               class="font-semibold text-blue-600 hover:text-blue-800">
                                                {{ $form->name }}
                                            </a>
                                            <span class="text-xs text-gray-500 ml-2">v{{ $form->version }}</span>
                                        </div>
                                        <a href="{{ route('tenant.forms.show', $form->id) }}"
                                           class="text-blue-600 hover:text-blue-800 ml-2">
                                            <i class="fas fa-external-link-alt text-xs"></i>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                                    <div>
                                        <p class="text-sm font-medium text-yellow-800">No forms assigned</p>
                                        <p class="text-xs text-yellow-700 mt-1">
                                            This work order has no form templates assigned yet.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        @php
                            $statusConfig = [
                                0 => ['label' => 'Draft', 'class' => 'bg-gray-100 text-gray-800', 'icon' => 'fa-file'],
                                1 => ['label' => 'Assigned', 'class' => 'bg-blue-100 text-blue-800', 'icon' => 'fa-user-check'],
                                2 => ['label' => 'In Progress', 'class' => 'bg-yellow-100 text-yellow-800', 'icon' => 'fa-spinner'],
                                3 => ['label' => 'Completed', 'class' => 'bg-green-100 text-green-800', 'icon' => 'fa-check-circle'],
                            ];
                            $status = $statusConfig[$workOrder->status] ?? $statusConfig[0];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full {{ $status['class'] }}">
                            <i class="fas {{ $status['icon'] }} mr-2"></i>{{ $status['label'] }}
                        </span>
                    </div>

                    <!-- Priority (SLA) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Priority (SLA)</label>
                        @php
                            /** Priority display logic:
                             *  - If priority_value + priority_unit exist: "4 Hours", "1 Day"
                             *  - Else if priority_minutes exist: convert to best unit
                             */
                            $priorityLabel = null;

                            if (!is_null($workOrder->priority_value ?? null) && !empty($workOrder->priority_unit ?? null)) {
                                $value = (int) $workOrder->priority_value;
                                $unit  = $workOrder->priority_unit; // hour, day, week, month
                                $unitLabel = \Illuminate\Support\Str::plural($unit, $value);
                                $priorityLabel = $value . ' ' . ucfirst($unitLabel);
                            } elseif (!empty($workOrder->priority_minutes ?? null)) {
                                $minutes = (int) $workOrder->priority_minutes;

                                if ($minutes >= 60 * 24 * 30) {
                                    $months = (int) round($minutes / (60 * 24 * 30));
                                    $priorityLabel = $months.' '.\Illuminate\Support\Str::plural('Month', $months);
                                } elseif ($minutes >= 60 * 24 * 7) {
                                    $weeks = (int) round($minutes / (60 * 24 * 7));
                                    $priorityLabel = $weeks.' '.\Illuminate\Support\Str::plural('Week', $weeks);
                                } elseif ($minutes >= 60 * 24) {
                                    $days = (int) round($minutes / (60 * 24));
                                    $priorityLabel = $days.' '.\Illuminate\Support\Str::plural('Day', $days);
                                } else {
                                    $hours = max(1, (int) round($minutes / 60));
                                    $priorityLabel = $hours.' '.\Illuminate\Support\Str::plural('Hour', $hours);
                                }
                            }
                        @endphp

                        @if($priorityLabel)
                            <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">
                                <i class="fas fa-bolt mr-2"></i>{{ $priorityLabel }}
                            </span>
                            <p class="text-xs text-gray-500 mt-1">
                                Target completion time based on SLA priority.
                            </p>
                        @else
                            <p class="text-sm text-gray-400 italic">No SLA priority set</p>
                        @endif
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Due Date</label>
                        @if($workOrder->due_date)
                            <div class="text-lg font-semibold text-gray-900">
                                {{ $workOrder->due_date->format('M d, Y h:i A') }}
                            </div>
                            <p class="text-sm text-gray-600">{{ $workOrder->due_date->diffForHumans() }}</p>
                        @else
                            <p class="text-sm text-gray-400 italic">No due date set</p>
                        @endif
                    </div>

                    <!-- Created -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Created</label>
                        <div class="text-sm text-gray-900">
                            {{ $workOrder->created_at->format('M d, Y h:i A') }}
                        </div>
                        @if($workOrder->creator)
                            <p class="text-sm text-gray-600">by {{ $workOrder->creator->name }}</p>
                        @endif
                    </div>

                    <!-- Last Updated -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Last Updated</label>
                        <div class="text-sm text-gray-900">
                            {{ $workOrder->updated_at->format('M d, Y h:i A') }}
                        </div>
                        <p class="text-sm text-gray-600">{{ $workOrder->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>

            <!-- Associated Records -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Associated Records
                        <span class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                            {{ $workOrder->records->count() }}
                        </span>
                    </h2>
                    <a href="{{ route('tenant.records.create', ['work_order_id' => $workOrder->id]) }}"
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                        <i class="fas fa-plus mr-2"></i>Add Record
                    </a>
                </div>

                @if($workOrder->records->count() > 0)
                    <div class="space-y-3">
                        @foreach($workOrder->records as $record)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <a href="{{ route('tenant.records.show', $record->id) }}"
                                               class="text-lg font-semibold text-blue-600 hover:text-blue-800">
                                                Record #{{ $record->id }}
                                            </a>
                                            @php
                                                $recordStatusConfig = [
                                                    0 => ['label' => 'Draft', 'class' => 'bg-gray-100 text-gray-800'],
                                                    1 => ['label' => 'Submitted', 'class' => 'bg-blue-100 text-blue-800'],
                                                    2 => ['label' => 'In Review', 'class' => 'bg-yellow-100 text-yellow-800'],
                                                    3 => ['label' => 'Approved', 'class' => 'bg-green-100 text-green-800'],
                                                    4 => ['label' => 'Rejected', 'class' => 'bg-red-100 text-red-800'],
                                                ];
                                                $recordStatus = $recordStatusConfig[$record->status] ?? $recordStatusConfig[0];
                                            @endphp
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $recordStatus['class'] }}">
                                                {{ $recordStatus['label'] }}
                                            </span>
                                        </div>
                                        <div class="mt-2 flex items-center text-sm text-gray-600">
                                            <i class="fas fa-user mr-2"></i>
                                            <span>{{ $record->submittedBy->name ?? 'Unknown' }}</span>
                                            <span class="mx-3">•</span>
                                            <i class="fas fa-clock mr-2"></i>
                                            <span>{{ $record->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('tenant.records.show', $record->id) }}"
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-inbox text-5xl text-gray-300 mb-3"></i>
                        <p class="text-gray-600 mb-4">No records submitted yet</p>
                        <a href="{{ route('tenant.records.create', ['work_order_id' => $workOrder->id]) }}"
                           class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Create First Record
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Assignment Card -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Assignment</h3>

                @if($workOrder->assignedUser)
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg">
                            {{ strtoupper(substr($workOrder->assignedUser->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $workOrder->assignedUser->name }}</div>
                            <div class="text-sm text-gray-600">{{ $workOrder->assignedUser->email }}</div>
                        </div>
                    </div>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                            <div>
                                <p class="text-sm font-medium text-yellow-800">Unassigned</p>
                                <p class="text-xs text-yellow-700 mt-1">
                                    This work order has not been assigned to anyone yet.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <a href="{{ route('tenant.work-orders.edit', $workOrder->id) }}"
                   class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-user-edit mr-2"></i>Change Assignment
                </a>
            </div>

            <!-- Location Card (Map Component) -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Location</h3>

           @if($workOrder->latitude && $workOrder->longitude)
    {{-- Readonly map component --}}
                            <x-location-picker
        :latitude="$workOrder->latitude"
        :longitude="$workOrder->longitude"
        :zoom="15"
        height="260"
        :show-marker="true"
        :draggable="false"
        :show-controls="true"
    />

    <p class="mt-3 text-xs text-gray-500">
        Latitude: {{ $workOrder->latitude }} • Longitude: {{ $workOrder->longitude }}
    </p>
@else
    <div class="bg-gray-50 border border-dashed border-gray-300 rounded-lg p-4 text-center">
        <i class="fas fa-map-marker-alt text-2xl text-gray-400 mb-2"></i>
        <p class="text-sm text-gray-600 mb-1">No location set for this work order.</p>
        <p class="text-xs text-gray-500 mb-3">Set a location on the map when editing the work order.</p>
        <a href="{{ route('tenant.work-orders.edit', $workOrder->id) }}"
           class="inline-flex items-center text-blue-600 hover:text-blue-800 text-xs font-medium">
            <i class="fas fa-edit mr-1"></i>Set Location
        </a>
    </div>
@endif

            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>

                <div class="space-y-2">
                    <a href="{{ route('tenant.work-orders.edit', $workOrder->id) }}"
                       class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-edit mr-2"></i>Edit Work Order
                    </a>

                    <a href="{{ route('tenant.records.create', ['work_order_id' => $workOrder->id]) }}"
                       class="block w-full text-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-plus mr-2"></i>Add Record
                    </a>

                    <a href="{{ route('tenant.projects.show', $workOrder->project->id) }}"
                       class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-folder mr-2"></i>View Project
                    </a>

                    @if($workOrder->forms->count() > 0)
                        <a href="{{ route('tenant.forms.show', $workOrder->forms->first()->id) }}"
                           class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-file-alt mr-2"></i>View Form Templates ({{ $workOrder->forms->count() }})
                        </a>
                    @endif
                </div>
            </div>

            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>

                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total Records</span>
                        <span class="text-lg font-bold text-gray-900">
                            {{ $workOrder->records->count() }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Approved</span>
                        <span class="text-lg font-bold text-green-600">
                            {{ $workOrder->records->where('status', 3)->count() }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">In Review</span>
                        <span class="text-lg font-bold text-yellow-600">
                            {{ $workOrder->records->where('status', 2)->count() }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Draft</span>
                        <span class="text-lg font-bold text-gray-600">
                            {{ $workOrder->records->where('status', 0)->count() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
