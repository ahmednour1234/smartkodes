import 'dart:convert';

import 'package:dio/dio.dart';

import '../../../core/api/api_client.dart';
import '../../../core/api/api_response.dart';
import '../../../core/storage/secure_storage.dart';
import '../../../domain/models/user.dart';

class AuthRepository {
  AuthRepository(this._client, this._storage);

  final ApiClient _client;
  final SecureStorage _storage;

  Future<LoginResult> login(String email, String password) async {
    try {
      final response = await _client.request<Map<String, dynamic>>(
        'login',
        method: 'POST',
        data: {'email': email, 'password': password},
        fromJsonT: (d) => d as Map<String, dynamic>,
      );
      if (!response.success || response.data == null) {
        return LoginResult.failure(
          response.message.isNotEmpty ? response.message : 'Invalid email or password',
        );
      }
      final data = response.data!;
      final token = data['token'] as String?;
      final userMap = data['user'] as Map<String, dynamic>?;
      if (token == null || userMap == null) {
        return LoginResult.failure('Invalid response');
      }
      final user = User.fromJson(userMap);
      await _storage.setToken(token);
      await _storage.setUserJson(jsonEncode(user.toJson()));
      return LoginResult.success(user, token);
    } on DioException catch (e) {
      final msg = e.response?.data is Map
          ? (e.response!.data as Map)['message'] as String?
          : null;
      if (msg != null && msg.toString().trim().isNotEmpty) {
        return LoginResult.failure(msg.toString().trim());
      }
      if (e.response?.data is Map && (e.response!.data as Map)['errors'] != null) {
        final errors = (e.response!.data as Map)['errors'];
        if (errors is Map && errors.isNotEmpty) {
          final first = errors.values.first;
          final text = first is List ? first.join(' ') : first.toString();
          return LoginResult.failure(text);
        }
      }
      return LoginResult.failure(
        e.response?.statusCode == 401
            ? 'Invalid email or password'
            : 'Login failed. Please try again.',
      );
    }
  }

  Future<void> logout() async {
    await _storage.clearAuth();
    try {
      await _client.dio.post('logout');
    } catch (_) {}
  }

  Future<String?> refreshToken() async {
    final response = await _client.dio.post<Map<String, dynamic>>('refresh');
    final data = response.data?['data'];
    if (data is Map && data['token'] != null) {
      final token = data['token'] as String;
      await _storage.setToken(token);
      return token;
    }
    return null;
  }

  Future<User?> getMe() async {
    final response = await _client.request<Map<String, dynamic>>(
      'me',
      fromJsonT: (d) => d as Map<String, dynamic>,
    );
    if (!response.success || response.data == null) return null;
    return User.fromJson(response.data!);
  }

  Future<bool> setPasscode(String passcode) async {
    await _storage.setPasscode(passcode);
    await _storage.setPasscodeConfigured();
    try {
      await _client.request<dynamic>(
        'users/set-passcode',
        method: 'POST',
        data: {'passcode': passcode},
      );
    } catch (_) {}
    return true;
  }

  Future<bool> verifyPasscode(String passcode) async {
    final response = await _client.request<dynamic>(
      'users/verify-passcode',
      method: 'POST',
      data: {'passcode': passcode},
    );
    return response.success;
  }

  Future<String?> getStoredToken() => _storage.getToken();
  Future<User?> getStoredUser() async {
    final json = await _storage.getUserJson();
    if (json == null) return null;
    try {
      return User.fromJson(Map<String, dynamic>.from(jsonDecode(json) as Map));
    } catch (_) {
      return null;
    }
  }
}

class LoginResult {
  final User? user;
  final String? token;
  final String? error;

  LoginResult._({this.user, this.token, this.error});
  factory LoginResult.success(User user, String token) =>
      LoginResult._(user: user, token: token);
  factory LoginResult.failure(String error) => LoginResult._(error: error);
  bool get isSuccess => user != null && token != null;
}
