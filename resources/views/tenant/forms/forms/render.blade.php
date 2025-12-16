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
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $form->name }}</h1>
                    <p class="text-sm text-gray-500">Please fill out all required fields marked with *</p>
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

                <!-- Form -->
                <form id="form-submission" method="POST" action="{{ route('tenant.forms.submit', $form) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    @foreach($form->formFields as $field)
                        @php
                            $config = is_array($field->config_json) ? $field->config_json : json_decode($field->config_json, true);
                            $required = $config['required'] ?? false;
                            $label = $config['label'] ?? $field->name;
                            $placeholder = $field->placeholder ?? ($config['placeholder'] ?? '');
                            $defaultValue = $field->default_value ?? ($config['default'] ?? '');
                        @endphp

                        @if($field->type === 'section')
                            <!-- Section Header -->
                            <div class="border-t-2 border-gray-300 pt-6 mt-8">
                                <h2 class="text-2xl font-semibold text-gray-900">{{ $config['section_title'] ?? $config['title'] ?? 'Section' }}</h2>
                            </div>
                        @elseif($field->type === 'pagebreak')
                            <!-- Page Break -->
                            <div class="border-t-2 border-dashed border-gray-300 my-8 flex items-center justify-center">
                                <span class="bg-white px-4 text-sm text-gray-500">Page Break</span>
                            </div>
                        @else
                            <!-- Regular Field -->
                            <div class="field-container" data-field-id="{{ $field->id }}" data-field-name="{{ $field->name }}"
                                 data-visibility-rules="{{ $field->visibility_rules ? json_encode($field->visibility_rules) : '{}' }}">

                                <label for="{{ $field->name }}" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ $label }}
                                    @if($required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                    @if($field->is_sensitive)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 ml-2">
                                            🔒 Sensitive
                                        </span>
                                    @endif
                                </label>

                                @switch($field->type)
                                    @case('text')
                                    @case('email')
                                    @case('phone')
                                    @case('url')
                                        <input type="{{ $field->type }}"
                                               id="{{ $field->name }}"
                                               name="{{ $field->name }}"
                                               value="{{ old($field->name, $defaultValue) }}"
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
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">{{ old($field->name, $defaultValue) }}</textarea>
                                        @break

                                    @case('number')
                                    @case('currency')
                                        <div class="relative">
                                            @if($field->type === 'currency')
                                                <span class="absolute left-3 top-2 text-gray-500">{{ $field->currency_symbol ?? '$' }}</span>
                                                <input type="number"
                                                       id="{{ $field->name }}"
                                                       name="{{ $field->name }}"
                                                       value="{{ old($field->name, $defaultValue) }}"
                                                       placeholder="{{ $placeholder }}"
                                                       step="0.01"
                                                       {{ $field->min_value !== null ? "min={$field->min_value}" : '' }}
                                                       {{ $field->max_value !== null ? "max={$field->max_value}" : '' }}
                                                       {{ $required ? 'required' : '' }}
                                                       class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                            @else
                                                <input type="number"
                                                       id="{{ $field->name }}"
                                                       name="{{ $field->name }}"
                                                       value="{{ old($field->name, $defaultValue) }}"
                                                       placeholder="{{ $placeholder }}"
                                                       {{ $field->min_value !== null ? "min={$field->min_value}" : '' }}
                                                       {{ $field->max_value !== null ? "max={$field->max_value}" : '' }}
                                                       {{ $required ? 'required' : '' }}
                                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                            @endif
                                        </div>
                                        @break

                                    @case('date')
                                        <input type="date"
                                               id="{{ $field->name }}"
                                               name="{{ $field->name }}"
                                               value="{{ old($field->name, $defaultValue) }}"
                                               {{ $required ? 'required' : '' }}
                                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        @break

                                    @case('time')
                                        <input type="time"
                                               id="{{ $field->name }}"
                                               name="{{ $field->name }}"
                                               value="{{ old($field->name, $defaultValue) }}"
                                               {{ $required ? 'required' : '' }}
                                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        @break

                                    @case('datetime')
                                        <input type="datetime-local"
                                               id="{{ $field->name }}"
                                               name="{{ $field->name }}"
                                               value="{{ old($field->name, $defaultValue) }}"
                                               {{ $required ? 'required' : '' }}
                                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        @break

                                    @case('select')
                                        <select id="{{ $field->name }}"
                                                name="{{ $field->name }}"
                                                {{ $required ? 'required' : '' }}
                                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select an option</option>
                                            @foreach($field->options ?? [] as $option)
                                                <option value="{{ $option }}" {{ old($field->name, $defaultValue) == $option ? 'selected' : '' }}>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @break

                                    @case('multiselect')
                                        <select id="{{ $field->name }}"
                                                name="{{ $field->name }}[]"
                                                multiple
                                                {{ $required ? 'required' : '' }}
                                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                                size="4">
                                            @foreach($field->options ?? [] as $option)
                                                <option value="{{ $option }}" {{ in_array($option, old($field->name, [])) ? 'selected' : '' }}>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple options</p>
                                        @break

                                    @case('radio')
                                        <div class="space-y-2">
                                            @foreach($field->options ?? [] as $option)
                                                <label class="inline-flex items-center mr-6">
                                                    <input type="radio"
                                                           name="{{ $field->name }}"
                                                           value="{{ $option }}"
                                                           {{ old($field->name, $defaultValue) == $option ? 'checked' : '' }}
                                                           {{ $required ? 'required' : '' }}
                                                           class="rounded-full border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <span class="ml-2 text-sm text-gray-700">{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @break

                                    @case('checkbox')
                                        <label class="inline-flex items-center">
                                            <input type="checkbox"
                                                   name="{{ $field->name }}"
                                                   value="1"
                                                   {{ old($field->name, $defaultValue) ? 'checked' : '' }}
                                                   {{ $required ? 'required' : '' }}
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                        </label>
                                        @break

                                    @case('signature')
                                        <div class="border-2 border-dashed border-gray-300 rounded-md p-4">
                                            <canvas id="signature-{{ $field->name }}"
                                                    class="w-full h-40 border border-gray-200 rounded cursor-crosshair bg-white"
                                                    data-field="{{ $field->name }}"></canvas>
                                            <input type="hidden" name="{{ $field->name }}" id="{{ $field->name }}-data">
                                            <div class="mt-2 flex gap-2">
                                                <button type="button"
                                                        onclick="clearSignature('{{ $field->name }}')"
                                                        class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded">
                                                    Clear
                                                </button>
                                            </div>
                                        </div>
                                        @break

                                    @case('photo')
                                    @case('video')
                                    @case('audio')
                                    @case('file')
                                        <div class="file-upload-wrapper">
                                            <input type="file"
                                                   id="{{ $field->name }}"
                                                   name="{{ $field->name }}"
                                                   {{ $required ? 'required' : '' }}
                                                   @if($field->type === 'photo') accept="image/*" @endif
                                                   @if($field->type === 'video') accept="video/*" @endif
                                                   @if($field->type === 'audio') accept="audio/*" @endif
                                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                            <p class="text-xs text-gray-500 mt-1">
                                                @if($field->type === 'photo') Max size: 5MB @endif
                                                @if($field->type === 'video') Max size: 50MB @endif
                                                @if($field->type === 'audio') Max size: 10MB @endif
                                                @if($field->type === 'file') Max size: 10MB @endif
                                            </p>
                                        </div>
                                        @break

                                    @case('gps')
                                        <div class="space-y-2">
                                            <button type="button"
                                                    onclick="captureLocation('{{ $field->name }}')"
                                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                                📍 Capture My Location
                                            </button>
                                            <input type="hidden" name="{{ $field->name }}" id="{{ $field->name }}-data">
                                            <div id="{{ $field->name }}-display" class="text-sm text-gray-600"></div>
                                        </div>
                                        @break

                                    @case('barcode')
                                    @case('qr')
                                        <div class="space-y-2">
                                            <button type="button"
                                                    onclick="scanCode('{{ $field->name }}', '{{ $field->type }}')"
                                                    data-type="{{ ucfirst($field->type) }}"
                                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                                📱 Scan {{ ucfirst($field->type) }}
                                            </button>
                                            <input type="text"
                                                   id="{{ $field->name }}"
                                                   name="{{ $field->name }}"
                                                   value="{{ old($field->name, $defaultValue) }}"
                                                   placeholder="Or enter code manually"
                                                   {{ $required ? 'required' : '' }}
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                            <div id="{{ $field->name }}-scanner" class="hidden border border-gray-300 rounded-lg overflow-hidden" style="width: 100%; max-width: 500px;"></div>
                                        </div>
                                        @break

                                    @case('calculated')
                                        <div class="bg-gray-50 px-4 py-2 border border-gray-300 rounded-md">
                                            <input type="text"
                                                   id="{{ $field->name }}"
                                                   name="{{ $field->name }}"
                                                   value="{{ old($field->name, $defaultValue) }}"
                                                   readonly
                                                   data-formula="{{ $field->calculation_formula }}"
                                                   class="w-full bg-transparent border-none focus:ring-0 text-gray-700 font-medium">
                                            <p class="text-xs text-gray-500 mt-1">This field is automatically calculated</p>
                                        </div>
                                        @break

                                    @default
                                        <input type="text"
                                               id="{{ $field->name }}"
                                               name="{{ $field->name }}"
                                               value="{{ old($field->name, $defaultValue) }}"
                                               placeholder="{{ $placeholder }}"
                                               {{ $required ? 'required' : '' }}
                                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                @endswitch

                                @error($field->name)
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                @if(!empty($field->config_json['help_text'] ?? ''))
                                    <p class="mt-1 text-sm text-gray-500">{{ $field->config_json['help_text'] }}</p>
                                @endif
                            </div>
                        @endif
                    @endforeach

                    <!-- Hidden field for location data -->
                    <input type="hidden" name="_location" id="form-location">

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end pt-6 border-t border-gray-200">
                        <button type="submit"
                                class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Submit Form
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

    // Initialize calculated fields
    initializeCalculatedFields();

    // Attempt to capture location on page load
    captureLocationSilently();
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

        // Save signature data whenever it changes
        signaturePad.addEventListener('endStroke', () => {
            const dataURL = signaturePad.toDataURL('image/png');
            document.getElementById(fieldName + '-data').value = dataURL;
        });

        // Handle window resize to maintain signature
        window.addEventListener('resize', () => {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = 160 * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            signaturePad.clear(); // Clear on resize as signature would be distorted
        });
    });
}

