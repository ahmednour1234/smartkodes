import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';

import '../../../core/config/env.dart';
import '../../../core/widgets/gps_map_field.dart';
import '../../../domain/models/form_model.dart';
import '../data/forms_repository.dart';
import 'forms_providers.dart';

class FormUpdateRecordScreen extends ConsumerStatefulWidget {
  const FormUpdateRecordScreen({
    super.key,
    required this.formId,
    required this.form,
    this.recordId,
    this.initialFields,
  });

  final String formId;
  final FormModel form;
  final String? recordId;
  final Map<String, dynamic>? initialFields;

  @override
  ConsumerState<FormUpdateRecordScreen> createState() => _FormUpdateRecordScreenState();
}

class _FormUpdateRecordScreenState extends ConsumerState<FormUpdateRecordScreen> {
  final _recordIdController = TextEditingController();
  final _values = <String, dynamic>{};
  final _fileData = <String, ({Uint8List bytes, String filename})>{};
  final _fieldErrors = <String, String>{};
  bool _submitting = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    if (widget.recordId != null) {
      _recordIdController.text = widget.recordId!;
    }
    for (final f in widget.form.fields ?? []) {
      if (f.defaultValue != null) _values[f.name] = f.defaultValue;
    }
    if (widget.initialFields != null) {
      for (final e in widget.initialFields!.entries) {
        _values[e.key] = e.value;
      }
    }
  }

  @override
  void dispose() {
    _recordIdController.dispose();
    super.dispose();
  }

  String? _existingFileLabel(dynamic value) {
    if (value == null) return null;
    if (value is String && value.trim().isNotEmpty) {
      final s = value.trim();
      if (s.startsWith('{')) return null;
      return s.contains('/') ? s.split('/').last : s;
    }
    if (value is Map && value.isNotEmpty) {
      final name = value['filename'] ?? value['name'] ?? value['path'];
      if (name != null) return name.toString().split('/').last;
    }
    return null;
  }

  String? _existingImageUrl(dynamic value) {
    if (value == null) return null;
    final base = Env.apiBaseUrl.replaceAll(RegExp(r'/api/v1/?$'), '');
    if (value is String && value.trim().isNotEmpty) {
      final s = value.trim();
      if (s.startsWith('http')) return s;
      if (s.contains('/')) return '$base/storage/$s'.replaceFirst(RegExp(r'//+'), '//');
      return null;
    }
    if (value is Map) {
      final u = value['url'] ?? value['path'];
      if (u != null) {
        final s = u.toString().trim();
        if (s.startsWith('http')) return s;
        if (s.contains('/')) return '$base/storage/$s'.replaceFirst(RegExp(r'//+'), '//');
      }
    }
    return null;
  }

  Widget _filePlaceholder(BuildContext context, String? label) {
    return Container(
      height: 180,
      color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(0.5),
      alignment: Alignment.center,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.insert_drive_file_outlined, size: 48, color: Theme.of(context).colorScheme.onSurfaceVariant),
          if (label != null && label.isNotEmpty) ...[
            const SizedBox(height: 8),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Text(label, style: Theme.of(context).textTheme.bodySmall, textAlign: TextAlign.center, maxLines: 2, overflow: TextOverflow.ellipsis),
            ),
          ],
        ],
      ),
    );
  }

  Future<void> _pickFile(FormFieldModel field) async {
    final picker = ImagePicker();
    final x = await picker.pickImage(source: ImageSource.gallery);
    if (x != null) {
      final bytes = await x.readAsBytes();
      final filename = x.name.isNotEmpty ? x.name : 'image.jpg';
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
    final recordId = widget.recordId ?? _recordIdController.text.trim();
    if (recordId.isEmpty) {
      setState(() => _error = 'Enter record ID');
      return;
    }
    final fields = widget.form.fields ?? [];
    final errs = <String, String>{};
    for (final f in fields) {
      final v = _values[f.name];
      final isFileType = ['file', 'photo', 'video', 'audio', 'image'].contains(f.type);
      final hasFileValue = isFileType && (_fileData[f.name] != null || (v != null && v.toString().trim().isNotEmpty));
      if (isFileType && hasFileValue) {
        continue;
      }
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
    final fileTypes = ['file', 'photo', 'video', 'audio', 'image'];
    final fileFieldNames = <String>{};
    for (final f in fields) {
      if (fileTypes.contains(f.type)) fileFieldNames.add(f.name);
    }
    final fieldsToSend = <String, dynamic>{};
    for (final entry in _values.entries) {
      if (fileFieldNames.contains(entry.key) && _fileData[entry.key] == null) {
        continue;
      }
      fieldsToSend[entry.key] = entry.value;
    }
    final repo = ref.read(formsRepositoryProvider);
    try {
      final ok = await repo.updateRecord(
        widget.formId,
        recordId,
        fieldsToSend,
        fileFields: _fileData.isEmpty ? null : _fileData,
      );
      setState(() => _submitting = false);
      if (ok && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Record updated')),
        );
        Navigator.of(context).pop();
      } else {
        setState(() => _error = 'Update failed');
      }
    } catch (e) {
      setState(() {
        _submitting = false;
        if (e is RecordUpdateValidationException) {
          _fieldErrors.clear();
          _fieldErrors.addAll(e.fieldErrors);
          _error = e.message;
        } else {
          _error = e.toString();
        }
      });
    }
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
      final existingFile = _values[f.name];
      final existingLabel = _existingFileLabel(existingFile);
      final existingUrl = _existingImageUrl(existingFile);
      final hasNewFile = _fileData[f.name] != null;
      final title = f.label ?? f.name;
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('$title${f.required ? ' *' : ''}'),
            if (_fieldErrors[f.name] != null)
              Padding(
                padding: const EdgeInsets.only(bottom: 4),
                child: Text(_fieldErrors[f.name]!, style: const TextStyle(color: Colors.red, fontSize: 12)),
              ),
            const SizedBox(height: 8),
            Card(
              clipBehavior: Clip.antiAlias,
              elevation: 1,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12), side: BorderSide(color: Theme.of(context).dividerColor)),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Padding(
                    padding: const EdgeInsets.fromLTRB(12, 10, 12, 6),
                    child: Text(title, style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w600)),
                  ),
                  if (hasNewFile)
                    Image.memory(_fileData[f.name]!.bytes, height: 180, fit: BoxFit.cover)
                  else if (existingUrl != null)
                    Image.network(existingUrl, height: 180, fit: BoxFit.cover, headers: const {'ngrok-skip-browser-warning': 'true'}, errorBuilder: (_, __, ___) => _filePlaceholder(context, existingLabel))
                  else if (existingLabel != null)
                    _filePlaceholder(context, existingLabel)
                  else
                    _filePlaceholder(context, 'No file'),
                  Padding(
                    padding: const EdgeInsets.all(10),
                    child: OutlinedButton(
                      onPressed: () => _pickFile(f),
                      child: Text(hasNewFile ? 'Change file' : (existingLabel != null ? 'Replace file' : 'Pick file')),
                    ),
                  ),
                ],
              ),
            ),
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

  @override
  Widget build(BuildContext context) {
    final fields = widget.form.fields ?? [];
    fields.sort((a, b) => (a.order ?? 0).compareTo(b.order ?? 0));

    return Scaffold(
      appBar: AppBar(title: Text('${widget.form.name} – Update')),
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
                if (widget.recordId == null)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 16),
                    child: TextFormField(
                      controller: _recordIdController,
                      decoration: const InputDecoration(
                        labelText: 'Record ID',
                        border: OutlineInputBorder(),
                      ),
                    ),
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
                  : const Text('Update'),
            ),
          ),
        ],
      ),
    );
  }
}
