import 'package:flutter/material.dart';
import '../colors.dart';

class InfoChip extends StatelessWidget {
  final String label;
  final Color? bg;
  final Color? textColor;

  const InfoChip(this.label, {super.key, this.bg, this.textColor});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: bg ?? DSColors.primaryLight, // 🌸 antes era purpleTint
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: textColor ?? DSColors.primaryDark,
          fontWeight: FontWeight.w600,
          fontSize: 13,
        ),
      ),
    );
  }
}
