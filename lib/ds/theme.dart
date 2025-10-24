import 'package:flutter/material.dart';
import 'colors.dart';

class DSTheme {
  static ThemeData light() {
    final base = ThemeData.light(useMaterial3: true);
    return base.copyWith(
      scaffoldBackgroundColor: DSColors.bg,
      colorScheme: ColorScheme.fromSeed(
        seedColor: DSColors.primary,
        primary: DSColors.primary,
        secondary: DSColors.secondary,
        surface: DSColors.surface,
      ),
      cardTheme: CardTheme(
        elevation: 0,
        color: DSColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: DSColors.surface,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide.none,
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      ),
    );
  }
}
