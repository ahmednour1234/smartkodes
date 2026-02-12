import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../domain/models/form_model.dart';
import '../data/forms_repository.dart';
import 'forms_providers.dart';

class FormUpdateRecordScreen extends ConsumerStatefulWidget {
  const FormUpdateRecordScreen({
    super.key,
    required this.formId,
    required this.form,
  });

  final String formId;
  final FormModel form;

  @override
  ConsumerState<FormUpdateRecordScreen> createState() => _FormUpdateRecordScreenState();
}

class _FormUpdateRecordScreenState extends ConsumerState<FormUpdateRecordScreen> {
  final _recordIdController = TextEditingController();
  final _values = <String, dynamic>{};
  bool _submitting = false;
  String? _error;

  @override
  void dispose() {
    _recordIdController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    final recordId = _recordIdController.text.trim();
    if (recordId.isEmpty) {
      setState(() => _error = 'Enter record ID');
      return;
    }
    setState(() => _submitting = true);
    final repo = ref.read(formsRepositoryProvider);
    try {
      final ok = await repo.updateRecord(widget.formId, recordId, _values);
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
        _error = e.toString();
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final fields = widget.form.fields ?? [];
    fields.sort((a, b) => (a.order ?? 0).compareTo(b.order ?? 0));

    return Scaffold(
      appBar: AppBar(title: const Text('Update record')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (_error != null)
            Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: Text(_error!, style: const TextStyle(color: Colors.red)),
            ),
          TextField(
            controller: _recordIdController,
            decoration: const InputDecoration(
              labelText: 'Record ID',
              border: OutlineInputBorder(),
            ),
          ),
          const SizedBox(height: 16),
          ...fields.map((f) {
            if (f.type == 'select' && (f.options?.isNotEmpty ?? false)) {
              return Padding(
                padding: const EdgeInsets.only(bottom: 16),
                child: DropdownButtonFormField<String>(
                  value: _values[f.name] as String?,
                  decoration: InputDecoration(
                    labelText: f.label ?? f.name,
                    border: const OutlineInputBorder(),
                  ),
                  items: f.options!
                      .map((o) => DropdownMenuItem(value: o, child: Text(o)))
                      .toList(),
                  onChanged: (v) => setState(() => _values[f.name] = v),
                ),
              );
            }
            return Padding(
              padding: const EdgeInsets.only(bottom: 16),
              child: TextFormField(
                decoration: InputDecoration(
                  labelText: f.label ?? f.name,
                  border: const OutlineInputBorder(),
                ),
                onChanged: (v) => setState(() => _values[f.name] = v.isEmpty ? null : v),
              ),
            );
          }),
          const SizedBox(height: 16),
          FilledButton(
            onPressed: _submitting ? null : _submit,
            child: _submitting
                ? const SizedBox(
                    height: 20,
                    width: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Text('Update'),
          ),
        ],
      ),
    );
  }
}
