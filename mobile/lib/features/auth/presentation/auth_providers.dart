import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/api/api_client_provider.dart';
import '../data/auth_repository.dart';
import 'auth_notifier.dart';

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(
    ref.watch(apiClientProvider),
    ref.watch(secureStorageProvider),
  );
});

final authStateProvider =
    AsyncNotifierProvider<AuthNotifier, AsyncValue<User?>>(AuthNotifier.new);
