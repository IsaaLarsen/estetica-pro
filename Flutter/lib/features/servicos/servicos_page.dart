import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// Imports de Infra/Core
import '../../core/api_client.dart';
import '../../core/auth_storage.dart';
import '../../ds/colors.dart';
import '../../ds/formatters.dart';
import '../../ds/theme.dart';

// Imports de Widgets do DS
import '../../ds/widgets/search_field.dart';
import '../../ds/widgets/states_empty_error_loading.dart';
import '../../ds/widgets/gradient_app_bar.dart'; // <--- O novo widget que criamos

// Imports das outras features
import '../../features/agendamento/meus_agendamentos_page.dart';
import '../../features/agendamento/agendamento_page.dart';

// Import local (assumindo que está na mesma pasta 'servicos')
import 'servicos_repo.dart';

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
  String? _clienteNome;

  @override
  void initState() {
    super.initState();
    _loadCliente();
  }

  Future<void> _loadCliente() async {
    try {
      final dio = ApiClient.build();
      final res = await dio.get('/auth/cliente/me');
      final nome = res.data['nome']?.toString() ?? res.data['name']?.toString();
      // Pega o primeiro nome para não quebrar o layout do header
      setState(() => _clienteNome = nome?.split(' ').first ?? 'Cliente');
    } catch (_) {
      setState(() => _clienteNome = null);
    }
  }

  Future<void> _logout() async {
    try {
      final dio = ApiClient.build();
      await dio.post('/auth/cliente/logout');
    } catch (_) {}
    await AuthStorage.clearToken();
    if (!mounted) return;
    Navigator.of(context).pushNamedAndRemoveUntil('/', (route) => false);
  }

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
      child: Scaffold(
        backgroundColor: const Color(0xFFF9FAFB), // Fundo levemente cinza

        // --- 1. O NOVO HEADER ---
        appBar: GradientAppBar(
          title: 'Serviços Disponíveis',
          actions: [
            // Chip de boas-vindas translúcido
            if (_clienteNome != null)
              Center(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.2),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: Colors.white24),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.person, size: 14, color: Colors.white),
                      const SizedBox(width: 6),
                      Text(
                        'Olá, $_clienteNome',
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w600,
                          fontSize: 13,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            const SizedBox(width: 8),
            // Botão de histórico branco
            IconButton(
              tooltip: 'Meus agendamentos',
              icon: const Icon(Icons.calendar_today_rounded, color: Colors.white),
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const MeusAgendamentosPage()),
                );
              },
            ),
            const SizedBox(width: 8),
          ],
        ),

        // --- 2. O CONTEÚDO ---
        body: RefreshIndicator(
          onRefresh: () async {
            await _loadCliente();
            await ref.refresh(servicosFutureProvider.future);
          },
          child: Column(
            children: [
              // Barra de busca em container branco para destaque
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: const BoxDecoration(
                  color: Colors.white,
                  border: Border(bottom: BorderSide(color: Color(0xFFE5E7EB))),
                ),
                child: SearchField(
                  controller: _search,
                  hint: 'Buscar procedimento...', // Texto mais convidativo
                  onChanged: (_) => setState(() {}),
                ),
              ),

              // Lista de Cards
              Expanded(
                child: asyncServicos.when(
                  loading: () => const Padding(
                    padding: EdgeInsets.only(top: 20),
                    child: DSLoadingList(),
                  ),
                  error: (err, st) => DSError(
                    msg: err.toString(),
                    onRetry: () => ref.refresh(servicosFutureProvider),
                  ),
                  data: (items) {
                    final list = _filtered(items);
                    if (list.isEmpty) {
                      return const Padding(
                        padding: EdgeInsets.only(top: 40),
                        child: DSEmpty(
                          title: 'Nenhum serviço encontrado',
                          subtitle: 'Tente buscar por outro nome.',
                        ),
                      );
                    }

                    return ListView.separated(
                      padding: const EdgeInsets.all(16),
                      itemCount: list.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 12),
                      itemBuilder: (context, index) {
                        final s = list[index];
                        return _buildServiceCard(context, s);
                      },
                    );
                  },
                ),
              ),
            ],
          ),
        ),

        // FAB de Logout (Mantido)
        floatingActionButton: FloatingActionButton(
          heroTag: 'logout-fab',
          backgroundColor: DSColors.danger,
          mini: true, // Deixei menor para ser discreto
          onPressed: _logout,
          child: const Icon(Icons.logout, color: Colors.white, size: 20),
        ),
      ),
    );
  }

  // --- 3. WIDGET DO CARD REFEITO ---
  Widget _buildServiceCard(BuildContext context, Map<String, dynamic> s) {
    // Tratamento de dados seguro
    final nome = (s['nome'] ?? s['name'] ?? 'Serviço').toString();
    final descricao = (s['descricao'] ?? s['description'] ?? s['detalhes'] ?? '').toString();
    final precoNum = (s['valor'] ?? s['preco'] ?? 0);
    final preco = DSFormat.brl.format(precoNum is num ? precoNum : num.tryParse(precoNum.toString()) ?? 0);
    final duracao = (s['duracao_minutos'] ?? s['duracao'] ?? '—').toString();

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(16),
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => AgendamentoPage(servico: s)),
            );
          },
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Ícone destacado com fundo leve
                    Container(
                      width: 48,
                      height: 48,
                      decoration: BoxDecoration(
                        color: const Color(0xFFFCE7F3), // Rosa bem claro
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.spa, color: Color(0xFFDB2777)),
                    ),
                    const SizedBox(width: 14),

                    // Título e Descrição
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            nome,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF111827),
                            ),
                          ),
                          if (descricao.isNotEmpty)
                            Padding(
                              padding: const EdgeInsets.only(top: 4),
                              child: Text(
                                descricao,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontSize: 13,
                                  color: Color(0xFF6B7280),
                                  height: 1.4,
                                ),
                              ),
                            ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // Rodapé do Card: Info + Botão Agendar
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    // Chips de info (Preço e Tempo)
                    Row(
                      children: [
                        _infoBadge(Icons.payments_outlined, preco, Colors.green),
                        const SizedBox(width: 12),
                        _infoBadge(Icons.schedule, '$duracao min', Colors.blue),
                      ],
                    ),

                    // Botão "Agendar" visualmente mais leve
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEC4899),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Text(
                        "Agendar",
                        style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 13,
                        ),
                      ),
                    )
                  ],
                )
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _infoBadge(IconData icon, String text, MaterialColor color) {
    return Row(
      children: [
        Icon(icon, size: 16, color: color.shade700),
        const SizedBox(width: 4),
        Text(
          text,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: color.shade700,
          ),
        ),
      ],
    );
  }

  List<Map<String, dynamic>> _filtered(List<Map<String, dynamic>> items) {
    final q = _search.text.trim().toLowerCase();
    if (q.isEmpty) return items;
    return items.where((s) {
      final nome = (s['nome'] ?? s['name'] ?? '').toString().toLowerCase();
      final desc = (s['descricao'] ?? s['description'] ?? '').toString().toLowerCase();
      return nome.contains(q) || desc.contains(q);
    }).toList();
  }
}