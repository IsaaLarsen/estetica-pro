import 'package:flutter/material.dart';
import '../colors.dart';
import '../text.dart';

class GradientAppBar extends StatelessWidget implements PreferredSizeWidget {
  final String title;
  final List<Widget>? actions;
  const GradientAppBar({super.key, required this.title, this.actions});

  @override
  Size get preferredSize => const Size.fromHeight(100);

  @override
  Widget build(BuildContext context) {
    return AppBar(
      toolbarHeight: 100,
      flexibleSpace: const DecoratedBox(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [DSColors.primary, DSColors.secondary],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
      ),
      title: Text(title, style: DSText.h1.copyWith(color: Colors.white)),
      actions: actions,
    );
  }
}
