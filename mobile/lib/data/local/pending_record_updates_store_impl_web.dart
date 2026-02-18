import 'dart:typed_data';

List<Map<String, dynamic>> _list = [];

Future<Map<String, String>> writeFilesForUpdate(
  String id,
  Map<String, ({Uint8List bytes, String filename})> fileData,
) async {
  return {};
}

Future<List<Map<String, dynamic>>> loadPendingRecordUpdatesJson() async {
  return List.from(_list);
}

Future<void> savePendingRecordUpdatesJson(List<Map<String, dynamic>> list) async {
  _list = List.from(list);
}
