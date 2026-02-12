import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'core/api/api_client_provider.dart';
import 'features/auth/presentation/auth_providers.dart';
import 'features/auth/presentation/login_screen.dart';
import 'features/work_orders/presentation/work_orders_list_screen.dart';
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
    final authState = ref.watch(authStateProvider);

    return authState.when(
      loading: () => const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      ),
      error: (_, __) => const LoginScreen(),
      data: (user) {
        if (user == null) return const LoginScreen();
        return _SyncOnConnectivity(child: const WorkOrdersListScreen());
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
