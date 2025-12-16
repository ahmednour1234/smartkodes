@extends('tenant.layouts.app')

@push('head')
    <!-- Signature Pad Library -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <!-- Html5-QRCode Library for Barcode/QR Scanning -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endpush

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-8">
                <!-- Form Header -->
                <div class="mb-8 flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Record: {{ $record->formVersion->form->name ?? $record->form->name ?? 'Record' }}</h1>
                        <p class="text-sm text-gray-500">Make changes to the submitted data below</p>
                    </div>
                    <a href="{{ route('tenant.records.show', $record->id) }}"
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Cancel
                    </a>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <p class="font-bold mb-2">Please correct the following errors:</p>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Record Metadata -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <h3 class="font-semibold text-blue-900 mb-3">Record Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                            <select name="project_id" id="project_id" form="edit-record-form"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ $record->project_id == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status" form="edit-record-form"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="draft" {{ $record->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="submitted" {{ $record->status == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                <option value="reviewed" {{ $record->status == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                <option value="approved" {{ $record->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $record->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Form Fields -->
                <form id="edit-record-form" method="POST" action="{{ route('tenant.records.update', $record) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @foreach(($record->formVersion->form->formFields ?? $record->form->formFields ?? []) as $field)
                        @php
                            $config = is_array($field->config_json) ? $field->config_json : json_decode($field->config_json, true);
                            $required = $field->is_required;
                            $label = $field->label ?? $field->name;
                            $placeholder = $field->placeholder ?? '';
                            $currentValue = $currentValues[$field->name] ?? null;
                        @endphp

                        @if($field->type === 'section')
                            <!-- Section Header -->
                            <div class="border-t-2 border-gray-300 pt-6 mt-8">
                                <h2 class="text-2xl font-semibold text-gray-900">{{ $config['section_title'] ?? $config['title'] ?? 'Section' }}</h2>
                            </div>
                        @elseif($field->type === 'pagebreak')
                            <!-- Page Break -->
                            <div class="border-t-2 border-dashed border-gray-300 my-8"></div>
                        @elseif($field->type === 'calculated')
                            <!-- Skip calculated fields in edit mode - they should be auto-calculated -->
                        @else
                            <!-- Regular Field -->
                            <div class="field-container" data-visibility-rules='{{ json_encode($field->visibility_rules ?? []) }}'>
                                <label for="{{ $field->name }}" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ $label }}
                                    @if($required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>

                                @switch($field->type)
                                    @case('text')
                                    @case('email')
                                    @case('phone')
                                    @case('url')
                                        <input type="{{ $field->type === 'email' ? 'email' : ($field->type === 'url' ? 'url' : 'text') }}"
                                               id="{{ $field->name }}"
                                               name="{{ $field->name }}"
                                               value="{{ old($field->name, $currentValue) }}"
                                               placeholder="{{ $placeholder }}"
                                               {{ $required ? 'required' : '' }}
                                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        @break

                                    @case('textarea')
                                        <textarea id="{{ $field->name }}"
                                                  name="{{ $field->name }}"
                                                  rows="4"
                                                  placeholder="{{ $placeholder }}"
                                                  {{ $required ? 'required' : '' }}
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">{{ old($field->name, $currentValue) }}</textarea>
                                        @break

                                    @case('number')
                                    @case('currency')
                                    @case('percentage')
                                        <div class="relative">
                                            @if($field->type === 'currency')
                                                <span class="absolute left-3 top-2 text-gray-500">$</span>
                                            @endif
                                            <input type="number"
                                                   id="{{ $field->name }}"
                                                   name="{{ $field->name }}"
                                                   value="{{ old($field->name, $currentValue) }}"
                                                   step="{{ $field->type === 'currency' ? '0.01' : 'any' }}"
                                                   min="{{ $field->min_value }}"
                                                   max="{{ $field->max_value }}"
                                                   {{ $required ? 'required' : '' }}
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 {{ $field->type === 'currency' ? 'pl-7' : '' }}">
                                            @if($field->type === 'percentage')
                                                <span class="absolute right-3 top-2 text-gray-500">%</span>
                                            @endif
                                        </div>
                                        @break

                                    @case('date')
                                        <input type="date"
                                               id="{{ $field->name }}"
                                               name="{{ $field->name }}"
                                               value="{{ old($field->name, $currentValue) }}"
                                               {{ $required ? 'required' : '' }}
                                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        @break

                                    @case('time')
                                        <input type="time"
                                               id="{{ $field->name }}"
                                               name="{{ $field->name }}"
                                               value="{{ old($field->name, $currentValue) }}"
                                               {{ $required ? 'required' : '' }}
                                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        @break

                                    @case('datetime')
                                        <input type="datetime-local"
                                               id="{{ $field->name }}"
                                               name="{{ $field->name }}"
                                               value="{{ old($field->name, $currentValue) }}"
                                               {{ $required ? 'required' : '' }}
                                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        @break

                                    @case('select')
                                    @case('dropdown')
                                        <select id="{{ $field->name }}"
                                                name="{{ $field->name }}"
                                                {{ $required ? 'required' : '' }}
                                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select an option</option>
                                            @if(isset($field->options) && is_array($field->options))
                                                @foreach($field->options as $option)
                                                    <option value="{{ $option }}" {{ old($field->name, $currentValue) == $option ? 'selected' : '' }}>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @break

                                    @case('radio')
                                        <div class="space-y-2">
                                            @if(isset($field->options) && is_array($field->options))
                                                @foreach($field->options as $option)
                                                    <label class="flex items-center">
                                                        <input type="radio"
                                                               name="{{ $field->name }}"
                                                               value="{{ $option }}"
                                                               {{ old($field->name, $currentValue) == $option ? 'checked' : '' }}
                                                               {{ $required ? 'required' : '' }}
                                                               class="rounded-full border-gray-300 text-blue-600 focus:ring-blue-500">
                                                        <span class="ml-2 text-sm text-gray-700">{{ $option }}</span>
                                                    </label>
                                                @endforeach
                                            @endif
                                        </div>
                                        @break

                                    @case('checkbox')
                                        <label class="flex items-center">
                                            <input type="checkbox"
                                                   name="{{ $field->name }}"
                                                   value="1"
                                                   {{ old($field->name, $currentValue) ? 'checked' : '' }}
                                                   {{ $required ? 'required' : '' }}
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                        </label>
                                        @break

                                    @case('signature')
                                        <div class="border-2 border-dashed border-gray-300 rounded-md p-4">
                                            @if($currentValue)
                                                <div class="mb-2">
                                                    <p class="text-sm text-gray-600 mb-2">Current Signature:</p>
                                                    <img src="{{ $currentValue }}" alt="Current Signature" class="border border-gray-300 rounded max-w-md mb-2">
                                                    <p class="text-xs text-gray-500">Draw a new signature below to replace the current one.</p>
                                                </div>
                                            @endif
                                            <canvas id="signature-{{ $field->name }}"
                                                    class="w-full h-40 border border-gray-200 rounded cursor-crosshair bg-white"
                                                    data-field="{{ $field->name }}"></canvas>
                                            <input type="hidden" name="{{ $field->name }}" id="{{ $field->name }}-data" value="{{ $currentValue }}">
                                            <div class="mt-2 flex gap-2">
                                                <button type="button"
                                                        onclick="clearSignature('{{ $field->name }}')"
                                                        class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded">
                                                    Clear
                                                </button>
                                            </div>
                                        </div>
                                        @break

                                    @case('gps')
                                        <div class="space-y-2">
                                            @if(is_array($currentValue) && isset($currentValue['latitude']))
                                                <p class="text-sm text-gray-600">
                                                    Current: {{ number_format($currentValue['latitude'], 6) }}, {{ number_format($currentValue['longitude'], 6) }}
                                                </p>
                                            @endif
                                            <button type="button"
                                                    onclick="captureLocation('{{ $field->name }}')"
                                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                                📍 Update Location
                                            </button>
                                            <input type="hidden" name="{{ $field->name }}" id="{{ $field->name }}-data" value="{{ json_encode($currentValue) }}">
                                            <div id="{{ $field->name }}-display" class="text-sm text-gray-600"></div>
                                        </div>
                                        @break

                                    @case('file')
                                    @case('photo')
                                    @case('video')
                                    @case('audio')
                                        <div class="space-y-2">
                                            @php
                                                $files = $record->files->where('form_field_id', $field->id);
                                            @endphp
                                            @if($files->count() > 0)
                                                <div class="mb-3">
                                                    <p class="text-sm font-medium text-gray-700 mb-2">Current Files:</p>
                                                    @foreach($files as $file)
                                                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded mb-1">
                                                            <span class="text-sm text-gray-700">{{ $file->original_filename }}</span>
                                                            <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">View</a>
                                                        </div>
                                                    @endforeach
                                                    <p class="text-xs text-gray-500 mt-2">Upload a new file to replace the current one.</p>
                                                </div>
                                            @endif
                                            <input type="file"
                                                   id="{{ $field->name }}"
                                                   name="{{ $field->name }}"
                                                   accept="{{ $field->type === 'photo' ? 'image/*' : ($field->type === 'video' ? 'video/*' : ($field->type === 'audio' ? 'audio/*' : '*/*')) }}"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        @break

                                    @default
                                        <input type="text"
                                               id="{{ $field->name }}"
                                               name="{{ $field->name }}"
                                               value="{{ old($field->name, $currentValue) }}"
                                               placeholder="{{ $placeholder }}"
                                               {{ $required ? 'required' : '' }}
                                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                @endswitch

                                @error($field->name)
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                @if(!empty($field->help_text))
                                    <p class="mt-1 text-sm text-gray-500">{{ $field->help_text }}</p>
                                @endif
                            </div>
                        @endif
                    @endforeach

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('tenant.records.show', $record->id) }}"
                           class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize signature pads
    initializeSignaturePads();

    // Initialize conditional logic
    initializeConditionalLogic();
});

