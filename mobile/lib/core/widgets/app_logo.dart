import 'package:flutter/material.dart';

import '../theme/app_theme.dart';

class AppLogo extends StatelessWidget {
  const AppLogo({
    super.key,
    this.size = 22,
    this.lightBackground = false,
    this.color,
  });

  final double size;
  final bool lightBackground;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final c = color ??
        (lightBackground
            ? AppPalette.primaryDark
            : theme.colorScheme.onPrimaryContainer);
    return Row(
      mainAxisSize: MainAxisSize.min,
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Icon(Icons.code, size: size + 2, color: c),
        const SizedBox(width: 8),
        Text(
          'SMARTKODES',
          style: theme.textTheme.titleLarge?.copyWith(
            color: c,
            fontWeight: FontWeight.w800,
            letterSpacing: 1.2,
            fontSize: size,
          ),
        ),
      ],
    );
  }
}
