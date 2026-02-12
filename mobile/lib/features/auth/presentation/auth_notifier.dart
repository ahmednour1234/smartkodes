import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../domain/models/user.dart';
import '../data/auth_repository.dart';
import 'auth_providers.dart';

class AuthNotifier extends AsyncNotifier<User?> {
  @override
  Future<User?> build() async {
    final token = await ref.read(authRepositoryProvider).getStoredToken();
    if (token == null || token.isEmpty) return null;
    return ref.read(authRepositoryProvider).getStoredUser();
  }

  Future<void> login(String email, String password) async {
    state = const AsyncValue.loading();
    final result = await ref.read(authRepositoryProvider).login(email, password);
    if (result.isSuccess) {
      state = AsyncValue.data(result.user);
    } else {
      state = AsyncValue.error(
        Exception(result.error),
        StackTrace.current,
      );
    }
  }

  Future<void> logout() async {
    await ref.read(authRepositoryProvider).logout();
    state = const AsyncValue.data(null);
  }

  void forceUnauthenticated() {
    state = const AsyncValue.data(null);
  }

  Future<bool> setPasscode(String passcode) async {
    return ref.read(authRepositoryProvider).setPasscode(passcode);
  }

  Future<bool> verifyPasscode(String passcode) async {
    return ref.read(authRepositoryProvider).verifyPasscode(passcode);
  }
}