// Signature pad functionality using SignaturePad library
const signaturePads = {};

function initializeSignaturePads() {
    const canvases = document.querySelectorAll('canvas[id^="signature-"]');
    canvases.forEach(canvas => {
        const fieldName = canvas.dataset.field;

        // Set canvas size
        canvas.width = canvas.offsetWidth;
        canvas.height = 160;

        // Initialize SignaturePad
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)',
            minWidth: 0.5,
            maxWidth: 2.5,
            throttle: 16,
            velocityFilterWeight: 0.7
        });

        // Store the SignaturePad instance for later access
        signaturePads[fieldName] = signaturePad;

        // Load existing signature if present
        const hiddenInput = document.getElementById(fieldName + '-data');
        if (hiddenInput.value && hiddenInput.value !== 'null' && hiddenInput.value !== '{}') {
            try {
                signaturePad.fromDataURL(hiddenInput.value);
            } catch(e) {
                console.log('Could not load signature:', e);
            }
        }

        // Save signature data whenever it changes
        signaturePad.addEventListener('endStroke', () => {
            const dataURL = signaturePad.toDataURL('image/png');
            hiddenInput.value = dataURL;
        });
    });
}

function clearSignature(fieldName) {
    if (signaturePads[fieldName]) {
        signaturePads[fieldName].clear();
        document.getElementById(fieldName + '-data').value = '';
    }
}

