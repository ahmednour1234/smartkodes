@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold">Create Work Order</h2>
                            <p class="text-blue-100 mt-1">Assign forms and tasks to your field teams</p>
                        </div>
                        <a href="{{ route('tenant.work-orders.index') }}"
                           class="text-blue-100 hover:text-white transition duration-200">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Work Orders
                        </a>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Info Banner -->
                    <div
                        class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">What is a work order?</h3>
                                <p class="mt-1 text-sm text-blue-700">
                                    Work orders connect forms to a project and assign tasks to team members. Assigned users will see this work order in their list and can fill out the attached forms; submitted records are tracked under this work order.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('tenant.work-orders.store') }}" class="space-y-6">
                        @csrf

                        <!-- Work Order Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                Work Order Title <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="title"
                                id="title"
                                value="{{ old('title') }}"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                placeholder="e.g., Office Building Safety Inspection, Warehouse Inventory Check"
                                required
                            >
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Give this work order a clear, descriptive title.</p>
                        </div>

                        <!-- Project & Assignee -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Project Selection -->
                            <div>
                                <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Project <span class="text-red-500">*</span>
                                </label>
                                <select
                                    name="project_id"
                                    id="project_id"
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                    required
                                >
                                    <option value="">Select a project...</option>
                                    @foreach($projects as $project)
                                        <option
                                            value="{{ $project->id }}"
                                            {{ old('project_id') == $project->id ? 'selected' : '' }}
                                        >
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Assign To -->
                            <div>
                                <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">
                                    Assign To
                                </label>
                                <select
                                    name="assigned_to"
                                    id="assigned_to"
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                >
                                    <option value="">Unassigned (assign later)</option>
                                    @foreach($users as $user)
                                        <option
                                            value="{{ $user->id }}"
                                            {{ old('assigned_to') == $user->id ? 'selected' : '' }}
                                        >
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">
                                    Select a team member to assign this work order to. Unassigned work orders remain pending and are not visible or actionable to field users until assigned.
                                </p>
                            </div>
                        </div>

                        <!-- Form Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Assign Forms <span class="text-red-500">*</span>
                            </label>
                            <p class="text-sm text-gray-600 mb-2">
                                Create or select a form before assigning a work order. Form templates are built in <a href="{{ route('tenant.forms.index') }}" class="text-blue-600 hover:text-blue-800 underline">Forms</a>; then choose one or more below to attach to this work order.
                            </p>

                            <div
                                class="border border-gray-300 rounded-lg p-4 max-h-80 overflow-y-auto @error('form_ids') border-red-500 @enderror">
                                @if($forms->count() > 0)
                                    <div class="space-y-3">
                                        @foreach($forms as $form)
                                            <label
                                                class="flex items-start p-3 hover:bg-gray-50 rounded-lg cursor-pointer transition-colors border border-gray-200">
                                                <input
                                                    type="checkbox"
                                                    name="form_ids[]"
                                                    value="{{ $form->id }}"
                                                    {{ (is_array(old('form_ids')) && in_array($form->id, old('form_ids'))) ? 'checked' : '' }}
                                                    class="mt-1 mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                >
                                                <div class="flex-1">
                                                    <div class="font-medium text-gray-900">{{ $form->name }}</div>
                                                    <div class="text-sm text-gray-600 mt-1">
                                                        {{ Str::limit($form->description, 100) }}
                                                    </div>
                                                    <div class="flex items-center space-x-4 mt-2">
                                                        <span
                                                            class="text-xs px-2 py-1 rounded-full
                                                                @if($form->status == 0) bg-gray-100 text-gray-800
                                                                @elseif($form->status == 1) bg-green-100 text-green-800
                                                                @else bg-red-100 text-red-800 @endif">
                                                            @if($form->status == 0) Draft
                                                            @elseif($form->status == 1) Active
                                                            @else Inactive @endif
                                                        </span>
                                                        <span class="text-xs text-gray-500">v{{ $form->version }}</span>
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No forms available</h3>
                                        <p class="text-gray-500 mb-4">
                                            You need to create form templates before creating work orders.
                                        </p>
                                        <a
                                            href="{{ route('tenant.forms.create') }}"
                                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200"
                                        >
                                            Create Form Template
                                        </a>
                                    </div>
                                @endif
                            </div>

                            @error('form_ids')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <p class="mt-1 text-sm text-gray-500">
                                Select one or more forms that must be completed for this work order.
                            </p>
                            @if($forms->count() === 0)
                                <p class="mt-1 text-sm text-amber-600">
                                    No forms yet. Create a form template first, then return here to assign it.
                                </p>
                            @endif
                        </div>

                        <!-- Status, Priority (importance), Due Date -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Initial Status <span class="text-red-500">*</span>
                                </label>
                                <select
                                    name="status"
                                    id="status"
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                    required
                                >
                                    <option value="0" {{ old('status', '0') == '0' ? 'selected' : '' }}>Draft</option>
                                    <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Assigned</option>
                                    <option value="2" {{ old('status') == '2' ? 'selected' : '' }}>In Progress</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Priority (importance level) -->
                            <div>
                                <label for="importance_level" class="block text-sm font-medium text-gray-700 mb-2">
                                    Priority
                                </label>
                                <select
                                    name="importance_level"
                                    id="importance_level"
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                >
                                    <option value="">No priority</option>
                                    <option value="low" {{ old('importance_level') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('importance_level') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('importance_level') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="critical" {{ old('importance_level') == 'critical' ? 'selected' : '' }}>Critical</option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500">Importance level (how critical this work is).</p>
                                @error('importance_level')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Due Date -->
                            <div>
                                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Due Date
                                </label>
                                <input
                                    type="datetime-local"
                                    name="due_date"
                                    id="due_date"
                                    value="{{ old('due_date') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                >
                                @error('due_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Optional deadline for completion.</p>
                            </div>
                        </div>

                        <!-- SLA (time to completion) - separate from Priority -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                SLA (time to completion)
                            </label>
                            <p class="text-sm text-gray-500 mb-2">Target time allowed to complete this work order (e.g. 4 hours, 1 day). This is separate from Priority (importance).</p>
                            <div class="flex space-x-2 max-w-xs">
                                <input
                                    type="number"
                                    name="priority_value"
                                    min="1"
                                    value="{{ old('priority_value') }}"
                                    class="block w-24 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                    placeholder="1"
                                >
                                <select
                                    name="priority_unit"
                                    class="block w-32 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                >
                                    <option value="">Select...</option>
                                    <option value="hour"  {{ old('priority_unit') == 'hour' ? 'selected' : '' }}>Hour(s)</option>
                                    <option value="day"   {{ old('priority_unit') == 'day' ? 'selected' : '' }}>Day(s)</option>
                                    <option value="week"  {{ old('priority_unit') == 'week' ? 'selected' : '' }}>Week(s)</option>
                                    <option value="month" {{ old('priority_unit') == 'month' ? 'selected' : '' }}>Month(s)</option>
                                </select>
                            </div>
                            @error('priority_value')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('priority_unit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Location on Map -->
                        <div>
                            <x-location-picker
                                lat-name="latitude"
                                lng-name="longitude"
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

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description / Instructions
                            </label>
                            <textarea
                                name="description"
                                id="description"
                                rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                placeholder="Provide any special instructions or context for this work order..."
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">Next step: after you create</h3>
                                    <p class="mt-1 text-sm text-green-700">Once this work order is created:</p>
                                    <ul class="mt-2 text-sm text-green-700 list-disc list-inside space-y-1">
                                        <li>Assigned users will see it in their work orders and can open the forms</li>
                                        <li>You can view progress and submitted records on the work order page</li>
                                        <li>You can edit the work order or reassign it anytime</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a
                                href="{{ route('tenant.work-orders.index') }}"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200"
                            >
                                Cancel
                            </a>
                            <button
                                type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200"
                            >
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Create Work Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
