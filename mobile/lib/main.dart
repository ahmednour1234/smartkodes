import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app_shell.dart';
import 'core/fcm/fcm_service.dart';

void main() async {
  WidgetsBinding.flutterBinding.ensureInitialized();
  await FcmService.init();
  runApp(const ProviderScope(child: SmartKodesApp()));
}

class SmartKodesApp extends StatelessWidget {
  const SmartKodesApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'SmartKodes',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.blue),
        useMaterial3: true,
      ),
      home: const AppShell(),
    );
  }
}
