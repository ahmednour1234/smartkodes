import 'dart:convert';
import 'dart:io';
import 'dart:typed_data';

import 'package:dio/dio.dart';
import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';

import '../../../core/api/api_client.dart';
import '../../../core/api/api_response.dart';
import '../../../domain/models/form_model.dart';
import '../../../domain/models/record_model.dart';

class FormsRepository {
  FormsRepository(this._client);

  final ApiClient _client;

  Future<PaginatedResponse<FormModel>> list({int perPage = 15}) async {
    return _client.requestPaginated<FormModel>(
      'forms',
      queryParameters: {'per_page': perPage},
      fromJsonT: (d) => FormModel.fromJson(d as Map<String, dynamic>),
    );
  }

  Future<PaginatedResponse<RecordModel>> listMyRecords({
    int perPage = 20,
    String? workOrderId,
    String? projectId,
  }) async {
    final query = <String, dynamic>{
      'per_page': perPage,
      if (workOrderId != null && workOrderId.isNotEmpty) 'work_order_id': workOrderId,
      if (projectId != null && projectId.isNotEmpty) 'project_id': projectId,
    };
    return _client.requestPaginated<RecordModel>(
      'records',
      queryParameters: query,
      fromJsonT: (d) => RecordModel.fromJson(d as Map<String, dynamic>),
    );
  }

  Future<RecordModel?> getRecord(String recordId) async {
    final response = await _client.request<Map<String, dynamic>>(
      'records/$recordId',
      fromJsonT: (d) => d as Map<String, dynamic>,
    );
    if (!response.success || response.data == null) return null;
    return RecordModel.fromJson(response.data!);
  }

  Future<Uint8List?> getRecordPdfBytes(String recordId) async {
    try {
      final response = await _client.dio.get<Uint8List>(
        'records/$recordId/pdf',
        options: Options(responseType: ResponseType.bytes),
      );
      return response.data;
    } catch (_) {
      return null;
    }
  }

  Future<FormModel?> get(String id) async {
    final response = await _client.request<Map<String, dynamic>>(
      'forms/$id',
      fromJsonT: (d) => d as Map<String, dynamic>,
    );
    if (!response.success || response.data == null) return null;
    return FormModel.fromJson(response.data!);
  }

  Future<bool> updateRecord(
    String formId,
    String recordId,
    Map<String, dynamic> fields, {
    Map<String, dynamic>? fileFields,
  }) async {
    try {
      if (fileFields != null && fileFields.isNotEmpty) {
        final map = <String, dynamic>{'_method': 'PUT'};
        for (final entry in fields.entries) {
          final v = entry.value;
          if (v is List) {
            for (var i = 0; i < v.length; i++) {
              map['${entry.key}[$i]'] = v[i]?.toString() ?? '';
            }
          } else {
            map[entry.key] = v?.toString() ?? '';
          }
        }
        for (final e in fileFields.entries) {
          final v = e.value;
          final list = v is List ? v as List<({Uint8List bytes, String filename})> : [v as ({Uint8List bytes, String filename})];
          for (var i = 0; i < list.length; i++) {
            final f = list[i];
            map['${e.key}[$i]'] = MultipartFile.fromBytes(f.bytes, filename: f.filename);
          }
        }
        final formData = FormData.fromMap(map);
        final response = await _client.dio.post<Map<String, dynamic>>(
          'forms/$formId/records/$recordId',
          data: formData,
          options: Options(
            headers: {'Content-Type': 'multipart/form-data'},
          ),
        );
        final data = response.data;
        return data != null && (data['success'] as bool? ?? false);
      }
      final response = await _client.request<dynamic>(
        'forms/$formId/records/$recordId',
        method: 'PUT',
        data: fields,
      );
      return response.success;
    } on DioException catch (e) {
      if (e.response?.statusCode == 422) {
        final body = e.response?.data;
        final errors = <String, String>{};
        if (body is Map && body['errors'] is Map) {
          for (final entry in (body['errors'] as Map).entries) {
            final key = entry.key.toString();
            final list = entry.value;
            if (list is List && list.isNotEmpty) {
              errors[key] = list.first.toString();
            }
          }
        }
        throw RecordUpdateValidationException(
          body is Map ? body['message']?.toString() ?? 'Validation failed' : 'Validation failed',
          errors,
        );
      }
      rethrow;
    }
  }

  Future<bool> delete(String id) async {
    final response = await _client.request<dynamic>(
      'forms/$id',
      method: 'DELETE',
    );
    return response.success;
  }

  Future<String> _cacheDir() async {
    final dir = await getApplicationDocumentsDirectory();
    final cache = p.join(dir.path, 'forms_cache');
    await Directory(cache).create(recursive: true);
    return cache;
  }

  Future<void> cacheForm(FormModel form) async {
    final dir = await _cacheDir();
    final file = File(p.join(dir, '${form.id}.json'));
    await file.writeAsString(jsonEncode(_formToCacheJson(form)));
  }

  Future<FormModel?> getCachedForm(String id) async {
    final dir = await _cacheDir();
    final file = File(p.join(dir, '$id.json'));
    if (!await file.exists()) return null;
    try {
      final map = jsonDecode(await file.readAsString()) as Map<String, dynamic>;
      return FormModel.fromJson(map);
    } catch (_) {
      return null;
    }
  }

  Map<String, dynamic> _formToCacheJson(FormModel form) {
    return {
      'id': form.id,
      'name': form.name,
      'description': form.description,
      'status': form.status,
      'version': form.version,
      'fields_count': form.fieldsCount,
      'fields': form.fields?.map((f) => {
            'id': f.id,
            'name': f.name,
            'type': f.type,
            'label': f.label,
            'placeholder': f.placeholder,
            'required': f.required,
            'order': f.order,
            'default_value': f.defaultValue,
            'config': f.config,
            'options': f.options,
          }).toList(),
    };
  }
}

class RecordUpdateValidationException implements Exception {
  RecordUpdateValidationException(this.message, this.fieldErrors);
  final String message;
  final Map<String, String> fieldErrors;
}
