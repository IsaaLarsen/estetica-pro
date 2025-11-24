import 'package:dio/dio.dart';

class ServicosRepo {
  final Dio dio;
  ServicosRepo(this.dio);

  Future<List<Map<String, dynamic>>> listarServicos() async {
    final res = await dio.get('/servicos');
    // Esperado: [{id, nome, duracao_minutos, valor, ativo}, ...]
    final data = (res.data as List).cast<Map<String, dynamic>>();
    return data;
  }
}
