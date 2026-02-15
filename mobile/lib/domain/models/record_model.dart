class RecordModel {
  final String id;
  final RecordFormRef? form;
  final RecordWorkOrderRef? workOrder;
  final String? status;
  final String? submittedAt;
  final Map<String, dynamic>? fields;

  const RecordModel({
    required this.id,
    this.form,
    this.workOrder,
    this.status,
    this.submittedAt,
    this.fields,
  });

  factory RecordModel.fromJson(Map<String, dynamic> json) {
    Map<String, dynamic>? fieldsMap;
    if (json['fields'] is Map) {
      fieldsMap = Map<String, dynamic>.from(json['fields'] as Map);
    }
    return RecordModel(
      id: _str(json['id']) ?? '',
      form: json['form'] != null
          ? RecordFormRef.fromJson(json['form'] as Map<String, dynamic>)
          : null,
      workOrder: json['work_order'] != null
          ? RecordWorkOrderRef.fromJson(json['work_order'] as Map<String, dynamic>)
          : null,
      status: _str(json['status']),
      submittedAt: _str(json['submitted_at']),
      fields: fieldsMap,
    );
  }
}

class RecordFormRef {
  final String id;
  final String? name;

  RecordFormRef({required this.id, this.name});

  factory RecordFormRef.fromJson(Map<String, dynamic> json) {
    return RecordFormRef(
      id: _str(json['id']) ?? '',
      name: _str(json['name']),
    );
  }
}

class RecordWorkOrderRef {
  final String id;

  RecordWorkOrderRef({required this.id});

  factory RecordWorkOrderRef.fromJson(Map<String, dynamic> json) {
    return RecordWorkOrderRef(id: _str(json['id']) ?? '');
  }
}

String? _str(dynamic v) {
  if (v == null) return null;
  return v.toString();
}
