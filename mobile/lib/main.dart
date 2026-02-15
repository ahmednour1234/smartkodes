import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app_shell.dart';
import 'core/fcm/fcm_service.dart';
import 'core/presentation/splash_screen.dart';
import 'core/theme/app_theme.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await FcmService.init();
  runApp(const ProviderScope(child: SmartKodesApp()));
}

class SmartKodesApp extends StatelessWidget {
  const SmartKodesApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'SmartKodes',
      theme: AppTheme.light,
      home: const SplashScreen(),
    );
  }
}
