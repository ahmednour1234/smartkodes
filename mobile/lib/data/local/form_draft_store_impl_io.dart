import 'dart:convert';
import 'dart:io';
import 'dart:typed_data';

import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';

import 'form_draft_store.dart';

String _sanitizeKey(String key) =>
    key.replaceAll(RegExp(r'[/\\:]'), '_');

Future<void> saveDraft(
  String key,
  Map<String, dynamic> values,
  Map<String, ({Uint8List bytes, String filename})>? fileData,
) async {
  final dir = await getApplicationDocumentsDirectory();
  final base = p.join(dir.path, 'form_drafts');
  await Directory(base).create(recursive: true);
  final safeKey = _sanitizeKey(key);
  Map<String, String>? filePaths;
  if (fileData != null && fileData.isNotEmpty) {
    final filesDir = Directory(p.join(base, safeKey));
    if (await filesDir.exists()) await filesDir.delete(recursive: true);
    await filesDir.create(recursive: true);
    filePaths = {};
    for (final e in fileData.entries) {
      final name = e.value.filename.isNotEmpty ? e.value.filename : '${e.key}.bin';
      final f = File(p.join(filesDir.path, '${e.key}_${p.basename(name)}'));
      await f.writeAsBytes(e.value.bytes);
      filePaths[e.key] = f.path;
    }
  }
  final json = {
    'values': values,
    if (filePaths != null && filePaths.isNotEmpty) 'file_paths': filePaths,
  };
  final f = File(p.join(base, '$safeKey.json'));
  await f.writeAsString(jsonEncode(json));
}

Future<FormDraft?> loadDraft(String key) async {
  final dir = await getApplicationDocumentsDirectory();
  final safeKey = _sanitizeKey(key);
  final f = File(p.join(dir.path, 'form_drafts', '$safeKey.json'));
  if (!await f.exists()) return null;
  try {
    final map = jsonDecode(await f.readAsString()) as Map<String, dynamic>;
    final values = Map<String, dynamic>.from(map['values'] as Map);
    Map<String, String>? filePaths;
    if (map['file_paths'] != null) {
      filePaths = (map['file_paths'] as Map).map((k, v) => MapEntry(k as String, v as String));
    }
    return FormDraft(values: values, filePaths: filePaths);
  } catch (_) {
    return null;
  }
}

Future<FormDraftWithFiles?> loadDraftWithFiles(String key) async {
  final draft = await loadDraft(key);
  if (draft == null || draft.filePaths == null || draft.filePaths!.isEmpty) {
    return draft != null ? FormDraftWithFiles(values: draft.values, fileData: null) : null;
  }
  final fileData = <String, ({Uint8List bytes, String filename})>{};
  for (final e in draft.filePaths!.entries) {
    final f = File(e.value);
    if (await f.exists()) {
      final bytes = await f.readAsBytes();
      final filename = p.basename(e.value);
      final name = filename.contains('_') ? filename.substring(filename.indexOf('_') + 1) : filename;
      fileData[e.key] = (bytes: bytes, filename: name);
    }
  }
  return FormDraftWithFiles(values: draft.values, fileData: fileData.isEmpty ? null : fileData);
}

Future<bool> draftExists(String key) async {
  final dir = await getApplicationDocumentsDirectory();
  final safeKey = _sanitizeKey(key);
  final f = File(p.join(dir.path, 'form_drafts', '$safeKey.json'));
  return f.existsSync();
}

Future<List<String>> listDraftKeys() async {
  final dir = await getApplicationDocumentsDirectory();
  final base = Directory(p.join(dir.path, 'form_drafts'));
  if (!await base.exists()) return [];
  final keys = <String>[];
  await for (final e in base.list()) {
    if (e is File && e.path.endsWith('.json')) {
      keys.add(p.basenameWithoutExtension(e.path));
    }
  }
  return keys;
}

Future<void> removeDraft(String key) async {
  final dir = await getApplicationDocumentsDirectory();
  final base = p.join(dir.path, 'form_drafts');
  final safeKey = _sanitizeKey(key);
  final f = File(p.join(base, '$safeKey.json'));
  if (await f.exists()) await f.delete();
  final filesDir = Directory(p.join(base, safeKey));
  if (await filesDir.exists()) await filesDir.delete(recursive: true);
}
