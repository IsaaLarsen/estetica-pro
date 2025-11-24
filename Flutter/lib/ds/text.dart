import 'package:flutter/material.dart';
import 'colors.dart';

class DSText {
  static TextStyle get h1 => const TextStyle(fontSize: 22, fontWeight: FontWeight.w700, color: DSColors.text);
  static TextStyle get h2 => const TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: DSColors.text);
  static TextStyle get body => const TextStyle(fontSize: 14, color: DSColors.text);
  static TextStyle get mute => const TextStyle(fontSize: 13, color: DSColors.textLight);
}
