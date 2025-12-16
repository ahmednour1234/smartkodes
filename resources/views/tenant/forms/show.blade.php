@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="flex items-center space-x-3">
                                <h2 class="text-2xl font-bold">{{ $form->name }}</h2>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @if($form->status == 0) bg-yellow-100 text-yellow-800
                                    @elseif($form->status == 1) bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    @if($form->status == 0) Draft
                                    @elseif($form->status == 1) Live
                                    @else Archived @endif
                                </span>
                            </div>
                            <p class="text-blue-100 mt-2">{{ $form->description ?: 'No description provided' }}</p>
                            <div class="flex items-center space-x-4 mt-3 text-sm text-blue-100">
                                <span>Created {{ $form->created_at->diffForHumans() }}</span>
                                @if($form->creator)
                                <span>•</span>
                                <span>by {{ $form->creator->name }}</span>
                                @endif
                                @if($form->category)
                                <span>•</span>
                                <span>Category: {{ ucfirst($form->category) }}</span>
                                @endif
                            </div>
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

            <!-- Action Buttons -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('tenant.forms.edit', $form->id) }}"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Settings
                            </a>
                            <a href="{{ route('tenant.forms.builder', $form->id) }}"
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Open Form Builder
                            </a>
                            <a href="{{ route('tenant.forms.clone', $form->id) }}"
                               class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-medium rounded-lg transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Clone Form
                            </a>
                        </div>
                        @if($form->status == 0)
                        <form method="POST" action="{{ route('tenant.forms.publish', $form->id) }}" class="inline">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                                Publish Form
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Form Fields -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Form Fields</h3>
                                <span class="text-sm text-gray-500">{{ $form->formFields->count() }} fields</span>
                            </div>

                            @if($form->formFields->isEmpty())
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No fields yet</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by adding fields to your form.</p>
                                    <div class="mt-6">
                                        <a href="{{ route('tenant.forms.builder', $form->id) }}"
                                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Add Fields
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="space-y-3">
                                    @foreach($form->formFields->sortBy('order') as $field)
                                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition duration-150">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-2">
                                                        <h4 class="text-sm font-medium text-gray-900">
                                                            {{ $field->label }}
                                                            @if($field->is_required)
                                                                <span class="text-red-500">*</span>
                                                            @endif
                                                        </h4>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ ucfirst(str_replace('_', ' ', $field->type)) }}
                                                        </span>
                                                    </div>
                                                    @if($field->placeholder)
                                                        <p class="mt-1 text-xs text-gray-500">Placeholder: {{ $field->placeholder }}</p>
                                                    @endif
                                                    @if($field->help_text)
                                                        <p class="mt-1 text-xs text-gray-600">{{ $field->help_text }}</p>
                                                    @endif
                                                    @if(in_array($field->type, ['select', 'radio', 'checkbox', 'dropdown']))
                                                        @php
                                                            $config = is_string($field->config_json) ? json_decode($field->config_json, true) : $field->config_json;
                                                            $options = $config['options'] ?? [];
                                                        @endphp
                                                        @if(!empty($options))
                                                            <div class="mt-2">
                                                                <p class="text-xs text-gray-500 mb-1">Options:</p>
                                                                <div class="flex flex-wrap gap-1">
                                                                    @foreach($options as $option)
                                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">
                                                                            {{ is_array($option) ? ($option['label'] ?? $option['value'] ?? '') : $option }}
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                                <span class="text-xs text-gray-400 ml-4">#{{ $field->order }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-4 text-center">
                                    <a href="{{ route('tenant.forms.builder', $form->id) }}"
                                       class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                        Edit fields in Form Builder →
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Statistics -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Total Fields</span>
                                        <span class="text-2xl font-bold text-blue-600">{{ $form->formFields->count() }}</span>
                                    </div>
                                </div>
                                <div class="border-t border-gray-200 pt-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Required Fields</span>
                                        <span class="text-2xl font-bold text-red-600">{{ $form->formFields->where('is_required', true)->count() }}</span>
                                    </div>
                                </div>
                                <div class="border-t border-gray-200 pt-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Work Orders</span>
                                        <span class="text-2xl font-bold text-green-600">{{ $form->workOrders->count() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Work Orders -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Assigned to Work Orders</h3>
                            
                            @if($form->workOrders->isEmpty())
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">Not assigned to any work orders yet</p>
                                </div>
                            @else
                                <div class="space-y-3 max-h-96 overflow-y-auto">
                                    @foreach($form->workOrders->take(10) as $workOrder)
                                        <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition duration-150">
                                            <a href="{{ route('tenant.work-orders.show', $workOrder->id) }}" class="block">
                                                <div class="flex items-start justify-between">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $workOrder->title }}</p>
                                                        <p class="text-xs text-gray-500 mt-1">{{ $workOrder->project->name }}</p>
                                                        <div class="flex items-center mt-2 space-x-2">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
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
                                                        </div>
                                                    </div>
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                                @if($form->workOrders->count() > 10)
                                    <div class="mt-3 text-center">
                                        <a href="{{ route('tenant.work-orders.index', ['form' => $form->id]) }}" 
                                           class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                            View all {{ $form->workOrders->count() }} work orders →
                                        </a>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Meta Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Form ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $form->id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $form->created_at->format('M d, Y \a\t g:i A') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Last Updated</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $form->updated_at->format('M d, Y \a\t g:i A') }}</dd>
                                </div>
                                @if($form->creator)
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Created By</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $form->creator->name }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