function clearSignature(fieldName) {
    if (signaturePads[fieldName]) {
        signaturePads[fieldName].clear();
        document.getElementById(fieldName + '-data').value = '';
    }
}

// GPS Location capture
function captureLocation(fieldName) {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const location = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy: position.coords.accuracy
            };

            document.getElementById(fieldName + '-data').value = JSON.stringify(location);
            document.getElementById(fieldName + '-display').innerHTML =
                `✅ Location captured: ${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)}`;
        },
        (error) => {
            alert('Error capturing location: ' + error.message);
        }
    );
}

function captureLocationSilently() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const location = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                };
                document.getElementById('form-location').value = JSON.stringify(location);
            },
            () => {} // Silently fail
        );
    }
}

// Barcode/QR Scanner using Html5-QRCode library
const scanners = {};

function scanCode(fieldName, codeType) {
    const scannerDiv = document.getElementById(fieldName + '-scanner');
    const inputField = document.getElementById(fieldName);
    const scanButton = event.target;

    // Toggle scanner visibility
    if (scannerDiv.classList.contains('hidden')) {
        scannerDiv.classList.remove('hidden');
        scanButton.textContent = '🛑 Stop Scanning';
        scanButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        scanButton.classList.add('bg-red-600', 'hover:bg-red-700');

        // Initialize scanner if not already done
        if (!scanners[fieldName]) {
            scanners[fieldName] = new Html5Qrcode(fieldName + '-scanner');
        }

        const scanner = scanners[fieldName];

        // Configure scanner based on code type
        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        };

        // Determine which formats to support
        let formatsToSupport = [];
        if (codeType === 'qr') {
            formatsToSupport = [Html5QrcodeSupportedFormats.QR_CODE];
        } else if (codeType === 'barcode') {
            formatsToSupport = [
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E
            ];
        } else {
            // Support both QR and common barcodes
            formatsToSupport = [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8
            ];
        }

        // Start scanning
        scanner.start(
            { facingMode: "environment" }, // Use back camera
            { ...config, formatsToSupport },
            (decodedText, decodedResult) => {
                // Success callback
                inputField.value = decodedText;
                stopScanner(fieldName, scanButton);

                // Show success message
                const successMsg = document.createElement('div');
                successMsg.className = 'mt-2 p-2 bg-green-100 text-green-700 rounded text-sm';
                successMsg.textContent = `✓ Successfully scanned: ${decodedText}`;
                scannerDiv.parentElement.appendChild(successMsg);
                setTimeout(() => successMsg.remove(), 3000);
            },
            (errorMessage) => {
                // Error callback - silently ignore scanning errors
            }
        ).catch(err => {
            console.error('Failed to start scanner:', err);
            alert('Unable to access camera. Please check permissions or enter the code manually.');
            stopScanner(fieldName, scanButton);
        });
    } else {
        stopScanner(fieldName, scanButton);
    }
}

