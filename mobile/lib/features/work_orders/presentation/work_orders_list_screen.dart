import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';

import '../../../core/api/api_response.dart';
import '../../../domain/models/work_order.dart';
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Work Orders'),
        actions: [
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
          return ListView.builder(
            itemCount: res.data.length,
            itemBuilder: (context, i) {
              final wo = res.data[i];
              final formName = wo.forms?.isNotEmpty == true
                  ? wo.forms!.first.name
                  : '—';
              return ListTile(
                title: Text(wo.id),
                subtitle: Text('$formName • ${wo.status}${wo.distance != null ? ' • ${wo.distance} ${wo.distanceUnit ?? 'km'}' : ''}'),
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (_) => WorkOrderDetailScreen(workOrderId: wo.id),
                  ),
                ),
              );
            },
          );
        },
      ),
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
