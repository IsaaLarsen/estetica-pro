import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../../core/api_client.dart';

class RecuperarSenhaPage extends StatefulWidget {
  const RecuperarSenhaPage({super.key});

  @override
  State<RecuperarSenhaPage> createState() => _RecuperarSenhaPageState();
}

class _RecuperarSenhaPageState extends State<RecuperarSenhaPage> {
  final email = TextEditingController();
  final cpf = TextEditingController();
  final endereco = TextEditingController();
  final novaSenha = TextEditingController();
  final confirma = TextEditingController();

  bool loading = false;
  String? error;
  String? success;

  Future<void> _enviar() async {
    setState(() { loading = true; error = null; success = null; });

    final dio = ApiClient.build();
    try {
      final res = await dio.post('/auth/cliente/forgot-password', data: {
        'email': email.text.trim(),
        'cpf': cpf.text.trim(),
        'endereco': endereco.text.trim(),
        'new_password': novaSenha.text,
        'new_password_confirmation': confirma.text,
      });

      if (res.statusCode == 200) {
        setState(() => success = 'Senha atualizada com sucesso! Faça login com a nova senha.');
      } else {
        setState(() => error = res.data is Map && res.data['message'] != null
            ? res.data['message'].toString()
            : 'Falha ao atualizar senha');
      }
    } on DioException catch (e) {
      final data = e.response?.data;
      String msg = 'Erro de conexão';
      if (data is Map && data['errors'] is Map) {
        msg = (data['errors'] as Map).entries
            .expand((kv) => (kv.value as List).map((x) => '• ${kv.key}: $x'))
            .join('\n');
      } else if (data is Map && data['message'] != null) {
        msg = data['message'].toString();
      } else if (data is String) {
        msg = data;
      }
      setState(() => error = msg);
    } catch (e) {
      setState(() => error = e.toString());
    } finally {
      setState(() => loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Recuperar senha')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: ListView(
          children: [
            TextField(controller: email, decoration: const InputDecoration(labelText: 'E-mail')),
            const SizedBox(height: 12),
            TextField(controller: cpf, decoration: const InputDecoration(labelText: 'CPF')),
            const SizedBox(height: 12),
            TextField(controller: endereco, decoration: const InputDecoration(labelText: 'Endereço')),
            const SizedBox(height: 12),
            TextField(controller: novaSenha, obscureText: true, decoration: const InputDecoration(labelText: 'Nova senha')),
            const SizedBox(height: 12),
            TextField(controller: confirma, obscureText: true, decoration: const InputDecoration(labelText: 'Confirmar nova senha')),
            const SizedBox(height: 20),
            if (error != null) Text(error!, style: const TextStyle(color: Colors.red)),
            if (success != null) Text(success!, style: const TextStyle(color: Colors.green)),
            const SizedBox(height: 12),
            SizedBox(
              height: 48,
              child: FilledButton(
                onPressed: loading ? null : _enviar,
                child: loading ? const CircularProgressIndicator() : const Text('Atualizar senha'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
