import 'package:flutter/material.dart';
import 'package:dio/dio.dart';

import '../../core/api_client.dart';
import '../../ds/theme.dart';
import '../../ds/colors.dart';
import '../../ds/widgets/app_scaffold.dart';

class FeedbackPage extends StatefulWidget {
  final Map<String, dynamic> agendamento;

  const FeedbackPage({
    super.key,
    required this.agendamento,
  });

  @override
  State<FeedbackPage> createState() => _FeedbackPageState();
}

class _FeedbackPageState extends State<FeedbackPage> {
  final _comentarioController = TextEditingController();
  int? _nota;
  bool _submitting = false;
  String? _error;

  @override
  void dispose() {
    _comentarioController.dispose();
    super.dispose();
  }

  int? _getServicoId() {
    // tenta direto
    final idDireto = widget.agendamento['servico_id'];
    if (idDireto is int) return idDireto;
    if (idDireto is String) {
      final parsed = int.tryParse(idDireto);
      if (parsed != null) return parsed;
    }

    // tenta aninhado (agendamento['servico']['id'])
    final servico = widget.agendamento['servico'];
    if (servico is Map && servico['id'] != null) {
      final nestedId = servico['id'];
      if (nestedId is int) return nestedId;
      if (nestedId is String) {
        final parsed = int.tryParse(nestedId);
        if (parsed != null) return parsed;
      }
    }

    return null;
  }

  Future<void> _enviarFeedback() async {
    final servicoId = _getServicoId();
    final comentario = _comentarioController.text.trim();

    if (servicoId == null) {
      setState(() {
        _error = 'Não foi possível identificar o serviço deste agendamento.';
      });
      return;
    }

    if (comentario.isEmpty) {
      setState(() {
        _error = 'Por favor, escreva um comentário sobre o atendimento.';
      });
      return;
    }

    setState(() {
      _submitting = true;
      _error = null;
    });

    try {
      final dio = ApiClient.build();
      await dio.post('/feedbacks', data: {
        'servico_id': servicoId,
        'nota': _nota,
        'comentario': comentario,
      });

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Feedback enviado com sucesso!'),
        ),
      );

      Navigator.of(context).pop(true); // pode usar pra refresh na tela anterior
    } on DioException catch (e) {
      String msg = 'Erro ao enviar feedback. Tente novamente.';
      if (e.response?.data is Map &&
          (e.response?.data as Map)['message'] != null) {
        msg = (e.response!.data['message']).toString();
      }
      setState(() {
        _error = msg;
      });
    } catch (_) {
      setState(() {
        _error = 'Erro inesperado ao enviar feedback.';
      });
    } finally {
      if (mounted) {
        setState(() {
          _submitting = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final servicoNome = (() {
      final s = widget.agendamento['servico'];
      if (s is Map && s['nome'] != null) return s['nome'].toString();
      if (widget.agendamento['servico_nome'] != null) {
        return widget.agendamento['servico_nome'].toString();
      }
      return 'Serviço';
    })();

    final dataFormatada =
        widget.agendamento['inicio_formatado']?.toString() ??
            widget.agendamento['data']?.toString() ??
            '';

    return Theme(
      data: DSTheme.light(),
      child: AppScaffold(
        title: 'Feedback do atendimento',
        body: ListView(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
          children: [
            // Cabeçalho do atendimento
            Card(
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
              elevation: 1,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      servicoNome,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: DSColors.text,
                      ),
                    ),
                    const SizedBox(height: 4),
                    if (dataFormatada.isNotEmpty)
                      Text(
                        dataFormatada,
                        style: const TextStyle(
                          fontSize: 13,
                          color: DSColors.textLight,
                        ),
                      ),
                    const SizedBox(height: 12),
                    const Text(
                      'Como foi sua experiência com este atendimento?',
                      style: TextStyle(
                        fontSize: 14,
                        color: DSColors.text,
                      ),
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 16),

            // Nota (estrelas)
            const Text(
              'Dê uma nota (opcional)',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: DSColors.text,
              ),
            ),
            const SizedBox(height: 8),
            Row(
              children: List.generate(5, (index) {
                final starIndex = index + 1;
                final isSelected = _nota != null && _nota! >= starIndex;
                return IconButton(
                  padding: EdgeInsets.zero,
                  visualDensity: VisualDensity.compact,
                  onPressed: () {
                    setState(() {
                      _nota = starIndex;
                    });
                  },
                  icon: Icon(
                    isSelected ? Icons.star_rounded : Icons.star_border_rounded,
                    size: 30,
                    color: const Color(0xFFEC4899),
                  ),
                );
              }),
            ),

            const SizedBox(height: 16),

            // Comentário
            const Text(
              'Seu comentário',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: DSColors.text,
              ),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: _comentarioController,
              maxLines: 5,
              decoration: InputDecoration(
                hintText: 'Conte como foi o atendimento, o que você achou...',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),

            if (_error != null) ...[
              const SizedBox(height: 12),
              Text(
                _error!,
                style: const TextStyle(
                  color: Colors.red,
                  fontSize: 13,
                ),
              ),
            ],

            const SizedBox(height: 24),

            SizedBox(
              height: 48,
              child: FilledButton(
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFFEC4899),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                onPressed: _submitting ? null : _enviarFeedback,
                child: _submitting
                    ? const SizedBox(
                  width: 22,
                  height: 22,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    valueColor: AlwaysStoppedAnimation<Color>(
                      Colors.white,
                    ),
                  ),
                )
                    : const Text(
                  'Enviar feedback',
                  style: TextStyle(
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
