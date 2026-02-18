import 'dart:convert';
import 'dart:io';
import 'dart:typed_data';

import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';

Future<Map<String, String>> writeFilesForSubmission(
  String id,
  Map<String, ({Uint8List bytes, String filename})> fileData,
) async {
  final dir = await getApplicationDocumentsDirectory();
  final base = Directory(p.join(dir.path, 'pending_submissions', id));
  if (!await base.exists()) await base.create(recursive: true);
  final paths = <String, String>{};
  for (final e in fileData.entries) {
    final name = e.value.filename.isNotEmpty ? e.value.filename : '${e.key}.bin';
    final f = File(p.join(base.path, '${e.key}_${p.basename(name)}'));
    await f.writeAsBytes(e.value.bytes);
    paths[e.key] = f.path;
  }
  return paths;
}

Future<List<Map<String, dynamic>>> loadPendingJson() async {
  final dir = await getApplicationDocumentsDirectory();
  final f = File(p.join(dir.path, 'pending_submissions.json'));
  if (!await f.exists()) return [];
  try {
    final content = await f.readAsString();
    final list = jsonDecode(content) as List<dynamic>;
    return list.map((e) => Map<String, dynamic>.from(e as Map)).toList();
  } catch (_) {
    return [];
  }
}

Future<void> savePendingJson(List<Map<String, dynamic>> list) async {
  final dir = await getApplicationDocumentsDirectory();
  final f = File(p.join(dir.path, 'pending_submissions.json'));
  await f.writeAsString(jsonEncode(list));
}
