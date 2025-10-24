import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'features/servicos/servicos_page.dart';

void main() {
  runApp(const ProviderScope(child: EsteticaApp()));
}

class EsteticaApp extends StatelessWidget {
  const EsteticaApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Estética PRO',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFFEC4899)),
        useMaterial3: true,
      ),
      home: const ServicosPage(),
    );
  }
}
