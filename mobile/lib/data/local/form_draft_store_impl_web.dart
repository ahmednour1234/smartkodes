import 'dart:typed_data';

import 'form_draft_store.dart';

Future<void> saveDraft(
  String key,
  Map<String, dynamic> values,
  Map<String, ({Uint8List bytes, String filename})>? fileData,
) async {}

Future<FormDraft?> loadDraft(String key) async => null;

Future<FormDraftWithFiles?> loadDraftWithFiles(String key) async => null;

Future<bool> draftExists(String key) async => false;

Future<List<String>> listDraftKeys() async => [];

Future<void> removeDraft(String key) async {}
