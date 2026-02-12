import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../work_orders/presentation/work_order_providers.dart';

class CollectedDataScreen extends ConsumerStatefulWidget {
  const CollectedDataScreen({super.key});

  @override
  ConsumerState<CollectedDataScreen> createState() => _CollectedDataScreenState();
}

class _CollectedDataScreenState extends ConsumerState<CollectedDataScreen> {
  bool _syncing = false;

  Future<void> _sync() async {
    setState(() => _syncing = true);
    final n = await ref.read(syncServiceProvider).syncPending();
    setState(() => _syncing = false);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(n > 0 ? 'Synced $n item(s)' : 'Nothing to sync')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
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
      body: FutureBuilder(
        future: ref.read(pendingSubmissionsStoreProvider).load(),
        builder: (context, snapshot) {
          final list = snapshot.data ?? [];
          return RefreshIndicator(
            onRefresh: () async => setState(() {}),
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
                            'Data you submit is synced when online.\nPull to refresh.',
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
                    final p = e.value;
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      child: ListTile(
                        leading: Icon(
                          p.hasFiles ? Icons.attach_file : Icons.description,
                          color: Theme.of(context).colorScheme.primary,
                        ),
                        title: Text('Work order: ${p.workOrderId}'),
                        subtitle: Text('Form: ${p.formId}'),
                        trailing: Text(
                          p.createdAt.toString().length >= 16
                              ? p.createdAt.toString().substring(0, 16)
                              : p.createdAt.toString(),
                          style: Theme.of(context).textTheme.bodySmall,
                        ),
                      ),
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
