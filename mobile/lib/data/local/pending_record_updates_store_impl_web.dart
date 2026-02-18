List<Map<String, dynamic>> _list = [];

Future<List<Map<String, dynamic>>> loadPendingRecordUpdatesJson() async {
  return List.from(_list);
}

Future<void> savePendingRecordUpdatesJson(List<Map<String, dynamic>> list) async {
  _list = List.from(list);
}
