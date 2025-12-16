@extends('tenant.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Form Submission Details</h1>
                <p class="mt-2 text-sm text-gray-600">Viewing submission for {{ $record->formVersion->form->name ?? $record->form->name ?? 'Record' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('tenant.records.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
                <a href="{{ route('tenant.records.edit', $record->id) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Form Fields -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Submitted Data</h2>
                </div>
                <div class="px-6 py-4">
                    @if($record->form && $record->form->formFields->count() > 0)
                        <dl class="space-y-6">
                            @foreach($record->form->formFields as $field)
                                @php
                                    $value = $fieldValues[$field->name] ?? null;
                                    $isSensitive = $field->is_sensitive ?? false;
                                @endphp

                                <div class="border-b border-gray-200 pb-4 last:border-b-0">
                                    <dt class="text-sm font-medium text-gray-500 mb-2 flex items-center">
                                        {{ $field->label }}
                                        @if($field->is_required)
                                            <span class="ml-1 text-red-500">*</span>
                                        @endif
                                        @if($isSensitive)
                                            <span class="ml-2 px-2 py-0.5 text-xs bg-yellow-100 text-yellow-800 rounded-full">Sensitive</span>
                                        @endif
                                    </dt>
                                    <dd class="text-sm text-gray-900">
                                        @if($value === null || $value === '')
                                            <span class="text-gray-400 italic">No data provided</span>
                                        @else
                                            @switch($field->type)
                                                @case('text')
                                                @case('textarea')
                                                @case('email')
                                                @case('phone')
                                                @case('url')
                                                    @if($isSensitive)
                                                        <div class="relative">
                                                            <span class="masked-value">{{ str_repeat('•', min(strlen($value), 20)) }}</span>
                                                            <span class="real-value hidden">{{ $value }}</span>
                                                            <button type="button" onclick="toggleSensitive(this)" class="ml-2 text-indigo-600 hover:text-indigo-800 text-xs">
                                                                <span class="show-text">Show</span>
                                                                <span class="hide-text hidden">Hide</span>
                                                            </button>
                                                        </div>
                                                    @else
                                                        <p class="whitespace-pre-wrap">{{ $value }}</p>
                                                    @endif
                                                    @break

                                                @case('number')
                                                @case('currency')
                                                @case('percentage')
                                                    {{ $value }}
                                                    @if($field->type === 'currency') USD @endif
                                                    @if($field->type === 'percentage') % @endif
                                                    @break

                                                @case('date')
                                                    {{ \Carbon\Carbon::parse($value)->format('M d, Y') }}
                                                    @break

                                                @case('time')
                                                    {{ \Carbon\Carbon::parse($value)->format('g:i A') }}
                                                    @break

                                                @case('datetime')
                                                    {{ \Carbon\Carbon::parse($value)->format('M d, Y g:i A') }}
                                                    @break

                                                @case('checkbox')
                                                    @if($value === true || $value === 1 || $value === '1' || $value === 'on')
                                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">✓ Checked</span>
                                                    @else
                                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">☐ Unchecked</span>
                                                    @endif
                                                    @break

                                                @case('radio')
                                                @case('select')
                                                @case('dropdown')
                                                    <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm">{{ $value }}</span>
                                                    @break

                                                @case('checkboxgroup')
                                                @case('multiselect')
                                                    @if(is_array($value))
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach($value as $item)
                                                                <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm">{{ $item }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                    @break

                                                @case('file')
                                                @case('photo')
                                                @case('video')
                                                @case('audio')
                                                    @php
                                                        $files = $record->files->where('form_field_id', $field->id);
                                                    @endphp
                                                    @if($files->count() > 0)
                                                        <div class="space-y-2">
                                                            @foreach($files as $file)
                                                                <div class="flex items-center p-3 bg-gray-50 rounded-md">
                                                                    @if(str_starts_with($file->mime_type, 'image/'))
                                                                        <img src="{{ Storage::url($file->file_path) }}" alt="{{ $file->original_filename }}" class="h-16 w-16 object-cover rounded mr-3">
                                                                    @else
                                                                        <svg class="h-8 w-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                        </svg>
                                                                    @endif
                                                                    <div class="flex-1 min-w-0">
                                                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $file->original_filename }}</p>
                                                                        <p class="text-xs text-gray-500">{{ number_format($file->file_size / 1024, 2) }} KB</p>
                                                                    </div>
                                                                    <a href="{{ Storage::url($file->file_path) }}" download class="ml-3 text-indigo-600 hover:text-indigo-800">
                                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                                        </svg>
                                                                    </a>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-gray-400 italic">No file uploaded</span>
                                                    @endif
                                                    @break

                                                @case('signature')
                                                    @if($value)
                                                        <img src="{{ $value }}" alt="Signature" class="border border-gray-300 rounded max-w-md">
                                                    @else
                                                        <span class="text-gray-400 italic">No signature provided</span>
                                                    @endif
                                                    @break

                                                @case('gps')
                                                    @if(is_array($value))
                                                        <div class="space-y-2">
                                                            <p><strong>Latitude:</strong> {{ $value['latitude'] ?? 'N/A' }}</p>
                                                            <p><strong>Longitude:</strong> {{ $value['longitude'] ?? 'N/A' }}</p>
                                                            @if(isset($value['latitude']) && isset($value['longitude']))
                                                                <a href="https://www.google.com/maps?q={{ $value['latitude'] }},{{ $value['longitude'] }}" target="_blank" class="inline-flex items-center text-indigo-600 hover:text-indigo-800">
                                                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                    </svg>
                                                                    View on Map
                                                                </a>
                                                            @endif
                                                        </div>
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                    @break

                                                @case('barcode')
                                                @case('qrcode')
                                                    <div class="font-mono bg-gray-50 px-3 py-2 rounded">{{ $value }}</div>
                                                    @break

                                                @case('rating')
                                                    <div class="flex items-center">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            @if($i <= $value)
                                                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                                </svg>
                                                            @else
                                                                <svg class="h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                                </svg>
                                                            @endif
                                                        @endfor
                                                        <span class="ml-2 text-sm text-gray-600">({{ $value }}/5)</span>
                                                    </div>
                                                    @break

                                                @default
                                                    @if(is_array($value))
                                                        <pre class="bg-gray-50 p-3 rounded text-xs overflow-x-auto">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                            @endswitch
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    @else
                        <p class="text-gray-500 text-center py-8">No form fields found.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Submission Info -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Submission Info</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Status</p>
                        <span class="mt-1 inline-block px-3 py-1 text-sm font-semibold rounded-full
                            @if($record->status === 'submitted') bg-blue-100 text-blue-800
                            @elseif($record->status === 'reviewed') bg-yellow-100 text-yellow-800
                            @elseif($record->status === 'approved') bg-green-100 text-green-800
                            @elseif($record->status === 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($record->status ?? 'draft') }}
                        </span>
                    </div>

                    @if($record->submittedBy)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Submitted By</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $record->submittedBy->name }}</p>
                            <p class="text-xs text-gray-500">{{ $record->submittedBy->email }}</p>
                        </div>
                    @endif

                    @if($record->submitted_at)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Submitted At</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $record->submitted_at->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $record->submitted_at->format('g:i A') }}</p>
                        </div>
                    @endif

                    @if($record->project)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Project</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $record->project->name }}</p>
                        </div>
                    @endif

                    @if($record->form)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Form</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $record->form->name }}</p>
                            @if($record->form_version)
                                <p class="text-xs text-gray-500">Version {{ $record->form_version }}</p>
                            @endif
                        </div>
                    @endif

                    @if($record->location)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Location</p>
                            <div class="mt-1 text-sm text-gray-900">
                                <p>Lat: {{ number_format($record->location['latitude'] ?? 0, 6) }}</p>
                                <p>Lng: {{ number_format($record->location['longitude'] ?? 0, 6) }}</p>
                                <a href="https://www.google.com/maps?q={{ $record->location['latitude'] ?? 0 }},{{ $record->location['longitude'] ?? 0 }}" target="_blank" class="mt-1 inline-flex items-center text-indigo-600 hover:text-indigo-800 text-xs">
                                    <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    Open in Maps
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($record->ip_address)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">IP Address</p>
                            <p class="mt-1 text-sm text-gray-900 font-mono">{{ $record->ip_address }}</p>
                        </div>
                    @endif

                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Record ID</p>
                        <p class="mt-1 text-xs text-gray-900 font-mono">{{ $record->id }}</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                </div>
                <div class="px-6 py-4 space-y-2">
                    <!-- Edit Record -->
                    @can('update', $record)
                    <a href="{{ route('tenant.records.edit', $record->id) }}" class="flex items-center justify-center w-full px-4 py-2 bg-indigo-600 text-white text-center rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Record
                    </a>
                    @endcan

                    <!-- View Form Template -->
                    @if($record->form)
                    <a href="{{ route('tenant.forms.show', $record->form->id) }}" class="flex items-center justify-center w-full px-4 py-2 bg-gray-600 text-white text-center rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        View Form Template
                    </a>
                    @endif
                </div>
            </div>

            <!-- Workflow Actions -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Workflow</h3>
                </div>
                <div class="px-6 py-4 space-y-2">
                    <!-- Assign Record -->
                    <button onclick="toggleAssignModal()" class="flex items-center justify-center w-full px-4 py-2 bg-purple-600 text-white text-center rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-colors">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Assign to User
                    </button>

                    <!-- Request Approval -->
                    <button onclick="toggleApprovalModal()" class="flex items-center justify-center w-full px-4 py-2 bg-green-600 text-white text-center rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Request Approval
                    </button>

                    <!-- Change Status -->
                    <button onclick="toggleStatusModal()" class="flex items-center justify-center w-full px-4 py-2 bg-blue-600 text-white text-center rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                        </svg>
                        Change Status
                    </button>
                </div>
            </div>

            <!-- Danger Zone -->
            @can('delete', $record)
            <div class="bg-white rounded-lg shadow-md overflow-hidden border-2 border-red-200">
                <div class="px-6 py-4 bg-red-50 border-b border-red-200">
                    <h3 class="text-lg font-semibold text-red-900">Danger Zone</h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-gray-600 mb-3">Deleting this record is permanent and cannot be undone.</p>
                    <form action="{{ route('tenant.records.destroy', $record->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this submission? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="flex items-center justify-center w-full px-4 py-2 bg-red-600 text-white text-center rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Record
                        </button>
                    </form>
                </div>
            </div>
            @endcan            <!-- Approval Status -->
            @if($record->approvals && $record->approvals->count() > 0)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Approval Status</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="space-y-3">
                        @foreach($record->approvals->sortBy('sequence') as $approval)
                        <div class="border rounded-lg p-4 {{ $approval->status === 'approved' ? 'border-green-300 bg-green-50' : ($approval->status === 'rejected' ? 'border-red-300 bg-red-50' : ($approval->status === 'delegated' ? 'border-blue-300 bg-blue-50' : 'border-yellow-300 bg-yellow-50')) }}">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <span class="font-medium text-sm text-gray-900">{{ $approval->approver->name }}</span>
                                        @if($approval->sequence > 1)
                                        <span class="ml-2 px-2 py-0.5 text-xs bg-gray-200 text-gray-700 rounded-full">Step {{ $approval->sequence }}</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1">{{ $approval->approver->email }}</p>

                                    @if($approval->comments)
                                    <p class="mt-2 text-sm text-gray-700 italic">"{{ $approval->comments }}"</p>
                                    @endif

                                    @if($approval->delegated_to)
                                    <div class="mt-2 flex items-center text-xs text-blue-700">
                                        <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                        Delegated to {{ $approval->delegatedUser->name }}
                                    </div>
                                    @endif

                                    @if($approval->approved_at)
                                    <p class="mt-1 text-xs text-green-700">✓ Approved {{ $approval->approved_at->diffForHumans() }}</p>
                                    @elseif($approval->rejected_at)
                                    <p class="mt-1 text-xs text-red-700">✗ Rejected {{ $approval->rejected_at->diffForHumans() }}</p>
                                    @endif
                                </div>

                                <div class="ml-3">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $approval->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $approval->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $approval->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $approval->status === 'delegated' ? 'bg-blue-100 text-blue-800' : '' }}">
                                        {{ ucfirst($approval->status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Approve/Reject Actions -->
                            @if($approval->status === 'pending' && ($approval->approver_id === Auth::id() || $approval->delegated_to === Auth::id()))
                            <div class="mt-3 pt-3 border-t border-gray-300 flex gap-2">
                                <button onclick="showApproveForm('{{ $approval->id }}')" class="flex-1 px-3 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                    Approve
                                </button>
                                <button onclick="showRejectForm('{{ $approval->id }}')" class="flex-1 px-3 py-1.5 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                                    Reject
                                </button>
                            </div>

                            <!-- Approve Form (Hidden) -->
                            <form id="approve-form-{{ $approval->id }}" action="{{ route('tenant.records.approve', [$record->id, $approval->id]) }}" method="POST" class="hidden mt-3 pt-3 border-t border-gray-300">
                                @csrf
                                <textarea name="comments" rows="2" placeholder="Optional comments..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                                <div class="mt-2 flex gap-2">
                                    <button type="submit" class="flex-1 px-3 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                        Confirm Approval
                                    </button>
                                    <button type="button" onclick="hideApproveForm('{{ $approval->id }}')" class="flex-1 px-3 py-1.5 bg-gray-300 text-gray-700 text-sm rounded hover:bg-gray-400">
                                        Cancel
                                    </button>
                                </div>
                            </form>

                            <!-- Reject Form (Hidden) -->
                            <form id="reject-form-{{ $approval->id }}" action="{{ route('tenant.records.reject', [$record->id, $approval->id]) }}" method="POST" class="hidden mt-3 pt-3 border-t border-gray-300">
                                @csrf
                                <textarea name="comments" rows="2" placeholder="Required: Reason for rejection..." required class="w-full px-3 py-2 border border-red-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                                <div class="mt-2 flex gap-2">
                                    <button type="submit" class="flex-1 px-3 py-1.5 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                                        Confirm Rejection
                                    </button>
                                    <button type="button" onclick="hideRejectForm('{{ $approval->id }}')" class="flex-1 px-3 py-1.5 bg-gray-300 text-gray-700 text-sm rounded hover:bg-gray-400">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Comments & Activity Section -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Comments Section -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Comments & Discussion</h3>
            </div>
            <div class="px-6 py-4">
                <!-- Add Comment Form -->
                <form action="{{ route('tenant.records.add-comment', $record->id) }}" method="POST" class="mb-6">
                    @csrf
                    <div class="relative">
                        <textarea name="comment" id="comment-textarea" rows="3" required placeholder="Add a comment... Use @username to mention someone" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>

                        <!-- @Mention Dropdown (Hidden by default) -->
                        <div id="mention-dropdown" class="hidden absolute z-10 w-64 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            @if(isset($users) && $users->count() > 0)
                                @foreach($users as $user)
                                <div class="mention-option px-4 py-2 hover:bg-indigo-50 cursor-pointer" data-username="{{ $user->name }}" data-userid="{{ $user->id }}">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-semibold mr-2">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="mt-2 flex items-center justify-between">
                        <label class="flex items-center text-sm text-gray-600">
                            <input type="checkbox" name="is_internal" value="1" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            Internal note (staff only)
                        </label>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Add Comment
                        </button>
                    </div>
                </form>

                <!-- Comments List -->
                <div class="space-y-4">
                    @forelse($record->comments->where('parent_id', null) as $comment)
                    <div class="border-l-4 {{ $comment->is_internal ? 'border-yellow-400 bg-yellow-50' : 'border-indigo-400 bg-gray-50' }} rounded-r-lg p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start flex-1">
                                <div class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-semibold mr-3 flex-shrink-0">
                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center flex-wrap gap-2">
                                        <span class="font-semibold text-gray-900">{{ $comment->user->name }}</span>
                                        <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                        @if($comment->is_internal)
                                        <span class="px-2 py-0.5 bg-yellow-200 text-yellow-800 text-xs rounded-full">Internal</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ $comment->comment }}</p>

                                    @if($comment->mentions && count($comment->mentions) > 0)
                                    <div class="mt-2 flex items-center text-xs text-gray-600">
                                        <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Mentioned: {{ $comment->mentionedUsers()->pluck('name')->join(', ') }}
                                    </div>
                                    @endif

                                    <!-- Reply Button -->
                                    <button onclick="toggleReplyForm('{{ $comment->id }}')" class="mt-2 text-xs text-indigo-600 hover:text-indigo-800">
                                        Reply
                                    </button>

                                    <!-- Reply Form (Hidden) -->
                                    <form id="reply-form-{{ $comment->id }}" action="{{ route('tenant.records.add-comment', $record->id) }}" method="POST" class="hidden mt-3">
                                        @csrf
                                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                        <textarea name="comment" rows="2" required placeholder="Write a reply..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                                        <div class="mt-2 flex gap-2">
                                            <button type="submit" class="px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                                                Reply
                                            </button>
                                            <button type="button" onclick="toggleReplyForm('{{ $comment->id }}')" class="px-3 py-1 bg-gray-300 text-gray-700 text-sm rounded hover:bg-gray-400">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>

                                    <!-- Replies -->
                                    @if($comment->replies->count() > 0)
                                    <div class="mt-3 space-y-3 pl-4 border-l-2 border-gray-300">
                                        @foreach($comment->replies as $reply)
                                        <div class="bg-white rounded-lg p-3">
                                            <div class="flex items-start">
                                                <div class="w-8 h-8 rounded-full bg-gray-400 text-white flex items-center justify-center text-xs font-semibold mr-2 flex-shrink-0">
                                                    {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-semibold text-sm text-gray-900">{{ $reply->user->name }}</span>
                                                        <span class="text-xs text-gray-500">{{ $reply->created_at->diffForHumans() }}</span>
                                                    </div>
                                                    <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ $reply->comment }}</p>

                                                    @if($reply->user_id === Auth::id())
                                                    <form action="{{ route('tenant.records.delete-comment', [$record->id, $reply->id]) }}" method="POST" class="inline-block mt-1" onsubmit="return confirm('Delete this reply?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-xs text-red-600 hover:text-red-800">Delete</button>
                                                    </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>

                            @if($comment->user_id === Auth::id())
                            <form action="{{ route('tenant.records.delete-comment', [$record->id, $comment->id]) }}" method="POST" onsubmit="return confirm('Delete this comment and all replies?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-600 ml-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <p class="mt-2">No comments yet. Be the first to comment!</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Activity Timeline</h3>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-4">
                    @forelse($record->activities->sortByDesc('created_at')->take(20) as $activity)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mr-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center
                                {{ $activity->action === 'created' ? 'bg-blue-100 text-blue-600' : '' }}
                                {{ $activity->action === 'updated' ? 'bg-yellow-100 text-yellow-600' : '' }}
                                {{ $activity->action === 'status_changed' ? 'bg-purple-100 text-purple-600' : '' }}
                                {{ $activity->action === 'commented' ? 'bg-green-100 text-green-600' : '' }}
                                {{ $activity->action === 'approved' ? 'bg-green-100 text-green-600' : '' }}
                                {{ $activity->action === 'rejected' ? 'bg-red-100 text-red-600' : '' }}
                                {{ $activity->action === 'assigned' ? 'bg-indigo-100 text-indigo-600' : '' }}
                                {{ $activity->action === 'approval_requested' ? 'bg-orange-100 text-orange-600' : '' }}">

                                @switch($activity->action)
                                    @case('created')
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        @break
                                    @case('updated')
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        @break
                                    @case('commented')
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                        @break
                                    @case('approved')
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        @break
                                    @case('rejected')
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        @break
                                    @case('assigned')
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        @break
                                    @default
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                @endswitch
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm">
                                <span class="font-medium text-gray-900">{{ $activity->user ? $activity->user->name : 'System' }}</span>
                                <span class="text-gray-600">{{ $activity->description }}</span>
                            </div>

                            @if($activity->old_value || $activity->new_value)
                            <div class="mt-1 text-xs text-gray-600">
                                @if($activity->old_value)
                                <span class="line-through text-red-600">{{ Str::limit($activity->old_value, 30) }}</span>
                                →
                                @endif
                                @if($activity->new_value)
                                <span class="text-green-600">{{ Str::limit($activity->new_value, 30) }}</span>
                                @endif
                            </div>
                            @endif

                            <div class="mt-1 text-xs text-gray-500">
                                {{ $activity->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2">No activity yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Modal -->
    <div id="assign-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Assign Record</h3>
                <button onclick="toggleAssignModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="{{ route('tenant.records.assign', $record->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                    <select name="user_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Choose a user...</option>
                        @if(isset($users))
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $record->submitted_by === $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                        Assign
                    </button>
                    <button type="button" onclick="toggleAssignModal()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Approval Request Modal -->
    <div id="approval-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Request Approval</h3>
                <button onclick="toggleApprovalModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="{{ route('tenant.records.request-approval', $record->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Approvers (in order)</label>
                    <div class="space-y-2 max-h-64 overflow-y-auto border border-gray-300 rounded-md p-3">
                        @if(isset($users))
                            @foreach($users as $user)
                            <label class="flex items-center space-x-2 hover:bg-gray-50 p-2 rounded">
                                <input type="checkbox" name="approvers[]" value="{{ $user->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                </div>
                            </label>
                            @endforeach
                        @endif
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Approvals will be requested in the order selected.</p>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Request Approval
                    </button>
                    <button type="button" onclick="toggleApprovalModal()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Status Modal -->
    <div id="status-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Change Status</h3>
                <button onclick="toggleStatusModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="{{ route('tenant.records.update', $record->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Status</label>
                    <p class="text-sm text-gray-900 mb-4 px-3 py-2 bg-gray-50 rounded-md">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            @if($record->status === 'submitted') bg-blue-100 text-blue-800
                            @elseif($record->status === 'reviewed') bg-yellow-100 text-yellow-800
                            @elseif($record->status === 'approved') bg-green-100 text-green-800
                            @elseif($record->status === 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($record->status ?? 'draft') }}
                        </span>
                    </p>

                    <label class="block text-sm font-medium text-gray-700 mb-2">New Status</label>
                    <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select status...</option>
                        <option value="draft" {{ $record->status === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ $record->status === 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="reviewed" {{ $record->status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                        <option value="approved" {{ $record->status === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $record->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Update Status
                    </button>
                    <button type="button" onclick="toggleStatusModal()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleSensitive(button) {
    const container = button.closest('div');
    const maskedValue = container.querySelector('.masked-value');
    const realValue = container.querySelector('.real-value');
    const showText = button.querySelector('.show-text');
    const hideText = button.querySelector('.hide-text');

    maskedValue.classList.toggle('hidden');
    realValue.classList.toggle('hidden');
    showText.classList.toggle('hidden');
    hideText.classList.toggle('hidden');
}

// Toggle reply form
function toggleReplyForm(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    form.classList.toggle('hidden');
}

// Show approve form
function showApproveForm(approvalId) {
    const form = document.getElementById('approve-form-' + approvalId);
    const rejectForm = document.getElementById('reject-form-' + approvalId);
    form.classList.remove('hidden');
    rejectForm.classList.add('hidden');
}

// Hide approve form
function hideApproveForm(approvalId) {
    const form = document.getElementById('approve-form-' + approvalId);
    form.classList.add('hidden');
}

// Show reject form
function showRejectForm(approvalId) {
    const form = document.getElementById('reject-form-' + approvalId);
    const approveForm = document.getElementById('approve-form-' + approvalId);
    form.classList.remove('hidden');
    approveForm.classList.add('hidden');
}

// Hide reject form
function hideRejectForm(approvalId) {
    const form = document.getElementById('reject-form-' + approvalId);
    form.classList.add('hidden');
}

// Toggle assign modal
function toggleAssignModal() {
    const modal = document.getElementById('assign-modal');
    modal.classList.toggle('hidden');
}

// Toggle approval modal
function toggleApprovalModal() {
    const modal = document.getElementById('approval-modal');
    modal.classList.toggle('hidden');
}

// Toggle status modal
function toggleStatusModal() {
    const modal = document.getElementById('status-modal');
    modal.classList.toggle('hidden');
}

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('assign-modal').classList.add('hidden');
        document.getElementById('approval-modal').classList.add('hidden');
        document.getElementById('status-modal').classList.add('hidden');
    }
});

// @Mention functionality
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('comment-textarea');
    const dropdown = document.getElementById('mention-dropdown');

    if (textarea && dropdown) {
        let mentionStartPos = -1;
        let mentionQuery = '';

        textarea.addEventListener('input', function(e) {
            const cursorPos = this.selectionStart;
            const textBeforeCursor = this.value.substring(0, cursorPos);
            const lastAtSymbol = textBeforeCursor.lastIndexOf('@');

            // Check if we're typing after an @ symbol
            if (lastAtSymbol !== -1) {
                const textAfterAt = textBeforeCursor.substring(lastAtSymbol + 1);

                // Check if there's no space after @
                if (!textAfterAt.includes(' ') && !textAfterAt.includes('\n')) {
                    mentionStartPos = lastAtSymbol;
                    mentionQuery = textAfterAt.toLowerCase();

                    // Filter mention options
                    const options = dropdown.querySelectorAll('.mention-option');
                    let hasVisibleOptions = false;

                    options.forEach(option => {
                        const username = option.dataset.username.toLowerCase();
                        if (username.includes(mentionQuery)) {
                            option.style.display = 'block';
                            hasVisibleOptions = true;
                        } else {
                            option.style.display = 'none';
                        }
                    });

                    // Show dropdown if we have matches
                    if (hasVisibleOptions && mentionQuery.length >= 0) {
                        dropdown.classList.remove('hidden');

                        // Position dropdown
                        const rect = textarea.getBoundingClientRect();
                        dropdown.style.top = (rect.bottom + window.scrollY + 5) + 'px';
                        dropdown.style.left = rect.left + 'px';
                    } else {
                        dropdown.classList.add('hidden');
                    }
                } else {
                    dropdown.classList.add('hidden');
                    mentionStartPos = -1;
                }
            } else {
                dropdown.classList.add('hidden');
                mentionStartPos = -1;
            }
        });

        // Handle mention selection
        const mentionOptions = dropdown.querySelectorAll('.mention-option');
        mentionOptions.forEach(option => {
            option.addEventListener('click', function() {
                if (mentionStartPos !== -1) {
                    const username = this.dataset.username;
                    const beforeMention = textarea.value.substring(0, mentionStartPos);
                    const afterMention = textarea.value.substring(textarea.selectionStart);

                    textarea.value = beforeMention + '@' + username + ' ' + afterMention;

                    // Set cursor position after the mention
                    const newCursorPos = mentionStartPos + username.length + 2;
                    textarea.setSelectionRange(newCursorPos, newCursorPos);
                    textarea.focus();

                    dropdown.classList.add('hidden');
                    mentionStartPos = -1;
                }
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!textarea.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Close dropdown on escape
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
                mentionStartPos = -1;
                e.preventDefault();
            }
        });
    }
});
</script>
@endsection
