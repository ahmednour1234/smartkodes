class WorkOrder {
  final String id;
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
      id: json['id'] as String,
      project: json['project'] != null
          ? WorkOrderProject.fromJson(json['project'] as Map<String, dynamic>)
          : null,
      status: json['status'] as String? ?? '',
      importanceLevel: json['importance_level'] as int?,
      priorityValue: json['priority_value'] as int?,
      priorityUnit: json['priority_unit'] as String?,
      dueDate: json['due_date'] as String?,
      location: json['location'] != null
          ? WorkOrderLocation.fromJson(json['location'] as Map<String, dynamic>)
          : null,
      forms: (json['forms'] as List<dynamic>?)
          ?.map((e) => WorkOrderFormRef.fromJson(e as Map<String, dynamic>))
          .toList(),
      recordsCount: json['records_count'] as int?,
      distance: (json['distance'] as num?)?.toDouble(),
      distanceUnit: json['distance_unit'] as String?,
      createdAt: json['created_at'] as String?,
      updatedAt: json['updated_at'] as String?,
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
      id: json['id'] as String,
      name: json['name'] as String? ?? '',
      code: json['code'] as String?,
    );
  }
}

class WorkOrderLocation {
  final double? latitude;
  final double? longitude;

  WorkOrderLocation({this.latitude, this.longitude});

  factory WorkOrderLocation.fromJson(Map<String, dynamic> json) {
    return WorkOrderLocation(
      latitude: (json['latitude'] as num?)?.toDouble(),
      longitude: (json['longitude'] as num?)?.toDouble(),
    );
  }
}

class WorkOrderFormRef {
  final String id;
  final String name;
  final String? version;

  WorkOrderFormRef({required this.id, required this.name, this.version});

  factory WorkOrderFormRef.fromJson(Map<String, dynamic> json) {
    return WorkOrderFormRef(
      id: json['id'] as String,
      name: json['name'] as String? ?? '',
      version: json['version'] as String?,
    );
  }
}