function stopScanner(fieldName, button) {
    const scannerDiv = document.getElementById(fieldName + '-scanner');

    if (scanners[fieldName]) {
        scanners[fieldName].stop().then(() => {
            scannerDiv.classList.add('hidden');
            button.textContent = `📱 Scan ${button.dataset.type || 'Code'}`;
            button.classList.remove('bg-red-600', 'hover:bg-red-700');
            button.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }).catch(err => {
            console.error('Error stopping scanner:', err);
        });
    } else {
        scannerDiv.classList.add('hidden');
        button.textContent = `📱 Scan ${button.dataset.type || 'Code'}`;
        button.classList.remove('bg-red-600', 'hover:bg-red-700');
        button.classList.add('bg-blue-600', 'hover:bg-blue-700');
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
    // Simple implementation - can be enhanced
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

// Calculated Fields
function initializeCalculatedFields() {
    const calculatedFields = document.querySelectorAll('[data-formula]');

    calculatedFields.forEach(calcField => {
        const formula = calcField.dataset.formula;

        // Watch for changes in any input field
        document.addEventListener('input', () => updateCalculatedField(calcField, formula));
        document.addEventListener('change', () => updateCalculatedField(calcField, formula));

        // Initial calculation
        updateCalculatedField(calcField, formula);
    });
}

function updateCalculatedField(calcField, formula) {
    // Extract field references from formula
    const fieldRefs = formula.match(/\{([^}]+)\}/g);

    if (!fieldRefs) return;

    let expression = formula;

    fieldRefs.forEach(ref => {
        const fieldName = ref.slice(1, -1);
        const input = document.querySelector(`[name="${fieldName}"]`);
        const value = input ? (parseFloat(input.value) || 0) : 0;
        expression = expression.replace(ref, value);
    });

    // Remove any non-numeric/operator characters for safety
    expression = expression.replace(/[^0-9+\-*\/().\s]/g, '');

    try {
        const result = eval(expression);
        calcField.value = Math.round(result * 100) / 100;
    } catch (e) {
        calcField.value = 'Error';
    }
}
</script>
@endsection
