import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart'; // kIsWeb
import 'dart:io';
import 'auth_storage.dart';

class ApiClient {
  static Dio build() {
    //
    // 🔧 BASE URL DO LARAVEL NA SUA REDE (PC)
    //
    const localNetworkBase = 'http://10.105.66.219:8000/api';

    //
    // URLs especiais
    //
    const webBase = 'http://127.0.0.1:8000/api';
    const androidEmulatorBase = 'http://10.0.2.2:8000/api';

    late final String baseUrl;

    if (kIsWeb) {
      // Flutter Web → acessa o próprio PC
      baseUrl = webBase;
    } else if (Platform.isAndroid) {
      // Android físico X Emulador
      final bool isEmulator = _isProbablyEmulator();
      baseUrl = isEmulator ? androidEmulatorBase : localNetworkBase;
    } else {
      // iOS, Windows, macOS, Linux
      baseUrl = localNetworkBase;
    }

    print('[ApiClient] Usando baseUrl = $baseUrl');

    final dio = Dio(
      BaseOptions(
        baseUrl: baseUrl,
        connectTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 30),
        headers: {
          'Accept': 'application/json',
        },
      ),
    );

    // 🔒 Envio automático do token
    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await AuthStorage.readToken();

          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }

          return handler.next(options);
        },
      ),
    );

    return dio;
  }

  /// Detecta heurística se está rodando em emulador Android
  static bool _isProbablyEmulator() {
    // Em dispositivos físicos, Android expõe ANDROID_INTERNAL_STORAGE no ambiente
    return !Platform.environment.containsKey('ANDROID_INTERNAL_STORAGE');
  }
}
