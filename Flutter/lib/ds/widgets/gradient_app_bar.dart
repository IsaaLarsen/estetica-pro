import 'package:flutter/material.dart';

class GradientAppBar extends StatelessWidget implements PreferredSizeWidget {
  final String title;
  final String? imageAsset; // <--- NOVO: Caminho da imagem (opcional)
  final List<Widget>? actions;
  final double height;

  const GradientAppBar({
    super.key,
    required this.title,
    this.imageAsset = 'assets/images/logoEP.png', // <--- Adicione no construtor
    this.actions,
    this.height = kToolbarHeight + 10,
  });

  @override
  Size get preferredSize => Size.fromHeight(height);

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            Color(0xFFEC4899),
            Color(0xFFBE185D),
          ],
        ),
        boxShadow: [
          BoxShadow(color: Colors.black26, blurRadius: 4, offset: Offset(0, 2))
        ],
      ),
      child: AppBar(
        // Alteramos o Title para aceitar uma Row (Imagem + Texto)
        title: Row(
          mainAxisSize: MainAxisSize.min, // Ocupa apenas o espaço necessário
          children: [
            if (imageAsset != null) ...[
              Image.asset(
                imageAsset!,
                height: 32, // Altura ajustada para caber na barra
                // color: Colors.white, // Descomente se sua logo for preta e você quiser pintar de branco
                fit: BoxFit.contain,
              ),
              const SizedBox(width: 12), // Espaço entre logo e texto
            ],
            Text(
              title,
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.bold,
                fontSize: 20,
              ),
            ),
          ],
        ),
        centerTitle: false, // Mantém alinhado a esquerda junto com a logo
        actions: actions,
        backgroundColor: Colors.transparent,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
    );
  }
}