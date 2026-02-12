import 'dart:convert';

class FormModel {
  final String id;
  final String name;
  final String? description;
  final String? status;
  final String? version;
  final List<FormFieldModel>? fields;
  final int? fieldsCount;

  const FormModel({
    required this.id,
    required this.name,
    this.description,
    this.status,
    this.version,
    this.fields,
    this.fieldsCount,
  });

  factory FormModel.fromJson(Map<String, dynamic> json) {
    return FormModel(
      id: _str(json['id']) ?? '',
      name: _str(json['name']) ?? '',
      description: _str(json['description']),
      status: _str(json['status']),
      version: _str(json['version']),
      fields: (json['fields'] as List<dynamic>?)
          ?.map((e) => FormFieldModel.fromJson(e as Map<String, dynamic>))
          .toList(),
      fieldsCount: _toInt(json['fields_count']),
    );
  }
}

String? _str(dynamic v) => v == null ? null : v.toString();
int? _toInt(dynamic v) {
  if (v == null) return null;
  if (v is int) return v;
  if (v is num) return v.toInt();
  if (v is String) return int.tryParse(v);
  return null;
}

class FormFieldModel {
  final String id;
  final String name;
  final String type;
  final String? label;
  final String? placeholder;
  final bool required;
  final int? order;
  final dynamic defaultValue;
  final Map<String, dynamic>? config;
  final List<String>? options;

  const FormFieldModel({
    required this.id,
    required this.name,
    required this.type,
    this.label,
    this.placeholder,
    this.required = false,
    this.order,
    this.defaultValue,
    this.config,
    this.options,
  });

  factory FormFieldModel.fromJson(Map<String, dynamic> json) {
    List<String>? opts;
    final o = json['options'];
    if (o is List) {
      opts = o.map((e) => e.toString()).toList();
    }
    return FormFieldModel(
      id: _str(json['id']) ?? '',
      name: _str(json['name']) ?? '',
      type: _str(json['type']) ?? 'text',
      label: _str(json['label']) ?? _str(json['name']),
      placeholder: _str(json['placeholder']),
      required: json['required'] == true,
      order: _toInt(json['order']),
      defaultValue: json['default_value'],
      config: _toMap(json['config']),
      options: opts,
    );
  }
}

Map<String, dynamic>? _toMap(dynamic v) {
  if (v == null) return null;
  if (v is Map<String, dynamic>) return v;
  if (v is String) {
    try {
      final decoded = jsonDecode(v);
      return decoded is Map ? Map<String, dynamic>.from(decoded) : null;
    } catch (_) {
      return null;
    }
  }
  return null;
}
