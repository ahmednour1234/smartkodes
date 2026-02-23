import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/navigation/route_observer.dart';
import '../../../core/widgets/no_connection_widget.dart';
import '../../../data/local/form_draft_store.dart';
import '../../../domain/models/record_model.dart';
import '../../../domain/models/work_order.dart';
import '../../forms/data/forms_repository.dart';
import '../../forms/presentation/form_update_record_screen.dart';
import '../../forms/presentation/forms_providers.dart';
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

String _priorityLabel(int? p) {
  if (p == null) return '—';
  switch (p) {
    case 1: return 'High';
    case 2: return 'Medium';
    case 3: return 'Low';
    default: return '—';
  }
}

Color _priorityColor(int? p) {
  if (p == null) return Colors.grey;
  switch (p) {
    case 1: return Colors.red.shade700;
    case 2: return Colors.orange.shade700;
    case 3: return Colors.green.shade700;
    default: return Colors.grey;
  }
}

String _slaLabel(WorkOrder wo) {
  if (wo.sla != null && wo.sla!.isNotEmpty) return wo.sla!;
  final v = wo.priorityValue;
  final u = wo.priorityUnit?.trim();
  if (v == null || u == null || u.isEmpty) return '—';
  final cap = u.length > 1 ? '${u[0].toUpperCase()}${u.substring(1).toLowerCase()}' : u.toUpperCase();
  final plural = (v != 1 && cap.isNotEmpty) ? '${cap}s' : cap;
  return '$v $plural';
}

class WorkOrderDetailScreen extends ConsumerStatefulWidget {
  const WorkOrderDetailScreen({super.key, required this.workOrderId});

  final String workOrderId;

  @override
  ConsumerState<WorkOrderDetailScreen> createState() => _WorkOrderDetailState();
}

