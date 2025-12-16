@extends('admin.layouts.app')

@section('title', 'Work Orders')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Work Orders</h1>
            <p class="text-gray-600 mt-1">Manage and track work orders for your projects</p>
        </div>
        <a href="{{ route('admin.work-orders.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200 flex items-center">
            <i class="fas fa-plus mr-2"></i>Create Work Order
        </a>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('admin.work-orders.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search work orders..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Draft</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Assigned</option>
                    <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>In Progress</option>
                    <option value="3" {{ request('status') === '3' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                <select name="sort" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="created_at_desc" {{ request('sort') === 'created_at_desc' ? 'selected' : '' }}>Newest First</option>
                    <option value="created_at_asc" {{ request('sort') === 'created_at_asc' ? 'selected' : '' }}>Oldest First</option>
                    <option value="due_date_asc" {{ request('sort') === 'due_date_asc' ? 'selected' : '' }}>Due Date (Earliest)</option>
                    <option value="due_date_desc" {{ request('sort') === 'due_date_desc' ? 'selected' : '' }}>Due Date (Latest)</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200 w-full">
                    <i class="fas fa-filter mr-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Work Orders Table -->
    @if($workOrders->count() > 0)
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Form</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Records</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($workOrders as $workOrder)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $workOrder->project->name ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ Str::limit($workOrder->project->description ?? '', 40) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($workOrder->forms->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($workOrder->forms->take(2) as $form)
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full font-medium">
                                                    {{ $form->name }}
                                                </span>
                                            @endforeach
                                            @if($workOrder->forms->count() > 2)
                                                <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full font-medium">
                                                    +{{ $workOrder->forms->count() - 2 }} more
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400 italic">No forms assigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($workOrder->assignedUser)
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold mr-2">
                                                {{ strtoupper(substr($workOrder->assignedUser->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $workOrder->assignedUser->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $workOrder->assignedUser->email }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400 italic">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusConfig = [
                                            0 => ['label' => 'Draft', 'class' => 'bg-gray-100 text-gray-800'],
                                            1 => ['label' => 'Assigned', 'class' => 'bg-blue-100 text-blue-800'],
                                            2 => ['label' => 'In Progress', 'class' => 'bg-yellow-100 text-yellow-800'],
                                            3 => ['label' => 'Completed', 'class' => 'bg-green-100 text-green-800'],
                                        ];
                                        $status = $statusConfig[$workOrder->status] ?? $statusConfig[0];
                                    @endphp
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $status['class'] }}">
                                        {{ $status['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($workOrder->due_date)
                                        <div class="text-sm text-gray-900">{{ $workOrder->due_date->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $workOrder->due_date->diffForHumans() }}</div>
                                    @else
                                        <span class="text-sm text-gray-400">No due date</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="font-semibold">{{ $workOrder->records->count() }}</span> records
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.work-orders.show', $workOrder->id) }}" class="text-blue-600 hover:text-blue-900 mr-3" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.work-orders.edit', $workOrder->id) }}" class="text-green-600 hover:text-green-900 mr-3" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.work-orders.destroy', $workOrder->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this work order? This will also delete all associated records.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200">
                {{ $workOrders->links() }}
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Work Orders Found</h3>
            <p class="text-gray-600 mb-6">Get started by creating your first work order.</p>
            <a href="{{ route('admin.work-orders.create') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-200">
                <i class="fas fa-plus mr-2"></i>Create Work Order
            </a>
        </div>
    @endif
</div>
@endsection
