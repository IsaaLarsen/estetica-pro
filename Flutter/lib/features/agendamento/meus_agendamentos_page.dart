import 'package:flutter/material.dart';
import 'package:dio/dio.dart';

import '../../core/api_client.dart';
import '../../ds/colors.dart';
import '../../ds/theme.dart';
import '../../ds/formatters.dart';
import '../../ds/widgets/app_scaffold.dart';
import '../../ds/widgets/states_empty_error_loading.dart';
import '../../ds/widgets/info_chip.dart';

import 'feedback_page.dart';

class MeusAgendamentosPage extends StatefulWidget {
  const MeusAgendamentosPage({super.key});

  @override
  State<MeusAgendamentosPage> createState() => _MeusAgendamentosPageState();
}

class _MeusAgendamentosPageState extends State<MeusAgendamentosPage> {
  late final Dio dio;

  String? _status; // null = todos | agendado | confirmado | concluido | cancelado
  bool _loading = true;
  String? _error;

  /// lista completa trazida da API
  List<Map<String, dynamic>> _allItems = [];

  /// contagem por status: {'agendado': 2, 'concluido': 3, ...}
  Map<String, int> _statusCounts = {};

  @override
  void initState() {
    super.initState();
    dio = ApiClient.build();
    _load();
  }

  /// Busca todos os agendamentos do cliente logado
  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final res = await dio.get('/agendamentos');

      final data = res.data;
      if (data is List) {
        _allItems = data
            .map((e) => e is Map<String, dynamic>
            ? e
            : Map<String, dynamic>.from(e as Map))
            .toList();
      } else if (data is Map && data['data'] is List) {
        // caso venha paginado
        final list = data['data'] as List;
        _allItems = list
            .map((e) => e is Map<String, dynamic>
            ? e
            : Map<String, dynamic>.from(e as Map))
            .toList();
      } else {
        _allItems = [];
      }

