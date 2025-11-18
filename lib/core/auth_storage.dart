import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AuthStorage {
  static const _key = 'token';

  /// Salva token
  static Future<void> saveToken(String token) async {
    if (kIsWeb) {
      final sp = await SharedPreferences.getInstance();
      await sp.setString(_key, token);
    } else {
      const secure = FlutterSecureStorage();
      await secure.write(key: _key, value: token);
    }
  }

  /// Lê token
  static Future<String?> readToken() async {
    if (kIsWeb) {
      final sp = await SharedPreferences.getInstance();
      return sp.getString(_key);
    } else {
      const secure = FlutterSecureStorage();
      return await secure.read(key: _key);
    }
  }

  /// Remove token
  static Future<void> clearToken() async {
    if (kIsWeb) {
      final sp = await SharedPreferences.getInstance();
      await sp.remove(_key);
    } else {
      const secure = FlutterSecureStorage();
      await secure.delete(key: _key);
    }
  }
}
