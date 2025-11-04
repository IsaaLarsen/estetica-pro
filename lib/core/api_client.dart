import 'dart:io' show Platform;
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:dio/dio.dart';
import 'auth_storage.dart';

class ApiClient {
  static String get _host {
    if (kIsWeb) return 'http://127.0.0.1:8000';       // Flutter Web
    if (Platform.isAndroid) return 'http://10.0.2.2:8000'; // Emulador Android
    return 'http://127.0.0.1:8000';                   // iOS/mac/desktop
  }

  static Dio build() {
    final dio = Dio(BaseOptions(
      baseUrl: '$_host/api',
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 50),
    ));

    dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await AuthStorage.readToken();
        if (token != null && token.isNotEmpty) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        handler.next(options);
      },
      onError: (e, handler) async {
        // se o token expirou, limpa e deixa a UI redirecionar para login
        if (e.response?.statusCode == 401) {
          await AuthStorage.clearToken();
        }
        handler.next(e);
      },
    ));
    return dio;
  }
}
