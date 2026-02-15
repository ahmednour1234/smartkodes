import 'package:flutter/material.dart';

abstract class AppPalette {
  static const Color primaryDark = Color(0xFF03045E);
  static const Color primaryRich = Color(0xFF023E8A);
  static const Color primary = Color(0xFF0077B6);
  static const Color primaryBright = Color(0xFF0096C7);
  static const Color accentCyan = Color(0xFF00B4D8);
  static const Color aquaLight = Color(0xFF48CAE4);
  static const Color skyLight = Color(0xFF90E0EF);
  static const Color pastelBlue = Color(0xFFADE8F4);
  static const Color surfacePale = Color(0xFFCAF0F8);
  static const Color surfaceGrey = Color(0xFFF5F5F5);
}


class AppTheme {
  static ThemeData get light {
    const scheme = ColorScheme.light(
      primary: AppPalette.primary,
      onPrimary: Colors.white,
      primaryContainer: AppPalette.primaryRich,
      onPrimaryContainer: Colors.white,
      secondary: AppPalette.primaryBright,
      onSecondary: Colors.white,
      secondaryContainer: AppPalette.aquaLight,
      onSecondaryContainer: AppPalette.primaryDark,
      tertiary: AppPalette.accentCyan,
      onTertiary: AppPalette.primaryDark,
      tertiaryContainer: AppPalette.skyLight,
      onTertiaryContainer: AppPalette.primaryDark,
      surface: AppPalette.surfaceGrey,
      onSurface: AppPalette.primaryDark,
      onSurfaceVariant: AppPalette.primaryRich,
      outline: AppPalette.primary,
      outlineVariant: AppPalette.skyLight,
      surfaceContainerHighest: Color(0xFFEEEEEE),
      surfaceContainerHigh: Color(0xFFF0F0F0),
      surfaceContainer: AppPalette.surfaceGrey,
      surfaceContainerLow: Color(0xFFFAFAFA),
      surfaceBright: Colors.white,
      error: Color(0xFFBA1A1A),
      onError: Colors.white,
      errorContainer: Color(0xFFFFDAD6),
      onErrorContainer: Color(0xFF410002),
      inverseSurface: AppPalette.primaryDark,
      onInverseSurface: AppPalette.surfacePale,
      inversePrimary: AppPalette.aquaLight,
      shadow: Color(0xFF000000),
      scrim: Color(0xFF000000),
    );
    return ThemeData(
      colorScheme: scheme,
      useMaterial3: true,
      appBarTheme: const AppBarTheme(
        backgroundColor: AppPalette.primaryDark,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
      ),
      cardTheme: CardThemeData(
        elevation: 1,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        color: Colors.white,
        margin: EdgeInsets.zero,
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: AppPalette.primary,
          foregroundColor: Colors.white,
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        border: const OutlineInputBorder(),
        focusedBorder: const OutlineInputBorder(
          borderSide: BorderSide(color: AppPalette.primary, width: 2),
        ),
        filled: true,
        fillColor: Colors.white,
      ),
      drawerTheme: const DrawerThemeData(
        backgroundColor: AppPalette.surfaceGrey,
      ),
      scaffoldBackgroundColor: AppPalette.surfaceGrey,
      bottomSheetTheme: const BottomSheetThemeData(
        backgroundColor: Colors.white,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
        ),
      ),
    );
  }
}
