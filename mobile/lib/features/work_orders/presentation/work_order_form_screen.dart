import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';

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
  bool _loading = true;
  bool _submitting = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
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
    final x = await picker.pickImage(source: ImageSource.gallery);
    if (x != null) {
      final bytes = await x.readAsBytes();
      final filename = x.name.isNotEmpty ? x.name : 'image.jpg';
      setState(() => _fileData[field.name] = (bytes: bytes, filename: filename));
    }
  }

  Future<void> _submit() async {
    if (_form == null) return;
    setState(() => _submitting = true);
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
      final store = ref.read(pendingSubmissionsStoreProvider);
      await store.add(PendingSubmission(
        workOrderId: widget.workOrderId,
        formId: widget.formId,
        fields: Map<String, dynamic>.from(_values),
        filePaths: null,
        latitude: lat,
        longitude: lon,
        createdAt: DateTime.now(),
      ));
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
        _error = e.toString();
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

  Widget _buildField(FormFieldModel f) {
    final isFile = ['file', 'photo', 'video', 'audio', 'image'].contains(f.type);
    if (isFile) {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('${f.label ?? f.name}${f.required ? ' *' : ''}'),
            const SizedBox(height: 4),
            OutlinedButton(
              onPressed: () => _pickFile(f),
              child: Text(_fileData[f.name] != null ? 'Change file' : 'Pick file'),
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
          decoration: InputDecoration(
            labelText: '${f.label ?? f.name}${f.required ? ' *' : ''}',
            border: const OutlineInputBorder(),
          ),
          items: f.options!
              .map((o) => DropdownMenuItem(value: o, child: Text(o)))
              .toList(),
          onChanged: (v) => setState(() => _values[f.name] = v),
        ),
      );
    }
    if (f.type == 'number' || f.type == 'currency') {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: TextFormField(
          keyboardType: TextInputType.number,
          decoration: InputDecoration(
            labelText: '${f.label ?? f.name}${f.required ? ' *' : ''}',
            border: const OutlineInputBorder(),
          ),
          initialValue: _values[f.name]?.toString(),
          onChanged: (v) => setState(() => _values[f.name] = v.isEmpty ? null : num.tryParse(v)),
        ),
      );
    }
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: TextFormField(
        decoration: InputDecoration(
          labelText: '${f.label ?? f.name}${f.required ? ' *' : ''}',
          border: const OutlineInputBorder(),
        ),
        initialValue: _values[f.name]?.toString(),
        maxLines: f.type == 'textarea' ? 4 : 1,
        onChanged: (v) => setState(() => _values[f.name] = v.isEmpty ? null : v),
      ),
    );
  }
}
