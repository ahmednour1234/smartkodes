import 'pending_submissions_store_impl_io.dart'
    if (dart.library.html) 'pending_submissions_store_impl_web.dart' as impl;

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
  Future<List<PendingSubmission>> load() async {
    final list = await impl.loadPendingJson();
    return list.map((e) => PendingSubmission.fromJson(e)).toList();
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
    await impl.savePendingJson(list.map((e) => e.toJson()).toList());
  }
}
