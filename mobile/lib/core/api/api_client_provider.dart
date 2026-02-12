import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../storage/secure_storage.dart';
import 'api_client.dart';

final secureStorageProvider = Provider<SecureStorage>((ref) => SecureStorage());

final authFailureCountProvider = StateProvider<int>((ref) => 0);

final apiClientProvider = Provider<ApiClient>((ref) {
  final storage = ref.watch(secureStorageProvider);
  final failureCount = ref.read(authFailureCountProvider.notifier);
  return ApiClient(
    getToken: storage.getToken,
    onTokenRefreshed: (token) => storage.setToken(token),
    onAuthFailure: () async {
      await storage.clearAuth();
      failureCount.state++;
    },
  );
});
