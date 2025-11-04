import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../../core/api_client.dart';
import '../../core/auth_storage.dart';
import 'cadastro_page.dart'; // ⬅️ importa a nova tela
import 'recuperar_senha_page.dart';

class LoginPage extends StatefulWidget {
  final VoidCallback? onLogged;

  const LoginPage({super.key, this.onLogged});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final email = TextEditingController();
  final pass = TextEditingController();
  bool loading = false;
  String? error;

  Future<void> _login() async {
    setState(() {
      loading = true;
      error = null;
    });

    final dio = ApiClient.build();

    try {
      final res = await dio.post('/auth/cliente/login', data: {
        'email': email.text.trim(),
        'password': pass.text,
      });

      final token = res.data['token'] as String;
      await AuthStorage.saveToken(token);

      if (!mounted) return;
      if (widget.onLogged != null) {
        Future.microtask(widget.onLogged!);
      } else {
        Navigator.pop(context, true);
      }
    } on DioException catch (e) {
      setState(() {
        error = e.response?.data?['message']?.toString() ?? 'Falha no login';
      });
    } catch (e) {
      setState(() {
        error = e.toString();
      });
    } finally {
      setState(() => loading = false);
    }
  }

  void _goCadastro() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const CadastroPage()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Bem vindo ao Estética Pro!')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            TextField(
              controller: email,
              decoration: const InputDecoration(labelText: 'E-mail'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: pass,
              obscureText: true,
              decoration: const InputDecoration(labelText: 'Senha'),
            ),
            const SizedBox(height: 16),
            if (error != null)
              Text(error!, style: const TextStyle(color: Colors.red)),
            const SizedBox(height: 8),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: FilledButton(
                onPressed: loading ? null : _login,
                child: loading
                    ? const CircularProgressIndicator()
                    : const Text('Entrar'),
              ),
            ),
            const SizedBox(height: 24),

            const SizedBox(height: 12),
            TextButton(
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const RecuperarSenhaPage()),
                );
              },
              child: const Text('Esqueceu sua senha?'),
            ),
            // 🔹 Botão criar conta
            TextButton(
              onPressed: _goCadastro,
              child: const Text(
                'Ainda não tem conta? Crie a sua!',
                style: TextStyle(color: Color(0xFFEC4899)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
