import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client_provider.dart';
import '../../../core/storage/secure_storage.dart';
import '../../../domain/models/user.dart';
import '../data/auth_repository.dart';
import 'auth_notifier.dart';

final passcodeVerifiedForSessionProvider = StateProvider<bool>((ref) => false);
final skipPasscodeSetupProvider = StateProvider<bool>((ref) => false);

final hasPasscodeProvider = FutureProvider<bool>((ref) async {
  final storage = ref.watch(secureStorageProvider);
  return storage.hasPasscodeConfigured();
});

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(
    ref.watch(apiClientProvider),
    ref.watch(secureStorageProvider),
  );
});

final authStateProvider = AsyncNotifierProvider<AuthNotifier, User?>(AuthNotifier.new);
