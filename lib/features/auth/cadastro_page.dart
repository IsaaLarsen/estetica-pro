import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../../core/api_client.dart';

class CadastroPage extends StatefulWidget {
  const CadastroPage({super.key});

  @override
  State<CadastroPage> createState() => _CadastroPageState();
}

class _CadastroPageState extends State<CadastroPage> {
  // controllers
  final nome = TextEditingController();
  final email = TextEditingController();
  final telefone = TextEditingController();
  final senha = TextEditingController();
  final cpf = TextEditingController();
  final endereco = TextEditingController();
  DateTime? dataNascimento;
  bool ativo = true;

  bool loading = false;
  String? error;
  String? success;

  Future<void> _registrar() async {
    setState(() {
      loading = true;
      error = null;
      success = null;
    });

    final dio = ApiClient.build();

    // monta data YYYY-MM-DD (ou null)
    String? dataIso;
    if (dataNascimento != null) {
      dataIso =
      "${dataNascimento!.year.toString().padLeft(4, '0')}-"
          "${dataNascimento!.month.toString().padLeft(2, '0')}-"
          "${dataNascimento!.day.toString().padLeft(2, '0')}";
    }

    try {
      final res = await dio.post('/auth/cliente/register', data: {
        'nome': nome.text.trim(),
        'email': email.text.trim(),
        'telefone': telefone.text.trim().isEmpty ? null : telefone.text.trim(),
        'password': senha.text,
        'cpf': cpf.text.trim().isEmpty ? null : cpf.text.trim(),
        'endereco': endereco.text.trim().isEmpty ? null : endereco.text.trim(),
        'data_nascimento': dataIso,
        'ativo': ativo ? 1 : 0,
      });

      if (res.statusCode == 201) {
        setState(() {
          success = 'Conta criada com sucesso! Você já pode entrar com seu e-mail e senha.';
        });
      } else {
        // resposta válida mas inesperada
        final data = res.data;
        final msg = data is Map && data['message'] != null
            ? data['message'].toString()
            : 'Erro ao criar conta';
        setState(() => error = msg);
      }
    } on DioException catch (e) {
      // TRATAMENTO ROBUSTO
      final data = e.response?.data;
      String msg = 'Erro de conexão';

      if (data is Map) {
        if (data['errors'] is Map) {
          // validação do Laravel
          final errs = (data['errors'] as Map).entries
              .expand((kv) => (kv.value as List).map((x) => '• ${kv.key}: $x'))
              .join('\n');
          msg = errs.isEmpty ? 'Dados inválidos' : errs;
        } else if (data['message'] != null) {
          msg = data['message'].toString();
        }
      } else if (data is String) {
        // quando Laravel devolve string pura
        msg = data;
      } else if (e.message != null) {
        msg = e.message!;
      }

      setState(() => error = msg);
    } catch (e) {
      setState(() => error = e.toString());
    } finally {
      setState(() => loading = false);
    }
  }

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime(now.year - 18, now.month, now.day),
      firstDate: DateTime(1900),
      lastDate: now,
    );
    if (picked != null) {
      setState(() => dataNascimento = picked);
    }
  }

  @override
  Widget build(BuildContext context) {
    final dataLabel = dataNascimento == null
        ? 'Selecionar data de nascimento'
        : '${dataNascimento!.day.toString().padLeft(2, '0')}/'
        '${dataNascimento!.month.toString().padLeft(2, '0')}/'
        '${dataNascimento!.year}';

    return Scaffold(
      appBar: AppBar(title: const Text('Criar conta')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: ListView(
          children: [
            TextField(controller: nome, decoration: const InputDecoration(labelText: 'Nome completo')),
            const SizedBox(height: 12),
            TextField(controller: email, decoration: const InputDecoration(labelText: 'E-mail')),
            const SizedBox(height: 12),
            TextField(controller: telefone, decoration: const InputDecoration(labelText: 'Telefone (opcional)')),
            const SizedBox(height: 12),
            TextField(controller: cpf, decoration: const InputDecoration(labelText: 'CPF (opcional)')),
            const SizedBox(height: 12),
            TextField(controller: endereco, decoration: const InputDecoration(labelText: 'Endereço (opcional)')),
            const SizedBox(height: 12),

            // data de nascimento
            InkWell(
              onTap: _pickDate,
              child: InputDecorator(
                decoration: const InputDecoration(labelText: 'Data de nascimento (opcional)'),
                child: Text(dataLabel),
              ),
            ),
            const SizedBox(height: 12),

            // ativo/inativo


            TextField(
              controller: senha,
              obscureText: true,
              decoration: const InputDecoration(labelText: 'Senha'),
            ),
            const SizedBox(height: 24),

            if (error != null) Text(error!, style: const TextStyle(color: Colors.red)),
            if (success != null) Text(success!, style: const TextStyle(color: Colors.green)),
            const SizedBox(height: 12),

            SizedBox(
              width: double.infinity,
              height: 48,
              child: FilledButton(
                onPressed: loading ? null : _registrar,
                child: loading
                    ? const CircularProgressIndicator()
                    : const Text('Cadastrar'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
