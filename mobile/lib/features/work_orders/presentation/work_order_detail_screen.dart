import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../domain/models/work_order.dart';
import '../data/work_order_repository.dart';
import 'work_order_providers.dart';
import 'work_order_form_screen.dart';

class WorkOrderDetailScreen extends ConsumerStatefulWidget {
  const WorkOrderDetailScreen({super.key, required this.workOrderId});

  final String workOrderId;

  @override
  ConsumerState<WorkOrderDetailScreen> createState() => _WorkOrderDetailState();
}

class _WorkOrderDetailState extends ConsumerState<WorkOrderDetailScreen> {
  WorkOrder? _wo;
  String? _directionsUrl;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    double? lat;
    double? lon;
    try {
      final pos = await Geolocator.getCurrentPosition();
      lat = pos.latitude;
      lon = pos.longitude;
    } catch (_) {}
    final repo = ref.read(workOrderRepositoryProvider);
    try {
      final wo = await repo.get(widget.workOrderId, currentLatitude: lat, currentLongitude: lon);
      final url = await repo.getDirectionsUrl(widget.workOrderId, latitude: lat, longitude: lon);
      setState(() {
        _wo = wo;
        _directionsUrl = url;
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  Future<void> _openDirections() async {
    if (_directionsUrl == null) return;
    final uri = Uri.tryParse(_directionsUrl!);
    if (uri != null && await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }
    if (_error != null || _wo == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Work Order')),
        body: Center(child: Text(_error ?? 'Not found')),
      );
    }
    final wo = _wo!;
    return Scaffold(
      appBar: AppBar(title: Text(wo.id)),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text('Status: ${wo.status}'),
            if (wo.project != null) Text('Project: ${wo.project!.name}'),
            if (wo.dueDate != null) Text('Due: ${wo.dueDate}'),
            if (wo.map != null) ...[
              const SizedBox(height: 8),
              if (wo.map!['distance'] != null)
                Text('Distance: ${wo.map!['distance']} km'),
              if (wo.map!['estimated_time'] != null)
                Text('Est. time: ${wo.map!['estimated_time']}'),
            ],
            const SizedBox(height: 16),
            if (_directionsUrl != null)
              FilledButton.icon(
                onPressed: _openDirections,
                icon: const Icon(Icons.directions),
                label: const Text('Open in Google Maps'),
              ),
            const SizedBox(height: 24),
            const Text('Forms', style: TextStyle(fontSize: 18)),
            if (wo.forms == null || wo.forms!.isEmpty)
              const Padding(
                padding: EdgeInsets.all(8),
                child: Text('No forms'),
              )
            else
              ...wo.forms!.map((f) => ListTile(
                    title: Text(f.name),
                    subtitle: Text('Version ${f.version ?? '—'}'),
                    onTap: () => Navigator.of(context).push(
                      MaterialPageRoute(
                        builder: (_) => WorkOrderFormScreen(
                          workOrderId: widget.workOrderId,
                          formId: f.id,
                        ),
                      ),
                    ),
                  )),
          ],
        ),
      ),
    );
  }
}
