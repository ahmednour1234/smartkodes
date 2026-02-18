import 'pending_record_updates_store_impl_io.dart'
    if (dart.library.html) 'pending_record_updates_store_impl_web.dart' as impl;

class PendingRecordUpdate {
  final String formId;
  final String recordId;
  final Map<String, dynamic> fields;
  final DateTime createdAt;

  PendingRecordUpdate({
    required this.formId,
    required this.recordId,
    required this.fields,
    required this.createdAt,
  });

  Map<String, dynamic> toJson() => {
        'form_id': formId,
        'record_id': recordId,
        'fields': fields,
        'created_at': createdAt.toIso8601String(),
      };

  static PendingRecordUpdate fromJson(Map<String, dynamic> json) {
    return PendingRecordUpdate(
      formId: json['form_id'] as String,
      recordId: json['record_id'] as String,
      fields: Map<String, dynamic>.from(json['fields'] as Map),
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }
}

class PendingRecordUpdatesStore {
  Future<List<PendingRecordUpdate>> load() async {
    final list = await impl.loadPendingRecordUpdatesJson();
    return list.map((e) => PendingRecordUpdate.fromJson(e)).toList();
  }

  Future<void> add(PendingRecordUpdate u) async {
    final list = await load();
    list.add(u);
    await _save(list);
  }

  Future<void> removeFirst() async {
    final list = await load();
    if (list.isNotEmpty) {
      list.removeAt(0);
      await _save(list);
    }
  }

  Future<void> _save(List<PendingRecordUpdate> list) async {
    await impl.savePendingRecordUpdatesJson(list.map((e) => e.toJson()).toList());
  }
}
