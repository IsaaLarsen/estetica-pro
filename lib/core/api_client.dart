import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart'; // kIsWeb
import 'auth_storage.dart';

class ApiClient {
  static Dio build() {
    //
    // 🔥 IMPORTANTE
    // PARA FLUTTER WEB, APENAS ESTE ENDEREÇO FUNCIONA:
    //
    const webBase = 'http://127.0.0.1:8000/api';
    const androidBase = 'http://10.0.2.2:8000/api';

    final baseUrl = kIsWeb ? webBase : androidBase;

    print('[ApiClient] kIsWeb=$kIsWeb  baseUrl=$baseUrl');

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

    // 🔒 Interceptor para enviar token em todas requisições
    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await AuthStorage.readToken(); // ← aqui corrigido

          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }

          return handler.next(options);
        },
      ),
    );

    return dio;
  }
}
