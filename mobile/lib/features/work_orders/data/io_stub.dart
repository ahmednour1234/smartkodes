import 'dart:typed_data';

class File {
  File(String path);
  Future<bool> exists() => Future.value(false);
  Future<Uint8List> readAsBytes() => throw UnsupportedError('File not available on web');
}
