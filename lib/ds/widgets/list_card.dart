import 'package:flutter/material.dart';
import '../colors.dart';
import '../text.dart';

class ListCard extends StatelessWidget {
  final Widget leading;
  final String title;
  final String? subtitle;
  final int subtitleMaxLines; // << NOVO
  final List<Widget> trailingChips;
  final Widget? trailingBadge;
  final VoidCallback? onTap;

  const ListCard({
    super.key,
    required this.leading,
    required this.title,
    this.subtitle,
    this.subtitleMaxLines = 2, // << NOVO
    this.trailingChips = const [],
    this.trailingBadge,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
            leading,
            const SizedBox(width: 12),
            Expanded(
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Row(children: [
                  Expanded(child: Text(title, style: DSText.h2)),
                  if (trailingBadge != null) trailingBadge!,
                ]),
                if (subtitle != null && subtitle!.trim().isNotEmpty) ...[
                  const SizedBox(height: 6),
                  Text(subtitle!, style: DSText.mute, maxLines: 2, overflow: TextOverflow.ellipsis),
                ],
                const SizedBox(height: 10),
                Wrap(spacing: 8, runSpacing: 8, children: trailingChips),
              ]),
            ),
          ]),
        ),
      ),
    );
  }
}

class ListIcon extends StatelessWidget {
  final IconData icon;
  const ListIcon(this.icon, {super.key});
  @override
  Widget build(BuildContext context) {
    return Container(
      width: 48, height: 48,
      decoration: BoxDecoration(color: DSColors.roseSoft, borderRadius: BorderRadius.circular(12)),
      child: Icon(icon),
    );
  }
}
