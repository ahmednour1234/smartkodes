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
                            $rawVal = old($field->name, $currentValue);
                            $inputValue = is_array($rawVal) ? implode(', ', $rawVal) : (string) ($rawVal ?? '');
                            $fieldOptions = $config['options'] ?? $field->options ?? [];
                            if (is_string($fieldOptions)) {
                                $fieldOptions = array_filter(array_map('trim', explode("\n", $fieldOptions)));
                            }
                            $multiselectVal = is_array($rawVal) ? $rawVal : (array) $rawVal;
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
                                               value="{{ $inputValue }}"
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
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">{{ $inputValue }}</textarea>
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
                                                   value="{{ $inputValue }}"
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
                                               value="{{ $inputValue }}"
                                               {{ $required ? 'required' : '' }}
                                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        @break

                                    @case('time')
                                        <input type="time"
                                               id="{{ $field->name }}"
                                               name="{{ $field->name }}"
                                               value="{{ $inputValue }}"
                                               {{ $required ? 'required' : '' }}
                                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        @break

                                    @case('datetime')
                                        <input type="datetime-local"
                                               id="{{ $field->name }}"
                                               name="{{ $field->name }}"
                                               value="{{ $inputValue }}"
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
                                            @foreach($fieldOptions as $option)
                                                <option value="{{ $option }}" {{ $inputValue === (string)$option ? 'selected' : '' }}>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                        @break

                                    @case('multiselect')
                                        <div class="space-y-2 border border-gray-300 rounded-md p-3 bg-gray-50 max-h-48 overflow-y-auto">
                                            @foreach($fieldOptions as $option)
                                                <label class="flex items-center cursor-pointer hover:bg-gray-100 p-2 rounded">
                                                    <input type="checkbox"
                                                           name="{{ $field->name }}[]"
                                                           value="{{ $option }}"
                                                           {{ in_array($option, $multiselectVal) ? 'checked' : '' }}
                                                           {{ $required ? 'required' : '' }}
                                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <span class="ml-2 text-sm text-gray-700">{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Select one or more options</p>
                                        @break

                                    @case('radio')
                                        <div class="space-y-2">
                                            @foreach($fieldOptions as $option)
                                                <label class="flex items-center">
                                                    <input type="radio"
                                                           name="{{ $field->name }}"
                                                           value="{{ $option }}"
                                                           {{ $inputValue === (string)$option ? 'checked' : '' }}
                                                           {{ $required ? 'required' : '' }}
                                                           class="rounded-full border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <span class="ml-2 text-sm text-gray-700">{{ $option }}</span>
                                                </label>
                                            @endforeach
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
                                        @php
                                            $signatureValue = is_array($currentValue) && isset($currentValue['value']) ? (string)$currentValue['value'] : (string)($currentValue ?? '');
                                        @endphp
                                        <div class="border-2 border-dashed border-gray-300 rounded-md p-4">
                                            <label class="text-sm text-gray-600 mb-2 block">Current Signature:</label>
                                            <input type="text"
                                                   name="{{ $field->name }}"
                                                   id="{{ $field->name }}-data"
                                                   value="{{ $signatureValue }}"
                                                   placeholder="Signature (editable text)"
                                                   {{ $required ? 'required' : '' }}
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        @break

                                    @case('gps')
                                        @php
                                            $gpsLat = is_array($currentValue) && isset($currentValue['latitude']) ? (float)$currentValue['latitude'] : null;
                                            $gpsLng = is_array($currentValue) && isset($currentValue['longitude']) ? (float)$currentValue['longitude'] : null;
                                        @endphp
                                        <div class="space-y-2 gps-field" data-field-name="{{ $field->name }}" data-initial-lat="{{ $gpsLat }}" data-initial-lng="{{ $gpsLng }}">
                                            <div id="map-{{ $field->name }}" class="w-full h-64 rounded-lg border border-gray-300 z-0" style="min-height: 256px;"></div>
                                            <div class="flex gap-2 flex-wrap">
                                                <button type="button" onclick="gpsUseMyLocation('{{ $field->name }}')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                                    📍 Use my location
                                                </button>
                                                <button type="button" onclick="gpsClearLocation('{{ $field->name }}')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm">
                                                    Clear
                                                </button>
                                            </div>
                                            <input type="hidden" name="{{ $field->name }}" id="{{ $field->name }}-data" value="{{ is_array($currentValue) ? json_encode($currentValue) : (string)($currentValue ?? '') }}">
                                            <div id="{{ $field->name }}-display" class="text-sm text-gray-600"></div>
                                        </div>
                                        @break

                                    @case('barcode')
                                        <div class="space-y-2">
                                            <button type="button"
                                                    onclick="scanCode('{{ $field->name }}', 'barcode')"
                                                    data-type="Barcode"
                                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                                📱 Scan Barcode
                                            </button>
                                            <input type="text"
                                                   id="{{ $field->name }}"
                                                   name="{{ $field->name }}"
                                                   value="{{ $inputValue }}"
                                                   placeholder="Or enter code manually"
                                                   {{ $required ? 'required' : '' }}
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                            <div id="{{ $field->name }}-scanner" class="hidden border border-gray-300 rounded-lg overflow-hidden" style="width: 100%; max-width: 500px;"></div>
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
                                               value="{{ $inputValue }}"
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
    initializeSignaturePads();
    initializeConditionalLogic();
    initializeGpsMaps();
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

window.gpsMaps = {};
function initializeGpsMaps() {
    document.querySelectorAll('.gps-field').forEach(el => {
        const fieldName = el.dataset.fieldName;
        const mapEl = document.getElementById('map-' + fieldName);
        if (!mapEl || typeof L === 'undefined') return;
        const lat = el.dataset.initialLat ? parseFloat(el.dataset.initialLat) : 30.0444;
        const lng = el.dataset.initialLng ? parseFloat(el.dataset.initialLng) : 31.2357;
        const map = L.map(mapEl).setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
        let marker = null;
        if (el.dataset.initialLat && el.dataset.initialLng) {
            marker = L.marker([lat, lng]).addTo(map);
        }
        map.on('click', function(e) {
            if (marker) marker.remove();
            marker = L.marker(e.latlng).addTo(map);
            const loc = { latitude: e.latlng.lat, longitude: e.latlng.lng, accuracy: null };
            document.getElementById(fieldName + '-data').value = JSON.stringify(loc);
            document.getElementById(fieldName + '-display').innerHTML = '<span class="text-green-600">✓ ' + e.latlng.lat.toFixed(6) + ', ' + e.latlng.lng.toFixed(6) + '</span>';
        });
        window.gpsMaps[fieldName] = { map, marker };
    });
}
function gpsUseMyLocation(fieldName) {
    if (!navigator.geolocation) { alert('Geolocation not supported'); return; }
    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const data = window.gpsMaps[fieldName];
            if (data && data.map) {
                data.map.setView([lat, lng], 16);
                if (data.marker) data.marker.remove();
                data.marker = L.marker([lat, lng]).addTo(data.map);
                window.gpsMaps[fieldName].marker = data.marker;
            }
            const loc = { latitude: lat, longitude: lng, accuracy: position.coords.accuracy };
            document.getElementById(fieldName + '-data').value = JSON.stringify(loc);
            document.getElementById(fieldName + '-display').innerHTML = '<span class="text-green-600">✓ ' + lat.toFixed(6) + ', ' + lng.toFixed(6) + ' (±' + Math.round(position.coords.accuracy) + 'm)</span>';
        },
        function() { alert('Unable to get location'); }
    );
}
function gpsClearLocation(fieldName) {
    const data = window.gpsMaps[fieldName];
    if (data && data.marker) { data.marker.remove(); window.gpsMaps[fieldName].marker = null; }
    document.getElementById(fieldName + '-data').value = '';
    document.getElementById(fieldName + '-display').innerHTML = '';
}
function captureLocation(fieldName) {
    gpsUseMyLocation(fieldName);
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

const scanners = {};
function scanCode(fieldName, codeType) {
    const scannerDiv = document.getElementById(fieldName + '-scanner');
    const inputField = document.getElementById(fieldName);
    const scanButton = event.target;
    if (scannerDiv.classList.contains('hidden')) {
        scannerDiv.classList.remove('hidden');
        scanButton.textContent = '🛑 Stop Scanning';
        scanButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        scanButton.classList.add('bg-red-600', 'hover:bg-red-700');
        if (!scanners[fieldName]) scanners[fieldName] = new Html5Qrcode(fieldName + '-scanner');
        const config = { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 };
        const formatsToSupport = (codeType === 'qr') ? [Html5QrcodeSupportedFormats.QR_CODE] : [Html5QrcodeSupportedFormats.CODE_128, Html5QrcodeSupportedFormats.CODE_39, Html5QrcodeSupportedFormats.EAN_13, Html5QrcodeSupportedFormats.EAN_8, Html5QrcodeSupportedFormats.UPC_A, Html5QrcodeSupportedFormats.UPC_E];
        scanners[fieldName].start({ facingMode: 'environment' }, { ...config, formatsToSupport }, (decodedText) => { inputField.value = decodedText; stopScanner(fieldName, scanButton); }, () => {}).catch(() => { alert('Unable to access camera. Please check permissions or enter the code manually.'); stopScanner(fieldName, scanButton); });
    } else stopScanner(fieldName, scanButton);
}
function stopScanner(fieldName, button) {
    const scannerDiv = document.getElementById(fieldName + '-scanner');
    if (scanners[fieldName]) scanners[fieldName].stop().then(() => { scannerDiv.classList.add('hidden'); button.textContent = '📱 Scan Barcode'; button.classList.remove('bg-red-600', 'hover:bg-red-700'); button.classList.add('bg-blue-600', 'hover:bg-blue-700'); }).catch(() => {});
    else { scannerDiv.classList.add('hidden'); button.textContent = '📱 Scan Barcode'; button.classList.remove('bg-red-600', 'hover:bg-red-700'); button.classList.add('bg-blue-600', 'hover:bg-blue-700'); }
}
</script>
@endsection
