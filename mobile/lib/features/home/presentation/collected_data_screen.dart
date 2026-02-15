import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../data/local/pending_submissions_store.dart';
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

  Future<void> _sync() async {
    final isOnline = await ref.read(syncServiceProvider).isOnline;
    if (!isOnline && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('You\'re offline. Connect to sync.')),
      );
      return;
    }
    setState(() => _syncing = true);
    final n = await ref.read(syncServiceProvider).syncPending();
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
    return Scaffold(
      appBar: AppBar(
        title: const Text('Collected Data'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.of(context).pop(),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.home_outlined),
            onPressed: () => Navigator.of(context).popUntil((r) => r.isFirst),
            tooltip: 'Home',
          ),
        ],
      ),
      body: FutureBuilder<List<PendingSubmission>>(
        key: ValueKey('$refreshTrigger-$_refreshKey'),
        future: ref.read(pendingSubmissionsStoreProvider).load(),
        builder: (context, snapshot) {
          final list = snapshot.data ?? [];
          return RefreshIndicator(
            onRefresh: _refresh,
            child: ListView(
              padding: const EdgeInsets.all(20),
              children: [
                if (list.isEmpty)
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
                            'Data you submit offline appears here. Pull to refresh or tap Sync when online.',
                            textAlign: TextAlign.center,
                            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                  color: Theme.of(context).colorScheme.onSurfaceVariant,
                                ),
                          ),
                        ],
                      ),
                    ),
                  )
                else
                  ...list.asMap().entries.map((e) {
                    final p = e.value as PendingSubmission;
                    final idx = e.key;
                    return _PendingCard(
                      key: ValueKey('${p.workOrderId}-${p.formId}-${p.createdAt.millisecondsSinceEpoch}'),
                      pending: p,
                      index: idx,
                      onOpenForm: _openFormWithPending,
                    );
                  }),
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

class _PendingCard extends StatefulWidget {
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
  State<_PendingCard> createState() => _PendingCardState();
}

class _PendingCardState extends State<_PendingCard> {
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
                child: Column(
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
                            TextSpan(text: '${e.key}: ', style: const TextStyle(fontWeight: FontWeight.w600)),
                            TextSpan(text: '${e.value}'),
                          ],
                        ),
                      ),
                    )),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}
