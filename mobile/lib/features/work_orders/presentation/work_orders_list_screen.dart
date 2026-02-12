import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';

import '../../../core/api/api_response.dart';
import '../../../domain/models/work_order.dart';
import '../../auth/presentation/auth_providers.dart';
import '../../forms/presentation/forms_list_screen.dart';
import '../../notifications/presentation/notifications_list_screen.dart';
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
  String _sortBy = 'distance';
  String _sortOrder = 'asc';
  double? _lat;
  double? _lon;
  double _radius = 50;
  bool _useNearby = false;
  bool _isListView = true;

  Future<void> _fetchLocation() async {
    final ok = await Geolocator.checkPermission();
    if (ok == LocationPermission.denied) {
      await Geolocator.requestPermission();
    }
    final pos = await Geolocator.getCurrentPosition();
    setState(() {
      _lat = pos.latitude;
      _lon = pos.longitude;
    });
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
          IconButton(
            icon: const Icon(Icons.home_outlined),
            onPressed: () => Navigator.of(context).popUntil((r) => r.isFirst),
            tooltip: 'Home',
          ),
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: () => _showFilters(context),
          ),
        ],
      ),
      drawer: Drawer(
        child: ListView(
          children: [
            const DrawerHeader(
              child: Text('SmartKodes', style: TextStyle(color: Colors.white, fontSize: 20)),
            ),
            ListTile(
              leading: const Icon(Icons.assignment),
              title: const Text('Work Orders'),
              onTap: () => Navigator.pop(context),
            ),
            ListTile(
              leading: const Icon(Icons.description),
              title: const Text('Forms'),
              onTap: () {
                Navigator.pop(context);
                Navigator.of(context).pushReplacement(
                  MaterialPageRoute(builder: (_) => const FormsListScreen()),
                );
              },
            ),
            ListTile(
              leading: const Icon(Icons.notifications),
              title: const Text('Notifications'),
              onTap: () {
                Navigator.pop(context);
                Navigator.of(context).pushReplacement(
                  MaterialPageRoute(builder: (_) => const NotificationsListScreen()),
                );
              },
            ),
            const Divider(),
            ListTile(
              leading: const Icon(Icons.logout),
              title: const Text('Log out'),
              onTap: () {
                Navigator.pop(context);
                ref.read(authStateProvider.notifier).logout();
              },
            ),
          ],
        ),
      ),
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
            return Center(child: Text('Error: ${snapshot.error}'));
          }
          final res = snapshot.data;
          if (res == null || res.data.isEmpty) {
            return const Center(child: Text('No work orders'));
          }
          if (_isListView) {
            return _buildListView(context, res.data);
          }
          return _buildMapView(context, res.data);
        },
      ),
    );
  }

  Widget _buildListView(BuildContext context, List<WorkOrder> list) {
    final highCount = list.where((wo) {
      final p = wo.priorityValue ?? wo.importanceLevel;
      return p == 1;
    }).length;
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
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: Row(
            children: [
              FilterChip(
                label: const Text('Sort By Distance'),
                selected: _sortBy == 'distance',
                onSelected: (_) => setState(() => _sortBy = 'distance'),
              ),
              const SizedBox(width: 8),
              FilterChip(
                label: const Text('Sort By Priority'),
                selected: _sortBy == 'priority',
                onSelected: (_) => setState(() => _sortBy = 'priority'),
              ),
            ],
          ),
        ),
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            itemCount: list.length,
            itemBuilder: (context, i) {
              final wo = list[i];
              final formName = wo.forms?.isNotEmpty == true ? wo.forms!.first.name : '—';
              final priorityStr = _priorityLabel(wo);
              final dist = wo.distance != null ? '${wo.distance} ${wo.distanceUnit ?? 'km'}' : '—';
              final priorityColor = _priorityColor(wo);
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
                                wo.id,
                                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                  fontWeight: FontWeight.w600,
                                ),
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 4),
                              Text(
                                formName,
                                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                  color: Theme.of(context).colorScheme.onSurfaceVariant,
                                ),
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 8),
                              Row(
                                children: [
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
                                  const SizedBox(width: 12),
                                  Icon(Icons.near_me, size: 16, color: Theme.of(context).colorScheme.onSurfaceVariant),
                                  const SizedBox(width: 4),
                                  Text(
                                    dist,
                                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                      color: Theme.of(context).colorScheme.onSurfaceVariant,
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

  Widget _buildMapView(BuildContext context, List<WorkOrder> list) {
    if (!_isListView && _lat == null) {
      _fetchLocation();
    }
    final woWithLocation = list.where((wo) => wo.location?.latitude != null && wo.location?.longitude != null).toList();
    LatLng? center;
    if (_lat != null && _lon != null) {
      center = LatLng(_lat!, _lon!);
    } else if (woWithLocation.isNotEmpty) {
      final loc = woWithLocation.first.location!;
      center = LatLng(loc.latitude!, loc.longitude!);
    }
    if (center == null) {
      return const Center(child: Text('No location data. Enable location for map.'));
    }
    final markers = <Marker>{};
    if (_lat != null && _lon != null) {
      markers.add(
        Marker(
          markerId: const MarkerId('worker'),
          position: LatLng(_lat!, _lon!),
          icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueBlue),
        ),
      );
    }
    for (var i = 0; i < woWithLocation.length; i++) {
      final wo = woWithLocation[i];
      final loc = wo.location!;
      final pos = LatLng(loc.latitude!, loc.longitude!);
      markers.add(
        Marker(
          markerId: MarkerId(wo.id),
          position: pos,
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
    return GoogleMap(
      initialCameraPosition: CameraPosition(target: center, zoom: 12),
      markers: markers,
      myLocationEnabled: true,
      myLocationButtonEnabled: true,
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
