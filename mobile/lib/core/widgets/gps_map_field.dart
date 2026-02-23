import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';

final LatLng _lebanonSw = const LatLng(33.05, 35.1);
final LatLng _lebanonNe = const LatLng(34.69, 36.6);
final LatLng _lebanonCenter = const LatLng(33.9, 35.9);

LatLngBounds _boundsFor(LatLng? marker) {
  if (marker == null) return LatLngBounds(southwest: _lebanonSw, northeast: _lebanonNe);
  final sw = LatLng(
    _lebanonSw.latitude < marker.latitude ? _lebanonSw.latitude : marker.latitude,
    _lebanonSw.longitude < marker.longitude ? _lebanonSw.longitude : marker.longitude,
  );
  final ne = LatLng(
    _lebanonNe.latitude > marker.latitude ? _lebanonNe.latitude : marker.latitude,
    _lebanonNe.longitude > marker.longitude ? _lebanonNe.longitude : marker.longitude,
  );
  return LatLngBounds(southwest: sw, northeast: ne);
}

void parseGpsValue(dynamic value, void Function(double lat, double lng)? onParsed) {
  if (value == null || onParsed == null) return;
  double? lat, lng;
  if (value is String && value.trim().isNotEmpty) {
    final parts = value.split(',');
    if (parts.length >= 2) {
      lat = double.tryParse(parts[0].trim());
      lng = double.tryParse(parts[1].trim());
    }
  } else if (value is Map) {
    final la = value['latitude'] ?? value['lat'];
    final lo = value['longitude'] ?? value['lng'] ?? value['lon'];
    if (la != null && lo != null) {
      lat = double.tryParse(la.toString());
      lng = double.tryParse(lo.toString());
    }
  }
  if (lat != null && lng != null) onParsed(lat, lng);
}

class GpsMapField extends StatefulWidget {
  const GpsMapField({
    super.key,
    required this.label,
    this.value,
    required this.onChanged,
    this.errorText,
  });

  final String label;
  final dynamic value;
  final ValueChanged<String> onChanged;
  final String? errorText;

  @override
  State<GpsMapField> createState() => _GpsMapFieldState();
}

class _GpsMapFieldState extends State<GpsMapField> {
  LatLng? _markerPosition;
  bool _parsed = false;

