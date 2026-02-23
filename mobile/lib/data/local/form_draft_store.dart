import 'dart:typed_data';

import 'form_draft_store_impl_io.dart'
    if (dart.library.html) 'form_draft_store_impl_web.dart' as impl;

class FormDraft {
  final Map<String, dynamic> values;
  final Map<String, String>? filePaths;

  FormDraft({required this.values, this.filePaths});
}

class FormDraftWithFiles {
  final Map<String, dynamic> values;
  final Map<String, ({Uint8List bytes, String filename})>? fileData;

  FormDraftWithFiles({required this.values, this.fileData});
}

class FormDraftStore {
  static String sanitizeKey(String key) =>
      key.replaceAll(RegExp(r'[/\\:]'), '_');

  Future<void> saveDraft(
    String key,
    Map<String, dynamic> values,
    Map<String, ({Uint8List bytes, String filename})>? fileData,
  ) async {
    await impl.saveDraft(key, values, fileData);
  }

  Future<FormDraft?> loadDraft(String key) async {
    return impl.loadDraft(key);
  }

  Future<FormDraftWithFiles?> loadDraftWithFiles(String key) async {
    return impl.loadDraftWithFiles(key);
  }

  Future<bool> draftExists(String key) async {
    return impl.draftExists(key);
  }

  Future<List<String>> listDraftKeys() async {
    return impl.listDraftKeys();
  }

  Future<void> removeDraft(String key) async {
    await impl.removeDraft(key);
  }
}
