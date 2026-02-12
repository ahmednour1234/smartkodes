@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold">{{ $project->name }}</h2>
                            <p class="text-blue-100 mt-1">Project Details & Management</p>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('tenant.projects.edit', $project) }}"
                               class="bg-white text-blue-600 hover:bg-blue-50 font-medium py-2 px-4 rounded-lg transition duration-200">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Project
                            </a>
                            <a href="{{ route('tenant.projects.index') }}"
                               class="text-blue-100 hover:text-white transition duration-200">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Projects
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Status Banner -->
            <div class="mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <span class="px-3 py-1 text-sm font-semibold rounded-full
                                    @if($project->status == 3) bg-gray-100 text-gray-800
                                    @elseif($project->status == 1) bg-green-100 text-green-800
                                    @elseif($project->status == 2) bg-blue-100 text-blue-800
                                    @else bg-gray-200 text-gray-700 @endif">
                                    @if($project->status == 3) Draft
                                    @elseif($project->status == 1) Active
                                    @elseif($project->status == 2) Paused
                                    @else Archived @endif
                                </span>
                                <span class="text-sm text-gray-500">
                                    Created {{ $project->created_at->format('M d, Y') }}
                                    @if($project->creator)
                                        by {{ $project->creator->name }}
                                    @endif
                                </span>
                            </div>
                            @if($project->updated_at && $project->updated_at != $project->created_at)
                                <span class="text-sm text-gray-500">
                                    Last updated {{ $project->updated_at->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Project Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Project Name</label>
                                    <p class="text-gray-900">{{ $project->name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <span class="px-2 py-1 text-sm font-semibold rounded-full
                                        @if($project->status == 3) bg-gray-100 text-gray-800
                                        @elseif($project->status == 1) bg-green-100 text-green-800
                                        @elseif($project->status == 2) bg-blue-100 text-blue-800
                                        @else bg-gray-200 text-gray-700 @endif">
                                        @if($project->status == 3) Draft
                                        @elseif($project->status == 1) Active
                                        @elseif($project->status == 2) Paused
                                        @else Archived @endif
                                    </span>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                    <p class="text-gray-900">{{ $project->description ?: 'No description provided' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Work Orders -->
                    @if($project->workOrders->count() > 0)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Work Orders</h3>
                                    <a href="{{ route('tenant.work-orders.create', ['project' => $project->id]) }}"
                                       class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded-lg transition duration-200">
                                        Create Work Order
                                    </a>
                                </div>
                                <div class="space-y-3">
                                    @foreach($project->workOrders as $workOrder)
                                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition duration-200">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-gray-900">{{ $workOrder->title }}</h4>
                                                    <div class="flex items-center space-x-4 mt-1">
                                                        <span class="text-sm text-gray-500">
                                                            {{ $workOrder->forms->count() }} form{{ $workOrder->forms->count() !== 1 ? 's' : '' }}
                                                        </span>
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                            @if($workOrder->status == 0) bg-gray-100 text-gray-800
                                                            @elseif($workOrder->status == 1) bg-blue-100 text-blue-800
                                                            @elseif($workOrder->status == 2) bg-yellow-100 text-yellow-800
                                                            @elseif($workOrder->status == 3) bg-green-100 text-green-800
                                                            @else bg-red-100 text-red-800 @endif">
                                                            @if($workOrder->status == 0) Draft
                                                            @elseif($workOrder->status == 1) Open
                                                            @elseif($workOrder->status == 2) In Progress
                                                            @elseif($workOrder->status == 3) Completed
                                                            @else Cancelled @endif
                                                        </span>
                                                    </div>
                                                </div>
                                                <a href="{{ route('tenant.work-orders.show', $workOrder) }}"
                                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                    View Details →
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Work Orders Yet</h3>
                                <p class="text-gray-500 mb-6">Create work orders to assign forms and tasks to your team.</p>
                                <a href="{{ route('tenant.work-orders.create', ['project' => $project->id]) }}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                    Create First Work Order
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Project Stats -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Statistics</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Work Orders</span>
                                    <span class="text-lg font-semibold text-gray-900">{{ $project->workOrders->count() }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Active Work Orders</span>
                                    <span class="text-lg font-semibold text-green-600">{{ $project->workOrders->where('status', 1)->count() + $project->workOrders->where('status', 2)->count() }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Completed Work Orders</span>
                                    <span class="text-lg font-semibold text-blue-600">{{ $project->workOrders->where('status', 3)->count() }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Total Records</span>
                                    <span class="text-lg font-semibold text-purple-600">{{ $project->records->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <a href="{{ route('tenant.work-orders.create', ['project' => $project->id]) }}"
                                   class="block w-full bg-blue-50 hover:bg-blue-100 text-blue-700 text-center py-2 px-4 rounded-lg transition duration-200">
                                    Create Work Order
                                </a>
                                <a href="{{ route('tenant.records.create', ['project' => $project->id]) }}"
                                   class="block w-full bg-green-50 hover:bg-green-100 text-green-700 text-center py-2 px-4 rounded-lg transition duration-200">
                                    Submit Record
                                </a>
                                <a href="{{ route('tenant.reports.index') }}?project={{ $project->id }}"
                                   class="block w-full bg-purple-50 hover:bg-purple-100 text-purple-700 text-center py-2 px-4 rounded-lg transition duration-200">
                                    View Reports
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Project Timeline -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
                            <div class="space-y-3">
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0 w-2 h-2 bg-blue-600 rounded-full mt-2"></div>
                                    <div>
                                        <p class="text-sm text-gray-900">Project created</p>
                                        <p class="text-xs text-gray-500">{{ $project->created_at->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>
                                @if($project->workOrders->count() > 0)
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0 w-2 h-2 bg-green-600 rounded-full mt-2"></div>
                                        <div>
                                            <p class="text-sm text-gray-900">First work order created</p>
                                            <p class="text-xs text-gray-500">{{ $project->workOrders->sortBy('created_at')->first()->created_at->format('M d, Y H:i') }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if($project->updated_at && $project->updated_at != $project->created_at)
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0 w-2 h-2 bg-yellow-600 rounded-full mt-2"></div>
                                        <div>
                                            <p class="text-sm text-gray-900">Last updated</p>
                                            <p class="text-xs text-gray-500">{{ $project->updated_at->format('M d, Y H:i') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
