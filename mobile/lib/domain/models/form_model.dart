class FormModel {
  final String id;
  final String name;
  final String? description;
  final String? status;
  final String? version;
  final List<FormFieldModel>? fields;

  const FormModel({
    required this.id,
    required this.name,
    this.description,
    this.status,
    this.version,
    this.fields,
  });

  factory FormModel.fromJson(Map<String, dynamic> json) {
    return FormModel(
      id: json['id'] as String,
      name: json['name'] as String? ?? '',
      description: json['description'] as String?,
      status: json['status'] as String?,
      version: json['version'] as String?,
      fields: (json['fields'] as List<dynamic>?)
          ?.map((e) => FormFieldModel.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
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
      id: json['id'] as String,
      name: json['name'] as String? ?? '',
      type: json['type'] as String? ?? 'text',
      label: json['label'] as String? ?? json['name'] as String?,
      placeholder: json['placeholder'] as String?,
      required: json['required'] as bool? ?? false,
      order: json['order'] as int?,
      defaultValue: json['default_value'],
      config: json['config'] as Map<String, dynamic>?,
      options: opts,
    );
  }
}
