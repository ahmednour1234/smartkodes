import 'package:dio/dio.dart';

import '../config/env.dart';
import 'api_response.dart';

typedef GetToken = Future<String?> Function();
typedef OnTokenRefreshed = void Function(String token);
typedef OnAuthFailure = void Function();

class ApiClient {
  late final Dio _dio;
  final GetToken getToken;
  final OnTokenRefreshed? onTokenRefreshed;
  final OnAuthFailure? onAuthFailure;

  ApiClient({
    required this.getToken,
    this.onTokenRefreshed,
    this.onAuthFailure,
    String? baseUrl,
  }) {
    _dio = Dio(BaseOptions(
      baseUrl: baseUrl ?? Env.apiBaseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ));
    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: _onRequest,
      onError: _onError,
    ));
  }

  Future<void> _onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    final path = options.path;
    if (_isPublicPath(path)) {
      return handler.next(options);
    }
    final token = await getToken();
    if (token != null && token.isNotEmpty) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    handler.next(options);
  }

  bool _isPublicPath(String path) {
    return path.contains('login') ||
        path.contains('forgot-password') ||
        path.contains('reset-password');
  }

  Future<void> _onError(
    DioException err,
    ErrorInterceptorHandler handler,
  ) async {
    if (err.response?.statusCode == 401 &&
        !err.requestOptions.path.contains('refresh') &&
        !err.requestOptions.path.contains('set-passcode')) {
      final token = await getToken();
      if (token != null && token.isNotEmpty) {
        try {
          final r = await _dio.post<Map<String, dynamic>>(
            'refresh',
            options: Options(
              headers: {'Authorization': 'Bearer $token'},
              extra: {'skipAuth': true},
            ),
          );
          final newToken = r.data?['data']?['token'] as String?;
          if (newToken != null && newToken.isNotEmpty) {
            onTokenRefreshed?.call(newToken);
            err.requestOptions.headers['Authorization'] = 'Bearer $newToken';
            final response = await _dio.fetch(err.requestOptions);
            return handler.resolve(response);
          }
        } catch (_) {}
      }
      onAuthFailure?.call();
    }
    handler.next(err);
  }

  Dio get dio => _dio;

  Future<ApiResponse<T>> request<T>(
    String path, {
    String method = 'GET',
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
    T Function(dynamic)? fromJsonT,
  }) async {
    final response = await _dio.request<Map<String, dynamic>>(
      path,
      data: data,
      queryParameters: queryParameters,
      options: (options ?? Options()).copyWith(method: method),
    );
    final map = response.data ?? {};
    return ApiResponse.fromJson(map, fromJsonT);
  }

  Future<PaginatedResponse<T>> requestPaginated<T>(
    String path, {
    Map<String, dynamic>? queryParameters,
    required T Function(dynamic) fromJsonT,
  }) async {
    final response = await _dio.get<Map<String, dynamic>>(
      path,
      queryParameters: queryParameters,
    );
    final map = response.data ?? {};
    return PaginatedResponse.fromJson(map, fromJsonT);
  }
}
