import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app_shell.dart';
import 'core/fcm/fcm_service.dart';
import 'core/presentation/splash_screen.dart';
import 'core/theme/app_theme.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await FcmService.init();
  runApp(const ProviderScope(child: SmartSiteApp()));
}

class SmartSiteApp extends StatelessWidget {
  const SmartSiteApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'SmartSite',
      theme: AppTheme.light,
      home: const SplashScreen(),
    );
  }
}
