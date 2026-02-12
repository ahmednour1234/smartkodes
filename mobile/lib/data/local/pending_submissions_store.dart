import 'dart:convert';
import 'dart:io';

import 'package:path_provider/path_provider.dart';
import 'package:path/path.dart' as p;

class PendingSubmission {
  final String workOrderId;
  final String formId;
  final Map<String, dynamic> fields;
  final Map<String, String>? filePaths;
  final double? latitude;
  final double? longitude;
  final DateTime createdAt;

  PendingSubmission({
    required this.workOrderId,
    required this.formId,
    required this.fields,
    this.filePaths,
    this.latitude,
    this.longitude,
    required this.createdAt,
  });

  Map<String, dynamic> toJson() => {
        'work_order_id': workOrderId,
        'form_id': formId,
        'fields': fields,
        'file_paths': filePaths,
        'latitude': latitude,
        'longitude': longitude,
        'created_at': createdAt.toIso8601String(),
      };

  static PendingSubmission fromJson(Map<String, dynamic> json) {
    return PendingSubmission(
      workOrderId: json['work_order_id'] as String,
      formId: json['form_id'] as String,
      fields: Map<String, dynamic>.from(json['fields'] as Map),
      filePaths: json['file_paths'] != null
          ? (json['file_paths'] as Map).map((k, v) => MapEntry(k as String, v as String))
          : null,
      latitude: (json['latitude'] as num?)?.toDouble(),
      longitude: (json['longitude'] as num?)?.toDouble(),
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }

  bool get hasFiles => filePaths != null && filePaths!.isNotEmpty;
}

class PendingSubmissionsStore {
  static const _fileName = 'pending_submissions.json';

  Future<File> _file() async {
    final dir = await getApplicationDocumentsDirectory();
    return File(p.join(dir.path, _fileName));
  }

  Future<List<PendingSubmission>> load() async {
    final f = await _file();
    if (!await f.exists()) return [];
    try {
      final content = await f.readAsString();
      final list = jsonDecode(content) as List<dynamic>;
      return list.map((e) => PendingSubmission.fromJson(e as Map<String, dynamic>)).toList();
    } catch (_) {
      return [];
    }
  }

  Future<void> add(PendingSubmission s) async {
    final list = await load();
    list.add(s);
    await _save(list);
  }

  Future<void> removeAt(int index) async {
    final list = await load();
    if (index >= 0 && index < list.length) {
      list.removeAt(index);
      await _save(list);
    }
  }

  Future<void> removeFirst() async {
    final list = await load();
    if (list.isNotEmpty) {
      list.removeAt(0);
      await _save(list);
    }
  }

  Future<void> _save(List<PendingSubmission> list) async {
    final f = await _file();
    final encoded = jsonEncode(list.map((e) => e.toJson()).toList());
    await f.writeAsString(encoded);
  }
}
