<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Método Não Permitido - Estética PRO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ec4899;
            --secondary: #7e22ce;
            --text: #1f2937;
            --text-light: #6b7280;
            --warning: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fdf2f8 0%, #f3e8ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }

        .error-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            color: white;
            padding: 40px 30px;
        }

        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .error-code {
            font-size: 120px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 10px;
            opacity: 0.9;
        }

        .error-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .error-subtitle {
            font-size: 16px;
            opacity: 0.9;
        }

        .error-content {
            padding: 40px 30px;
        }

        .error-message {
            color: var(--text-light);
            margin-bottom: 30px;
            font-size: 16px;
            line-height: 1.6;
        }

        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(236, 72, 153, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(236, 72, 153, 0.5);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: var(--text);
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .method-info {
            margin: 25px 0;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            text-align: left;
            border: 2px dashed #e2e8f0;
        }

        .method-info h4 {
            color: var(--text);
            margin-bottom: 10px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @media (max-width: 768px) {
            .error-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }

            .error-code {
                font-size: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-header">
            <div class="error-icon">
                <i class="fas fa-ban"></i>
            </div>
            <div class="error-code">405</div>
            <h1 class="error-title">Método Não Permitido</h1>
            <p class="error-subtitle">Esta operação não é permitida</p>
        </div>

        <div class="error-content">
            <p class="error-message">
                O método de requisição usado não é suportado para esta URL. 
                A ação que você tentou realizar não é permitida no contexto atual.
            </p>

            <div class="method-info">
                <h4><i class="fas fa-info-circle"></i> Informação Técnica:</h4>
                <p style="color: var(--text-light); margin: 0;">
                    O servidor reconheceu o método da requisição, mas o recurso de destino 
                    não suporta este método específico.
                </p>
            </div>

            <div class="error-actions">
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                
                <a href="{{ url('/') }}" class="btn btn-primary">
                    <i class="fas fa-home"></i> Página Inicial
                </a>

                <a href="mailto:suporte@esteticapro.com" class="btn btn-primary">
                    <i class="fas fa-envelope"></i> Suporte
                </a>
            </div>
        </div>
    </div>
</body>
</html>