  @override
  void didUpdateWidget(GpsMapField oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.value != widget.value) _parsed = false;
  }

  void _ensureParsed() {
    if (_parsed) return;
    _parsed = true;
    parseGpsValue(widget.value, (lat, lng) {
      if (mounted) setState(() => _markerPosition = LatLng(lat, lng));
    });
  }

  Future<void> _openMapFocus() async {
    _ensureParsed();
    final initial = _markerPosition ?? _lebanonCenter;
    final result = await Navigator.of(context).push<LatLng>(
      MaterialPageRoute(
        fullscreenDialog: true,
        builder: (ctx) => _GpsMapFocusScreen(
          initialPosition: initial,
          initialMarker: _markerPosition,
        ),
      ),
    );
    if (result != null && mounted) {
      setState(() => _markerPosition = result);
      widget.onChanged('${result.latitude},${result.longitude}');
    }
  }

  Future<void> _setCurrentLocation() async {
    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Location services are disabled')),
        );
      }
      return;
    }
    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }
    if (permission == LocationPermission.denied || permission == LocationPermission.deniedForever) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Location permission denied')),
        );
      }
      return;
    }
    try {
      final pos = await Geolocator.getCurrentPosition();
      final latLng = LatLng(pos.latitude, pos.longitude);
      if (mounted) {
        setState(() => _markerPosition = latLng);
        widget.onChanged('${latLng.latitude},${latLng.longitude}');
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Could not get location: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    _ensureParsed();
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(widget.label),
          if (widget.errorText != null)
            Padding(
              padding: const EdgeInsets.only(top: 4),
              child: Text(widget.errorText!, style: const TextStyle(color: Colors.red, fontSize: 12)),
            ),
          const SizedBox(height: 8),
          Card(
            clipBehavior: Clip.antiAlias,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  InkWell(
                    onTap: _openMapFocus,
                    borderRadius: BorderRadius.circular(8),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Theme.of(context).colorScheme.primaryContainer.withOpacity(0.5),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Icon(Icons.map_outlined, size: 32, color: Theme.of(context).colorScheme.primary),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _markerPosition != null
                                    ? '${_markerPosition!.latitude.toStringAsFixed(5)}, ${_markerPosition!.longitude.toStringAsFixed(5)}'
                                    : 'Tap to set location',
                                style: Theme.of(context).textTheme.bodyMedium,
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Tap to open map',
                                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                  color: Theme.of(context).colorScheme.primary,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ],
                          ),
                        ),
                        Icon(Icons.chevron_right, color: Theme.of(context).colorScheme.onSurfaceVariant),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                  OutlinedButton.icon(
                    onPressed: _setCurrentLocation,
                    icon: const Icon(Icons.my_location, size: 20),
                    label: const Text('Get current location'),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _GpsMapFocusScreen extends StatefulWidget {
  const _GpsMapFocusScreen({
    required this.initialPosition,
    this.initialMarker,
  });

  final LatLng initialPosition;
  final LatLng? initialMarker;

  @override
  State<_GpsMapFocusScreen> createState() => _GpsMapFocusScreenState();
}

class _GpsMapFocusScreenState extends State<_GpsMapFocusScreen> {
  LatLng? _markerPosition;
  GoogleMapController? _mapController;

  @override
  void initState() {
    super.initState();
    _markerPosition = widget.initialMarker;
  }

  Future<void> _goToCurrentLocation() async {
    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Location services are disabled')),
        );
      }
      return;
    }
    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }
    if (permission == LocationPermission.denied || permission == LocationPermission.deniedForever) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Location permission denied')),
        );
      }
      return;
    }
    try {
      final pos = await Geolocator.getCurrentPosition();
      final latLng = LatLng(pos.latitude, pos.longitude);
      if (mounted) {
        setState(() => _markerPosition = latLng);
        _mapController?.animateCamera(CameraUpdate.newLatLngZoom(latLng, 14));
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Could not get location: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final markers = <Marker>{};
    if (_markerPosition != null) {
      markers.add(
        Marker(
          markerId: const MarkerId('gps'),
          position: _markerPosition!,
          draggable: true,
          onDragEnd: (pos) {
            setState(() => _markerPosition = pos);
          },
        ),
      );
    }
    return Scaffold(
      appBar: AppBar(
        title: const Text('Select location'),
        leading: IconButton(
          icon: const Icon(Icons.close),
          onPressed: () => Navigator.of(context).pop(),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.my_location),
            onPressed: _goToCurrentLocation,
            tooltip: 'Get current location',
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(_markerPosition ?? widget.initialPosition),
            child: const Text('Done'),
          ),
        ],
      ),
      body: Stack(
        children: [
          GoogleMap(
            initialCameraPosition: CameraPosition(
              target: widget.initialPosition,
              zoom: _markerPosition != null ? 14 : 10,
            ),
            markers: markers,
            mapType: MapType.normal,
            zoomControlsEnabled: true,
            zoomGesturesEnabled: true,
            scrollGesturesEnabled: true,
            rotateGesturesEnabled: true,
            tiltGesturesEnabled: true,
            liteModeEnabled: false,
            minMaxZoomPreference: const MinMaxZoomPreference(6, 18),
            cameraTargetBounds: CameraTargetBounds(_boundsFor(_markerPosition)),
            onTap: (pos) {
              setState(() => _markerPosition = pos);
            },
            onMapCreated: (c) {
              _mapController = c;
              if (_markerPosition != null) {
                c.animateCamera(CameraUpdate.newLatLngZoom(_markerPosition!, 14));
              }
            },
          ),
          Positioned(
            left: 16,
            right: 16,
            bottom: 16,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                OutlinedButton.icon(
                  onPressed: _goToCurrentLocation,
                  icon: const Icon(Icons.my_location, size: 20),
                  label: const Text('Get current location'),
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  ),
                ),
                const SizedBox(height: 8),
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(12),
                    child: Text(
                      _markerPosition != null
                          ? '${_markerPosition!.latitude.toStringAsFixed(5)}, ${_markerPosition!.longitude.toStringAsFixed(5)}'
                          : 'Tap map to set marker • Drag to pan • Pinch to zoom',
                      style: Theme.of(context).textTheme.bodySmall,
                      textAlign: TextAlign.center,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
