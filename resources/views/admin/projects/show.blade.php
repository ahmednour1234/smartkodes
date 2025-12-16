@extends('admin.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">{{ $project->name }}</h2>
                        <div>
                            <a href="{{ route('admin.projects.edit', $project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">Edit</a>
                            <a href="{{ route('admin.projects.index') }}" class="text-gray-600 hover:text-gray-900">← Back to Projects</a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Project Details</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                                    <dd class="text-sm text-gray-900">{{ $project->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="text-sm text-gray-900">{{ $project->description ?: 'No description provided' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($project->status == 0) bg-gray-100 text-gray-800
                                            @elseif($project->status == 1) bg-green-100 text-green-800
                                            @else bg-red-100 text-red-800 @endif">
                                            @if($project->status == 0) Draft
                                            @elseif($project->status == 1) Active
                                            @else Completed @endif
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created By</dt>
                                    <dd class="text-sm text-gray-900">{{ $project->creator->name ?? 'Unknown' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created At</dt>
                                    <dd class="text-sm text-gray-900">{{ $project->created_at->format('M d, Y H:i') }}</dd>
                                </div>
                                @if($project->updated_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                    <dd class="text-sm text-gray-900">{{ $project->updated_at->format('M d, Y H:i') }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Related Data</h3>
                            <div class="space-y-4">
                                <div class="bg-green-50 p-4 rounded">
                                    <h4 class="font-medium text-green-900">Work Orders</h4>
                                    <p class="text-green-700">{{ $project->workOrders->count() }} work orders</p>
                                </div>
                                <div class="bg-yellow-50 p-4 rounded">
                                    <h4 class="font-medium text-yellow-900">Records</h4>
                                    <p class="text-yellow-700">{{ $project->records->count() }} submitted records</p>
                                </div>
                                <div class="bg-purple-50 p-4 rounded">
                                    <h4 class="font-medium text-purple-900">Forms (via Work Orders)</h4>
                                    <p class="text-purple-700">{{ $project->workOrders->pluck('forms')->flatten()->unique('id')->count() }} unique forms</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($project->workOrders->count() > 0)
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Work Orders</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Work Order Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Forms</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($project->workOrders as $workOrder)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $workOrder->title }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                @if($workOrder->forms->count() > 0)
                                                    @foreach($workOrder->forms->take(2) as $form)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1 mb-1">
                                                            {{ $form->name }}
                                                        </span>
                                                    @endforeach
                                                    @if($workOrder->forms->count() > 2)
                                                        <span class="text-xs text-gray-500">+{{ $workOrder->forms->count() - 2 }} more</span>
                                                    @endif
                                                @else
                                                    <span class="text-xs text-gray-400">No forms assigned</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
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
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $workOrder->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('admin.work-orders.show', $workOrder->id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
