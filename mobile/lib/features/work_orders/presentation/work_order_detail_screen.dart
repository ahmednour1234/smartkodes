import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../domain/models/work_order.dart';
import '../data/work_order_repository.dart';
import 'work_order_providers.dart';
import 'work_order_form_screen.dart';

Color _statusColor(String status) {
  final s = status.toLowerCase();
  if (s.contains('complete') || s == 'done') return Colors.green;
  if (s.contains('progress') || s.contains('active')) return Colors.blue;
  if (s.contains('pending') || s.contains('assigned')) return Colors.orange;
  if (s.contains('cancel')) return Colors.grey;
  return Colors.teal;
}

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
    final theme = Theme.of(context);
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
    final statusColor = _statusColor(wo.status);

    return Scaffold(
      appBar: AppBar(
        title: Text(wo.id),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(color: statusColor.withValues(alpha: 0.3), height: 3),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Card(
                elevation: 1,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                            decoration: BoxDecoration(
                              color: statusColor.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: statusColor.withValues(alpha: 0.5)),
                            ),
                            child: Text(
                              wo.status,
                              style: TextStyle(
                                color: statusColor,
                                fontWeight: FontWeight.w600,
                                fontSize: 13,
                              ),
                              overflow: TextOverflow.ellipsis,
                              maxLines: 1,
                            ),
                          ),
                          if (wo.priorityValue != null) ...[
                            Icon(Icons.flag_outlined, size: 18, color: theme.colorScheme.primary),
                            const SizedBox(width: 4),
                            Text('P${wo.priorityValue}', style: theme.textTheme.labelLarge),
                          ],
                        ],
                      ),
                      if (wo.project != null) ...[
                        const SizedBox(height: 12),
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Icon(Icons.folder_outlined, size: 20, color: theme.colorScheme.outline),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                wo.project!.name,
                                style: theme.textTheme.titleMedium,
                                overflow: TextOverflow.ellipsis,
                                maxLines: 3,
                              ),
                            ),
                          ],
                        ),
                      ],
                      if (wo.dueDate != null) ...[
                        const SizedBox(height: 8),
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Icon(Icons.event_outlined, size: 20, color: theme.colorScheme.outline),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                'Due ${wo.dueDate}',
                                style: theme.textTheme.bodyMedium,
                                overflow: TextOverflow.ellipsis,
                                maxLines: 2,
                              ),
                            ),
                          ],
                        ),
                      ],
                      if (wo.map != null && (wo.map!['distance'] != null || wo.map!['estimated_time'] != null)) ...[
                        const SizedBox(height: 12),
                        LayoutBuilder(
                          builder: (context, constraints) {
                            return Wrap(
                              spacing: 12,
                              runSpacing: 8,
                              children: [
                                if (wo.map!['distance'] != null)
                                  _InfoChip(
                                    icon: Icons.straighten,
                                    label: '${wo.map!['distance']} km',
                                    maxWidth: constraints.maxWidth,
                                  ),
                                if (wo.map!['estimated_time'] != null)
                                  _InfoChip(
                                    icon: Icons.schedule,
                                    label: '${wo.map!['estimated_time']}',
                                    maxWidth: constraints.maxWidth,
                                  ),
                              ],
                            );
                          },
                        ),
                      ],
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 12),
              if (_directionsUrl != null)
                Card(
                  elevation: 1,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  child: InkWell(
                    onTap: _openDirections,
                    borderRadius: BorderRadius.circular(12),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: Colors.green.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Icon(Icons.map_outlined, color: Colors.green, size: 28),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('Open in Google Maps', style: theme.textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w600)),
                                Text('Get directions', style: theme.textTheme.bodySmall?.copyWith(color: theme.colorScheme.outline)),
                              ],
                            ),
                          ),
                          Icon(Icons.arrow_forward_ios, size: 14, color: theme.colorScheme.outline),
                        ],
                      ),
                    ),
                  ),
                ),
              if (_directionsUrl != null) const SizedBox(height: 12),
              const SizedBox(height: 8),
              Text('Forms', style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),
              if (wo.forms == null || wo.forms!.isEmpty)
                Card(
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                    side: BorderSide(color: theme.colorScheme.outline.withValues(alpha: 0.3)),
                  ),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
                    child: Center(
                      child: Column(
                        children: [
                          Icon(Icons.assignment_outlined, size: 48, color: theme.colorScheme.outline.withValues(alpha: 0.6)),
                          const SizedBox(height: 8),
                          Text('No forms', style: theme.textTheme.bodyMedium?.copyWith(color: theme.colorScheme.outline)),
                        ],
                      ),
                    ),
                  ),
                )
              else
                ...wo.forms!.asMap().entries.map((e) {
                  final f = e.value;
                  final idx = e.key;
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 10),
                    child: _FormCard(
                      form: f,
                      onTap: () => Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => WorkOrderFormScreen(
                            workOrderId: widget.workOrderId,
                            formId: f.id,
                          ),
                        ),
                      ),
                    ),
                  );
                }),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  const _InfoChip({required this.icon, required this.label, this.maxWidth});

  final IconData icon;
  final String label;
  final double? maxWidth;

  @override
  Widget build(BuildContext context) {
    final text = Text(
      label,
      style: Theme.of(context).textTheme.bodySmall,
      overflow: TextOverflow.ellipsis,
      maxLines: 2,
    );
    final row = Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 18, color: Theme.of(context).colorScheme.outline),
        const SizedBox(width: 6),
        if (maxWidth != null) Flexible(child: text) else text,
      ],
    );
    if (maxWidth != null) {
      return ConstrainedBox(
        constraints: BoxConstraints(maxWidth: maxWidth!),
        child: row,
      );
    }
    return row;
  }
}

class _FormCard extends StatelessWidget {
  const _FormCard({required this.form, required this.onTap});

  final WorkOrderFormRef form;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: theme.colorScheme.primaryContainer.withValues(alpha: 0.6),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(Icons.edit_note, color: theme.colorScheme.onPrimaryContainer, size: 26),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(form.name, style: theme.textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w600)),
                    const SizedBox(height: 2),
                    Text(
                      'Version ${form.version ?? '—'}',
                      style: theme.textTheme.bodySmall?.copyWith(color: theme.colorScheme.outline),
                    ),
                  ],
                ),
              ),
              Icon(Icons.chevron_right, color: theme.colorScheme.outline),
            ],
          ),
        ),
      ),
    );
  }
}
