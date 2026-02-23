import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';

import '../../../core/api/api_response.dart';
import '../../../core/widgets/app_drawer.dart';
import '../../../core/widgets/no_connection_widget.dart';
import '../../../domain/models/work_order.dart';
import '../data/work_order_repository.dart';
import 'work_order_providers.dart';
import 'work_order_detail_screen.dart';

class WorkOrdersListScreen extends ConsumerStatefulWidget {
  const WorkOrdersListScreen({super.key});

  @override
  ConsumerState<WorkOrdersListScreen> createState() => _WorkOrdersListState();
}

class _WorkOrdersListState extends ConsumerState<WorkOrdersListScreen> {
  int? _priorityFilter;
  String? _statusFilter;
  String? _projectFilter;
  String _sortBy = 'distance';
  String _sortOrder = 'asc';
  double? _lat;
  double? _lon;
  double _radius = 50;
  bool _useNearby = false;
  bool _isListView = true;
  bool _locationRequesting = false;
  bool _locationDenied = false;

  Future<void> _fetchLocation() async {
    if (_locationRequesting) return;
    _locationRequesting = true;
    setState(() {});
    try {
      LocationPermission ok = await Geolocator.checkPermission();
      if (ok == LocationPermission.denied) {
        ok = await Geolocator.requestPermission();
      }
      if (ok == LocationPermission.denied || ok == LocationPermission.deniedForever) {
        if (mounted) setState(() => _locationDenied = true);
        return;
      }
      final pos = await Geolocator.getCurrentPosition();
      if (mounted) setState(() {
        _lat = pos.latitude;
        _lon = pos.longitude;
      });
    } catch (_) {
      if (mounted) setState(() => _locationDenied = true);
    } finally {
      _locationRequesting = false;
      if (mounted) setState(() {});
    }
  }

  static String _priorityLabel(WorkOrder wo) {
    final p = wo.priorityValue ?? wo.importanceLevel;
    if (p == null) return '—';
    switch (p) {
      case 1: return 'High';
      case 2: return 'Medium';
      case 3: return 'Low';
      default: return '—';
    }
  }

  static double _priorityHue(WorkOrder wo) {
    final p = wo.priorityValue ?? wo.importanceLevel;
    if (p == null || p == 3) return BitmapDescriptor.hueGreen;
    if (p == 1) return BitmapDescriptor.hueRed;
    return BitmapDescriptor.hueOrange;
  }

