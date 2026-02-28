import 'dart:async';
import 'dart:convert';
import 'dart:typed_data';

import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';

import '../../../core/widgets/gps_map_field.dart';
import '../../../data/local/form_draft_store.dart';
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

const int _maxFileBytes = 5 * 1024 * 1024; // 5MB

class _WorkOrderFormScreenState extends ConsumerState<WorkOrderFormScreen>
    with WidgetsBindingObserver {
  FormModel? _form;
  final _values = <String, dynamic>{};
  final _fileData = <String, dynamic>{}; // single: (bytes, filename); photo multi: List<(bytes, filename)>
  final _fieldErrors = <String, String>{};
  bool _loading = true;
  bool _submitting = false;
  String? _error;
  Timer? _draftTimer;
  final _initialValues = <String, dynamic>{};
  bool _hasDraft = false;
  bool _draftWasCleared = false;
  bool _hasUserEdited = false;
  int _formFieldKey = 0;
  String get _draftKey => 'submission_${widget.workOrderId}_${widget.formId}';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _load();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _draftTimer?.cancel();
    if (_hasUserEdited) _saveDraft();
    super.dispose();
  }

  void _scheduleDraftSave() {
    _hasUserEdited = true;
    _draftTimer?.cancel();
    _draftTimer = Timer(const Duration(milliseconds: 1500), () {
      _saveDraft();
    });
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if ((state == AppLifecycleState.paused || state == AppLifecycleState.inactive) && _hasUserEdited) {
      _saveDraft();
    }
  }

  Future<void> _saveDraft() async {
    if (!_hasUserEdited || _form == null || _loading) return;
    final store = ref.read(formDraftStoreProvider);
    final values = <String, dynamic>{};
    for (final e in _values.entries) {
      values[e.key] = _toJsonSafeValue(e.value);
    }
    Map<String, ({Uint8List bytes, String filename})>? flatFiles;
    if (_fileData.isNotEmpty) {
      flatFiles = {};
      for (final e in _fileData.entries) {
        final v = e.value;
        if (v is List && v.isNotEmpty) {
          for (var i = 0; i < v.length; i++) {
            flatFiles['${e.key}__$i'] = v[i] as ({Uint8List bytes, String filename});
          }
        } else if (v is ({Uint8List bytes, String filename})) {
          flatFiles[e.key] = v;
        }
      }
      if (flatFiles!.isEmpty) flatFiles = null;
    }
    await store.saveDraft(_draftKey, values, flatFiles);
    if (mounted) setState(() => _hasDraft = true);
  }

  Future<void> _clearDraft() async {
    final store = ref.read(formDraftStoreProvider);
    await store.removeDraft(_draftKey);
    ref.invalidate(draftKeysProvider);
    ref.read(draftKeysRefreshTriggerProvider.notifier).update((s) => s + 1);
    if (!mounted) return;
    final form = _form;
    setState(() {
      _hasDraft = false;
      _draftWasCleared = true;
      _formFieldKey++;
      _fieldErrors.clear();
      _fileData.clear();
      _values.clear();
      if (form != null) {
        for (final f in form.fields ?? []) {
          if (f.defaultValue != null) _values[f.name] = f.defaultValue;
        }
        if (widget.initialFields != null) {
          for (final e in widget.initialFields!.entries) {
            _values[e.key] = e.value;
          }
        }
        _initialValues.clear();
        for (final e in _values.entries) {
          _initialValues[e.key] = _toJsonSafeValue(e.value);
        }
      }
    });
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
    final store = ref.read(formDraftStoreProvider);
    try {
      final form = await repo.getForm(widget.workOrderId, widget.formId);
      final draft = form != null ? await store.loadDraftWithFiles(_draftKey) : null;
      if (!mounted) return;
      setState(() {
        _form = form;
        if (form != null) {
          _values.clear();
          _fileData.clear();
          if (draft != null) {
            _hasDraft = true;
            for (final e in draft.values.entries) {
              _values[e.key] = e.value;
            }
            if (draft.fileData != null) {
              _fileData.addAll(draft.fileData!);
              final grouped = <String, List<(int, ({Uint8List bytes, String filename}))>>{};
              for (final k in _fileData.keys.toList()) {
                final m = RegExp(r'^(.+)__(\d+)$').firstMatch(k);
                if (m != null) {
                  final base = m.group(1)!;
                  final idx = int.parse(m.group(2)!);
                  final file = _fileData.remove(k) as ({Uint8List bytes, String filename});
                  grouped.putIfAbsent(base, () => []).add((idx, file));
                }
              }
              for (final e in grouped.entries) {
                e.value.sort((a, b) => a.$1.compareTo(b.$1));
                _fileData[e.key] = e.value.map((x) => x.$2).toList();
              }
            }
            for (final f in form.fields ?? []) {
              if (!_values.containsKey(f.name) && f.defaultValue != null) {
                _values[f.name] = f.defaultValue;
              }
            }
          } else {
            for (final f in form.fields ?? []) {
              if (f.defaultValue != null) _values[f.name] = f.defaultValue;
            }
            if (widget.initialFields != null) {
              for (final e in widget.initialFields!.entries) {
                _values[e.key] = e.value;
              }
            }
          }
          _initialValues.clear();
          for (final e in _values.entries) {
            _initialValues[e.key] = _toJsonSafeValue(e.value);
          }
        }
      });
      if (mounted) setState(() => _loading = false);
    } catch (e) {
      if (mounted) setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  List<({Uint8List bytes, String filename})> _getFileList(String fieldName) {
    final v = _fileData[fieldName];
    if (v == null) return [];
    if (v is List) return List<({Uint8List bytes, String filename})>.from(v);
    return [v as ({Uint8List bytes, String filename})];
  }

  Future<void> _pickFile(FormFieldModel field) async {
    if (field.type == 'file') {
      final result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'mp4', 'mov', 'avi', 'mkv', 'webm', 'mp3', 'wav', 'ogg', 'm4a'],
        withData: true,
      );
      if (result == null || result.files.isEmpty) return;
      final f = result.files.single;
      final bytes = f.bytes;
      if (bytes == null || bytes.isEmpty) return;
      if (bytes.length > _maxFileBytes) {
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('File must be 5MB or less')));
        return;
      }
      final filename = f.name.isNotEmpty ? f.name : 'file';
      setState(() => _fileData[field.name] = (bytes: bytes, filename: filename));
      _scheduleDraftSave();
      return;
    }
    if (field.type == 'photo' || field.type == 'image') {
      final source = await showModalBottomSheet<ImageSource>(
        context: context,
        builder: (ctx) => SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ListTile(
                leading: const Icon(Icons.camera_alt),
                title: const Text('Camera'),
                onTap: () => Navigator.pop(ctx, ImageSource.camera),
              ),
              ListTile(
                leading: const Icon(Icons.photo_library),
                title: const Text('Gallery'),
                onTap: () => Navigator.pop(ctx, ImageSource.gallery),
              ),
            ],
          ),
        ),
      );
      if (source == null) return;
      final picker = ImagePicker();
      final x = await picker.pickImage(source: source);
      if (x == null) return;
      final bytes = await x.readAsBytes();
      final list = _getFileList(field.name);
      final totalBytes = list.fold<int>(0, (s, f) => s + f.bytes.length) + bytes.length;
      if (totalBytes > _maxFileBytes) {
        if (mounted) {
          final totalMB = (totalBytes / (1024 * 1024)).toStringAsFixed(2);
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Total would be ${totalMB}MB. Max 5MB for all photos in this field.')));
        }
        return;
      }
      final filename = x.name.isNotEmpty ? x.name : 'image.jpg';
      setState(() {
        list.add((bytes: bytes, filename: filename));
        _fileData[field.name] = list;
      });
      _scheduleDraftSave();
      return;
    }
    final picker = ImagePicker();
    final XFile? x;
    if (field.type == 'video') {
      final source = await showModalBottomSheet<ImageSource>(
        context: context,
        builder: (ctx) => SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ListTile(leading: const Icon(Icons.videocam), title: const Text('Camera'), onTap: () => Navigator.pop(ctx, ImageSource.camera)),
              ListTile(leading: const Icon(Icons.photo_library), title: const Text('Gallery'), onTap: () => Navigator.pop(ctx, ImageSource.gallery)),
            ],
          ),
        ),
      );
      if (source == null) return;
      x = await picker.pickVideo(source: source);
    } else {
      x = await picker.pickImage(source: ImageSource.gallery);
    }
    if (x != null) {
      final bytes = await x.readAsBytes();
      if (bytes.length > _maxFileBytes) {
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('File must be 5MB or less')));
        return;
      }
      final defaultName = field.type == 'video' ? 'video.mp4' : 'image.jpg';
      final filename = x.name.isNotEmpty ? x.name : defaultName;
      setState(() => _fileData[field.name] = (bytes: bytes, filename: filename));
      _scheduleDraftSave();
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
      final isFileType = ['file', 'photo', 'video', 'audio', 'image'].contains(f.type);
      if (isFileType && f.required) {
        final hasFile = (f.type == 'photo' || f.type == 'image') ? _getFileList(f.name).isNotEmpty : _fileData[f.name] != null;
        if (!hasFile) errs[f.name] = '${f.label ?? f.name} is required';
      }
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
      Map<String, ({Uint8List bytes, String filename})>? filesForPending;
      if (_fileData.isNotEmpty) {
        filesForPending = {};
        for (final e in _fileData.entries) {
          final v = e.value;
          if (v is List && v.isNotEmpty) {
            filesForPending[e.key] = v.first as ({Uint8List bytes, String filename});
          } else if (v is ({Uint8List bytes, String filename})) {
            filesForPending[e.key] = v;
          }
        }
        if (filesForPending!.isEmpty) filesForPending = null;
      }
      if (filesForPending != null) {
        await store.addWithFiles(s, filesForPending);
      } else {
        await store.add(s);
      }
      await ref.read(formDraftStoreProvider).removeDraft(_draftKey);
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
        await ref.read(formDraftStoreProvider).removeDraft(_draftKey);
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
      return Scaffold(
        appBar: AppBar(title: const Text('Loading...')),
        body: const Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              CircularProgressIndicator(),
              SizedBox(height: 16),
              Text('Loading form...'),
            ],
          ),
        ),
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

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) async {
        if (didPop) return;
        _draftTimer?.cancel();
        if (!_draftWasCleared && _hasUserEdited) await _saveDraft();
        if (mounted) Navigator.of(context).pop();
      },
      child: Scaffold(
        appBar: AppBar(
          title: Text(form.name),
          actions: [
            if (_hasDraft)
              IconButton(
                onPressed: () async { await _clearDraft(); },
                icon: const Icon(Icons.delete_outline, color: Colors.orange),
                tooltip: 'Clear draft',
              ),
          ],
        ),
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
                ...fields.map((f) => KeyedSubtree(key: ValueKey('${f.name}_$_formFieldKey'), child: _buildField(f))),
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
      ),
    );
  }

  bool _isUpdated(FormFieldModel f) {
    if (['file', 'photo', 'video', 'audio', 'image'].contains(f.type)) {
      if (f.type == 'photo' || f.type == 'image') return _getFileList(f.name).isNotEmpty;
      return _fileData[f.name] != null;
    }
    final a = _initialValues[f.name];
    final b = _toJsonSafeValue(_values[f.name]);
    if (a == b) return false;
    return jsonEncode(a) != jsonEncode(b);
  }

  InputDecoration _decoration(FormFieldModel f) {
    final updated = _isUpdated(f);
    return InputDecoration(
      labelText: '${f.label ?? f.name}${f.required ? ' *' : ''}',
      border: OutlineInputBorder(borderSide: BorderSide(color: updated ? Colors.orange : const Color(0xFFE0E0E0), width: updated ? 2 : 1)),
      enabledBorder: OutlineInputBorder(borderSide: BorderSide(color: updated ? Colors.orange : const Color(0xFFE0E0E0), width: updated ? 2 : 1)),
      focusedBorder: OutlineInputBorder(borderSide: BorderSide(color: updated ? Colors.orange : Theme.of(context).colorScheme.primary, width: updated ? 2 : 1)),
      errorText: _fieldErrors[f.name],
      errorBorder: const OutlineInputBorder(borderSide: BorderSide(color: Colors.red)),
    );
  }

  Widget _wrapIfUpdated(Widget child, FormFieldModel f) {
    if (!_isUpdated(f)) return child;
    return Container(
      decoration: BoxDecoration(
        border: Border.all(color: Colors.orange, width: 2),
        borderRadius: BorderRadius.circular(8),
      ),
      padding: const EdgeInsets.all(8),
      child: child,
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
      _scheduleDraftSave();
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
      _scheduleDraftSave();
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
      _scheduleDraftSave();
    }
  }

  Widget _buildField(FormFieldModel f) {
    final isFile = ['file', 'photo', 'video', 'audio', 'image'].contains(f.type);
    if (isFile) {
      final isMultiPhoto = (f.type == 'photo' || f.type == 'image');
      final fileList = isMultiPhoto ? _getFileList(f.name) : null;
      final hasSingle = !isMultiPhoto && _fileData[f.name] != null;
      final buttonLabel = f.type == 'video'
          ? (hasSingle ? 'Change video' : 'Pick video')
          : (isMultiPhoto ? 'Add photo' : (hasSingle ? 'Change file' : 'Pick file'));
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: _wrapIfUpdated(
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('${f.label ?? f.name}${f.required ? ' *' : ''}'),
              if (_fieldErrors[f.name] != null)
                Padding(
                  padding: const EdgeInsets.only(bottom: 4),
                  child: Text(_fieldErrors[f.name]!, style: const TextStyle(color: Colors.red, fontSize: 12)),
                ),
              const SizedBox(height: 4),
              if (isMultiPhoto && (fileList?.isNotEmpty ?? false))
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    for (var i = 0; i < fileList!.length; i++)
                      Stack(
                        children: [
                          ClipRRect(
                            borderRadius: BorderRadius.circular(8),
                            child: Image.memory(fileList[i].bytes, width: 72, height: 72, fit: BoxFit.cover),
                          ),
                          Positioned(
                            top: 4,
                            right: 4,
                            child: GestureDetector(
                              onTap: () {
                                setState(() {
                                  fileList.removeAt(i);
                                  _fileData[f.name] = fileList.isEmpty ? null : fileList;
                                });
                                _scheduleDraftSave();
                              },
                              child: const CircleAvatar(radius: 12, backgroundColor: Colors.black54, child: Icon(Icons.close, size: 16, color: Colors.white)),
                            ),
                          ),
                        ],
                      ),
                  ],
                ),
              if (isMultiPhoto && (fileList?.isNotEmpty ?? false)) const SizedBox(height: 8),
              OutlinedButton(
                onPressed: () => _pickFile(f),
                child: Text(buttonLabel),
              ),
              if (!isMultiPhoto && _fileData[f.name] != null)
                Text((_fileData[f.name] as ({Uint8List bytes, String filename})).filename,
                    style: Theme.of(context).textTheme.bodySmall),
              if (isMultiPhoto && (fileList?.isNotEmpty ?? false))
                Text('${fileList!.length} photo(s). Total max 5MB.', style: Theme.of(context).textTheme.bodySmall),
            ],
          ),
          f,
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
          onChanged: (v) {
            setState(() {
              _values[f.name] = v;
              _fieldErrors.remove(f.name);
            });
            _scheduleDraftSave();
          },
        ),
      );
    }
    if (f.type == 'radio' && (f.options?.isNotEmpty ?? false)) {
      final current = _values[f.name]?.toString();
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: _wrapIfUpdated(
          Column(
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
                onChanged: (v) {
                  setState(() {
                    _values[f.name] = v;
                    _fieldErrors.remove(f.name);
                  });
                  _scheduleDraftSave();
                },
              )),
            ],
          ),
          f,
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
        child: _wrapIfUpdated(
          Column(
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
                      _scheduleDraftSave();
                    },
                  );
                }).toList(),
              ),
            ],
          ),
          f,
        ),
      );
    }
    if (f.type == 'checkbox') {
      final v = _values[f.name];
      final checked = v == true || v == 'true' || v == '1' || v == 1;
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: _wrapIfUpdated(
          CheckboxListTile(
            title: Text('${f.label ?? f.name}${f.required ? ' *' : ''}'),
            value: checked,
            onChanged: (val) {
              setState(() {
                _values[f.name] = val == true;
                _fieldErrors.remove(f.name);
              });
              _scheduleDraftSave();
            },
            controlAffinity: ListTileControlAffinity.leading,
          ),
          f,
        ),
      );
    }
    if (f.type == 'gps') {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: _wrapIfUpdated(
          GpsMapField(
        label: '${f.label ?? f.name}${f.required ? ' *' : ''}',
        value: _values[f.name],
        errorText: _fieldErrors[f.name],
        onChanged: (v) {
          setState(() {
            _values[f.name] = v;
            _fieldErrors.remove(f.name);
          });
          _scheduleDraftSave();
        },
          ),
          f,
        ),
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
          onChanged: (v) {
            setState(() {
              _values[f.name] = v.isEmpty ? null : num.tryParse(v);
              _fieldErrors.remove(f.name);
            });
            _scheduleDraftSave();
          },
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
        onChanged: (v) {
          setState(() {
            _values[f.name] = v.isEmpty ? null : v;
            _fieldErrors.remove(f.name);
          });
          _scheduleDraftSave();
        },
      ),
    );
  }
}
