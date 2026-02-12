import 'dart:io';

import 'package:connectivity_plus/connectivity_plus.dart';

import '../../../data/local/pending_submissions_store.dart';
import 'work_order_repository.dart';

class SyncService {
  SyncService(this._repo, this._store);

  final WorkOrderRepository _repo;
  final PendingSubmissionsStore _store;

  Future<bool> get isOnline async {
    final result = await Connectivity().checkConnectivity();
    return result.any((c) => c != ConnectivityResult.none);
  }

  Future<int> syncPending() async {
    if (!await isOnline) return 0;
    int synced = 0;
    while (true) {
      final list = await _store.load();
      if (list.isEmpty) break;
      final p = list.first;
      Map<String, File>? fileFields;
      if (p.hasFiles && p.filePaths != null) {
        fileFields = {};
        for (final e in p.filePaths!.entries) {
          final f = File(e.value);
          if (await f.exists()) fileFields[e.key] = f;
        }
      }
      try {
        final ok = await _repo.submitForm(
          p.workOrderId,
          p.formId,
          p.fields,
          fileFields: fileFields,
          latitude: p.latitude,
          longitude: p.longitude,
        );
        if (ok) {
          await _store.removeFirst();
          synced++;
        } else {
          break;
        }
      } catch (_) {
        break;
      }
    }
    return synced;
  }
}
