import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';

import '../../../core/widgets/gps_map_field.dart';
import '../../../data/local/pending_submissions_store.dart';
import '../../../domain/models/form_model.dart';
import '../data/work_order_repository.dart';
import 'work_order_providers.dart';

class WorkOrderFormScreen extends ConsumerStatefulWidget {
  const WorkOrderFormScreen({
    super.key,
    required this.workOrderId,
    required this.formId,
    this.initialFields,
  });

  final String workOrderId;
  final String formId;
  final Map<String, dynamic>? initialFields;

  @override
  ConsumerState<WorkOrderFormScreen> createState() => _WorkOrderFormScreenState();
}

class _WorkOrderFormScreenState extends ConsumerState<WorkOrderFormScreen> {
  FormModel? _form;
  final _values = <String, dynamic>{};
  final _fileData = <String, ({Uint8List bytes, String filename})>{};
  final _fieldErrors = <String, String>{};
  bool _loading = true;
  bool _submitting = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  static dynamic _toJsonSafeValue(dynamic v) {
    if (v == null) return null;
    if (v is String || v is num || v is bool) return v;
    if (v is List) return v.map((e) => _toJsonSafeValue(e)).toList();
    if (v is Map) return v.map((k, v2) => MapEntry(k.toString(), _toJsonSafeValue(v2)));
    return v.toString();
  }

