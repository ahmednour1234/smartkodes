List<Map<String, dynamic>> _pendingList = [];

Future<List<Map<String, dynamic>>> loadPendingJson() async {
  return List.from(_pendingList);
}

Future<void> savePendingJson(List<Map<String, dynamic>> list) async {
  _pendingList = List.from(list);
}
