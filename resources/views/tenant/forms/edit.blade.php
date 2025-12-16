@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold">Edit Form</h2>
                            <p class="text-blue-100 mt-1">Update form properties and settings</p>
                        </div>
                        <a href="{{ route('tenant.forms.index') }}"
                           class="text-blue-100 hover:text-white transition duration-200">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Forms
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($form->status == 0) bg-yellow-100 text-yellow-800
                                @elseif($form->status == 1) bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                @if($form->status == 0) Draft
                                @elseif($form->status == 1) Live
                                @else Archived @endif
                            </span>
                            <span class="text-sm text-gray-500">Created {{ $form->created_at->diffForHumans() }}</span>
                        </div>
                        <a href="{{ route('tenant.forms.builder', $form->id) }}"
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Open Form Builder
                        </a>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Info Banner -->
                    <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Form Settings</h3>
                                <p class="mt-1 text-sm text-blue-700">
                                    Update basic form information. To modify form fields, use the Form Builder.
                                    Changes to the form name will be reflected in all work orders using this template.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('tenant.forms.update', $form->id) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Form Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Form Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name', $form->name) }}"
                                   class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                   placeholder="e.g., Safety Inspection Form, Customer Survey, Equipment Check"
                                   required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Choose a descriptive name that clearly identifies the form's purpose</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description <span class="text-gray-500">(Optional)</span>
                            </label>
                            <textarea name="description"
                                      id="description"
                                      rows="4"
                                      class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                      placeholder="Describe what this form is used for, who should fill it out, and any special instructions...">{{ old('description', $form->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Help users understand when and how to use this form</p>
                        </div>

                        <!-- Category (Optional) -->
   <div>
    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
        Category <span class="text-gray-500">(Optional)</span>
    </label>

    <select name="category_id"
            id="category_id"
            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
        <option value="">Select a category...</option>

        @foreach($categories as $category)
            <option value="{{ $category->id }}"
                {{ (string) old('category_id', $form->category_id) === (string) $category->id ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
        @endforeach
    </select>

    <p class="mt-1 text-sm text-gray-500">
        Categorize your form for better organization
    </p>
</div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Form Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status"
                                    id="status"
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                    required>
                                <option value="0" {{ old('status', $form->status) == 0 ? 'selected' : '' }}>Draft</option>
                                <option value="1" {{ old('status', $form->status) == 1 ? 'selected' : '' }}>Live</option>
                                <option value="2" {{ old('status', $form->status) == 2 ? 'selected' : '' }}>Archived</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <div class="mt-2 space-y-2">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Draft</span>
                                    </div>
                                    <p class="ml-2 text-sm text-gray-600">Form is not available for work orders</p>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Live</span>
                                    </div>
                                    <p class="ml-2 text-sm text-gray-600">Form is active and can be assigned to work orders</p>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Archived</span>
                                    </div>
                                    <p class="ml-2 text-sm text-gray-600">Form is hidden but existing submissions remain accessible</p>
                                </div>
                            </div>
                        </div>

                        <!-- Usage Statistics (if available) -->
                        @if(isset($form->work_orders_count))
                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 border-l-4 border-purple-400 p-4 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-purple-800">Usage Statistics</h3>
                                    <p class="mt-1 text-sm text-purple-700">
                                        This form is currently assigned to <strong>{{ $form->work_orders_count }}</strong> work order(s).
                                        Archiving this form will not affect existing work orders.
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('tenant.forms.index') }}"
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Update Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Additional Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Actions</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Form Builder</h4>
                                <p class="text-sm text-gray-500">Add, edit, or remove form fields</p>
                            </div>
                            <a href="{{ route('tenant.forms.builder', $form->id) }}"
                               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Edit Fields →
                            </a>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Clone Form</h4>
                                <p class="text-sm text-gray-500">Create a copy of this form as a starting point</p>
                            </div>
                            <a href="{{ route('tenant.forms.clone', $form->id) }}"
                               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Clone →
                            </a>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 border border-red-200 rounded-lg bg-red-50">
                            <div>
                                <h4 class="text-sm font-medium text-red-900">Delete Form</h4>
                                <p class="text-sm text-red-600">Permanently remove this form and all its data</p>
                            </div>
                            <form method="POST" action="{{ route('tenant.forms.destroy', $form->id) }}" 
                                  onsubmit="return confirm('Are you sure you want to delete this form? This action cannot be undone.')"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center px-3 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                                    Delete →
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
