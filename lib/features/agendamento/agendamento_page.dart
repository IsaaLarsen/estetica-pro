import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/api_client.dart';
import '../../ds/theme.dart';
import '../../ds/widgets/app_scaffold.dart';
import '../../ds/widgets/info_chip.dart';
import '../../ds/formatters.dart';

class AgendamentoPage extends StatefulWidget {
  final Map<String, dynamic> servico;
  const AgendamentoPage({super.key, required this.servico});

  @override
  State<AgendamentoPage> createState() => _AgendamentoPageState();
}

class _AgendamentoPageState extends State<AgendamentoPage> {
  String? funcionarioSelecionado;
  String? horarioSelecionado;

  // simulação de dados — depois vamos puxar do Laravel
  final funcionarios = [
    'Ana Oliveira',
    'Beatriz Mendes',
    'Carla Souza',
  ];

  final horarios = [
    '09:00',
    '09:30',
    '10:00',
    '10:30',
    '11:00',
    '13:00',
    '13:30',
    '14:00',
    '14:30',
    '15:00',
  ];

  @override
  Widget build(BuildContext context) {
    final s = widget.servico;
    // helper local:
    num _toNum(dynamic v) => v is num ? v : num.tryParse(v?.toString() ?? '') ?? 0;

// ...
    final preco = DSFormat.brl.format(_toNum(s['valor'] ?? s['preco'] ?? 0));
    final duracao = (s['duracao_minutos'] ?? s['duracao'] ?? '—').toString();
    final descricao = (s['descricao'] ?? '').toString();

    return Theme(
      data: DSTheme.light(),
      child: AppScaffold(
        title: 'Agendar serviço',
        body: Padding(
          padding: const EdgeInsets.all(16),
          child: ListView(
            children: [
              // 🔹 Card com dados do serviço
              Card(
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        width: 56,
                        height: 56,
                        decoration: BoxDecoration(
                          color: const Color(0xFFFFE4F1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(Icons.spa_rounded,
                            color: Color(0xFFEC4899), size: 28),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              s['nome'] ?? 'Serviço',
                              style: const TextStyle(
                                  fontSize: 18, fontWeight: FontWeight.w700),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              descricao,
                              maxLines: 3,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                  color: Color(0xFF6B7280), fontSize: 13),
                            ),
                            const SizedBox(height: 10),
                            Row(
                              children: [
                                InfoChip(preco, bg: const Color(0xFFFEF3F8)),
                                const SizedBox(width: 8),
                                InfoChip('$duracao min',
                                    bg: const Color(0xFFEDE9FE)),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 20),

              // 🔹 Seletor de funcionário
              const Text(
                'Profissional disponível:',
                style: TextStyle(fontWeight: FontWeight.w600, fontSize: 15),
              ),
              const SizedBox(height: 6),
              DropdownButtonFormField<String>(
                decoration: InputDecoration(
                  filled: true,
                  fillColor: Colors.white,
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none),
                ),
                value: funcionarioSelecionado,
                hint: const Text('Selecione o profissional'),
                items: funcionarios
                    .map((f) => DropdownMenuItem(value: f, child: Text(f)))
                    .toList(),
                onChanged: (v) => setState(() => funcionarioSelecionado = v),
              ),

              const SizedBox(height: 24),

              // 🔹 Seleção de horário
              const Text(
                'Selecione o horário:',
                style: TextStyle(fontWeight: FontWeight.w600, fontSize: 15),
              ),
              const SizedBox(height: 12),
              Wrap(
                spacing: 12,
                runSpacing: 12,
                children: horarios.map((hora) {
                  final selecionado = hora == horarioSelecionado;
                  return GestureDetector(
                    onTap: () => setState(() => horarioSelecionado = hora),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 16, vertical: 10),
                      decoration: BoxDecoration(
                        color: selecionado
                            ? const Color(0xFFEC4899)
                            : const Color(0xFFF9FAFB),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: selecionado
                              ? const Color(0xFFEC4899)
                              : const Color(0xFFD1D5DB),
                        ),
                      ),
                      child: Text(
                        hora,
                        style: TextStyle(
                          color: selecionado ? Colors.white : Colors.black87,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  );
                }).toList(),
              ),

              const SizedBox(height: 32),

              // 🔹 Botão confirmar
              SizedBox(
                height: 50,
                child: FilledButton(
                  style: FilledButton.styleFrom(
                    backgroundColor: const Color(0xFFEC4899),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: (funcionarioSelecionado != null &&
                      horarioSelecionado != null)
                      ? () {
                    // TODO: integração Laravel → salvar agendamento
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text(
                            'Agendado: ${s['nome']} com $funcionarioSelecionado às $horarioSelecionado'),
                      ),
                    );
                  }
                      : null,
                  child: const Text(
                    'Confirmar agendamento',
                    style:
                    TextStyle(fontWeight: FontWeight.w700, fontSize: 16),
                  ),
                ),
              ),
              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }
}
