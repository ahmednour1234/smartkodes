import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../theme/app_theme.dart';

class AppLogo extends StatelessWidget {
  const AppLogo({
    super.key,
    this.size = 22,
    this.lightBackground = false,
    this.color,
    this.showImage = true,
    this.bigLogo = false,
  });

  final double size;
  final bool lightBackground;
  final Color? color;
  final bool showImage;
  final bool bigLogo;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final c = color ??
        (lightBackground
            ? AppPalette.primaryDark
            : theme.colorScheme.onPrimaryContainer);
    final coolFont = GoogleFonts.orbitron(color: c, fontWeight: FontWeight.w800);
    if (!showImage) {
      return Text(
        'SMARTSITE',
        style: coolFont.copyWith(fontSize: size, letterSpacing: 2),
      );
    }
    if (bigLogo) {
      const logoSize = 120.0;
      return Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Image.asset('assets/NewIcon.png', width: logoSize, height: logoSize, fit: BoxFit.contain),
          const SizedBox(height: 12),
          Text(
            'SMARTSITE',
            style: coolFont.copyWith(fontSize: 28, letterSpacing: 3),
          ),
        ],
      );
    }
    return Row(
      mainAxisSize: MainAxisSize.min,
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Image.asset('assets/NewIcon.png', width: size + 2, height: size + 2, fit: BoxFit.contain),
        const SizedBox(width: 8),
        Text(
          'SMARTSITE',
          style: coolFont.copyWith(fontSize: size, letterSpacing: 1.2),
        ),
      ],
    );
  }
}
