import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../domain/models/form_model.dart';
import '../data/forms_repository.dart';
import 'forms_providers.dart';
import 'form_update_record_screen.dart';

class FormDetailScreen extends ConsumerStatefulWidget {
  const FormDetailScreen({super.key, required this.formId});

  final String formId;

  @override
  ConsumerState<FormDetailScreen> createState() => _FormDetailScreenState();
}

class _FormDetailScreenState extends ConsumerState<FormDetailScreen> {
  FormModel? _form;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final repo = ref.read(formsRepositoryProvider);
    try {
      final form = await repo.get(widget.formId);
      setState(() {
        _form = form;
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  Future<void> _download() async {
    if (_form == null) return;
    final repo = ref.read(formsRepositoryProvider);
    await repo.cacheForm(_form!);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Form saved for offline use')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }
    if (_error != null || _form == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Form')),
        body: Center(child: Text(_error ?? 'Not found')),
      );
    }
    final form = _form!;
    return Scaffold(
      appBar: AppBar(
        title: Text(form.name),
        actions: [
          IconButton(
            icon: const Icon(Icons.download),
            onPressed: _download,
            tooltip: 'Save for offline',
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (form.description != null) Text(form.description!),
            if (form.version != null) Text('Version: ${form.version}'),
            Text('Fields: ${form.fields?.length ?? form.fieldsCount ?? 0}'),
            const SizedBox(height: 24),
            FilledButton.icon(
              onPressed: () => Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => FormUpdateRecordScreen(
                    formId: widget.formId,
                    form: form,
                  ),
                ),
              ),
              icon: const Icon(Icons.edit),
              label: const Text('Update record (missing data)'),
            ),
          ],
        ),
      ),
    );
  }
}
