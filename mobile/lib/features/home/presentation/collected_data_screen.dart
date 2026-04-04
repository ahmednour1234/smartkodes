import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/widgets/app_drawer.dart';
import '../../../data/local/pending_record_updates_store.dart';
import '../../../data/local/pending_submissions_store.dart';
import '../../forms/presentation/forms_providers.dart';
import '../../work_orders/presentation/work_order_providers.dart';
import '../../work_orders/presentation/work_order_form_screen.dart';

class CollectedDataScreen extends ConsumerStatefulWidget {
  const CollectedDataScreen({super.key});

  @override
  ConsumerState<CollectedDataScreen> createState() => _CollectedDataScreenState();
}

class _CollectedDataScreenState extends ConsumerState<CollectedDataScreen> {
  bool _syncing = false;
  int _refreshKey = 0;
  bool _autoSyncEnabled = true;
  bool _autoSyncLoading = true;

  @override
  void initState() {
    super.initState();
    _loadAutoSyncSetting();
  }

  Future<void> _loadAutoSyncSetting() async {
    final enabled = await ref.read(syncServiceProvider).isAutoSyncEnabled;
    if (!mounted) return;
    setState(() {
      _autoSyncEnabled = enabled;
      _autoSyncLoading = false;
    });
  }

  Future<void> _setAutoSyncEnabled(bool enabled) async {
    setState(() {
      _autoSyncEnabled = enabled;
      _autoSyncLoading = true;
    });
    await ref.read(syncServiceProvider).setAutoSyncEnabled(enabled);
    if (!mounted) return;
    setState(() {
      _autoSyncLoading = false;
    });
  }

