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
        @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
        @endif

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
                        <div class="field-item" data-type="select" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Select</span>
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
                        <div class="field-item" data-type="signature" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Signature</span>
                            </div>
                        </div>
                        <div class="field-item" data-type="photo" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Photo</span>
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
                        <div class="field-item" data-type="gps" draggable="true">
                            <div class="flex items-center p-3 border border-gray-200 rounded-md hover:border-blue-300 hover:bg-blue-50 cursor-move transition-colors">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">GPS Location</span>
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
                        <div class="text-center text-gray-400 py-12" id="empty-state">
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

    var draggedElement = null;
    var selectedField = null;
    var formFields = @json($form->formFields ?? []);

    // Field configurations
    function getDefaultFieldConfig(fieldType) {
        var configs = {
            text: { label: 'Text Input', placeholder: 'Enter text...', required: false },
            textarea: { label: 'Textarea', placeholder: 'Enter text...', required: false },
            email: { label: 'Email', placeholder: 'email@example.com', required: false },
            phone: { label: 'Phone', placeholder: '(555) 123-4567', required: false },
            number: { label: 'Number', placeholder: '0', required: false },
            url: { label: 'URL', placeholder: 'https://example.com', required: false },
            select: { label: 'Select', options: ['Option 1', 'Option 2'], required: false },
            multiselect: { label: 'Multi Select', options: ['Option 1', 'Option 2'], required: false },
            radio: { label: 'Radio Buttons', options: ['Option 1', 'Option 2'], required: false },
            checkbox: { label: 'Checkbox', required: false },
            date: { label: 'Date', required: false },
            time: { label: 'Time', required: false },
            signature: { label: 'Signature', required: false },
            photo: { label: 'Photo', required: false },
            file: { label: 'File Upload', required: false },
            gps: { label: 'GPS Location', required: false }
        };
        return configs[fieldType] || { label: fieldType, required: false };
    }

    // Create field element
    function createFieldElement(fieldType, fieldId) {
        var fieldDiv = document.createElement('div');
        fieldDiv.className = 'field-element mb-4 p-4 border border-gray-200 rounded-lg bg-white cursor-pointer hover:border-blue-300 transition-colors';
        fieldDiv.setAttribute('data-field-id', fieldId);
        fieldDiv.setAttribute('data-field-type', fieldType);

        var fieldConfig = getDefaultFieldConfig(fieldType);

        fieldDiv.innerHTML = '<div class="flex items-center justify-between mb-2">' +
            '<div class="flex items-center">' +
            '<span class="text-sm font-medium text-gray-700">' + fieldConfig.label + '</span>' +
            '<span class="ml-2 text-xs text-gray-500">(' + fieldType + ')</span>' +
            '</div>' +
            '<div class="flex items-center space-x-2">' +
            '<button type="button" class="delete-btn text-red-400 hover:text-red-600">' +
            '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>' +
            '</svg>' +
            '</button>' +
            '</div>' +
            '</div>' +
            '<div class="field-preview">' + generateFieldPreview(fieldType, fieldConfig) + '</div>';

        fieldDiv.addEventListener('click', function() {
            selectField(fieldDiv);
        });

        var deleteBtn = fieldDiv.querySelector('.delete-btn');
        deleteBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            deleteField(fieldId);
        });

        return fieldDiv;
    }

    // Generate field preview
    function generateFieldPreview(fieldType, config) {
        switch (fieldType) {
            case 'text':
            case 'email':
            case 'phone':
            case 'url':
                return '<input type="text" placeholder="' + (config.placeholder || '') + '" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" disabled>';
            case 'number':
                return '<input type="number" placeholder="' + (config.placeholder || '') + '" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" disabled>';
            case 'textarea':
                return '<textarea placeholder="' + (config.placeholder || '') + '" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" rows="3" disabled></textarea>';
            case 'select':
                return '<select class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" disabled><option>' + (config.options ? config.options[0] : 'Select option') + '</option></select>';
            case 'multiselect':
                return '<select multiple class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" disabled><option>' + (config.options ? config.options[0] : 'Select options') + '</option></select>';
            case 'radio':
                var radioHtml = '';
                if (config.options) {
                    for (var i = 0; i < config.options.length; i++) {
                        radioHtml += '<label class="inline-flex items-center mr-4"><input type="radio" disabled class="mr-2"><span class="text-sm">' + config.options[i] + '</span></label>';
                    }
                }
                return radioHtml || '<div class="text-sm text-gray-500">Radio options</div>';
            case 'checkbox':
                return '<label class="inline-flex items-center"><input type="checkbox" disabled class="mr-2"><span class="text-sm">' + (config.label || 'Checkbox') + '</span></label>';
            case 'date':
                return '<input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" disabled>';
            case 'time':
                return '<input type="time" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" disabled>';
            case 'signature':
                return '<div class="border border-gray-300 rounded-md h-20 flex items-center justify-center text-gray-400 text-sm">Signature Pad</div>';
            case 'photo':
                return '<div class="border border-dashed border-gray-300 rounded-md h-20 flex items-center justify-center text-gray-400 text-sm">📷 Photo Upload</div>';
            case 'file':
                return '<div class="border border-dashed border-gray-300 rounded-md h-20 flex items-center justify-center text-gray-400 text-sm">📎 File Upload</div>';
            case 'gps':
                return '<div class="border border-gray-300 rounded-md h-20 flex items-center justify-center text-gray-400 text-sm">📍 GPS Location</div>';
            default:
                return '<div class="text-sm text-gray-500">' + fieldType + ' field</div>';
        }
    }

    // Select field
    function selectField(fieldElement) {
        var allFields = document.querySelectorAll('.field-element');
        for (var i = 0; i < allFields.length; i++) {
            allFields[i].classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50');
        }

        fieldElement.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50');
        selectedField = fieldElement;

        showFieldProperties(fieldElement.getAttribute('data-field-type'), fieldElement.getAttribute('data-field-id'));
    }

    // Show field properties
    function showFieldProperties(fieldType, fieldId) {
        var propertiesPanel = document.getElementById('field-properties');
        var config = getDefaultFieldConfig(fieldType);

        var html = '<div class="space-y-4">' +
            '<div>' +
            '<label class="block text-sm font-medium text-gray-700 mb-1">Label</label>' +
            '<input type="text" id="field-label" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" value="' + config.label + '">' +
            '</div>';

        if (fieldType === 'text' || fieldType === 'textarea' || fieldType === 'email' || fieldType === 'phone' || fieldType === 'url' || fieldType === 'number') {
            html += '<div>' +
                '<label class="block text-sm font-medium text-gray-700 mb-1">Placeholder</label>' +
                '<input type="text" id="field-placeholder" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" value="' + (config.placeholder || '') + '">' +
                '</div>';
        }

        if (fieldType === 'select' || fieldType === 'multiselect' || fieldType === 'radio') {
            html += '<div>' +
                '<label class="block text-sm font-medium text-gray-700 mb-1">Options (one per line)</label>' +
                '<textarea id="field-options" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" rows="4">' + (config.options ? config.options.join('\n') : '') + '</textarea>' +
                '</div>';
        }

        html += '<div class="flex items-center">' +
            '<input type="checkbox" id="field-required" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">' +
            '<label for="field-required" class="ml-2 text-sm text-gray-700">Required field</label>' +
            '</div>' +
            '</div>';

        propertiesPanel.innerHTML = html;
    }

    // Delete field
    function deleteField(fieldId) {
        var fieldElement = document.querySelector('[data-field-id="' + fieldId + '"]');
        if (fieldElement) {
            fieldElement.remove();
            if (selectedField && selectedField.getAttribute('data-field-id') === fieldId) {
                selectedField = null;
                document.getElementById('field-properties').innerHTML = '<div class="text-center text-gray-400 py-8">' +
                    '<svg class="mx-auto h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>' +
                    '</svg>' +
                    '<p class="mt-2 text-sm">Select a field to configure its properties</p>' +
                    '</div>';
            }
        }
    }

    // Add field to canvas
    function addFieldToCanvas(fieldType) {
        var fieldId = 'field_' + Date.now();
        var fieldElement = createFieldElement(fieldType, fieldId);
        document.getElementById('form-canvas').appendChild(fieldElement);

        var emptyState = document.getElementById('empty-state');
        if (emptyState) {
            emptyState.remove();
        }

        selectField(fieldElement);
    }

    // Drag and Drop functionality
    var fieldItems = document.querySelectorAll('.field-item');
    for (var i = 0; i < fieldItems.length; i++) {
        fieldItems[i].addEventListener('dragstart', function(e) {
            draggedElement = this;
            e.dataTransfer.effectAllowed = 'copy';
        });
    }

    var formCanvas = document.getElementById('form-canvas');
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
            var fieldType = draggedElement.getAttribute('data-type');
            addFieldToCanvas(fieldType);
        }
    });

    // Save functionality
    document.getElementById('save-btn').addEventListener('click', function() {
        var fields = [];
        var fieldElements = document.querySelectorAll('.field-element');

        for (var i = 0; i < fieldElements.length; i++) {
            var element = fieldElements[i];
            var fieldId = element.getAttribute('data-field-id');
            var fieldType = element.getAttribute('data-field-type');
            var config = getDefaultFieldConfig(fieldType);

            if (selectedField && selectedField.getAttribute('data-field-id') === fieldId) {
                var labelInput = document.getElementById('field-label');
                var placeholderInput = document.getElementById('field-placeholder');
                var optionsInput = document.getElementById('field-options');
                var requiredInput = document.getElementById('field-required');

                config = {
                    key: fieldId,
                    label: labelInput ? labelInput.value : config.label,
                    placeholder: placeholderInput ? placeholderInput.value : config.placeholder,
                    required: requiredInput ? requiredInput.checked : false,
                    options: optionsInput ? optionsInput.value.split('\n').filter(function(opt) { return opt.trim(); }) : config.options,
                    type: fieldType
                };
            }

            fields.push({
                key: config.key || fieldId,
                type: fieldType,
                label: config.label,
                placeholder: config.placeholder || '',
                required: config.required || false,
                options: config.options || [],
                order: i
            });
        }

        var schema = { fields: fields };

        // Create dynamic form with proper array structure
        var saveForm = document.getElementById('save-form');
        saveForm.action = "{{ route('tenant.forms.save-builder', $form) }}";

        // Clear existing hidden inputs except CSRF
        var inputs = saveForm.querySelectorAll('input[type="hidden"]');
        for (var j = 0; j < inputs.length; j++) {
            if (inputs[j].name !== '_token') {
                inputs[j].remove();
            }
        }

        // Add schema fields as array
        for (var k = 0; k < fields.length; k++) {
            var field = fields[k];
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'schema[fields][' + k + '][key]';
            input.value = field.key;
            saveForm.appendChild(input);

            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'schema[fields][' + k + '][type]';
            input.value = field.type;
            saveForm.appendChild(input);

            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'schema[fields][' + k + '][label]';
            input.value = field.label;
            saveForm.appendChild(input);
        }

        // Add fields array
        for (var m = 0; m < fields.length; m++) {
            var fld = fields[m];
            for (var prop in fld) {
                if (fld.hasOwnProperty(prop)) {
                    var inp = document.createElement('input');
                    inp.type = 'hidden';
                    if (prop === 'options' && Array.isArray(fld[prop])) {
                        for (var n = 0; n < fld[prop].length; n++) {
                            var optInp = document.createElement('input');
                            optInp.type = 'hidden';
                            optInp.name = 'fields[' + m + '][options][' + n + ']';
                            optInp.value = fld[prop][n];
                            saveForm.appendChild(optInp);
                        }
                    } else {
                        inp.name = 'fields[' + m + '][' + prop + ']';
                        inp.value = fld[prop];
                        saveForm.appendChild(inp);
                    }
                }
            }
        }

        saveForm.submit();
    });

    // Publish functionality
    var publishBtn = document.getElementById('publish-btn');
    if (publishBtn) {
        publishBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to publish this form? This will create a new version and make it live.')) {
                document.getElementById('publish-form').submit();
            }
        });
    }

    // Preview functionality
    document.getElementById('preview-btn').addEventListener('click', function() {
        var fieldElements = document.querySelectorAll('.field-element');
        var formHtml = '<!DOCTYPE html><html><head><title>Form Preview - {{ $form->name }}<\/title>';
        formHtml += '<script src="https:\/\/cdn.tailwindcss.com"><\/script>';
        formHtml += '<\/head><body class="bg-gray-50 p-8">';
        formHtml += '<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg">';
        formHtml += '<h1 class="text-2xl font-bold mb-6">{{ $form->name }}<\/h1>';
        formHtml += '<form class="space-y-6">';

        for (var i = 0; i < fieldElements.length; i++) {
            var element = fieldElements[i];
            var fieldType = element.getAttribute('data-field-type');
            var config = getDefaultFieldConfig(fieldType);

            formHtml += '<div><label class="block text-sm font-medium text-gray-700 mb-2">' + config.label + '<\/label>';
            formHtml += generateFieldPreview(fieldType, config);
            formHtml += '<\/div>';
        }

        formHtml += '<div class="flex justify-end">';
        formHtml += '<button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">Submit<\/button>';
        formHtml += '<\/div><\/form><\/div><\/body><\/html>';

        var previewWindow = window.open('', '_blank');
        previewWindow.document.write(formHtml);
    });

    // Load existing form if any
    if (formFields && formFields.length > 0) {
        for (var i = 0; i < formFields.length; i++) {
            var field = formFields[i];
            var fieldElement = createFieldElement(field.type, field.name);
            document.getElementById('form-canvas').appendChild(fieldElement);
        }

        var emptyState = document.getElementById('empty-state');
        if (emptyState) {
            emptyState.remove();
        }
    }
})();
</script>
@endsection
