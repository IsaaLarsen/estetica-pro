import 'package:flutter/material.dart';
import 'gradient_app_bar.dart';

class AppScaffold extends StatelessWidget {
  final String title;
  final Widget body;
  final List<Widget>? actions;
  final Widget? fab;

  const AppScaffold({super.key, required this.title, required this.body, this.actions, this.fab});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: GradientAppBar(title: title, actions: actions),
      body: body,
      floatingActionButton: fab,
    );
  }
}
