import 'package:flutter/material.dart';
import '../colors.dart';

class InfoChip extends StatelessWidget {
  final String label;
  final Color? bg;
  const InfoChip(this.label, {super.key, this.bg});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: bg ?? DSColors.purpleTint,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(label, style: const TextStyle(fontWeight: FontWeight.w600)),
    );
  }
}
