import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class SecureStorage {
  static const _tokenKey = 'auth_token';
  static const _userKey = 'auth_user';
  static const _passcodeKey = 'passcode';

  final FlutterSecureStorage _storage = const FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );

  Future<String?> getToken() => _storage.read(key: _tokenKey);
  Future<void> setToken(String token) => _storage.write(key: _tokenKey, value: token);
  Future<void> deleteToken() => _storage.delete(key: _tokenKey);

  Future<String?> getUserJson() => _storage.read(key: _userKey);
  Future<void> setUserJson(String json) => _storage.write(key: _userKey, value: json);
  Future<void> deleteUser() => _storage.delete(key: _userKey);

  Future<String?> getPasscode() => _storage.read(key: _passcodeKey);
  Future<void> setPasscode(String code) => _storage.write(key: _passcodeKey, value: code);

  Future<void> clearAuth() async {
    await deleteToken();
    await deleteUser();
    await _storage.delete(key: _passcodeKey);
  }
}
