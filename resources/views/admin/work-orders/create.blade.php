@extends('admin.layouts.app')

@section('title', 'Create Work Order')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center text-sm text-gray-600 mb-2">
            <a href="{{ route('admin.work-orders.index') }}" class="hover:text-blue-600">Work Orders</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <span class="text-gray-900 font-medium">Create New</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Create Work Order</h1>
        <p class="text-gray-600 mt-1">Create a new work order and assign it to a team member</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('admin.work-orders.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Project -->
                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Project <span class="text-red-500">*</span>
                    </label>
                    <select id="project_id" name="project_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('project_id') border-red-500 @enderror">
                        <option value="">Select Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Forms (Multiple Selection) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Assign Form Templates <span class="text-red-500">*</span>
                    </label>
                    <div class="border border-gray-300 rounded-lg p-4 max-h-80 overflow-y-auto @error('form_ids') border-red-500 @enderror">
                        @if($forms->count() > 0)
                            <div class="space-y-2">
                                @foreach($forms as $form)
                                    <label class="flex items-start p-3 hover:bg-gray-50 rounded-lg cursor-pointer transition-colors">
                                        <input type="checkbox" name="form_ids[]" value="{{ $form->id }}"
                                               {{ (is_array(old('form_ids')) && in_array($form->id, old('form_ids'))) ? 'checked' : '' }}
                                               class="mt-1 mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
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
                                No forms available. <a href="{{ route('admin.forms.create') }}" class="text-blue-600 hover:text-blue-800">Create a form template first</a>.
                            </p>
                        @endif
                    </div>
                    @error('form_ids')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Select one or more forms that need to be completed for this work order</p>
                </div>

                <!-- Assigned To -->
                <div>
                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">
                        Assign To
                    </label>
                    <select id="assigned_to" name="assigned_to"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('assigned_to') border-red-500 @enderror">
                        <option value="">Unassigned</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Leave empty to assign later</p>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('status') border-red-500 @enderror">
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Draft</option>
                        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Assigned</option>
                        <option value="2" {{ old('status') == '2' ? 'selected' : '' }}>In Progress</option>
                        <option value="3" {{ old('status') == '3' ? 'selected' : '' }}>Completed</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Due Date -->
                <div class="md:col-span-2">
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Due Date
                    </label>
                    <input type="datetime-local" id="due_date" name="due_date" value="{{ old('due_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('due_date') border-red-500 @enderror">
                    @error('due_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Optional deadline for completing this work order</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.work-orders.index') }}"
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200 flex items-center">
                    <i class="fas fa-save mr-2"></i>Create Work Order
                </button>
            </div>
        </form>
    </div>

    <!-- Help Text -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
            <div>
                <h3 class="text-sm font-semibold text-blue-900 mb-1">About Work Orders</h3>
                <p class="text-sm text-blue-800">
                    Work orders link a specific form template to a project and can be assigned to team members.
                    Once assigned, users can fill out the form and submit records that are tracked under this work order.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