  Future<void> _load() async {
    final repo = ref.read(workOrderRepositoryProvider);
    try {
      final form = await repo.getForm(widget.workOrderId, widget.formId);
      setState(() {
        _form = form;
        _loading = false;
        if (form != null) {
          for (final f in form.fields ?? []) {
            if (f.defaultValue != null) _values[f.name] = f.defaultValue;
          }
          if (widget.initialFields != null) {
            for (final e in widget.initialFields!.entries) {
              _values[e.key] = e.value;
            }
          }
        }
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  Future<void> _pickFile(FormFieldModel field) async {
    final picker = ImagePicker();
    final XFile? x;
    if (field.type == 'video') {
      x = await picker.pickVideo(source: ImageSource.gallery);
    } else {
      x = await picker.pickImage(source: ImageSource.gallery);
    }
    if (x != null) {
      final bytes = await x.readAsBytes();
      final defaultName = field.type == 'video' ? 'video.mp4' : 'image.jpg';
      final filename = x.name.isNotEmpty ? x.name : defaultName;
      setState(() => _fileData[field.name] = (bytes: bytes, filename: filename));
    }
  }

  String? _validateField(FormFieldModel f, dynamic value) {
    final empty = value == null ||
        (value is List && value.isEmpty) ||
        (value is! bool && value is! List && value.toString().trim().isEmpty);
    if (f.required && (empty || (f.type == 'checkbox' && value != true))) {
      return '${f.label ?? f.name} is required';
    }
    if (empty && f.type != 'checkbox') return null;
    if (value == null || value.toString().trim().isEmpty) return null;
    final s = value.toString().trim();
    switch (f.type) {
      case 'date':
      case 'datetime':
        final d = DateTime.tryParse(s);
        if (d == null) return '${f.label ?? f.name} must be a valid date';
        break;
      case 'time':
        if (!RegExp(r'^\d{1,2}:\d{2}(:\d{2})?$').hasMatch(s)) {
          return '${f.label ?? f.name} must be a valid time (HH:mm)';
        }
        break;
      case 'number':
      case 'currency':
        if (num.tryParse(s) == null) return '${f.label ?? f.name} must be a number';
        break;
      case 'email':
        if (!RegExp(r'^[\w.-]+@[\w.-]+\.\w+$').hasMatch(s)) {
          return '${f.label ?? f.name} must be a valid email';
        }
        break;
    }
    return null;
  }

  Future<void> _submit() async {
    if (_form == null) return;
    final fields = _form!.fields ?? [];
    final errs = <String, String>{};
    for (final f in fields) {
      final v = _values[f.name];
      final msg = _validateField(f, v);
      if (msg != null) errs[f.name] = msg;
    }
    if (errs.isNotEmpty) {
      setState(() {
        _fieldErrors.clear();
        _fieldErrors.addAll(errs);
        _error = 'Please fix the errors below.';
      });
      return;
    }
    setState(() {
      _submitting = true;
      _error = null;
      _fieldErrors.clear();
    });
    double? lat;
    double? lon;
    try {
      final pos = await Geolocator.getCurrentPosition();
      lat = pos.latitude;
      lon = pos.longitude;
    } catch (_) {}
    final syncService = ref.read(syncServiceProvider);
    final isOnline = await syncService.isOnline;
    if (!isOnline) {
      final offlineFields = <String, dynamic>{};
      for (final entry in _values.entries) {
        offlineFields[entry.key] = _toJsonSafeValue(entry.value);
      }
      final createdAt = DateTime.now();
      final s = PendingSubmission(
        workOrderId: widget.workOrderId,
        formId: widget.formId,
        fields: offlineFields,
        filePaths: null,
        latitude: lat,
        longitude: lon,
        createdAt: createdAt,
      );
      final store = ref.read(pendingSubmissionsStoreProvider);
      if (_fileData.isNotEmpty) {
        await store.addWithFiles(s, _fileData);
      } else {
        await store.add(s);
      }
      setState(() => _submitting = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Saved offline. Will sync when connected.'),
          ),
        );
        Navigator.of(context).pop();
      }
      return;
    }
    final repo = ref.read(workOrderRepositoryProvider);
    try {
      final ok = await repo.submitForm(
        widget.workOrderId,
        widget.formId,
        Map<String, dynamic>.from(_values),
        fileFields: _fileData.isEmpty ? null : _fileData,
        latitude: lat,
        longitude: lon,
      );
      setState(() => _submitting = false);
      if (ok && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Form submitted')),
        );
        Navigator.of(context).pop();
      } else {
        setState(() => _error = 'Submit failed');
      }
    } catch (e) {
      setState(() {
        _submitting = false;
        if (e is SubmitFormValidationException) {
          _fieldErrors.clear();
          _fieldErrors.addAll(e.fieldErrors);
          _error = e.message;
        } else {
          _error = e.toString();
        }
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }
    if (_error != null && _form == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Form')),
        body: Center(child: Text(_error!)),
      );
    }
    final form = _form!;
    final fields = form.fields ?? [];
    fields.sort((a, b) => (a.order ?? 0).compareTo(b.order ?? 0));

    return Scaffold(
      appBar: AppBar(title: Text(form.name)),
      body: Column(
        children: [
          Expanded(
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                if (_error != null)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: Text(_error!, style: const TextStyle(color: Colors.red)),
                  ),
                ...fields.map((f) => _buildField(f)),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: FilledButton(
              onPressed: _submitting ? null : _submit,
              style: FilledButton.styleFrom(
                minimumSize: const Size(double.infinity, 48),
              ),
              child: _submitting
                  ? const SizedBox(
                      height: 24,
                      width: 24,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Text('Submit'),
            ),
          ),
        ],
      ),
    );
  }

  InputDecoration _decoration(FormFieldModel f) {
    return InputDecoration(
      labelText: '${f.label ?? f.name}${f.required ? ' *' : ''}',
      border: const OutlineInputBorder(),
      errorText: _fieldErrors[f.name],
      errorBorder: const OutlineInputBorder(borderSide: BorderSide(color: Colors.red)),
    );
  }

  Future<void> _pickDate(FormFieldModel f) async {
    final initial = _values[f.name] != null
        ? DateTime.tryParse(_values[f.name].toString())
        : null;
    final d = await showDatePicker(
      context: context,
      initialDate: initial ?? DateTime.now(),
      firstDate: DateTime(1900),
      lastDate: DateTime(2100),
    );
    if (d != null) {
      setState(() => _values[f.name] = '${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}');
    }
  }

  Future<void> _pickTime(FormFieldModel f) async {
    final s = _values[f.name]?.toString() ?? '';
    TimeOfDay initial = TimeOfDay.now();
    if (s.isNotEmpty) {
      final parts = s.split(':');
      if (parts.length >= 2) {
        final h = int.tryParse(parts[0]);
        final m = int.tryParse(parts[1]);
        if (h != null && m != null) initial = TimeOfDay(hour: h, minute: m);
      }
    }
    final t = await showTimePicker(context: context, initialTime: initial);
    if (t != null) {
      setState(() => _values[f.name] = '${t.hour.toString().padLeft(2, '0')}:${t.minute.toString().padLeft(2, '0')}');
    }
  }

  Future<void> _pickDateTime(FormFieldModel f) async {
    final initial = _values[f.name] != null
        ? DateTime.tryParse(_values[f.name].toString())
        : null;
    final d = await showDatePicker(
      context: context,
      initialDate: initial ?? DateTime.now(),
      firstDate: DateTime(1900),
      lastDate: DateTime(2100),
    );
    if (d == null) return;
    final t = await showTimePicker(
      context: context,
      initialTime: initial != null
          ? TimeOfDay(hour: initial.hour, minute: initial.minute)
          : TimeOfDay.now(),
    );
    if (t != null) {
      final dt = DateTime(d.year, d.month, d.day, t.hour, t.minute);
      setState(() => _values[f.name] = dt.toIso8601String().replaceFirst('T', ' ').substring(0, 19));
    }
  }

  Widget _buildField(FormFieldModel f) {
    final isFile = ['file', 'photo', 'video', 'audio', 'image'].contains(f.type);
    if (isFile) {
      final buttonLabel = f.type == 'video'
          ? (_fileData[f.name] != null ? 'Change video' : 'Pick video')
          : (_fileData[f.name] != null ? 'Change file' : 'Pick file');
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('${f.label ?? f.name}${f.required ? ' *' : ''}'),
            if (_fieldErrors[f.name] != null)
              Padding(
                padding: const EdgeInsets.only(bottom: 4),
                child: Text(_fieldErrors[f.name]!, style: const TextStyle(color: Colors.red, fontSize: 12)),
              ),
            const SizedBox(height: 4),
            OutlinedButton(
              onPressed: () => _pickFile(f),
              child: Text(buttonLabel),
            ),
            if (_fileData[f.name] != null)
              Text(_fileData[f.name]!.filename,
                  style: Theme.of(context).textTheme.bodySmall),
          ],
        ),
      );
    }
    if (f.type == 'select' && (f.options?.isNotEmpty ?? false)) {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: DropdownButtonFormField<String>(
          value: _values[f.name] as String?,
          decoration: _decoration(f),
          items: f.options!
              .map((o) => DropdownMenuItem(value: o, child: Text(o)))
              .toList(),
          onChanged: (v) => setState(() {
            _values[f.name] = v;
            _fieldErrors.remove(f.name);
          }),
        ),
      );
    }
    if (f.type == 'radio' && (f.options?.isNotEmpty ?? false)) {
      final current = _values[f.name]?.toString();
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('${f.label ?? f.name}${f.required ? ' *' : ''}'),
            if (_fieldErrors[f.name] != null)
              Padding(
                padding: const EdgeInsets.only(bottom: 4),
                child: Text(_fieldErrors[f.name]!, style: const TextStyle(color: Colors.red, fontSize: 12)),
              ),
            ...f.options!.map((opt) => RadioListTile<String>(
              title: Text(opt),
              value: opt,
              groupValue: current,
              onChanged: (v) => setState(() {
                _values[f.name] = v;
                _fieldErrors.remove(f.name);
              }),
            )),
          ],
        ),
      );
    }
    if (f.isMultiSelectType && (f.options?.isNotEmpty ?? false)) {
      final current = _values[f.name];
      List<String> selected = [];
      if (current is List) {
        selected = current.map((e) => e.toString()).toList();
      } else if (current is String && current.isNotEmpty) {
        selected = current.contains(',') ? current.split(',').map((e) => e.trim()).where((e) => e.isNotEmpty).toList() : [current];
      }
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('${f.label ?? f.name}${f.required ? ' *' : ''}'),
            if (_fieldErrors[f.name] != null)
              Padding(
                padding: const EdgeInsets.only(bottom: 4),
                child: Text(_fieldErrors[f.name]!, style: const TextStyle(color: Colors.red, fontSize: 12)),
              ),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: f.options!.map((opt) {
                final isSelected = selected.contains(opt);
                return FilterChip(
                  label: Text(opt),
                  selected: isSelected,
                  onSelected: (_) {
                    setState(() {
                      final next = isSelected ? selected.where((e) => e != opt).toList() : [...selected, opt];
                      _values[f.name] = next.isEmpty ? null : next;
                      _fieldErrors.remove(f.name);
                    });
                  },
                );
              }).toList(),
            ),
          ],
        ),
      );
    }
    if (f.type == 'checkbox') {
      final v = _values[f.name];
      final checked = v == true || v == 'true' || v == '1' || v == 1;
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: CheckboxListTile(
          title: Text('${f.label ?? f.name}${f.required ? ' *' : ''}'),
          value: checked,
          onChanged: (val) => setState(() {
            _values[f.name] = val == true;
            _fieldErrors.remove(f.name);
          }),
          controlAffinity: ListTileControlAffinity.leading,
        ),
      );
    }
    if (f.type == 'gps') {
      return GpsMapField(
        label: '${f.label ?? f.name}${f.required ? ' *' : ''}',
        value: _values[f.name],
        errorText: _fieldErrors[f.name],
        onChanged: (v) => setState(() {
          _values[f.name] = v;
          _fieldErrors.remove(f.name);
        }),
      );
    }
    if (f.type == 'date') {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: InkWell(
          onTap: () async {
            await _pickDate(f);
            if (mounted) setState(() => _fieldErrors.remove(f.name));
          },
          child: InputDecorator(
            decoration: _decoration(f),
            child: Text(_values[f.name]?.toString() ?? 'Tap to pick date'),
          ),
        ),
      );
    }
    if (f.type == 'time') {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: InkWell(
          onTap: () async {
            await _pickTime(f);
            if (mounted) setState(() => _fieldErrors.remove(f.name));
          },
          child: InputDecorator(
            decoration: _decoration(f),
            child: Text(_values[f.name]?.toString() ?? 'Tap to pick time'),
          ),
        ),
      );
    }
    if (f.type == 'datetime') {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: InkWell(
          onTap: () async {
            await _pickDateTime(f);
            if (mounted) setState(() => _fieldErrors.remove(f.name));
          },
          child: InputDecorator(
            decoration: _decoration(f),
            child: Text(_values[f.name]?.toString() ?? 'Tap to pick date & time'),
          ),
        ),
      );
    }
    if (f.type == 'number' || f.type == 'currency') {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: TextFormField(
          keyboardType: const TextInputType.numberWithOptions(decimal: true),
          decoration: _decoration(f),
          initialValue: _values[f.name]?.toString(),
          onChanged: (v) => setState(() {
            _values[f.name] = v.isEmpty ? null : num.tryParse(v);
            _fieldErrors.remove(f.name);
          }),
        ),
      );
    }
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: TextFormField(
        decoration: _decoration(f),
        initialValue: _values[f.name]?.toString(),
        maxLines: f.type == 'textarea' ? 4 : 1,
        keyboardType: f.type == 'email' ? TextInputType.emailAddress : null,
        onChanged: (v) => setState(() {
          _values[f.name] = v.isEmpty ? null : v;
          _fieldErrors.remove(f.name);
        }),
      ),
    );
  }
}
