import 'package:estetica_app/ds/theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart'; // ⬅️ importe

import 'core/auth_storage.dart';
import 'features/auth/login_page.dart';
import 'features/servicos/servicos_page.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(
    const ProviderScope( // ⬅️ envolva o app
      child: EsteticaApp(),
    ),
  );
}

class EsteticaApp extends StatelessWidget {
  const EsteticaApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Estética PRO',
      debugShowCheckedModeBanner: false,
      theme: DSTheme.light(),     // ⬅️ agora tudo herda esse tema
      // darkTheme: DSTheme.dark(), // (opcional)
      home: const _SplashGate(),
    );
  }
}

class _SplashGate extends StatefulWidget {
  const _SplashGate({super.key});

  @override
  State<_SplashGate> createState() => _SplashGateState();
}

class _SplashGateState extends State<_SplashGate> {
  Future<bool>? _hasToken;

  @override
  void initState() {
    super.initState();
    _hasToken = _check();
  }

  Future<bool> _check() async {
    final t = await AuthStorage.readToken();
    return t != null && t.isNotEmpty;
  }

  // só usa se você chamar a tela de login manualmente
  Future<void> _goLogin() async {
    final ok = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => const LoginPage()),
    );
    if (ok == true) {
      setState(() {
        _hasToken = _check(); // <-- bloco, não retorna Future
      });
    }
  }

  void _onLogged() {
    // chamado pelo LoginPage quando logar com sucesso
    setState(() {
      _hasToken = _check(); // <-- bloco, não retorna Future
    });
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<bool>(
      future: _hasToken,
      builder: (context, snap) {
        if (!snap.hasData) {
          return const Scaffold(body: Center(child: CircularProgressIndicator()));
        }
        final logged = snap.data ?? false;

        if (!logged) {
          // não logado → mostra login
          return LoginPage(onLogged: _onLogged); // callback sem async/retorno
        }

        // logado → home
        return const ServicosPage();
      },
    );
  }
}
