@extends('tenant.layouts.app')

@section('title', 'Edit Work Order')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center text-sm text-gray-600 mb-2">
            <a href="{{ route('tenant.work-orders.index') }}" class="hover:text-blue-600">Work Orders</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <a href="{{ route('tenant.work-orders.show', $workOrder->id) }}" class="hover:text-blue-600">Work Order Details</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <span class="text-gray-900 font-medium">Edit</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Edit Work Order</h1>
        <p class="text-gray-600 mt-1">Update work order details, assignment, and location.</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('tenant.work-orders.update', $workOrder->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Work Order Title <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title', $workOrder->title) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror"
                        placeholder="e.g., Office Building Safety Inspection, Warehouse Inventory Check"
                        required
                    >
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        Use a clear, descriptive title to help your team recognize this work order.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Project -->
                    <div>
                        <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Project <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="project_id"
                            name="project_id"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('project_id') border-red-500 @enderror"
                        >
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option
                                    value="{{ $project->id }}"
                                    {{ old('project_id', $workOrder->project_id) == $project->id ? 'selected' : '' }}
                                >
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Assigned To -->
                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">
                            Assign To
                        </label>
                        <select
                            id="assigned_to"
                            name="assigned_to"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('assigned_to') border-red-500 @enderror"
                        >
                            <option value="">Unassigned</option>
                            @foreach($users as $user)
                                <option
                                    value="{{ $user->id }}"
                                    {{ old('assigned_to', $workOrder->assigned_to) == $user->id ? 'selected' : '' }}
                                >
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            Choose a team member responsible for this work order (optional).
                        </p>
                    </div>
                </div>

                <!-- Forms (Multiple Selection) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Assign Form Templates <span class="text-red-500">*</span>
                    </label>
                    <div class="border border-gray-300 rounded-lg p-4 max-h-80 overflow-y-auto @error('form_ids') border-red-500 @enderror">
                        @php
                            $selectedFormIds = old('form_ids', $workOrder->forms->pluck('id')->toArray());
                        @endphp

                        @if($forms->count() > 0)
                            <div class="space-y-2">
                                @foreach($forms as $form)
                                    <label class="flex items-start p-3 hover:bg-gray-50 rounded-lg cursor-pointer transition-colors">
                                        <input
                                            type="checkbox"
                                            name="form_ids[]"
                                            value="{{ $form->id }}"
                                            {{ in_array($form->id, $selectedFormIds) ? 'checked' : '' }}
                                            class="mt-1 mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                        >
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">{{ $form->name }}</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Version {{ $form->version }} •
                                                @if($form->status == 0)
                                                    <span class="text-gray-600">Draft</span>
                                                @elseif($form->status == 1)
                                                    <span class="text-green-600">Active</span>
                                                @else
                                                    <span class="text-red-600">Inactive</span>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">
                                No forms available.
                            </p>
                        @endif
                    </div>
                    @error('form_ids')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        Select one or more forms that must be completed under this work order.
                    </p>
                </div>

                <!-- Status, Priority, Due Date -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="status"
                            name="status"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('status') border-red-500 @enderror"
                        >
                            <option value="0" {{ old('status', $workOrder->status) == '0' ? 'selected' : '' }}>Draft</option>
                            <option value="1" {{ old('status', $workOrder->status) == '1' ? 'selected' : '' }}>Assigned</option>
                            <option value="2" {{ old('status', $workOrder->status) == '2' ? 'selected' : '' }}>In Progress</option>
                            <option value="3" {{ old('status', $workOrder->status) == '3' ? 'selected' : '' }}>Completed</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Priority (SLA) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Priority (SLA)
                        </label>
                        <div class="flex space-x-2">
                            <input
                                type="number"
                                name="priority_value"
                                min="1"
                                value="{{ old('priority_value', $workOrder->priority_value) }}"
                                class="block w-1/2 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('priority_value') border-red-500 @enderror"
                                placeholder="1"
                            >
                            <select
                                name="priority_unit"
                                class="block w-1/2 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('priority_unit') border-red-500 @enderror"
                            >
                                <option value="">Select...</option>
                                <option value="hour"  {{ old('priority_unit', $workOrder->priority_unit) == 'hour' ? 'selected' : '' }}>Hour(s)</option>
                                <option value="day"   {{ old('priority_unit', $workOrder->priority_unit) == 'day' ? 'selected' : '' }}>Day(s)</option>
                                <option value="week"  {{ old('priority_unit', $workOrder->priority_unit) == 'week' ? 'selected' : '' }}>Week(s)</option>
                                <option value="month" {{ old('priority_unit', $workOrder->priority_unit) == 'month' ? 'selected' : '' }}>Month(s)</option>
                            </select>
                        </div>
                        @error('priority_value')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('priority_unit')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            Example: 1 day, 4 hours, 1 week. Used for SLA and scheduling.
                        </p>
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Due Date
                        </label>
                        <input
                            type="datetime-local"
                            id="due_date"
                            name="due_date"
                            value="{{ old('due_date', $workOrder->due_date ? $workOrder->due_date->format('Y-m-d\TH:i') : '') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('due_date') border-red-500 @enderror"
                        >
                        @error('due_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            Optional hard deadline for completing this work order.
                        </p>
                    </div>
                </div>

                <!-- Location on Map -->
                <div>
                    <x-location-picker
                        lat-name="latitude"
                        lng-name="longitude"
                        :lat-value="old('latitude', $workOrder->latitude)"
                        :lng-value="old('longitude', $workOrder->longitude)"
                        label="Work Order Location"
                        hint="Drag the marker or click on the map to set the exact location for this work order."
                    />
                    @error('latitude')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('longitude')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description / Instructions -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description / Instructions
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                        placeholder="Provide any specific instructions, safety notes, or context for your field team..."
                    >{{ old('description', $workOrder->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex items-center justify-end space-x-3">
                <a
                    href="{{ route('tenant.work-orders.show', $workOrder->id) }}"
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200"
                >
                    Cancel
                </a>
                <button
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200 flex items-center"
                >
                    <i class="fas fa-save mr-2"></i>
                    Update Work Order
                </button>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-red-900 mb-2">Danger Zone</h3>
        <p class="text-sm text-red-700 mb-4">
            Deleting this work order will also delete all associated records. This action cannot be undone.
        </p>
        <form
            action="{{ route('tenant.work-orders.destroy', $workOrder->id) }}"
            method="POST"
            onsubmit="return confirm('Are you absolutely sure? This will delete the work order and all {{ $workOrder->records->count() }} associated records. This cannot be undone!');"
        >
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition duration-200"
            >
                <i class="fas fa-trash mr-2"></i>
                Delete Work Order
            </button>
        </form>
    </div>
</div>
@endsection