class _WorkOrderDetailState extends ConsumerState<WorkOrderDetailScreen> with RouteAware {
  WorkOrder? _wo;
  List<RecordModel>? _myRecords;
  String? _directionsUrl;
  bool _loading = true;
  String? _error;
  String? _openingFormId;
  final Set<String> _clearedDraftKeys = {};
  List<String>? _draftKeys;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final route = ModalRoute.of(context);
    if (route is PageRoute && route.isCurrent) {
      appRouteObserver.subscribe(this, route);
    }
  }

  @override
  void dispose() {
    appRouteObserver.unsubscribe(this);
    super.dispose();
  }

  @override
  void didPopNext() {
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
    final formsRepo = ref.read(formsRepositoryProvider);
    try {
      final wo = await repo.get(widget.workOrderId, currentLatitude: lat, currentLongitude: lon);
      final url = await repo.getDirectionsUrl(widget.workOrderId, latitude: lat, longitude: lon);
      final recordsRes = await formsRepo.listMyRecords(workOrderId: widget.workOrderId, perPage: 100);
      final draftKeys = await ref.read(formDraftStoreProvider).listDraftKeys();
      if (!mounted) return;
      setState(() {
        _wo = wo;
        _myRecords = recordsRes.data;
        _directionsUrl = url;
        _draftKeys = draftKeys;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
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
        body: isConnectionError(_error)
            ? NoConnectionWidget(onRetry: () {
                setState(() => _loading = true);
                _error = null;
                _load();
              })
            : Center(child: Text(_error ?? 'Not found')),
      );
    }
    final wo = _wo!;
    final statusColor = _statusColor(wo.status);

    return Scaffold(
      appBar: AppBar(
        title: Text(wo.title?.isNotEmpty == true ? wo.title! : wo.id),
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
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      _DetailRow(
                        label: 'Project',
                        value: wo.project?.name ?? '—',
                        icon: Icons.folder_rounded,
                        color: theme.colorScheme.primary,
                      ),
                      const Divider(height: 24),
                      _DetailRow(
                        label: 'Due Date',
                        value: wo.dueDate ?? '—',
                        icon: Icons.calendar_today_rounded,
                        color: Colors.orange.shade700,
                      ),
                      const Divider(height: 24),
                      _DetailRow(
                        label: 'SLA',
                        value: _slaLabel(wo),
                        icon: Icons.schedule_rounded,
                        color: Colors.indigo.shade600,
                      ),
                      const Divider(height: 24),
                      _DetailRow(
                        label: 'Priority',
                        value: _priorityLabel(wo.priorityValue ?? wo.importanceLevel),
                        icon: Icons.flag_rounded,
                        color: _priorityColor(wo.priorityValue ?? wo.importanceLevel),
                      ),
                    ],
                  ),
                ),
              ),
              if (wo.description != null && wo.description!.trim().isNotEmpty) ...[
                const SizedBox(height: 12),
                Card(
                  elevation: 1,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Icon(Icons.notes_rounded, size: 18, color: theme.colorScheme.primary),
                            const SizedBox(width: 8),
                            Text(
                              'Description',
                              style: theme.textTheme.titleSmall?.copyWith(
                                fontWeight: FontWeight.w600,
                                color: theme.colorScheme.primary,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(
                          wo.description!,
                          style: theme.textTheme.bodyMedium?.copyWith(
                            color: theme.colorScheme.onSurface,
                            height: 1.4,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
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
                  RecordModel? record;
                  if (_myRecords != null) {
                    for (final r in _myRecords!) {
                      if (r.form?.id == f.id) {
                        record = r;
                        break;
                      }
                    }
                  }
                  final submissionKey = 'submission_${widget.workOrderId}_${f.id}';
                  final updateKey = record != null ? 'update_${f.id}_${record.id}' : null;
                  final draftKeys = _draftKeys ?? [];
                  final subKeySanitized = FormDraftStore.sanitizeKey(submissionKey);
                  final updKeySanitized = updateKey != null ? FormDraftStore.sanitizeKey(updateKey!) : null;
                  final hasDraft = !_clearedDraftKeys.contains(subKeySanitized) && !(updKeySanitized != null && _clearedDraftKeys.contains(updKeySanitized)) &&
                      (draftKeys.contains(subKeySanitized) || (updateKey != null && draftKeys.contains(updKeySanitized)));
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 10),
                    child: _FormCard(
                      form: f,
                      isSubmitted: record != null,
                      hasDraft: hasDraft,
                      isLoading: _openingFormId == f.id,
                      onTap: () async {
                        if (_openingFormId != null) return;
                        setState(() => _openingFormId = f.id);
                        final rec = record;
                        try {
                          if (rec != null && rec.form != null) {
                            final formModel = await ref.read(formsRepositoryProvider).get(f.id);
                            if (!mounted) return;
                            setState(() => _openingFormId = null);
                            if (formModel == null) return;
                            Navigator.of(context).push(
                              MaterialPageRoute(
                                builder: (_) => FormUpdateRecordScreen(
                                  formId: f.id,
                                  recordId: rec.id,
                                  form: formModel,
                                  initialFields: rec.fields,
                                ),
                              ),
                            ).then((result) async {
                              if (result != null && result is Map) {
                                final key = result['draftKey'] as String?;
                                if (key != null) {
                                  final sk = FormDraftStore.sanitizeKey(key);
                                  setState(() => result['cleared'] == true ? _clearedDraftKeys.add(sk) : _clearedDraftKeys.remove(sk));
                                }
                              }
                              ref.invalidate(draftKeysProvider);
                              ref.read(draftKeysRefreshTriggerProvider.notifier).update((s) => s + 1);
                              _draftKeys = await ref.read(formDraftStoreProvider).listDraftKeys();
                              if (mounted) setState(() {});
                              _load();
                            });
                          } else {
                            setState(() => _openingFormId = null);
                            Navigator.of(context).push(
                              MaterialPageRoute(
                                builder: (_) => WorkOrderFormScreen(
                                  workOrderId: widget.workOrderId,
                                  formId: f.id,
                                ),
                              ),
                            ).then((_) async {
                              ref.invalidate(draftKeysProvider);
                              ref.read(draftKeysRefreshTriggerProvider.notifier).update((s) => s + 1);
                              _draftKeys = await ref.read(formDraftStoreProvider).listDraftKeys();
                              if (mounted) setState(() {});
                              _load();
                            });
                          }
                        } catch (_) {
                          if (mounted) setState(() => _openingFormId = null);
                        }
                      },
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

class _DetailRow extends StatelessWidget {
  const _DetailRow({
    required this.label,
    required this.value,
    required this.icon,
    required this.color,
  });

  final String label;
  final String value;
  final IconData icon;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.12),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, size: 20, color: color),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: theme.textTheme.labelSmall?.copyWith(
                  color: theme.colorScheme.onSurfaceVariant,
                  fontWeight: FontWeight.w500,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                value,
                style: theme.textTheme.bodyMedium?.copyWith(
                  fontWeight: FontWeight.w500,
                  color: value == '—' ? theme.colorScheme.onSurfaceVariant : theme.colorScheme.onSurface,
                ),
                overflow: TextOverflow.ellipsis,
                maxLines: 2,
              ),
            ],
          ),
        ),
      ],
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
  const _FormCard({
    required this.form,
    required this.onTap,
    this.isSubmitted = false,
    this.hasDraft = false,
    this.isLoading = false,
  });

  final WorkOrderFormRef form;
  final VoidCallback onTap;
  final bool isSubmitted;
  final bool hasDraft;
  final bool isLoading;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        onTap: isLoading ? null : onTap,
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
                child: isLoading
                    ? SizedBox(width: 26, height: 26, child: CircularProgressIndicator(strokeWidth: 2, color: theme.colorScheme.onPrimaryContainer))
                    : Icon(Icons.edit_note, color: theme.colorScheme.onPrimaryContainer, size: 26),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(form.name, style: theme.textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w600)),
                    const SizedBox(height: 2),
                    Text(
                      isSubmitted ? 'Submitted' : 'Version ${form.version ?? '—'}',
                      style: theme.textTheme.bodySmall?.copyWith(
                        color: isSubmitted ? Colors.green.shade700 : theme.colorScheme.outline,
                        fontWeight: isSubmitted ? FontWeight.w600 : null,
                      ),
                    ),
                    if (hasDraft) ...[
                      const SizedBox(height: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: theme.colorScheme.tertiaryContainer.withValues(alpha: 0.9),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: theme.colorScheme.outlineVariant.withValues(alpha: 0.6), width: 1),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.edit_note_outlined, size: 14, color: theme.colorScheme.onTertiaryContainer),
                            const SizedBox(width: 4),
                            Text('Draft', style: theme.textTheme.labelSmall?.copyWith(color: theme.colorScheme.onTertiaryContainer, fontWeight: FontWeight.w600)),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              if (isSubmitted)
                Icon(Icons.check_circle, color: Colors.green.shade700, size: 22),
              if (isSubmitted) const SizedBox(width: 8),
              Icon(Icons.chevron_right, color: theme.colorScheme.outline),
            ],
          ),
        ),
      ),
    );
  }
}
