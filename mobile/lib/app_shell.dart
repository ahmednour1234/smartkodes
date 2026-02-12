import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'core/api/api_client_provider.dart';
import 'features/auth/presentation/auth_providers.dart';
import 'features/auth/presentation/login_screen.dart';
import 'features/auth/presentation/set_passcode_screen.dart';
import 'features/auth/presentation/verify_passcode_screen.dart';
import 'features/home/presentation/field_worker_home_screen.dart';
import 'features/work_orders/presentation/work_order_providers.dart';

class AppShell extends ConsumerStatefulWidget {
  const AppShell({super.key});

  @override
  ConsumerState<AppShell> createState() => _AppShellState();
}

class _AppShellState extends ConsumerState<AppShell> {
  @override
  Widget build(BuildContext context) {
    ref.listen(authFailureCountProvider, (prev, next) {
      if (next > 0) {
        ref.read(authStateProvider.notifier).forceUnauthenticated();
      }
    });
    ref.listen(authStateProvider, (prev, next) {
      final user = next.valueOrNull;
      if (user == null) {
        ref.read(passcodeVerifiedForSessionProvider.notifier).state = false;
        ref.read(skipPasscodeSetupProvider.notifier).state = false;
      }
    });
    final authState = ref.watch(authStateProvider);

    return authState.when(
      loading: () => const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      ),
      error: (_, __) => const LoginScreen(),
      data: (user) {
        if (user == null) return const LoginScreen();
        final hasPasscode = ref.watch(hasPasscodeProvider);
        final verified = ref.watch(passcodeVerifiedForSessionProvider);
        final skipped = ref.watch(skipPasscodeSetupProvider);

        return hasPasscode.when(
          loading: () => const Scaffold(
            body: Center(child: CircularProgressIndicator()),
          ),
          error: (_, __) => _SyncOnConnectivity(child: const FieldWorkerHomeScreen()),
          data: (has) {
            if (has && !verified) {
              return VerifyPasscodeScreen(
                onSuccess: () {
                  ref.read(passcodeVerifiedForSessionProvider.notifier).state = true;
                },
              );
            }
            if (!has && !skipped) {
              return SetPasscodeScreen(
                onDone: () {
                  ref.read(passcodeVerifiedForSessionProvider.notifier).state = true;
                  ref.invalidate(hasPasscodeProvider);
                },
                onSkip: () {
                  ref.read(skipPasscodeSetupProvider.notifier).state = true;
                },
              );
            }
            return _SyncOnConnectivity(child: const FieldWorkerHomeScreen());
          },
        );
      },
    );
  }
}

class _SyncOnConnectivity extends ConsumerStatefulWidget {
  const _SyncOnConnectivity({required this.child});

  final Widget child;

  @override
  ConsumerState<_SyncOnConnectivity> createState() => _SyncOnConnectivityState();
}

class _SyncOnConnectivityState extends ConsumerState<_SyncOnConnectivity> {
  StreamSubscription<ConnectivityResult>? _sub;

  @override
  void initState() {
    super.initState();
    _sub = Connectivity().onConnectivityChanged.listen((result) {
      if (result != ConnectivityResult.none) {
        ref.read(syncServiceProvider).syncPending();
      }
    });
  }

  @override
  void dispose() {
    _sub?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => widget.child;
}