      // atualiza contagem por status (sempre sobre a lista completa)
      final counts = <String, int>{};
      for (final it in _allItems) {
        final s = (it['status'] ?? '').toString().toLowerCase();
        if (s.isEmpty) continue;
        counts[s] = (counts[s] ?? 0) + 1;
      }
      _statusCounts = counts;
    } catch (e) {
      _error = e.toString();
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  /// Lista filtrada para exibição, de acordo com _status
  List<Map<String, dynamic>> get _items {
    if (_status == null) return _allItems;
    final filtro = _status!.toLowerCase();
    return _allItems.where((it) {
      final s = (it['status'] ?? '').toString().toLowerCase();
      return s == filtro;
    }).toList();
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'agendado':
        return const Color(0xFF2563EB); // azul
      case 'confirmado':
        return const Color(0xFF16A34A); // verde
      case 'concluido':
        return const Color(0xFF10B981); // verde água
      case 'cancelado':
        return const Color(0xFFEF4444); // vermelho
      default:
        return DSColors.textLight;
    }
  }

  String _statusLabel(String status) {
    switch (status) {
      case 'agendado':
        return 'Agendado';
      case 'confirmado':
        return 'Confirmado';
      case 'concluido':
        return 'Concluído';
      case 'cancelado':
        return 'Cancelado';
      default:
        return status;
    }
  }

  int _countFor(String? statusKey) {
    if (statusKey == null) {
      // total
      return _allItems.length;
    }
    return _statusCounts[statusKey.toLowerCase()] ?? 0;
  }

  Future<void> _cancelarAgendamento(Map<String, dynamic> item) async {
    final id = item['id'];
    if (id == null) return;

    final status = (item['status'] ?? '').toString().toLowerCase();
    if (status != 'agendado' && status != 'confirmado') {
      return; // não cancela outros status
    }

    final confirma = await showDialog<bool>(
      context: context,
      builder: (ctx) {
        return AlertDialog(
          title: const Text('Cancelar agendamento'),
          content: const Text(
              'Tem certeza que deseja cancelar este agendamento?'),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(ctx).pop(false),
              child: const Text('Não'),
            ),
            TextButton(
              onPressed: () => Navigator.of(ctx).pop(true),
              child: const Text(
                'Sim, cancelar',
                style: TextStyle(color: Colors.red),
              ),
            ),
          ],
        );
      },
    );

    if (confirma != true) return;

    try {
      await dio.delete('/agendamentos/$id');

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Agendamento cancelado com sucesso.')),
      );

      // remove da lista local e recalcula contagens
      _allItems.removeWhere((e) => e['id'] == id);

      final counts = <String, int>{};
      for (final it in _allItems) {
        final s = (it['status'] ?? '').toString().toLowerCase();
        if (s.isEmpty) continue;
        counts[s] = (counts[s] ?? 0) + 1;
      }

      setState(() {
        _statusCounts = counts;
      });
    } on DioException catch (e) {
      if (!mounted) return;
      final msg = e.response?.data is Map &&
          (e.response!.data['message'] != null)
          ? e.response!.data['message'].toString()
          : 'Não foi possível cancelar o agendamento.';
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(msg)),
      );
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
            content: Text('Erro inesperado ao cancelar o agendamento.')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Theme(
      data: DSTheme.light(),
      child: AppScaffold(
        title: 'Meus agendamentos',
        body: RefreshIndicator(
          onRefresh: _load,
          child: _loading
              ? const DSLoadingList()
              : _error != null
              ? DSError(
            msg: _error!,
            onRetry: _load,
          )
              : _items.isEmpty
              ? const DSEmpty(
            title: 'Nenhum agendamento encontrado',
            subtitle:
            'Você ainda não possui atendimentos cadastrados.',
          )
              : ListView(
            padding: const EdgeInsets.fromLTRB(0, 8, 0, 24),
            children: [
              _buildStatusFilter(),
              const SizedBox(height: 8),
              ..._items.map(_buildItemCard),
              const SizedBox(height: 80),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatusFilter() {
    final statuses = <String?, String>{
      null: 'Todos',
      'agendado': 'Agendados',
      'confirmado': 'Confirmados',
      'concluido': 'Concluídos',
      'cancelado': 'Cancelados',
    };

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      child: Row(
        children: statuses.entries.map((entry) {
          final selected = _status == entry.key;
          final count = _countFor(entry.key);
          final label =
          count > 0 ? '${entry.value} ($count)' : entry.value;

          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: ChoiceChip(
              label: Text(
                label,
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: selected ? FontWeight.w600 : FontWeight.w400,
                ),
              ),
              selected: selected,
              onSelected: (_) {
                setState(() {
                  _status = entry.key;
                });
              },
              selectedColor: const Color(0xFFEC4899).withOpacity(0.15),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildItemCard(Map<String, dynamic> item) {
    final statusRaw = (item['status'] ?? '').toString().toLowerCase();
    final isConcluido = statusRaw == 'concluido';
    final podeCancelar =
        statusRaw == 'agendado' || statusRaw == 'confirmado';

    final servicoNome = (item['servico'] is Map
        ? item['servico']['nome']
        : item['servico_nome']) ??
        'Serviço';

    final profissionalNome = (item['funcionario'] is Map
        ? item['funcionario']['nome']
        : item['funcionario_nome']) ??
        '';

    final inicioStr = (item['inicio_formatado'] ??
        item['inicio_br'] ??
        item['inicio'] ??
        item['data'])
        ?.toString() ??
        '';

    final valorNum =
        item['servico_valor'] ?? item['valor'] ?? item['preco'] ?? 0;

    final valor = DSFormat.brl.format(
      valorNum is num ? valorNum : num.tryParse(valorNum.toString()) ?? 0,
    );

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      elevation: 1,
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // linha título + data
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // ícone
                Container(
                  width: 42,
                  height: 42,
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFE4F1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(
                    Icons.event_available_rounded,
                    color: Color(0xFFEC4899),
                  ),
                ),
                const SizedBox(width: 10),
                // textos
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        servicoNome.toString(),
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                          color: DSColors.text,
                        ),
                      ),
                      if (profissionalNome.toString().isNotEmpty)
                        Padding(
                          padding: const EdgeInsets.only(top: 2),
                          child: Text(
                            'Profissional: $profissionalNome',
                            style: const TextStyle(
                              fontSize: 12,
                              color: DSColors.textLight,
                            ),
                          ),
                        ),
                      if (inicioStr.isNotEmpty)
                        Padding(
                          padding: const EdgeInsets.only(top: 2),
                          child: Text(
                            inicioStr,
                            style: const TextStyle(
                              fontSize: 12,
                              color: DSColors.textLight,
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ],
            ),

            const SizedBox(height: 10),

            // chips valor + status
            Row(
              children: [
                InfoChip(
                  valor,
                  bg: const Color(0xFFFEF3F8),
                ),
                const SizedBox(width: 8),
                InfoChip(
                  _statusLabel(statusRaw),
                  bg: _statusColor(statusRaw).withOpacity(0.08),
                  textColor: _statusColor(statusRaw),
                ),
              ],
            ),

            const SizedBox(height: 12),

            // ações: cancelar e/ou feedback
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                if (podeCancelar)
                  OutlinedButton.icon(
                    icon: const Icon(
                      Icons.close_rounded,
                      size: 18,
                      color: Colors.red,
                    ),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Colors.red),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 8,
                      ),
                    ),
                    label: const Text(
                      'Cancelar',
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        color: Colors.red,
                      ),
                    ),
                    onPressed: () => _cancelarAgendamento(item),
                  ),

                if (podeCancelar && isConcluido) const SizedBox(width: 8),

                if (isConcluido)
                  OutlinedButton.icon(
                    icon: const Icon(
                      Icons.rate_review_outlined,
                      size: 18,
                      color: Color(0xFFEC4899),
                    ),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Color(0xFFEC4899)),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      padding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 8,
                      ),
                    ),
                    label: const Text(
                      'Feedback',
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        color: Color(0xFFEC4899),
                      ),
                    ),
                    onPressed: () async {
                      final result = await Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => FeedbackPage(agendamento: item),
                        ),
                      );

                      if (result == true) {
                        _load();
                      }
                    },
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