  static Color _priorityColor(WorkOrder wo) {
    final p = wo.priorityValue ?? wo.importanceLevel;
    if (p == 1) return Colors.red.shade700;
    if (p == 2) return Colors.orange.shade700;
    return Colors.green.shade700;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Work Orders'),
        actions: [
          IconButton(
            icon: Icon(_isListView ? Icons.map_outlined : Icons.list),
            onPressed: () => setState(() => _isListView = !_isListView),
            tooltip: _isListView ? 'Map view' : 'List view',
          ),
        ],
      ),
      drawer: const AppDrawer(),
      body: FutureBuilder<PaginatedResponse<WorkOrder>>(
        future: ref.read(workOrderRepositoryProvider).list(
              priority: _priorityFilter,
              sortBy: _sortBy,
              sortOrder: _sortOrder,
              latitude: _useNearby ? _lat : null,
              longitude: _useNearby ? _lon : null,
              radius: _useNearby ? _radius : null,
            ),
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snapshot.hasError) {
            if (isConnectionError(snapshot.error)) {
              return NoConnectionWidget(onRetry: () => setState(() {}));
            }
            return Center(child: Text('Error: ${snapshot.error}'));
          }
          final res = snapshot.data;
          if (res == null || res.data.isEmpty) {
            return const Center(child: Text('No work orders'));
          }
          final list = res.data.where((wo) {
            if (_statusFilter != null && wo.status != _statusFilter) return false;
            if (_projectFilter != null && wo.project?.id != _projectFilter) return false;
            return true;
          }).toList();
          if (_isListView) {
            return _buildListView(context, res.data, list);
          }
          return _buildMapView(context, list);
        },
      ),
    );
  }

  Widget _buildListView(BuildContext context, List<WorkOrder> fullList, List<WorkOrder> list) {
    final highCount = list.where((wo) {
      final p = wo.priorityValue ?? wo.importanceLevel;
      return p == 1;
    }).length;
    final statusSet = <String>{'Assigned', 'In Progress'};
    for (final wo in fullList) {
      if (wo.status.isNotEmpty) statusSet.add(wo.status);
    }
    final statuses = statusSet.toList()..sort();
    final projects = <WorkOrderProject>[];
    final seenIds = <String>{};
    for (final wo in fullList) {
      if (wo.project != null && seenIds.add(wo.project!.id)) projects.add(wo.project!);
    }
    projects.sort((a, b) => a.name.compareTo(b.name));
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
          child: Row(
            children: [
              Expanded(
                child: _statChip(
                  context,
                  'High priority',
                  highCount.toString(),
                  Colors.red.shade700,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _statChip(
                  context,
                  'Total work orders',
                  list.length.toString(),
                  Theme.of(context).colorScheme.primary,
                ),
              ),
            ],
          ),
        ),
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 4),
          child: Text(
            'My Orders',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w600),
          ),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Theme.of(context).colorScheme.surfaceContainerLow,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: Theme.of(context).colorScheme.outlineVariant.withOpacity(0.5),
                width: 1,
              ),
              boxShadow: [
                BoxShadow(
                  color: Theme.of(context).colorScheme.shadow.withOpacity(0.04),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  children: [
                    Icon(Icons.tune_rounded, size: 18, color: Theme.of(context).colorScheme.primary),
                    const SizedBox(width: 6),
                    Text(
                      'Filters',
                      style: Theme.of(context).textTheme.labelLarge?.copyWith(
                        color: Theme.of(context).colorScheme.primary,
                        fontWeight: FontWeight.w600,
                        letterSpacing: 0.2,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: _FilterChipDropdown<String?>(
                        value: _statusFilter,
                        label: 'Status',
                        hint: 'All statuses',
                        icon: Icons.flag_rounded,
                        items: [const DropdownMenuItem(value: null, child: Text('All'))]
                          ..addAll(statuses.map((s) => DropdownMenuItem(value: s, child: Text(s, overflow: TextOverflow.ellipsis)))),
                        onChanged: (v) => setState(() => _statusFilter = v),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: _FilterChipDropdown<String?>(
                        value: _projectFilter,
                        label: 'Project',
                        hint: 'All projects',
                        icon: Icons.folder_rounded,
                        items: [const DropdownMenuItem(value: null, child: Text('All'))]
                          ..addAll(projects.map((p) => DropdownMenuItem(value: p.id, child: Text(p.name, overflow: TextOverflow.ellipsis)))),
                        onChanged: (v) => setState(() => _projectFilter = v),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 0, 16, 8),
          child: Text(
            'Sort',
            style: Theme.of(context).textTheme.labelLarge?.copyWith(
              color: Theme.of(context).colorScheme.onSurfaceVariant,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: Row(
            children: [
              _SortChip(
                label: 'Distance',
                icon: Icons.near_me,
                selected: _sortBy == 'distance',
                onTap: () => setState(() => _sortBy = 'distance'),
              ),
              const SizedBox(width: 8),
              _SortChip(
                label: 'Priority',
                icon: Icons.low_priority_rounded,
                selected: _sortBy == 'priority',
                onTap: () => setState(() => _sortBy = 'priority'),
              ),
            ],
          ),
        ),
        Expanded(
          child: RefreshIndicator(
            onRefresh: () async => setState(() {}),
            child: ListView.builder(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
              itemCount: list.length,
            itemBuilder: (context, i) {
              final wo = list[i];
              final formName = wo.forms?.isNotEmpty == true ? wo.forms!.first.name : '—';
              final priorityStr = _priorityLabel(wo);
              final priorityColor = _priorityColor(wo);
              final formTotal = wo.forms?.length ?? 0;
              final submittedCount = wo.recordsCount ?? 0;
              final allSubmitted = formTotal > 0 && submittedCount >= formTotal;
              return Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Material(
                color: Theme.of(context).colorScheme.surfaceContainerLow,
                borderRadius: BorderRadius.circular(12),
                elevation: 0,
                child: InkWell(
                  onTap: () => Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => WorkOrderDetailScreen(workOrderId: wo.id),
                    ),
                  ),
                  borderRadius: BorderRadius.circular(12),
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                wo.title?.isNotEmpty == true ? wo.title! : wo.id,
                                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                  fontWeight: FontWeight.w600,
                                ),
                                overflow: TextOverflow.ellipsis,
                              ),
                              if (wo.title?.isNotEmpty == true)
                                Padding(
                                  padding: const EdgeInsets.only(top: 2),
                                  child: Text(
                                    wo.id,
                                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                      color: Theme.of(context).colorScheme.outline,
                                    ),
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              const SizedBox(height: 4),
                              Text(
                                formName,
                                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                  color: Theme.of(context).colorScheme.onSurfaceVariant,
                                ),
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 6),
                              Row(
                                children: [
                                  Icon(Icons.description_outlined, size: 14, color: Theme.of(context).colorScheme.onSurfaceVariant),
                                  const SizedBox(width: 4),
                                  Text(
                                    '$submittedCount/${formTotal} forms',
                                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                      color: Theme.of(context).colorScheme.onSurfaceVariant,
                                    ),
                                  ),
                                  if (allSubmitted) ...[
                                    const SizedBox(width: 8),
                                    Icon(Icons.check_circle, size: 18, color: Colors.green.shade700),
                                    const SizedBox(width: 4),
                                    Text(
                                      'Submitted',
                                      style: Theme.of(context).textTheme.labelSmall?.copyWith(
                                        color: Colors.green.shade700,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                              const SizedBox(height: 8),
                              Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: Theme.of(context).colorScheme.primaryContainer.withOpacity(0.6),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Text(
                                      wo.status,
                                      style: Theme.of(context).textTheme.labelMedium?.copyWith(
                                        color: Theme.of(context).colorScheme.onPrimaryContainer,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: priorityColor.withOpacity(0.15),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Text(
                                      priorityStr,
                                      style: Theme.of(context).textTheme.labelMedium?.copyWith(
                                        color: priorityColor,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                        Icon(Icons.chevron_right, color: Theme.of(context).colorScheme.onSurfaceVariant),
                      ],
                    ),
                  ),
                ),
              ),
            );
            },
            ),
          ),
        ),
      ],
    );
  }

  Widget _statChip(BuildContext context, String label, String value, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color: Theme.of(context).colorScheme.onSurfaceVariant,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            value,
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.w700,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  static bool _isValidLatLon(double? lat, double? lon) {
    if (lat == null || lon == null) return false;
    return lat >= -90 && lat <= 90 && lon >= -180 && lon <= 180 &&
        (lat != 0 || lon != 0);
  }

  Widget _buildMapView(BuildContext context, List<WorkOrder> list) {
    if (!_locationDenied && _lat == null && !_locationRequesting) {
      WidgetsBinding.instance.addPostFrameCallback((_) => _fetchLocation());
    }
    if (_lat == null && !_locationDenied && _locationRequesting) {
      return const Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            CircularProgressIndicator(),
            SizedBox(height: 16),
            Text('Requesting location permission...'),
          ],
        ),
      );
    }
    final woWithLocation = list.where((wo) =>
        _isValidLatLon(wo.location?.latitude, wo.location?.longitude)).toList();
    final hasUserLocation = _lat != null && _lon != null &&
        _isValidLatLon(_lat, _lon);
    if (!hasUserLocation && !_locationDenied) {
      return const Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            CircularProgressIndicator(),
            SizedBox(height: 16),
            Text('Getting your location...'),
          ],
        ),
      );
    }
    if (!hasUserLocation && woWithLocation.isEmpty) {
      return const Center(
        child: Text('No location data. Enable location for map.'),
      );
    }
    LatLng center;
    if (hasUserLocation) {
      center = LatLng(_lat!, _lon!);
    } else {
      final loc = woWithLocation.first.location!;
      center = LatLng(loc.latitude!, loc.longitude!);
    }
    final markers = <Marker>{};
    if (hasUserLocation) {
      markers.add(
        Marker(
          markerId: const MarkerId('worker'),
          position: LatLng(_lat!, _lon!),
          icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueBlue),
        ),
      );
    }
    for (final wo in woWithLocation) {
      final loc = wo.location!;
      markers.add(
        Marker(
          markerId: MarkerId(wo.id),
          position: LatLng(loc.latitude!, loc.longitude!),
          icon: BitmapDescriptor.defaultMarkerWithHue(_priorityHue(wo)),
          onTap: () => Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => WorkOrderDetailScreen(workOrderId: wo.id),
            ),
          ),
        ),
      );
    }
    if (kIsWeb) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.map_outlined, size: 64, color: Theme.of(context).colorScheme.primary),
              const SizedBox(height: 16),
              Text(
                'Map view is available on Android and iOS.',
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.bodyLarge,
              ),
            ],
          ),
        ),
      );
    }
    return Stack(
      children: [
        GoogleMap(
          key: ValueKey('map_${center.latitude}_${center.longitude}_${markers.length}'),
          initialCameraPosition: CameraPosition(target: center, zoom: 15),
          markers: markers,
          myLocationEnabled: hasUserLocation,
          myLocationButtonEnabled: true,
          onMapCreated: (controller) {
        if (markers.length <= 1) {
          controller.animateCamera(
            CameraUpdate.newLatLngZoom(center, 15),
          );
          return;
        }
        double minLat = markers.first.position.latitude;
        double maxLat = minLat;
        double minLon = markers.first.position.longitude;
        double maxLon = minLon;
        for (final m in markers) {
          final p = m.position;
          if (p.latitude < minLat) minLat = p.latitude;
          if (p.latitude > maxLat) maxLat = p.latitude;
          if (p.longitude < minLon) minLon = p.longitude;
          if (p.longitude > maxLon) maxLon = p.longitude;
        }
        controller.animateCamera(
          CameraUpdate.newLatLngBounds(
            LatLngBounds(
              southwest: LatLng(minLat, minLon),
              northeast: LatLng(maxLat, maxLon),
            ),
            80,
          ),
        );
      },
        ),
        Positioned(
          left: 0,
          right: 0,
          bottom: 0,
          child: SafeArea(
            top: false,
            child: Padding(
              padding: const EdgeInsets.fromLTRB(12, 0, 12, 12),
              child: Card(
                elevation: 4,
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _LegendItem(color: Colors.blue, label: 'Your location'),
                      _LegendItem(color: Colors.red, label: 'High priority'),
                      _LegendItem(color: Colors.orange, label: 'Medium'),
                      _LegendItem(color: Colors.green, label: 'Low priority'),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }

  void _showFilters(BuildContext context) {
    showModalBottomSheet(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) {
          return Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Text('Filters', style: TextStyle(fontSize: 18)),
                const SizedBox(height: 8),
                DropdownButton<int?>(
                  value: _priorityFilter,
                  isExpanded: true,
                  hint: const Text('Priority'),
                  items: [
                    const DropdownMenuItem(value: null, child: Text('Any')),
                    const DropdownMenuItem(value: 1, child: Text('1')),
                    const DropdownMenuItem(value: 2, child: Text('2')),
                    const DropdownMenuItem(value: 3, child: Text('3')),
                  ],
                  onChanged: (v) => setModalState(() => _priorityFilter = v),
                ),
                DropdownButton<String>(
                  value: _sortBy,
                  isExpanded: true,
                  items: const [
                    DropdownMenuItem(value: 'distance', child: Text('Distance')),
                    DropdownMenuItem(value: 'priority', child: Text('Priority')),
                    DropdownMenuItem(value: 'due_date', child: Text('Due date')),
                  ],
                  onChanged: (v) => setModalState(() => _sortBy = v ?? 'distance'),
                ),
                DropdownButton<String>(
                  value: _sortOrder,
                  isExpanded: true,
                  items: const [
                    DropdownMenuItem(value: 'asc', child: Text('Asc')),
                    DropdownMenuItem(value: 'desc', child: Text('Desc')),
                  ],
                  onChanged: (v) => setModalState(() => _sortOrder = v ?? 'asc'),
                ),
                CheckboxListTile(
                  title: const Text('Nearby (use my location)'),
                  value: _useNearby,
                  onChanged: (v) {
                    setModalState(() => _useNearby = v ?? false);
                    if (_useNearby && _lat == null) _fetchLocation();
                  },
                ),
                FilledButton(
                  onPressed: () {
                    setState(() {});
                    Navigator.pop(ctx);
                  },
                  child: const Text('Apply'),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _FilterChipDropdown<T> extends StatelessWidget {
  const _FilterChipDropdown({
    required this.value,
    required this.label,
    required this.hint,
    required this.icon,
    required this.items,
    required this.onChanged,
  });

  final T? value;
  final String label;
  final String hint;
  final IconData icon;
  final List<DropdownMenuItem<T>> items;
  final ValueChanged<T?> onChanged;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisSize: MainAxisSize.min,
      children: [
        Row(
          children: [
            Icon(icon, size: 14, color: theme.colorScheme.onSurfaceVariant),
            const SizedBox(width: 4),
            Text(
              label,
              style: theme.textTheme.labelSmall?.copyWith(
                color: theme.colorScheme.onSurfaceVariant,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
        const SizedBox(height: 6),
        Container(
          decoration: BoxDecoration(
            color: theme.colorScheme.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: theme.colorScheme.outlineVariant.withOpacity(0.6)),
          ),
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 2),
          child: DropdownButtonHideUnderline(
            child: DropdownButton<T>(
              value: value,
              isExpanded: true,
              isDense: true,
              hint: Text(hint, style: theme.textTheme.bodyMedium?.copyWith(color: theme.colorScheme.onSurfaceVariant)),
              items: items,
              onChanged: onChanged,
            ),
          ),
        ),
      ],
    );
  }
}

class _SortChip extends StatelessWidget {
  const _SortChip({required this.label, required this.icon, required this.selected, required this.onTap});

  final String label;
  final IconData icon;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Material(
      color: selected
          ? theme.colorScheme.primaryContainer.withOpacity(0.5)
          : theme.colorScheme.surfaceContainerHighest,
      borderRadius: BorderRadius.circular(12),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                icon,
                size: 18,
                color: selected ? theme.colorScheme.primary : theme.colorScheme.onSurfaceVariant,
              ),
              const SizedBox(width: 8),
              Text(
                label,
                style: theme.textTheme.labelLarge?.copyWith(
                  fontWeight: FontWeight.w600,
                  color: selected ? theme.colorScheme.primary : theme.colorScheme.onSurfaceVariant,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _LegendItem extends StatelessWidget {
  const _LegendItem({required this.color, required this.label});

  final Color color;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 12,
          height: 12,
          decoration: BoxDecoration(
            color: color,
            shape: BoxShape.circle,
            border: Border.all(color: Colors.white, width: 1),
            boxShadow: const [BoxShadow(color: Colors.black26, blurRadius: 2)],
          ),
        ),
        const SizedBox(width: 6),
        Text(label, style: Theme.of(context).textTheme.bodySmall),
      ],
    );
  }
}
