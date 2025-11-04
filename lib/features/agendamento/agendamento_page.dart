import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../../core/api_client.dart';
import '../../ds/theme.dart';
import '../../ds/widgets/app_scaffold.dart';
import '../../ds/widgets/info_chip.dart';
import '../../ds/formatters.dart';

num _toNum(dynamic v) => v is num ? v : (num.tryParse(v?.toString() ?? '') ?? 0);

class AgendamentoPage extends StatefulWidget {
  final Map<String, dynamic> servico;
  const AgendamentoPage({super.key, required this.servico});

  @override
  State<AgendamentoPage> createState() => _AgendamentoPageState();
}

class _AgendamentoPageState extends State<AgendamentoPage> {
  late final Dio dio;

  // Funcionários
  bool carregandoFuncionarios = true;
  String? erroFuncionarios;
  List<Map<String, dynamic>> funcionarios = [];
  int? funcionarioId;

  // Data & Slots
  DateTime dataSelecionada = DateTime.now();
  bool carregandoSlots = false;
  String? erroSlots;
  List<String> slots = [];
  String? horarioSelecionado;

  // Cliente logado
  Map<String, dynamic>? cliente;
  bool carregandoCliente = true;

  @override
  void initState() {
    super.initState();
    dio = ApiClient.build();
    _carregarCliente();
    _carregarFuncionarios();
  }

  // ===== API: cliente logado =====
  Future<void> _carregarCliente() async {
    try {
      final res = await dio.get('/auth/cliente/me');
      setState(() {
        cliente = Map<String, dynamic>.from(res.data);
      });
    } catch (e) {
      // caso token inválido
      setState(() {
        cliente = null;
      });
    } finally {
      setState(() => carregandoCliente = false);
    }
  }

  // ===== API: funcionários =====
  Future<void> _carregarFuncionarios() async {
    setState(() {
      carregandoFuncionarios = true;
      erroFuncionarios = null;
    });
    try {
      final servicoId = widget.servico['id'];
      final res = await dio.get('/funcionarios', queryParameters: {
        if (servicoId != null) 'servico_id': servicoId,
      });

      final list = (res.data as List).cast<Map<String, dynamic>>();
      setState(() {
        funcionarios = list;
        funcionarioId = null;
      });
    } catch (e) {
      setState(() => erroFuncionarios = e.toString());
    } finally {
      setState(() => carregandoFuncionarios = false);
    }
  }

  // ===== API: slots =====
  Future<void> _carregarSlots() async {
    if (funcionarioId == null) return;
    setState(() {
      carregandoSlots = true;
      erroSlots = null;
      horarioSelecionado = null;
      slots = [];
    });

    try {
      // data YYYY-MM-DD
      final dataIso =
          "${dataSelecionada.year.toString().padLeft(4, '0')}-"
          "${dataSelecionada.month.toString().padLeft(2, '0')}-"
          "${dataSelecionada.day.toString().padLeft(2, '0')}";

      final servicoId = widget.servico['id'];

      final res = await dio.get('/agenda/slots', queryParameters: {
        'funcionario_id': funcionarioId,
        'servico_id': servicoId, // obrigatório no backend
        'data': dataIso,
      });

      // --- Parse robusto ---
      final payload = res.data;
      List<String> list = [];

      if (payload is Map && payload['slots'] is List) {
        list = (payload['slots'] as List).map((e) => e.toString()).toList();
      } else if (payload is Map && payload['data'] is List) {
        // compat com versões antigas que retornavam uma lista na chave 'data'
        list = (payload['data'] as List).map((e) => e.toString()).toList();
      } else if (payload is List) {
        // compat: resposta já é a lista
        list = payload.map((e) => e.toString()).toList();
      } else if (payload is Map && payload['slots'] == null) {
        // veio explicitamente null
        list = const [];
      } else {
        throw Exception('Formato inesperado: ${payload.runtimeType}');
      }

      setState(() => slots = list);
    } on DioException catch (e) {
      // Mostra mensagens de validação/erro do Laravel
      String msg = e.message ?? 'Erro';
      final data = e.response?.data;
      if (data is Map && data['errors'] is Map) {
        final errs = data['errors'] as Map;
        msg = errs.entries
            .expand((kv) => (kv.value as List).map((x) => '• ${kv.key}: $x'))
            .join('\n');
      } else if (data is Map && data['message'] != null) {
        msg = data['message'].toString();
      } else if (data is String) {
        msg = data;
      }
      setState(() => erroSlots = msg);
    } catch (e) {
      setState(() => erroSlots = e.toString());
    } finally {
      setState(() => carregandoSlots = false);
    }
  }
  bool get _podeConfirmar => funcionarioId != null && horarioSelecionado != null && cliente != null;

