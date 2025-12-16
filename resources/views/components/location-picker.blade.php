{{-- resources/views/components/location-picker.blade.php --}}

@php
    // لو مفيش قيمة قديمة خالص، خلي الديفولت القاهرة مثلاً
    $defaultLat = old($latName, $latValue ?? 30.0444);
    $defaultLng = old($lngName, $lngValue ?? 31.2357);
    $inputLatId = $latName . '_input';
    $inputLngId = $lngName . '_input';
    $mapId      = $latName . '_map';
@endphp

<div class="space-y-2">
    <label class="block text-sm font-medium text-gray-700">
        {{ $label }}
    </label>

    <div id="{{ $mapId }}" class="w-full h-64 rounded-lg border border-gray-300 overflow-hidden"></div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
        <div>
            <label for="{{ $inputLatId }}" class="text-xs text-gray-600">Latitude</label>
            <input type="text"
                   id="{{ $inputLatId }}"
                   name="{{ $latName }}"
                   value="{{ $defaultLat }}"
                   class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2 text-sm"
                   readonly>
        </div>

        <div>
            <label for="{{ $inputLngId }}" class="text-xs text-gray-600">Longitude</label>
            <input type="text"
                   id="{{ $inputLngId }}"
                   name="{{ $lngName }}"
                   value="{{ $defaultLng }}"
                   class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2 text-sm"
                   readonly>
        </div>
    </div>

    @if($hint)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @pushOnce('scripts')
        {{-- استخدم ENV بدل ما تحط الـ Key ثابت في الكود --}}
        <script
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initLocationPickers&libraries=places"
            async defer></script>

        <script>
            window.locationPickerConfigs = window.locationPickerConfigs || [];

            function initLocationPickers() {
                window.locationPickerConfigs.forEach(function (cfg) {
                    let mapElement = document.getElementById(cfg.mapId);
                    if (!mapElement) return;

                    let center = { lat: parseFloat(cfg.lat), lng: parseFloat(cfg.lng) };

                    const map = new google.maps.Map(mapElement, {
                        zoom: 14,
                        center: center,
                    });

                    let marker = new google.maps.Marker({
                        position: center,
                        map: map,
                        draggable: true,
                    });

                    const latInput = document.getElementById(cfg.latInputId);
                    const lngInput = document.getElementById(cfg.lngInputId);

                    function updateInputs(position) {
                        latInput.value = position.lat();
                        lngInput.value = position.lng();
                    }

                    marker.addListener('dragend', function (event) {
                        updateInputs(event.latLng);
                    });

                    map.addListener('click', function (event) {
                        marker.setPosition(event.latLng);
                        updateInputs(event.latLng);
                    });
                });
            }
        </script>
    @endPushOnce

    @push('scripts')
        <script>
            window.locationPickerConfigs = window.locationPickerConfigs || [];
            window.locationPickerConfigs.push({
                mapId: '{{ $mapId }}',
                latInputId: '{{ $inputLatId }}',
                lngInputId: '{{ $inputLngId }}',
                lat: '{{ $defaultLat }}',
                lng: '{{ $defaultLng }}',
            });
        </script>
    @endpush
</div>
