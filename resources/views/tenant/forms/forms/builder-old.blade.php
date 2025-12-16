@extends('tenant.layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-gray-900">Form Builder</h1>
                    <span class="text-sm text-gray-500">•</span>
                    <span class="text-lg font-medium text-gray-700">{{ $form->name }}</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($form->status == 0) bg-yellow-100 text-yellow-800
                        @elseif($form->status == 1) bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800 @endif">
                        @if($form->status == 0) Draft
                        @elseif($form->status == 1) Live
                        @else Archived @endif
                    </span>
                </div>
                <div class="flex items-center space-x-3">
                    <button id="preview-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Preview
                    </button>
                    <button id="save-btn" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Save
                    </button>
                    @if($form->status == 0)
                    <button id="publish-btn" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                        Publish
                    </button>
                    @endif
                    <a href="{{ route('tenant.forms.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        ← Back to Forms
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-12 gap-6">
            <!-- Field Palette -->
            <div class="col-span-3">
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Field Types</h3>
                        <p class="text-sm text-gray-500">Drag fields to add them to your form</p>
                    </div>
                    <div class="p-4 space-y-2" id="field-palette">
                        <!-- Basic Fields -->
                        <div class="field-item" data-type="text" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m0 0V1a1 1 0 011-1h2a1 1 0 011 1v3m-12 0h12m0 0v12a2 2 0 01-2 2H7a2 2 0 01-2-2V4z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Text Input</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="textarea" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Textarea</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="email" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Email</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="phone" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Phone</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="number" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Number</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="url" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">URL</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="select" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Select</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="multiselect" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Multi Select</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="radio" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Radio Buttons</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="checkbox" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Checkbox</span>
                            </div>
                        </div>
                        <!-- Date/Time Fields -->
                        <div class="field-item" data-type="date" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Date</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="time" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Time</span>
                            </div>
                        </div>
                        <!-- Advanced Fields -->
                        <div class="field-item" data-type="signature" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Signature</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="barcode" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 15h4.01M12 21h4.01M12 3h4.01M6 3h4.01M6 6h4.01M6 9h4.01M6 12h4.01M6 15h4.01M6 18h4.01M6 21h4.01"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Barcode</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="qr" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 15h4.01M12 21h4.01M12 3h4.01M6 3h4.01M6 6h4.01M6 9h4.01M6 12h4.01M6 15h4.01M6 18h4.01M6 21h4.01"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">QR Code</span>
                            </div>
                        </div>
                        <!-- Media Fields -->
                        <div class="field-item" data-type="photo" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Photo</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="video" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Video</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="file" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">File Upload</span>
                            </div>
                        </div>
                        <!-- Special Fields -->
                        <div class="field-item" data-type="gps" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">GPS Location</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="currency" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Currency</span>
                            </div>
                        </div>
                        <!-- Layout Fields -->
                        <div class="field-item" data-type="section" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Section</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="pagebreak" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Page Break</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Canvas -->
            <div class="col-span-6">
                <div class="bg-white rounded-lg shadow-sm border min-h-screen">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Form Canvas</h3>
                        <p class="text-sm text-gray-500">Drop fields here to build your form</p>
                    </div>
                    <div class="p-4" id="form-canvas" style="min-height: 600px;">
                        <div class="text-center text-gray-400 py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="mt-2 text-sm">Drag fields from the palette to start building</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Field Properties -->
            <div class="col-span-3">
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Field Properties</h3>
                        <p class="text-sm text-gray-500">Configure the selected field</p>
                    </div>
                    <div class="p-4" id="field-properties">
                        <div class="text-center text-gray-400 py-8">
                            <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p class="mt-2 text-sm">Select a field to configure its properties</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for saving -->
<form id="save-form" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="schema" id="schema-input">
    <input type="hidden" name="fields" id="fields-input">
</form>

<!-- Publish Form -->
<form id="publish-form" method="POST" action="{{ route('tenant.forms.publish', $form) }}" style="display: none;">
    @csrf
    @method('POST')
</form>

<script>
(function() {
    'use strict';
    
    let draggedElement = null;
    let selectedField = null;
    let formFields = @json($form->formFields ?? []);
    let formSchema = @json($form->schema_json ?? ['fields' => []]);

    // Initialize form if it has existing fields
    function loadExistingForm() {
        if (formFields && formFields.length > 0) {
            formFields.forEach(function(field) {
                const fieldElement = createFieldElement(field.type, field.name);
                document.getElementById('form-canvas').appendChild(fieldElement);
            });

            // Remove empty state
            const emptyState = document.getElementById('form-canvas').querySelector('.text-center');
            if (emptyState) {
                emptyState.remove();
            }
        }
    }

    // Drag and Drop functionality
    document.querySelectorAll('.field-item').forEach(item => {
        item.addEventListener('dragstart', function(e) {
            draggedElement = this;
            e.dataTransfer.effectAllowed = 'copy';
        });
    });

    const formCanvas = document.getElementById('form-canvas');
    formCanvas.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
        this.classList.add('bg-blue-50');
    });

    formCanvas.addEventListener('dragleave', function(e) {
        this.classList.remove('bg-blue-50');
    });

    formCanvas.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('bg-blue-50');

        if (draggedElement) {
            const fieldType = draggedElement.dataset.type;
            addFieldToCanvas(fieldType);
        }
    });

    function addFieldToCanvas(fieldType) {
        const fieldId = 'field_' + Date.now();
        const fieldElement = createFieldElement(fieldType, fieldId);
        formCanvas.appendChild(fieldElement);

        // Remove empty state if it exists
        const emptyState = formCanvas.querySelector('.text-center');
        if (emptyState) {
            emptyState.remove();
        }

        // Select the new field
        selectField(fieldElement);
    }

    function createFieldElement(fieldType, fieldId) {
        const fieldDiv = document.createElement('div');
        fieldDiv.className = 'field-element mb-4 p-4 border border-gray-200 rounded-lg bg-white cursor-pointer hover:border-blue-300 transition-colors';
        fieldDiv.dataset.fieldId = fieldId;
        fieldDiv.dataset.fieldType = fieldType;

        const fieldConfig = getDefaultFieldConfig(fieldType);

        fieldDiv.innerHTML = `
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center">
                    <span class="text-sm font-medium text-gray-700">${fieldConfig.icon} ${fieldConfig.label}</span>
                    <span class="ml-2 text-xs text-gray-500">(${fieldType})</span>
                </div>
                <div class="flex items-center space-x-2">
                    <button class="text-gray-400 hover:text-gray-600" onclick="duplicateField('${fieldId}')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </button>
                    <button class="text-red-400 hover:text-red-600" onclick="deleteField('${fieldId}')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="field-preview">
                ${generateFieldPreview(fieldType, fieldConfig)}
            </div>
        `;

        fieldDiv.addEventListener('click', function() {
            selectField(this);
        });

        return fieldDiv;
    }

    function getDefaultFieldConfig(fieldType) {
        const configs = {
            text: { label: 'Text Input', icon: '📝', placeholder: 'Enter text...', required: false, sensitive: false },
            textarea: { label: 'Textarea', icon: '📄', placeholder: 'Enter text...', required: false, sensitive: false },
            email: { label: 'Email', icon: '📧', placeholder: 'email@example.com', required: false, sensitive: false },
            phone: { label: 'Phone', icon: '📞', placeholder: '(555) 123-4567', required: false, sensitive: false },
            number: { label: 'Number', icon: '🔢', placeholder: '0', required: false, sensitive: false, min: '', max: '' },
            url: { label: 'URL', icon: '🔗', placeholder: 'https://example.com', required: false, sensitive: false },
            select: { label: 'Select', icon: '📋', options: ['Option 1', 'Option 2'], required: false, sensitive: false },
            multiselect: { label: 'Multi Select', icon: '☑️', options: ['Option 1', 'Option 2'], required: false, sensitive: false },
            radio: { label: 'Radio Buttons', icon: '🔘', options: ['Option 1', 'Option 2'], required: false, sensitive: false },
            checkbox: { label: 'Checkbox', icon: '☑️', label: 'Check this box', required: false, sensitive: false },
            date: { label: 'Date', icon: '📅', required: false, sensitive: false },
            time: { label: 'Time', icon: '⏰', required: false, sensitive: false },
            datetime: { label: 'Date & Time', icon: '📅⏰', required: false, sensitive: false },
            signature: { label: 'Signature', icon: '✍️', required: false, sensitive: false },
            barcode: { label: 'Barcode', icon: '📊', required: false, sensitive: false },
            qr: { label: 'QR Code', icon: '📱', required: false, sensitive: false },
            photo: { label: 'Photo', icon: '📷', required: false, sensitive: false },
            video: { label: 'Video', icon: '🎥', required: false, sensitive: false },
            audio: { label: 'Audio', icon: '🎵', required: false, sensitive: false },
            file: { label: 'File Upload', icon: '📎', required: false, sensitive: false },
            gps: { label: 'GPS Location', icon: '📍', required: false, sensitive: false },
            currency: { label: 'Currency', icon: '💰', placeholder: '0.00', required: false, sensitive: false, currency_symbol: '$' },
            calculated: { label: 'Calculated Field', icon: '🧮', required: false, sensitive: false, calculation_formula: '' },
            section: { label: 'Section', icon: '📑', title: 'Section Title' },
            pagebreak: { label: 'Page Break', icon: '📄' }
        };
        return configs[fieldType] || { label: fieldType, icon: '❓', required: false, sensitive: false };
    }

    function generateFieldPreview(fieldType, config) {
        switch (fieldType) {
            case 'text':
            case 'email':
            case 'phone':
            case 'url':
            case 'number':
            case 'currency':
                return `<input type="${fieldType === 'currency' ? 'number' : fieldType}" placeholder="${config.placeholder || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" disabled>`;
            case 'textarea':
                return `<textarea placeholder="${config.placeholder || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" rows="3" disabled></textarea>`;
            case 'select':
                return `<select class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" disabled><option>${config.options ? config.options[0] : 'Select option'}</option></select>`;
            case 'multiselect':
                return `<select multiple class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" disabled><option>${config.options ? config.options[0] : 'Select options'}</option></select>`;
            case 'radio':
                return config.options ? config.options.map(option => `<label class="inline-flex items-center mr-4"><input type="radio" disabled class="mr-2"><span class="text-sm">${option}</span></label>`).join('') : 'Radio options';
            case 'checkbox':
                return `<label class="inline-flex items-center"><input type="checkbox" disabled class="mr-2"><span class="text-sm">${config.label || 'Checkbox'}</span></label>`;
            case 'date':
                return `<input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" disabled>`;
            case 'time':
                return `<input type="time" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" disabled>`;
            case 'datetime':
                return `<input type="datetime-local" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" disabled>`;
            case 'signature':
                return `<div class="border border-gray-300 rounded-md h-20 flex items-center justify-center text-gray-400 text-sm">Signature Pad</div>`;
            case 'barcode':
            case 'qr':
                return `<div class="border border-gray-300 rounded-md h-20 flex items-center justify-center text-gray-400 text-sm">${fieldType.toUpperCase()} Scanner</div>`;
            case 'photo':
            case 'video':
            case 'audio':
            case 'file':
                return `<div class="border border-dashed border-gray-300 rounded-md h-20 flex items-center justify-center text-gray-400 text-sm">📎 Drop ${fieldType} here</div>`;
            case 'gps':
                return `<div class="border border-gray-300 rounded-md h-20 flex items-center justify-center text-gray-400 text-sm">📍 GPS Location</div>`;
            case 'calculated':
                const calcResult = config.calculation_formula ? evaluateCalculatedField(config.calculation_formula, {}) : 'Calculated value';
                return `<input type="text" value="${calcResult}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-gray-50" disabled>`;
            case 'section':
                return `<div class="border-t-2 border-gray-300 pt-4"><h3 class="text-lg font-medium text-gray-900">${config.title || 'Section Title'}</h3></div>`;
            case 'pagebreak':
                return `<div class="border-t-2 border-dashed border-gray-300 my-4 flex items-center justify-center"><span class="bg-white px-4 text-sm text-gray-500">Page Break</span></div>`;
            default:
                return `<div class="text-sm text-gray-500">${fieldType} field</div>`;
        }
    }

    function selectField(fieldElement) {
        // Remove selection from other fields
        document.querySelectorAll('.field-element').forEach(el => {
            el.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50');
        });

        // Select this field
        fieldElement.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50');
        selectedField = fieldElement;

        // Show field properties
        showFieldProperties(fieldElement.dataset.fieldType, fieldElement.dataset.fieldId);
    }

    function showFieldProperties(fieldType, fieldId) {
        const propertiesPanel = document.getElementById('field-properties');
        const config = getDefaultFieldConfig(fieldType);

        propertiesPanel.innerHTML = `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Field Key</label>
                    <input type="text" id="field-key" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="field_key" value="${fieldId}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Label</label>
                    <input type="text" id="field-label" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" value="${config.label}">
                </div>
                ${fieldType === 'text' || fieldType === 'textarea' || fieldType === 'email' || fieldType === 'phone' || fieldType === 'url' || fieldType === 'number' || fieldType === 'currency' ? `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Placeholder</label>
                    <input type="text" id="field-placeholder" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" value="${config.placeholder || ''}">
                </div>
                ` : ''}
                ${fieldType === 'number' || fieldType === 'currency' ? `
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Min Value</label>
                        <input type="number" id="field-min" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" value="${config.min || ''}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Value</label>
                        <input type="number" id="field-max" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" value="${config.max || ''}">
                    </div>
                </div>
                ` : ''}
                ${fieldType === 'select' || fieldType === 'multiselect' || fieldType === 'radio' ? `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Options (one per line)</label>
                    <textarea id="field-options" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" rows="4">${config.options ? config.options.join('\n') : ''}</textarea>
                </div>
                ` : ''}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Value</label>
                    <input type="text" id="field-default" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Default value">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Validation</label>
                    <div class="space-y-2">
                        <input type="text" id="field-regex" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Regex pattern (optional)">
                        <div class="text-xs text-gray-500">Use regex for custom validation patterns</div>
                    </div>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="field-required" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="field-required" class="ml-2 text-sm text-gray-700">Required field</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="field-sensitive" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="field-sensitive" class="ml-2 text-sm text-gray-700">Sensitive data (masked in exports)</label>
                </div>
                ${fieldType === 'calculated' ? `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Calculation Formula</label>
                    <textarea id="field-calculation-formula" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" rows="3" placeholder="e.g., {field1} + {field2} * 0.1">${config.calculation_formula || ''}</textarea>
                    <div class="text-xs text-gray-500 mt-1">Use field keys in curly braces: {field_key}. Supports +, -, *, /, (, )</div>
                </div>
                ` : ''}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Visibility Rules</label>
                    <textarea id="field-visibility" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" rows="3" placeholder='Example: {"show_when": {"field_key": "value"}}'></textarea>
                    <div class="text-xs text-gray-500 mt-1">JSON rules for when to show/hide this field</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Conditional Logic</label>
                    <textarea id="field-conditional" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" rows="3" placeholder='Example: {"if": {"field_key": "value"}, "then": {"action": "set_value", "value": "calculated"}}'></textarea>
                    <div class="text-xs text-gray-500 mt-1">JSON logic for conditional field behavior</div>
                </div>
                ${fieldType === 'currency' ? `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Currency Symbol</label>
                    <select id="field-currency-symbol" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="$">$ USD</option>
                        <option value="€">€ EUR</option>
                        <option value="£">£ GBP</option>
                        <option value="¥">¥ JPY</option>
                        <option value="₹">₹ INR</option>
                        <option value="₽">₽ RUB</option>
                    </select>
                </div>
                ` : ''}
                ${fieldType === 'section' ? `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                    <input type="text" id="field-section-title" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" value="${config.title || 'Section Title'}">
                </div>
                ` : ''}
                <div class="pt-4 border-t">
                    <button type="button" onclick="previewValidation('${fieldId}')" class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 text-sm font-medium">
                        Preview Validation
                    </button>
                </div>
            </div>
        `;
    }

    function evaluateCalculatedField(formula, fieldValues) {
        if (!formula) return '';

        try {
            // Replace field references with values
            let expression = formula.replace(/\{([^}]+)\}/g, (match, fieldKey) => {
                const value = fieldValues[fieldKey];
                return value !== undefined ? value : 0;
            });

            // Simple evaluation (in production, use a proper expression parser)
            return Function('"use strict"; return (' + expression + ')')();
        } catch (e) {
            return 'Error: ' + e.message;
        }
    }

    function previewValidation(fieldId) {
        const validationErrors = [];

        const label = document.getElementById('field-label')?.value;
        const key = document.getElementById('field-key')?.value;
        const required = document.getElementById('field-required')?.checked;
        const regex = document.getElementById('field-regex')?.value;
        const min = document.getElementById('field-min')?.value;
        const max = document.getElementById('field-max')?.value;
        const options = document.getElementById('field-options')?.value;
        const calculationFormula = document.getElementById('field-calculation-formula')?.value;

        // Basic validation rules
        if (!label || label.trim() === '') {
            validationErrors.push('Label is required');
        }

        if (!key || key.trim() === '') {
            validationErrors.push('Field key is required');
        } else if (!/^[a-zA-Z_][a-zA-Z0-9_]*$/.test(key)) {
            validationErrors.push('Field key must start with letter/underscore and contain only alphanumeric characters and underscores');
        }

        if (regex && regex.trim() !== '') {
            try {
                new RegExp(regex);
            } catch (e) {
                validationErrors.push('Invalid regex pattern: ' + e.message);
            }
        }

        if (min !== '' && max !== '' && parseFloat(min) >= parseFloat(max)) {
            validationErrors.push('Min value must be less than max value');
        }

        if (calculationFormula && calculationFormula.trim() !== '') {
            // Check for valid field references
            const fieldRefs = calculationFormula.match(/\{([^}]+)\}/g);
            if (fieldRefs) {
                fieldRefs.forEach(ref => {
                    const fieldKey = ref.slice(1, -1);
                    // In a real implementation, you'd check against existing fields
                    if (!/^[a-zA-Z_][a-zA-Z0-9_]*$/.test(fieldKey)) {
                        validationErrors.push(`Invalid field reference: ${ref}`);
                    }
                });
            }
        }

        // Show validation results
        if (validationErrors.length === 0) {
            alert('✅ All validations passed!');
        } else {
            alert('❌ Validation Errors:\n\n' + validationErrors.join('\n'));
        }
    }

    function loadExistingForm() {
        formFields.forEach(field => {
            const fieldElement = createFieldElement(field.type, field.name);
            formCanvas.appendChild(fieldElement);
        });

        // Remove empty state
        const emptyState = formCanvas.querySelector('.text-center');
        if (emptyState) {
            emptyState.remove();
        }
    }

    // Save functionality
    document.getElementById('save-btn').addEventListener('click', function() {
        saveForm();
    });

    function saveForm() {
        const fields = [];
        const fieldElements = document.querySelectorAll('.field-element');

        fieldElements.forEach((element, index) => {
            const fieldId = element.dataset.fieldId;
            const fieldType = element.dataset.fieldType;

            // Get field configuration from properties panel if this field is selected
            let config = getDefaultFieldConfig(fieldType);

            if (selectedField && selectedField.dataset.fieldId === fieldId) {
                config = {
                    key: document.getElementById('field-key')?.value || fieldId,
                    label: document.getElementById('field-label')?.value || config.label,
                    placeholder: document.getElementById('field-placeholder')?.value || config.placeholder,
                    required: document.getElementById('field-required')?.checked || false,
                    sensitive: document.getElementById('field-sensitive')?.checked || false,
                    default: document.getElementById('field-default')?.value || '',
                    regex: document.getElementById('field-regex')?.value || '',
                    visibility: document.getElementById('field-visibility')?.value || '',
                    conditional: document.getElementById('field-conditional')?.value || '',
                    min: document.getElementById('field-min')?.value || '',
                    max: document.getElementById('field-max')?.value || '',
                    options: document.getElementById('field-options')?.value.split('\n').filter(opt => opt.trim()) || config.options,
                    currency_symbol: document.getElementById('field-currency-symbol')?.value || '$',
                    calculation_formula: document.getElementById('field-calculation-formula')?.value || '',
                    section_title: document.getElementById('field-section-title')?.value || '',
                    type: fieldType
                };
            }

            fields.push({
                key: config.key || fieldId,
                type: fieldType,
                label: config.label,
                placeholder: config.placeholder,
                required: config.required,
                sensitive: config.sensitive,
                default: config.default,
                regex: config.regex,
                visibility: config.visibility,
                conditional: config.conditional,
                min: config.min,
                max: config.max,
                options: config.options,
                currency_symbol: config.currency_symbol,
                calculation_formula: config.calculation_formula,
                section_title: config.section_title,
                order: index
            });
        });

        const schema = {
            fields: fields
        };

        // Update hidden form inputs
        document.getElementById('schema-input').value = JSON.stringify(schema);
        document.getElementById('fields-input').value = JSON.stringify(fields);

        // Submit the form
        document.getElementById('save-form').action = "{{ route('tenant.forms.save-builder', $form) }}";
        document.getElementById('save-form').submit();
    }

    // Publish functionality (only if publish button exists)
    const publishBtn = document.getElementById('publish-btn');
    if (publishBtn) {
        publishBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to publish this form? This will create a new version and make it live.')) {
                document.getElementById('publish-form').submit();
            }
        });
    }

    // Preview functionality
    document.getElementById('preview-btn').addEventListener('click', function() {
        // Open preview modal or new window
        const previewWindow = window.open('', '_blank');
        previewWindow.document.write(generateFormPreview());
    });

    function generateFormPreview() {
        const fields = [];
        const fieldElements = document.querySelectorAll('.field-element');

        fieldElements.forEach(element => {
            const fieldId = element.dataset.fieldId;
            const fieldType = element.dataset.fieldType;
            let config = getDefaultFieldConfig(fieldType);

            // Use current config if field is selected
            if (selectedField && selectedField.dataset.fieldId === fieldId) {
                config = {
                    key: document.getElementById('field-key')?.value || fieldId,
                    label: document.getElementById('field-label')?.value || config.label,
                    placeholder: document.getElementById('field-placeholder')?.value || config.placeholder,
                    required: document.getElementById('field-required')?.checked || false,
                    options: document.getElementById('field-options')?.value.split('\n').filter(opt => opt.trim()) || config.options,
                    type: fieldType
                };
            }

            fields.push(config);
        });

        let formHtml = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Form Preview - {{ $form->name }}</title>
                <script src="https://cdn.tailwindcss.com"></script>
            </head>
            <body class="bg-gray-50 p-8">
                <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg">
                    <h1 class="text-2xl font-bold mb-6">{{ $form->name }}</h1>
                    <form class="space-y-6">
        `;

        fields.forEach(field => {
            formHtml += `<div class="field-${field.type}">`;
            formHtml += `<label class="block text-sm font-medium text-gray-700 mb-2">${field.label}${field.required ? ' *' : ''}</label>`;

            switch (field.type) {
                case 'text':
                    formHtml += `<input type="text" placeholder="${field.placeholder || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"${field.required ? ' required' : ''}>`;
                    break;
                case 'textarea':
                    formHtml += `<textarea placeholder="${field.placeholder || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" rows="4"${field.required ? ' required' : ''}></textarea>`;
                    break;
                case 'number':
                    formHtml += `<input type="number" placeholder="${field.placeholder || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"${field.required ? ' required' : ''}>`;
                    break;
                case 'select':
                    formHtml += `<select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"${field.required ? ' required' : ''}>`;
                    formHtml += `<option value="">Select an option</option>`;
                    (field.options || []).forEach(option => {
                        formHtml += `<option value="${option}">${option}</option>`;
                    });
                    formHtml += `</select>`;
                    break;
                case 'radio':
                    (field.options || []).forEach(option => {
                        formHtml += `<label class="inline-flex items-center mr-4"><input type="radio" name="${field.key}" value="${option}" class="mr-2"${field.required ? ' required' : ''}><span>${option}</span></label>`;
                    });
                    break;
                case 'checkbox':
                    formHtml += `<label class="inline-flex items-center"><input type="checkbox" class="mr-2"${field.required ? ' required' : ''}><span>${field.label}</span></label>`;
                    break;
                case 'date':
                    formHtml += `<input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"${field.required ? ' required' : ''}>`;
                    break;
                case 'time':
                    formHtml += `<input type="time" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"${field.required ? ' required' : ''}>`;
                    break;
                default:
                    formHtml += `<div class="text-sm text-gray-500">${field.type} field preview</div>`;
            }

            formHtml += `</div>`;
        });

        formHtml += `
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">Submit</button>
                        </div>
                    </form>
                </div>
            </body>
            </html>
        `;

        return formHtml;
    }

    // Global functions for field actions
    window.duplicateField = function(fieldId) {
        const originalField = document.querySelector(`[data-field-id="${fieldId}"]`);
        if (originalField) {
            const clonedField = originalField.cloneNode(true);
            const newFieldId = 'field_' + Date.now();
            clonedField.dataset.fieldId = newFieldId;
            originalField.parentNode.insertBefore(clonedField, originalField.nextSibling);

            // Update buttons
            clonedField.querySelectorAll('button').forEach(btn => {
                if (btn.onclick) {
                    const onclickStr = btn.onclick.toString();
                    if (onclickStr.includes('duplicateField')) {
                        btn.onclick = () => duplicateField(newFieldId);
                    } else if (onclickStr.includes('deleteField')) {
                        btn.onclick = () => deleteField(newFieldId);
                    }
                }
            });

            selectField(clonedField);
        }
    };

    window.deleteField = function(fieldId) {
        const fieldElement = document.querySelector(`[data-field-id="${fieldId}"]`);
        if (fieldElement) {
            fieldElement.remove();
            if (selectedField && selectedField.dataset.fieldId === fieldId) {
                selectedField = null;
                document.getElementById('field-properties').innerHTML = `
                    <div class="text-center text-gray-400 py-8">
                        <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="mt-2 text-sm">Select a field to configure its properties</p>
                    </div>
                `;
            }
        }
    };
});
</script>
@endsection