  // ===== Confirmar =====
  Future<void> _confirmar() async {
    if (!_podeConfirmar) return;

    final s = widget.servico;
    final servicoId = s['id'] as int?;
    if (servicoId == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Serviço inválido.')));
      return;
    }

    try {
      final dataIso =
          "${dataSelecionada.year.toString().padLeft(4, '0')}-"
          "${dataSelecionada.month.toString().padLeft(2, '0')}-"
          "${dataSelecionada.day.toString().padLeft(2, '0')}";

      await dio.post('/agendamentos', data: {
        'cliente_id': cliente!['id'],
        'servico_id': servicoId,
        'funcionario_id': funcionarioId,
        'data': dataIso,
        'hora_inicio': horarioSelecionado,
        'observacoes': 'Agendado pelo app',
      });

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Agendamento criado com sucesso!'),
        backgroundColor: Colors.green,
      ));
      Navigator.pop(context);
    } on DioException catch (e) {
      final msg = e.response?.data is Map && (e.response!.data['message'] != null)
          ? e.response!.data['message'].toString()
          : e.message ?? e.toString();
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Erro ao agendar: $msg')));
    }
  }

  // ===== UI =====
  @override
  Widget build(BuildContext context) {
    final s = widget.servico;
    final preco = DSFormat.brl.format(_toNum(s['valor'] ?? s['preco'] ?? 0));
    final duracao = (s['duracao_minutos'] ?? s['duracao'] ?? '—').toString();
    final descricao = (s['descricao'] ?? '').toString();

    return Theme(
      data: DSTheme.light(),
      child: AppScaffold(
        title: 'Agendar serviço',
        body: carregandoCliente
            ? const Center(child: CircularProgressIndicator())
            : Padding(
          padding: const EdgeInsets.all(16),
          child: ListView(
            children: [
              // Serviço
              Card(
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Container(
                      width: 56,
                      height: 56,
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFE4F1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.spa_rounded, color: Color(0xFFEC4899), size: 28),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        Text(s['nome'] ?? 'Serviço',
                            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
                        const SizedBox(height: 4),
                        if (descricao.isNotEmpty)
                          Text(descricao,
                              maxLines: 3,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(fontSize: 13, color: Color(0xFF6B7280))),
                        const SizedBox(height: 10),
                        Row(children: [
                          InfoChip(preco, bg: const Color(0xFFFEF3F8)),
                          const SizedBox(width: 8),
                          InfoChip('$duracao min', bg: const Color(0xFFEDE9FE)),
                          const Spacer(),
                          const Text('Expediente 08:00–18:00',
                              style: TextStyle(fontSize: 12, color: Color(0xFF6B7280))),
                        ]),
                      ]),
                    ),
                  ]),
                ),
              ),
              const SizedBox(height: 20),

              // Profissional
              const Text('Profissional disponível:',
                  style: TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
              const SizedBox(height: 6),
              if (carregandoFuncionarios)
                const LinearProgressIndicator()
              else if (erroFuncionarios != null)
                Text('Falha ao carregar: $erroFuncionarios', style: const TextStyle(color: Colors.red))
              else
                DropdownButtonFormField<int>(
                  decoration: InputDecoration(
                    filled: true,
                    fillColor: Colors.white,
                    border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                  ),
                  value: funcionarioId,
                  hint: const Text('Selecione o profissional'),
                  items: funcionarios.map((f) {
                    final id = f['id'] as int;
                    final nome = (f['nome'] ?? 'Funcionário').toString();
                    return DropdownMenuItem<int>(value: id, child: Text(nome));
                  }).toList(),
                  onChanged: (v) {
                    setState(() => funcionarioId = v);
                    _carregarSlots();
                  },
                ),

              const SizedBox(height: 20),

              // Data
              const Text('Data:',
                  style: TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
              const SizedBox(height: 6),
              TextFormField(
                readOnly: true,
                decoration: InputDecoration(
                  filled: true,
                  fillColor: Colors.white,
                  suffixIcon: const Icon(Icons.calendar_today),
                  hintText:
                  "${dataSelecionada.day.toString().padLeft(2, '0')}/"
                      "${dataSelecionada.month.toString().padLeft(2, '0')}/"
                      "${dataSelecionada.year}",
                ),
                onTap: () async {
                  final picked = await showDatePicker(
                    context: context,
                    firstDate: DateTime.now(),
                    lastDate: DateTime.now().add(const Duration(days: 60)),
                    initialDate: dataSelecionada,
                  );
                  if (picked != null) {
                    setState(() => dataSelecionada = picked);
                    _carregarSlots();
                  }
                },
              ),

              const SizedBox(height: 20),

              // Horários
              const Text('Horários disponíveis:',
                  style: TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
              const SizedBox(height: 12),
              if (carregandoSlots)
                const LinearProgressIndicator()
              else if (erroSlots != null)
                Text('Falha ao carregar: $erroSlots', style: const TextStyle(color: Colors.red))
              else if (slots.isEmpty)
                  const Text('Nenhum horário disponível para esta data/profissional.')
                else
                  Wrap(
                    spacing: 12,
                    runSpacing: 12,
                    children: slots.map((hora) {
                      final selecionado = hora == horarioSelecionado;
                      return ChoiceChip(
                        selected: selecionado,
                        label: Text(hora),
                        onSelected: (_) => setState(() => horarioSelecionado = hora),
                        selectedColor: const Color(0xFFEC4899),
                        labelStyle: TextStyle(
                          color: selecionado ? Colors.white : Colors.black87,
                          fontWeight: FontWeight.w600,
                        ),
                      );
                    }).toList(),
                  ),

              const SizedBox(height: 28),
              if (cliente != null)
                Text(
                  'Cliente: ${cliente!['nome']}',
                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15),
                ),
              const SizedBox(height: 20),
              SizedBox(
                height: 50,
                child: FilledButton(
                  style: FilledButton.styleFrom(
                    backgroundColor: const Color(0xFFEC4899),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: _podeConfirmar ? _confirmar : null,
                  child: const Text('Confirmar agendamento',
                      style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
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
