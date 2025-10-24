import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/api_client.dart';
import 'servicos_repo.dart';

// DS
import '../../ds/theme.dart';
import '../../ds/formatters.dart';
import '../../ds/widgets/app_scaffold.dart';
import '../../ds/widgets/search_field.dart';
import '../../ds/widgets/info_chip.dart';
import '../../ds/widgets/states_empty_error_loading.dart';
import '../../features/agendamento/agendamento_page.dart';

final dioProvider = Provider((ref) => ApiClient.build());
final servicosRepoProvider = Provider((ref) => ServicosRepo(ref.read(dioProvider)));

final servicosFutureProvider =
FutureProvider.autoDispose<List<Map<String, dynamic>>>((ref) async {
  final repo = ref.read(servicosRepoProvider);
  return repo.listarServicos();
});

class ServicosPage extends ConsumerStatefulWidget {
  const ServicosPage({super.key});

  @override
  ConsumerState<ServicosPage> createState() => _ServicosPageState();
}

class _ServicosPageState extends ConsumerState<ServicosPage> {
  final _search = TextEditingController();

  @override
  void dispose() {
    _search.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final asyncServicos = ref.watch(servicosFutureProvider);

    return Theme(
      data: DSTheme.light(),
      child: AppScaffold(
        title: 'Serviços',
        body: RefreshIndicator(
          onRefresh: () async {
            await ref.refresh(servicosFutureProvider.future);
          },
          child: ListView(
            children: [
              SearchField(
                controller: _search,
                hint: 'Buscar serviço...',
                onChanged: (_) => setState(() {}),
              ),
              asyncServicos.when(
                loading: () => const DSLoadingList(),
                error: (err, st) => DSError(
                  msg: err.toString(),
                  onRetry: () => ref.refresh(servicosFutureProvider),
                ),
                data: (items) {
                  final list = _filtered(items);
                  if (list.isEmpty) {
                    return const DSEmpty(
                      title: 'Nenhum serviço encontrado',
                      subtitle: 'Tente outro termo.',
                    );
                  }

                  return Column(
                    children: [
                      ...list.map((s) {
                        final nome = (s['nome'] ?? s['name'] ?? '—').toString();
                        final descricao = (s['descricao'] ??
                            s['description'] ??
                            s['detalhes'] ??
                            s['observacoes'] ??
                            '')
                            .toString();

                        final precoNum = (s['valor'] ?? s['preco'] ?? 0);
                        final preco = DSFormat.brl.format(
                          precoNum is num
                              ? precoNum
                              : num.tryParse(precoNum.toString()) ?? 0,
                        );

                        final duracao =
                        (s['duracao_minutos'] ?? s['duracao'] ?? '—')
                            .toString();


                        // novo layout do card
                        return Card(
                          margin: const EdgeInsets.symmetric(
                              horizontal: 16, vertical: 8),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(16)),
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.center,
                              children: [
                                // Ícone
                                Container(
                                  width: 48,
                                  height: 48,
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFFFE4F1),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: const Icon(Icons.spa_rounded,
                                      color: Color(0xFFEC4899)),
                                ),
                                const SizedBox(width: 12),

                                // Texto principal
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                    CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        nome,
                                        style: const TextStyle(
                                          fontSize: 16,
                                          fontWeight: FontWeight.w700,
                                          color: Color(0xFF1F2937),
                                        ),
                                      ),
                                      if (descricao.isNotEmpty)
                                        Padding(
                                          padding:
                                          const EdgeInsets.only(top: 4),
                                          child: Text(
                                            descricao,
                                            maxLines: 3,
                                            overflow: TextOverflow.ellipsis,
                                            style: const TextStyle(
                                              fontSize: 13,
                                              color: Color(0xFF6B7280),
                                            ),
                                          ),
                                        ),
                                      const SizedBox(height: 10),
                                      Row(
                                        children: [
                                          InfoChip(preco,
                                              bg: const Color(0xFFFEF3F8)),
                                          const SizedBox(width: 8),
                                          InfoChip('$duracao min',
                                              bg: const Color(0xFFEDE9FE)),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),

                                // Botão lateral “Agendar”
                                Container(
                                  margin: const EdgeInsets.only(left: 12),
                                  height: 44,
                                  child: FilledButton(
                                    style: FilledButton.styleFrom(
                                      backgroundColor:
                                      const Color(0xFFEC4899), // rosa
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      padding: const EdgeInsets.symmetric(
                                          horizontal: 20),
                                    ),
                                    onPressed: () {
                                      Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (_) => AgendamentoPage(servico: s),
                                        ),
                                      );
                                    },
                                    child: const Text(
                                      'Agendar',
                                      style: TextStyle(
                                          fontWeight: FontWeight.w700,
                                          color: Colors.white),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      }),
                      const SizedBox(height: 80),
                    ],
                  );
                },
              ),
            ],
          ),
        ),
        fab: null, // sem botão flutuante
      ),
    );
  }

  List<Map<String, dynamic>> _filtered(List<Map<String, dynamic>> items) {
    final q = _search.text.trim().toLowerCase();
    if (q.isEmpty) return items;
    return items.where((s) {
      final nome = (s['nome'] ?? s['name'] ?? '').toString().toLowerCase();
      final desc = (s['descricao'] ??
          s['description'] ??
          s['detalhes'] ??
          s['observacoes'] ??
          '')
          .toString()
          .toLowerCase();
      return nome.contains(q) || desc.contains(q);
    }).toList();
  }
}
