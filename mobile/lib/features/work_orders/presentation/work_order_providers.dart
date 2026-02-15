import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client_provider.dart';
import '../../../data/local/pending_submissions_store.dart';
import '../data/sync_service.dart';
import '../data/work_order_repository.dart';

final workOrderRepositoryProvider = Provider<WorkOrderRepository>((ref) {
  return WorkOrderRepository(ref.watch(apiClientProvider));
});

final pendingSubmissionsStoreProvider = Provider<PendingSubmissionsStore>((ref) {
  return PendingSubmissionsStore();
});

final syncServiceProvider = Provider<SyncService>((ref) {
  return SyncService(
    ref.watch(workOrderRepositoryProvider),
    ref.watch(pendingSubmissionsStoreProvider),
  );
});

final pendingListRefreshTriggerProvider = StateProvider<int>((ref) => 0);
