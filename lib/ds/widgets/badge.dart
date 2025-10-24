import 'package:flutter/material.dart';
import '../colors.dart';

class Badge extends StatelessWidget {
  final String text;
  final bool success;
  const Badge({super.key, required this.text, this.success = true});

  @override
  Widget build(BuildContext context) {
    final bg = success ? const Color(0xFFEFFBF3) : const Color(0xFFFFF1F2);
    final fg = success ? DSColors.success : DSColors.danger;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(999)),
      child: Text(text, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: fg)),
    );
  }
}
