import 'dart:io' if (dart.library.html) 'io_stub.dart' as io;

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/foundation.dart';

import '../../../data/local/pending_record_updates_store.dart';
import '../../../data/local/pending_submissions_store.dart';
import '../../../core/storage/secure_storage.dart';
import '../../forms/data/forms_repository.dart';
import 'work_order_repository.dart';

class SyncService {
  SyncService(
    this._repo,
    this._store, {
    required SecureStorage storage,
    FormsRepository? formsRepo,
    PendingRecordUpdatesStore? recordUpdatesStore,
  })  : _formsRepo = formsRepo,
        _recordUpdatesStore = recordUpdatesStore,
        _storage = storage;

  final WorkOrderRepository _repo;
  final PendingSubmissionsStore _store;
  final FormsRepository? _formsRepo;
  final PendingRecordUpdatesStore? _recordUpdatesStore;
  final SecureStorage _storage;

  Future<bool> get isAutoSyncEnabled => _storage.getAutoSyncEnabled();

  Future<void> setAutoSyncEnabled(bool enabled) =>
      _storage.setAutoSyncEnabled(enabled);

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

  Future<int> syncPending({bool force = false}) async {
    if (!force && !await isAutoSyncEnabled) return 0;
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
          if (p.filePaths != null && !kIsWeb) {
            for (final path in p.filePaths!.values) {
              try {
                await io.File(path).delete();
              } catch (_) {}
            }
            try {
              if (p.filePaths!.isNotEmpty) {
                final dirPath = io.File(p.filePaths!.values.first).parent.path;
                await io.Directory(dirPath).delete(recursive: true);
              }
            } catch (_) {}
          }
          await _store.removeFirst();
          synced++;
        } else {
          break;
        }
      } catch (_) {
        break;
      }
    }
    final formsRepo = _formsRepo;
    final recordUpdatesStore = _recordUpdatesStore;
    if (formsRepo != null && recordUpdatesStore != null) {
      while (true) {
        final list = await recordUpdatesStore.load();
        if (list.isEmpty) break;
        final u = list.first;
        try {
          Map<String, ({Uint8List bytes, String filename})>? fileFields;
          if (u.hasFiles && u.filePaths != null && !kIsWeb) {
            fileFields = <String, ({Uint8List bytes, String filename})>{};
            for (final e in u.filePaths!.entries) {
              final f = io.File(e.value);
              if (await f.exists()) {
                final bytes = await f.readAsBytes();
                final filename = e.value.split(RegExp(r'[/\\]')).last;
                final name = filename.contains('_') ? filename.substring(filename.indexOf('_') + 1) : filename;
                fileFields[e.key] = (bytes: bytes, filename: name);
              }
            }
            if (fileFields.isEmpty) fileFields = null;
          }
          final ok = await formsRepo.updateRecord(
            u.formId,
            u.recordId,
            u.fields,
            fileFields: fileFields,
          );
          if (ok) {
            if (u.filePaths != null && !kIsWeb) {
              for (final path in u.filePaths!.values) {
                try {
                  await io.File(path).delete();
                } catch (_) {}
              }
              try {
                final dirPath = io.File(u.filePaths!.values.first).parent.path;
                await io.Directory(dirPath).delete(recursive: true);
              } catch (_) {}
            }
            await recordUpdatesStore.removeFirst();
            synced++;
          } else {
            break;
          }
        } catch (_) {
          break;
        }
      }
    }
    return synced;
  }
}
