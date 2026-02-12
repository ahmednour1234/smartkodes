import 'dart:typed_data';

import 'package:dio/dio.dart';

import '../../../core/api/api_client.dart';
import '../../../core/api/api_response.dart';
import '../../../domain/models/form_model.dart';
import '../../../domain/models/work_order.dart';

class WorkOrderRepository {
  WorkOrderRepository(this._client);

  final ApiClient _client;

  Future<PaginatedResponse<WorkOrder>> list({
    int? priority,
    String? sortBy,
    String? sortOrder,
    double? latitude,
    double? longitude,
    double? radius,
    int perPage = 15,
  }) async {
    final query = <String, dynamic>{
      'per_page': perPage,
      if (priority != null) 'priority': priority,
      if (sortBy != null) 'sort_by': sortBy,
      if (sortOrder != null) 'sort_order': sortOrder,
      if (latitude != null) 'latitude': latitude,
      if (longitude != null) 'longitude': longitude,
      if (radius != null) 'radius': radius,
    };
    return _client.requestPaginated<WorkOrder>(
      'work-orders',
      queryParameters: query,
      fromJsonT: (d) => WorkOrder.fromJson(d as Map<String, dynamic>),
    );
  }

  Future<WorkOrder?> get(String id, {double? currentLatitude, double? currentLongitude}) async {
    final query = <String, dynamic>{
      if (currentLatitude != null) 'current_latitude': currentLatitude,
      if (currentLongitude != null) 'current_longitude': currentLongitude,
    };
    final response = await _client.request<Map<String, dynamic>>(
      'work-orders/$id',
      queryParameters: query.isEmpty ? null : query,
      fromJsonT: (d) => d as Map<String, dynamic>,
    );
    if (!response.success || response.data == null) return null;
    return WorkOrder.fromJson(response.data!);
  }

  Future<String?> getMapUrl(String id) async {
    final response = await _client.request<Map<String, dynamic>>(
      'work-orders/$id/map',
      fromJsonT: (d) => d as Map<String, dynamic>,
    );
    return response.data?['url'] as String?;
  }

  Future<String?> getDirectionsUrl(String id, {double? latitude, double? longitude}) async {
    final query = <String, dynamic>{
      if (latitude != null) 'latitude': latitude,
      if (longitude != null) 'longitude': longitude,
    };
    final response = await _client.request<Map<String, dynamic>>(
      'work-orders/$id/directions',
      queryParameters: query.isEmpty ? null : query,
      fromJsonT: (d) => d as Map<String, dynamic>,
    );
    return response.data?['url'] as String?;
  }

  Future<FormModel?> getForm(String workOrderId, String formId) async {
    final response = await _client.request<Map<String, dynamic>>(
      'work-orders/$workOrderId/forms/$formId',
      fromJsonT: (d) => d as Map<String, dynamic>,
    );
    if (!response.success || response.data == null) return null;
    return FormModel.fromJson(response.data!);
  }

  Future<bool> submitForm(
    String workOrderId,
    String formId,
    Map<String, dynamic> fields, {
    Map<String, ({Uint8List bytes, String filename})>? fileFields,
    double? latitude,
    double? longitude,
  }) async {
    final map = <String, dynamic>{
      'form_id': formId,
      'work_order_id': workOrderId,
      if (latitude != null) 'latitude': latitude,
      if (longitude != null) 'longitude': longitude,
      ...fields.map((k, v) => MapEntry(k, v?.toString() ?? '')),
    };
    if (fileFields != null) {
      for (final e in fileFields.entries) {
        map[e.key] = MultipartFile.fromBytes(
          e.value.bytes,
          filename: e.value.filename,
        );
      }
    }
    final formData = FormData.fromMap(map);
    final response = await _client.dio.post<Map<String, dynamic>>(
      'work-orders/$workOrderId/submit-form',
      data: formData,
      options: Options(
        headers: {'Content-Type': 'multipart/form-data'},
      ),
    );
    final data = response.data;
    return data != null && (data['success'] as bool? ?? false);
  }
}
