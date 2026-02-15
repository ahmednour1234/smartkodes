import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';

/// Lebanon approximate bounds (restrict map to Lebanon only).
final LatLng _lebanonSw = const LatLng(33.05, 35.1);
final LatLng _lebanonNe = const LatLng(34.69, 36.6);
final LatLng _lebanonCenter = const LatLng(33.9, 35.9);

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

  @override
  Widget build(BuildContext context) {
    _ensureParsed();
    final initialPosition = _markerPosition ?? _lebanonCenter;
    final markers = <Marker>{};
    if (_markerPosition != null) {
      markers.add(
        Marker(
          markerId: const MarkerId('gps'),
          position: _markerPosition!,
          draggable: true,
          onDragEnd: (pos) {
            widget.onChanged('${pos.latitude},${pos.longitude}');
            setState(() => _markerPosition = pos);
          },
        ),
      );
    }
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
            child: SizedBox(
              height: 220,
              child: Stack(
                children: [
                  GoogleMap(
                    initialCameraPosition: CameraPosition(target: initialPosition, zoom: _markerPosition != null ? 12 : 8),
                    markers: markers,
                    mapType: MapType.normal,
                    zoomControlsEnabled: true,
                    zoomGesturesEnabled: true,
                    scrollGesturesEnabled: true,
                    liteModeEnabled: false,
                    minMaxZoomPreference: const MinMaxZoomPreference(6, 18),
                    cameraTargetBounds: CameraTargetBounds(LatLngBounds(southwest: _lebanonSw, northeast: _lebanonNe)),
                    onTap: (pos) {
                      setState(() => _markerPosition = pos);
                      widget.onChanged('${pos.latitude},${pos.longitude}');
                    },
                    onMapCreated: (c) {
                      if (_markerPosition != null) {
                        c.animateCamera(CameraUpdate.newLatLngZoom(_markerPosition!, 12));
                      }
                    },
                  ),
                  Positioned(
                    left: 12,
                    bottom: 12,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(8),
                        boxShadow: const [BoxShadow(color: Colors.black26, blurRadius: 4)],
                      ),
                      child: Text(
                        _markerPosition != null
                            ? '${_markerPosition!.latitude.toStringAsFixed(5)}, ${_markerPosition!.longitude.toStringAsFixed(5)}'
                            : 'Tap map to set location',
                        style: Theme.of(context).textTheme.bodySmall,
                      ),
                    ),
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
