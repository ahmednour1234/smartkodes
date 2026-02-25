{{-- resources/views/components/location-picker.blade.php --}}

@php
    $hasExistingLocation = ($latValue !== null && $lngValue !== null);
    $defaultLat = old($latName, $latValue ?? 30.0444);
    $defaultLng = old($lngName, $lngValue ?? 31.2357);
    $inputLatId = $latName . '_input';
    $inputLngId = $lngName . '_input';
    $mapId      = $latName . '_map';
@endphp

<div class="space-y-2">
    <div class="flex items-center justify-between gap-2 flex-wrap">
        <label class="block text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
        <button type="button" onclick="window.useMyLocation('{{ $mapId }}')" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
            Use my location
        </button>
    </div>

    @if($description)
        <p class="text-sm text-gray-500">{{ $description }}</p>
    @endif

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
        <script>
            window.locationPickerConfigs = window.locationPickerConfigs || [];
            window.locationPickerInstances = window.locationPickerInstances || {};
            window.initLocationPickers = function () {
                if (typeof google === 'undefined' || !google.maps) return;
                (window.locationPickerConfigs || []).forEach(function (cfg) {
                    var mapElement = document.getElementById(cfg.mapId);
                    if (!mapElement) return;
                    var center = { lat: parseFloat(cfg.lat), lng: parseFloat(cfg.lng) };
                    var map = new google.maps.Map(mapElement, { zoom: 14, center: center });
                    var marker = new google.maps.Marker({ position: center, map: map, draggable: true });
                    var latInput = document.getElementById(cfg.latInputId);
                    var lngInput = document.getElementById(cfg.lngInputId);
                    function updateInputs(position) {
                        latInput.value = position.lat();
                        lngInput.value = position.lng();
                    }
                    marker.addListener('dragend', function (e) { updateInputs(e.latLng); });
                    map.addListener('click', function (e) { marker.setPosition(e.latLng); updateInputs(e.latLng); });
                    window.locationPickerInstances[cfg.mapId] = { map: map, marker: marker, latInput: latInput, lngInput: lngInput };
                    if (cfg.useCurrentLocationAsDefault && navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function (pos) {
                                var latLng = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                                marker.setPosition(latLng);
                                map.setCenter(latLng);
                                latInput.value = pos.coords.latitude;
                                lngInput.value = pos.coords.longitude;
                            },
                            function () {}
                        );
                    }
                });
            };
            window.useMyLocation = function (mapId) {
                var inst = window.locationPickerInstances && window.locationPickerInstances[mapId];
                if (!inst || !navigator.geolocation) return;
                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        var latLng = new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
                        inst.marker.setPosition(latLng);
                        inst.map.setCenter(latLng);
                        inst.latInput.value = pos.coords.latitude;
                        inst.lngInput.value = pos.coords.longitude;
                    },
                    function () {}
                );
            };
        </script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initLocationPickers&libraries=places" async defer></script>
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
                useCurrentLocationAsDefault: {{ $useCurrentLocationAsDefault && !$hasExistingLocation ? 'true' : 'false' }},
            });
        </script>
    @endpush
</div>