// GPS Location
function captureLocation(fieldName) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const location = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                };
                document.getElementById(fieldName + '-data').value = JSON.stringify(location);
                document.getElementById(fieldName + '-display').innerHTML =
                    `<span class="text-green-600">✓ Location captured: ${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)} (±${Math.round(location.accuracy)}m)</span>`;
            },
            (error) => {
                alert('Unable to capture location: ' + error.message);
            }
        );
    } else {
        alert('Geolocation is not supported by your browser');
    }
}

// Conditional Logic
function initializeConditionalLogic() {
    const fields = document.querySelectorAll('.field-container');

    fields.forEach(field => {
        const visibilityRules = JSON.parse(field.dataset.visibilityRules || '{}');

        if (Object.keys(visibilityRules).length > 0) {
            // Watch for changes in dependent fields
            document.addEventListener('change', () => evaluateVisibility(field, visibilityRules));
            document.addEventListener('input', () => evaluateVisibility(field, visibilityRules));

            // Initial evaluation
            evaluateVisibility(field, visibilityRules);
        }
    });
}

function evaluateVisibility(field, rules) {
    if (rules.show_when) {
        let shouldShow = true;

        for (const [fieldName, expectedValue] of Object.entries(rules.show_when)) {
            const inputField = document.querySelector(`[name="${fieldName}"]`);
            if (inputField && inputField.value != expectedValue) {
                shouldShow = false;
                break;
            }
        }

        field.style.display = shouldShow ? 'block' : 'none';
    }
}
</script>
@endsection