  Future<void> _sync() async {
    final isOnline = await ref.read(syncServiceProvider).isOnline;
    if (!isOnline && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('You\'re offline. Connect to sync.')),
      );
      return;
    }
    setState(() => _syncing = true);
    final n = await ref.read(syncServiceProvider).syncPending(force: true);
    setState(() {
      _syncing = false;
      _refreshKey++;
    });
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(n > 0 ? 'Synced $n item(s)' : 'Nothing to sync')),
      );
    }
  }

  Future<void> _refresh() async {
    await _sync();
    setState(() => _refreshKey++);
  }

  Future<void> _openFormWithPending(BuildContext context, int index, PendingSubmission p) async {
    await ref.read(pendingSubmissionsStoreProvider).removeAt(index);
    if (!context.mounted) return;
    setState(() => _refreshKey++);
    if (!context.mounted) return;
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => WorkOrderFormScreen(
          workOrderId: p.workOrderId,
          formId: p.formId,
          initialFields: p.fields,
        ),
      ),
    ).then((_) {
      if (context.mounted) setState(() => _refreshKey++);
    });
  }

  @override
  Widget build(BuildContext context) {
    final refreshTrigger = ref.watch(pendingListRefreshTriggerProvider);
    final submissionsFuture = ref.read(pendingSubmissionsStoreProvider).load();
    final updatesFuture = ref.read(pendingRecordUpdatesStoreProvider).load();
    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(
        title: const Text('Collected Data'),
      ),
      body: FutureBuilder<List<Object>>(
        key: ValueKey('$refreshTrigger-$_refreshKey'),
        future: Future.wait([submissionsFuture, updatesFuture]),
        builder: (context, snapshot) {
          if (!snapshot.hasData && snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          final result = snapshot.data;
          final submissions = (result != null && result.isNotEmpty ? result[0] as List<PendingSubmission> : <PendingSubmission>[]);
          final updates = (result != null && result.length > 1 ? result[1] as List<PendingRecordUpdate> : <PendingRecordUpdate>[]);
          final isEmpty = submissions.isEmpty && updates.isEmpty;
          return RefreshIndicator(
            onRefresh: _refresh,
            child: ListView(
              padding: const EdgeInsets.all(20),
              children: [
                Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: SwitchListTile.adaptive(
                    title: const Text('Auto Sync'),
                    subtitle: Text(
                      _autoSyncEnabled
                          ? 'Automatically sync pending data when app resumes or connection is restored.'
                          : 'Auto Sync is off. Use "Sync now" to sync manually.',
                    ),
                    value: _autoSyncEnabled,
                    onChanged: _autoSyncLoading ? null : _setAutoSyncEnabled,
                    secondary: _autoSyncLoading
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : Icon(
                            _autoSyncEnabled
                                ? Icons.sync
                                : Icons.sync_disabled,
                          ),
                  ),
                ),
                if (isEmpty)
                  Center(
                    child: Padding(
                      padding: const EdgeInsets.only(top: 48),
                      child: Column(
                        children: [
                          Icon(
                            Icons.check_circle_outline,
                            size: 64,
                            color: Theme.of(context).colorScheme.primary.withValues(alpha: 0.5),
                          ),
                          const SizedBox(height: 16),
                          Text(
                            'No pending data',
                            style: Theme.of(context).textTheme.titleMedium,
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Data you submit or update offline appears here. Pull to refresh or tap Sync when online.',
                            textAlign: TextAlign.center,
                            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                  color: Theme.of(context).colorScheme.onSurfaceVariant,
                                ),
                          ),
                        ],
                      ),
                    ),
                  )
                else ...[
                  ...submissions.asMap().entries.map((e) {
                    final p = e.value;
                    final idx = e.key;
                    return _PendingCard(
                      key: ValueKey('sub-${p.workOrderId}-${p.formId}-${p.createdAt.millisecondsSinceEpoch}'),
                      pending: p,
                      index: idx,
                      onOpenForm: _openFormWithPending,
                    );
                  }),
                  if (updates.isNotEmpty) ...[
                    if (submissions.isNotEmpty) const SizedBox(height: 16),
                    Text(
                      'Pending record updates',
                      style: Theme.of(context).textTheme.titleSmall?.copyWith(
                        color: Theme.of(context).colorScheme.onSurfaceVariant,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 8),
                    ...updates.map((u) => _PendingUpdateCard(
                      key: ValueKey('upd-${u.formId}-${u.recordId}-${u.createdAt.millisecondsSinceEpoch}'),
                      update: u,
                    )),
                  ],
                ],
                const SizedBox(height: 24),
                FilledButton.icon(
                  onPressed: _syncing ? null : _sync,
                  icon: _syncing
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Icon(Icons.cloud_upload_outlined),
                  label: Text(_syncing ? 'Syncing...' : 'Sync now'),
                  style: FilledButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 16),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _PendingCard extends ConsumerStatefulWidget {
  const _PendingCard({
    super.key,
    required this.pending,
    required this.index,
    required this.onOpenForm,
  });

  final PendingSubmission pending;
  final int index;
  final Future<void> Function(BuildContext context, int index, PendingSubmission p) onOpenForm;

  @override
  ConsumerState<_PendingCard> createState() => _PendingCardState();
}

class _PendingCardState extends ConsumerState<_PendingCard> {
  bool _expanded = false;

  @override
  Widget build(BuildContext context) {
    final p = widget.pending;
    final fields = p.fields;
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          ListTile(
            leading: Icon(
              p.hasFiles ? Icons.attach_file : Icons.description,
              color: Theme.of(context).colorScheme.primary,
            ),
            title: Text('Work order: ${p.workOrderId}'),
            subtitle: Text('Form: ${p.formId} • Tap to complete & submit'),
            trailing: IconButton(
              icon: Icon(_expanded ? Icons.expand_less : Icons.expand_more),
              onPressed: () => setState(() => _expanded = !_expanded),
              tooltip: 'View data',
            ),
            onTap: () => widget.onOpenForm(context, widget.index, p),
          ),
          if (_expanded)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
              child: Align(
                alignment: Alignment.centerLeft,
                child: FutureBuilder(
                  future: ref.read(formsRepositoryProvider).get(p.formId),
                  builder: (context, formSnapshot) {
                    final nameToLabel = <String, String>{};
                    if (formSnapshot.hasData && formSnapshot.data != null) {
                      for (final f in formSnapshot.data!.fields ?? []) {
                        nameToLabel[f.name] = f.label ?? f.name;
                      }
                    }
                    String label(String key) => nameToLabel[key] ?? key;
                    Object valueDisplay(Object v) {
                      if (v is List) return v.join(', ');
                      return v;
                    }
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Submitted data:', style: Theme.of(context).textTheme.labelLarge),
                        const SizedBox(height: 8),
                        ...fields.entries.map((e) => Padding(
                          padding: const EdgeInsets.only(bottom: 4),
                          child: Text.rich(
                            TextSpan(
                              style: Theme.of(context).textTheme.bodySmall,
                              children: [
                                TextSpan(text: '${label(e.key)}: ', style: const TextStyle(fontWeight: FontWeight.w600)),
                                TextSpan(text: '${valueDisplay(e.value)}'),
                              ],
                            ),
                          ),
                        )),
                      ],
                    );
                  },
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _PendingUpdateCard extends StatelessWidget {
  const _PendingUpdateCard({super.key, required this.update});

  final PendingRecordUpdate update;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        leading: Icon(Icons.edit_note, color: Theme.of(context).colorScheme.primary),
        title: Text('Form: ${update.formId}'),
        subtitle: Text('Record: ${update.recordId} • Will sync when you tap Sync now'),
      ),
    );
  }
}
