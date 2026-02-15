class WorkOrder {
  final String id;
  final String? title;
  final String? description;
  final WorkOrderProject? project;
  final String status;
  final int? importanceLevel;
  final int? priorityValue;
  final String? priorityUnit;
  final String? dueDate;
  final WorkOrderLocation? location;
  final List<WorkOrderFormRef>? forms;
  final int? recordsCount;
  final double? distance;
  final String? distanceUnit;
  final String? createdAt;
  final String? updatedAt;
  final Map<String, dynamic>? map;

  const WorkOrder({
    required this.id,
    this.title,
    this.description,
    this.project,
    required this.status,
    this.importanceLevel,
    this.priorityValue,
    this.priorityUnit,
    this.dueDate,
    this.location,
    this.forms,
    this.recordsCount,
    this.distance,
    this.distanceUnit,
    this.createdAt,
    this.updatedAt,
    this.map,
  });

  factory WorkOrder.fromJson(Map<String, dynamic> json) {
    return WorkOrder(
      id: _toStr(json['id']) ?? '',
      title: _toStr(json['title']),
      description: _toStr(json['description']),
      project: json['project'] != null
          ? WorkOrderProject.fromJson(json['project'] as Map<String, dynamic>)
          : null,
      status: _toStr(json['status']) ?? '',
      importanceLevel: _toInt(json['importance_level']),
      priorityValue: _toInt(json['priority_value']),
      priorityUnit: _toStr(json['priority_unit']),
      dueDate: _toStr(json['due_date']),
      location: json['location'] != null
          ? WorkOrderLocation.fromJson(json['location'] as Map<String, dynamic>)
          : null,
      forms: (json['forms'] as List<dynamic>?)
          ?.map((e) => WorkOrderFormRef.fromJson(e as Map<String, dynamic>))
          .toList(),
      recordsCount: _toInt(json['records_count']),
      distance: _toDouble(json['distance']),
      distanceUnit: _toStr(json['distance_unit']),
      createdAt: _toStr(json['created_at']),
      updatedAt: _toStr(json['updated_at']),
      map: json['map'] as Map<String, dynamic>?,
    );
  }
}

class WorkOrderProject {
  final String id;
  final String name;
  final String? code;

  WorkOrderProject({required this.id, required this.name, this.code});

  factory WorkOrderProject.fromJson(Map<String, dynamic> json) {
    return WorkOrderProject(
      id: _toStr(json['id']) ?? '',
      name: _toStr(json['name']) ?? '',
      code: _toStr(json['code']),
    );
  }
}

class WorkOrderLocation {
  final double? latitude;
  final double? longitude;

  WorkOrderLocation({this.latitude, this.longitude});

  factory WorkOrderLocation.fromJson(Map<String, dynamic> json) {
    return WorkOrderLocation(
      latitude: _toDouble(json['latitude']),
      longitude: _toDouble(json['longitude']),
    );
  }
}

double? _toDouble(dynamic v) {
  if (v == null) return null;
  if (v is num) return v.toDouble();
  if (v is String) return double.tryParse(v);
  return null;
}

int? _toInt(dynamic v) {
  if (v == null) return null;
  if (v is int) return v;
  if (v is num) return v.toInt();
  if (v is String) return int.tryParse(v);
  return null;
}

class WorkOrderFormRef {
  final String id;
  final String name;
  final String? version;

  WorkOrderFormRef({required this.id, required this.name, this.version});

  factory WorkOrderFormRef.fromJson(Map<String, dynamic> json) {
    return WorkOrderFormRef(
      id: _toStr(json['id']) ?? '',
      name: _toStr(json['name']) ?? '',
      version: _toStr(json['version']),
    );
  }
}

String? _toStr(dynamic v) {
  if (v == null) return null;
  return v.toString();
}
