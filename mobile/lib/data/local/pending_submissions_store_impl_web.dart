import 'dart:typed_data';

List<Map<String, dynamic>> _pendingList = [];

Future<Map<String, String>> writeFilesForSubmission(
  String id,
  Map<String, ({Uint8List bytes, String filename})> fileData,
) async {
  return {};
}

Future<List<Map<String, dynamic>>> loadPendingJson() async {
  return List.from(_pendingList);
}

Future<void> savePendingJson(List<Map<String, dynamic>> list) async {
  _pendingList = List.from(list);
}
