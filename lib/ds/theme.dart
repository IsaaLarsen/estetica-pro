import 'package:flutter/material.dart';
import 'colors.dart';

class DSTheme {
  DSTheme._();

  static ThemeData light() {
    final base = ThemeData(
      useMaterial3: true,
      brightness: Brightness.light,
      scaffoldBackgroundColor: DSColors.background,
      fontFamily: 'Poppins', // se não tiver, o Flutter cai no fallback
    );

    // ColorScheme consistente com a paleta do site
    final cs = ColorScheme.fromSeed(
      seedColor: DSColors.primary,
      brightness: Brightness.light,
      primary: DSColors.primary,
      onPrimary: Colors.white,
      secondary: DSColors.secondary,
      onSecondary: Colors.white,
      surface: DSColors.surface,
      onSurface: DSColors.text,
      background: DSColors.background,
      onBackground: DSColors.text,
      error: DSColors.danger,
      onError: Colors.white,
    );

    return base.copyWith(
      colorScheme: cs,

      // ------ Tipografia base ------
      textTheme: base.textTheme.apply(
        bodyColor: DSColors.text,
        displayColor: DSColors.text,
      ).copyWith(
        titleLarge: const TextStyle(fontWeight: FontWeight.w700),
        titleMedium: const TextStyle(fontWeight: FontWeight.w700),
        titleSmall: const TextStyle(fontWeight: FontWeight.w600),
        bodySmall: TextStyle(color: DSColors.textLight),
      ),

      // ------ AppBar (usada quando não há gradient custom) ------
      appBarTheme: const AppBarTheme(
        elevation: 0,
        backgroundColor: Colors.transparent,
        foregroundColor: DSColors.text,
        centerTitle: false,
        titleTextStyle: TextStyle(
          color: DSColors.text,
          fontWeight: FontWeight.w700,
          fontSize: 20,
        ),
        iconTheme: IconThemeData(color: DSColors.text),
      ),

      // ------ Botões ------
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          elevation: 0,
          backgroundColor: DSColors.primary,
          foregroundColor: Colors.white,
          disabledBackgroundColor: DSColors.primary.withOpacity(.5),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
          textStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          elevation: 0,
          backgroundColor: DSColors.primary,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
          textStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: DSColors.primaryDark,
          textStyle: const TextStyle(fontWeight: FontWeight.w600),
        ),
      ),

      // ------ Inputs ------
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: DSColors.surface,
        hintStyle: TextStyle(color: DSColors.textLight),
        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: DSColors.border),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: DSColors.border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: DSColors.primary, width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: DSColors.danger),
        ),
      ),

      // ------ Chips ------
      chipTheme: base.chipTheme.copyWith(
        selectedColor: DSColors.primary,
        disabledColor: DSColors.border,
        backgroundColor: DSColors.background,
        labelStyle: const TextStyle(color: DSColors.text),
        secondaryLabelStyle: const TextStyle(color: Colors.white),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      ),

      // ------ Cards ------
      cardTheme: CardTheme(
        color: DSColors.surface,
        elevation: 0,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        margin: const EdgeInsets.all(0),
      ),

      // ------ BottomSheet/Dialog/Snackbar ------
      bottomSheetTheme: const BottomSheetThemeData(
        backgroundColor: DSColors.surface,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
        ),
      ),
      dialogTheme: DialogTheme(
        backgroundColor: DSColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        elevation: 0,
      ),
      snackBarTheme: const SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        backgroundColor: DSColors.text,
        contentTextStyle: TextStyle(color: Colors.white),
      ),

      // ------ Itens gerais ------
      dividerTheme: const DividerThemeData(color: DSColors.border, thickness: 1),
      listTileTheme: const ListTileThemeData(
        contentPadding: EdgeInsets.symmetric(horizontal: 12),
        iconColor: DSColors.textLight,
        titleTextStyle: TextStyle(color: DSColors.text, fontWeight: FontWeight.w600),
        subtitleTextStyle: TextStyle(color: DSColors.textLight),
      ),
      iconTheme: const IconThemeData(color: DSColors.text),
    );
  }

  // Opcional – esqueleto de dark mode (se quiser depois)
  static ThemeData dark() {
    final base = ThemeData.dark(useMaterial3: true);
    final cs = ColorScheme.fromSeed(
      seedColor: DSColors.primary,
      brightness: Brightness.dark,
      primary: DSColors.primary,
      secondary: DSColors.secondary,
    );
    return base.copyWith(colorScheme: cs);
  }
}
