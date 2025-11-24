import 'package:flutter/material.dart';
import '../theme.dart';
import '../colors.dart';
import 'info_chip.dart';

class DesignPreviewPage extends StatelessWidget {
  const DesignPreviewPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Theme(
      data: DSTheme.light(),
      child: Scaffold(
        appBar: AppBar(title: const Text('Preview — Estética PRO')),
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 900),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Cards / Header
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Row(
                        children: [
                          Container(
                            width: 48, height: 48,
                            decoration: BoxDecoration(
                              color: DSColors.primaryLight,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Icon(Icons.spa_rounded, color: DSColors.primary),
                          ),
                          const SizedBox(width: 12),
                          const Expanded(
                            child: Text('Componentes base com paleta do site',
                              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
                            ),
                          ),
                          const InfoChip('R\$ 80,00'),
                          const SizedBox(width: 8),
                          const InfoChip('30 min', bg: Color(0xFFEDE9FE)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Buttons
                  const Text('Botões', style: TextStyle(fontWeight: FontWeight.w700)),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 12, runSpacing: 12,
                    children: [
                      FilledButton(onPressed: (){}, child: const Text('Primário')),
                      ElevatedButton(onPressed: (){}, child: const Text('Elevated')),
                      TextButton(onPressed: (){}, child: const Text('Link')),
                      FilledButton.tonal(onPressed: (){}, child: const Text('Tonal')),
                    ],
                  ),

                  const SizedBox(height: 20),
                  const Text('Inputs', style: TextStyle(fontWeight: FontWeight.w700)),
                  const SizedBox(height: 8),
                  const TextField(decoration: InputDecoration(labelText: 'Seu nome')),
                  const SizedBox(height: 8),
                  const TextField(obscureText: true, decoration: InputDecoration(labelText: 'Senha')),

                  const SizedBox(height: 20),
                  const Text('Chips / Badges', style: TextStyle(fontWeight: FontWeight.w700)),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 12, runSpacing: 12,
                    children: const [
                      InfoChip('R\$ 50,00'),
                      InfoChip('Duração 45 min', bg: Color(0xFFEDE9FE)),
                      InfoChip('Ativo', bg: Color(0xFFE6FFFB), textColor: Color(0xFF047857)),
                    ],
                  ),

                  const SizedBox(height: 20),
                  const Text('List Item / Card', style: TextStyle(fontWeight: FontWeight.w700)),
                  const SizedBox(height: 8),
                  Card(
                    child: ListTile(
                      leading: Container(
                        width: 44, height: 44,
                        decoration: BoxDecoration(
                          color: DSColors.primaryLight,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(Icons.person, color: DSColors.primary),
                      ),
                      title: const Text('Silvania Santander'),
                      subtitle: const Text('Profissional • Corte e química'),
                      trailing: const Icon(Icons.chevron_right),
                      onTap: (){},
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
