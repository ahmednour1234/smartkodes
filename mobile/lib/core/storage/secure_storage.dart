import 'dart:io';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:path_provider/path_provider.dart';

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

  Future<File> _passcodeConfiguredFile() async {
    final dir = await getApplicationDocumentsDirectory();
    return File('${dir.path}/.passcode_configured');
  }

  Future<void> setPasscodeConfigured() async {
    final file = await _passcodeConfiguredFile();
    await file.writeAsString('1');
  }

  Future<void> clearPasscodeConfigured() async {
    final file = await _passcodeConfiguredFile();
    if (await file.exists()) await file.delete();
  }

  Future<bool> hasPasscodeConfigured() async {
    final file = await _passcodeConfiguredFile();
    if (!await file.exists()) return false;
    final s = await file.readAsString();
    return s.trim().isNotEmpty;
  }

  Future<File> _passcodeSkippedFile() async {
    final dir = await getApplicationDocumentsDirectory();
    return File('${dir.path}/.passcode_skipped');
  }

  Future<void> setPasscodeSkipped() async {
    final file = await _passcodeSkippedFile();
    await file.writeAsString('1');
  }

  Future<bool> hasPasscodeSkipped() async {
    final file = await _passcodeSkippedFile();
    if (!await file.exists()) return false;
    final s = await file.readAsString();
    return s.trim().isNotEmpty;
  }

  Future<void> clearAuth() async {
    await deleteToken();
    await deleteUser();
    await _storage.delete(key: _passcodeKey);
    await clearPasscodeConfigured();
  }
}
