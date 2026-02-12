import 'dart:io' if (dart.library.html) 'io_stub.dart' as io;

import 'dart:typed_data';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/foundation.dart';

import '../../../data/local/pending_submissions_store.dart';
import 'work_order_repository.dart';

class SyncService {
  SyncService(this._repo, this._store);

  final WorkOrderRepository _repo;
  final PendingSubmissionsStore _store;

  Future<bool> get isOnline async {
    final result = await Connectivity().checkConnectivity();
    return result != ConnectivityResult.none;
  }

  Future<Map<String, ({Uint8List bytes, String filename})>?> _loadFileFields(PendingSubmission p) async {
    if (!p.hasFiles || p.filePaths == null) return null;
    if (kIsWeb) return null;
    final fileFields = <String, ({Uint8List bytes, String filename})>{};
    for (final e in p.filePaths!.entries) {
      final f = io.File(e.value);
      if (await f.exists()) {
        final bytes = await f.readAsBytes();
        final filename = e.value.split(RegExp(r'[/\\]')).last;
        fileFields[e.key] = (bytes: bytes, filename: filename);
      }
    }
    return fileFields.isEmpty ? null : fileFields;
  }

  Future<int> syncPending() async {
    if (!await isOnline) return 0;
    int synced = 0;
    while (true) {
      final list = await _store.load();
      if (list.isEmpty) break;
      final p = list.first;
      final fileFields = await _loadFileFields(p);
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
