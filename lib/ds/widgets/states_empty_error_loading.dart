import 'package:flutter/material.dart';

class DSLoadingList extends StatelessWidget {
  final int count;
  const DSLoadingList({super.key, this.count = 6});

  @override
  Widget build(BuildContext context) {
    return Column(children: List.generate(count, (i) => const _Skel()));
  }
}

class _Skel extends StatelessWidget {
  const _Skel();
  @override
  Widget build(BuildContext context) {
    return Card(
      child: SizedBox(
        height: 92,
        child: Row(children: [
          const SizedBox(width: 16),
          Container(width: 48, height: 48, decoration: BoxDecoration(color: Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(8))),
          const SizedBox(width: 12),
          Expanded(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
            Container(height: 14, margin: const EdgeInsets.symmetric(vertical: 6), decoration: _box),
            Container(height: 12, margin: const EdgeInsets.symmetric(vertical: 6), decoration: _box),
            Container(height: 12, margin: const EdgeInsets.symmetric(vertical: 6), decoration: _box),
          ])),
          const SizedBox(width: 16),
        ]),
      ),
    );
  }
  BoxDecoration get _box => BoxDecoration(color: Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(8));
}

class DSError extends StatelessWidget {
  final String msg;
  final VoidCallback onRetry;
  const DSError({super.key, required this.msg, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(child: Padding(
      padding: const EdgeInsets.all(24),
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        const Icon(Icons.wifi_off, size: 42),
        const SizedBox(height: 12),
        const Text('Falha ao carregar'),
        const SizedBox(height: 6),
        Text(msg, textAlign: TextAlign.center, style: const TextStyle(color: Colors.grey)),
        const SizedBox(height: 16),
        FilledButton(onPressed: onRetry, child: const Text('Tentar novamente')),
      ]),
    ));
  }
}

class DSEmpty extends StatelessWidget {
  final String title;
  final String subtitle;
  const DSEmpty({super.key, this.title = 'Nada por aqui', this.subtitle = 'Tente outro filtro ou cadastre um novo item.'});

  @override
  Widget build(BuildContext context) {
    return Center(child: Padding(
      padding: const EdgeInsets.all(24),
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        const Icon(Icons.search_off, size: 42),
        const SizedBox(height: 8),
        Text(title, style: Theme.of(context).textTheme.titleMedium),
        const SizedBox(height: 6),
        Text(subtitle),
      ]),
    ));
  }
}
