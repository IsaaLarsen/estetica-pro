import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

class ApiClient {
  // Resolve a baseUrl conforme a plataforma
  static String get _baseUrl {
    // Web (navegador)
    if (kIsWeb) return 'http://127.0.0.1:8000';

    // Emulador Android
    return 'http://10.0.2.2:8000';

    // Se for testar em celular físico:
    // return 'http://SEU_IP_LOCAL:8000';
  }

  static Dio build() {
    final dio = Dio(BaseOptions(
      baseUrl: '$_baseUrl/api',
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 50),
      headers: {'Accept': 'application/json'},
    ));

    // Logs úteis pra debugar chamadas
    dio.interceptors.add(LogInterceptor(
      request: true,
      requestBody: true,
      responseBody: true,
      error: true,
    ));

    // Espaço pra injetar Authorization futuramente
    dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) {
        // ex.: options.headers['Authorization'] = 'Bearer $token';
        return handler.next(options);
      },
    ));

    return dio;
  }
}
