import 'dart:convert';
import 'dart:io';

import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';

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
