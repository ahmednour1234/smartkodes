import 'dart:convert';
import 'dart:io';

import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';

import '../../../core/api/api_client.dart';
import '../../../core/api/api_response.dart';
import '../../../core/api/api_response.dart';
import '../../../domain/models/form_model.dart';

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

  Future<FormModel?> get(String id) async {
    final response = await _client.request<Map<String, dynamic>>(
      'forms/$id',
      fromJsonT: (d) => d as Map<String, dynamic>,
    );
    if (!response.success || response.data == null) return null;
    return FormModel.fromJson(response.data!);
  }

  Future<bool> updateRecord(String formId, String recordId, Map<String, dynamic> fields) async {
    final response = await _client.request<dynamic>(
      'forms/$formId/records/$recordId',
      method: 'PUT',
      data: fields,
    );
    return response.success;
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
