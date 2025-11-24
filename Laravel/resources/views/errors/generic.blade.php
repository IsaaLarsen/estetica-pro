<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro - Estética PRO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ec4899;
            --primary-dark: #db2777;
            --secondary: #7e22ce;
            --text: #1f2937;
            --text-light: #6b7280;
            --danger: #ef4444;
            --warning: #f59e0b;
            --success: #10b981;
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
            max-width: 800px;
            width: 100%;
        }

        .error-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .error-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .error-code {
            font-size: 80px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 10px;
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
            padding: 30px;
        }

        .error-message {
            color: var(--text-light);
            margin-bottom: 25px;
            font-size: 16px;
            line-height: 1.6;
            text-align: center;
        }

        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 25px;
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

        .error-details {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid var(--warning);
            display: none;
        }

        .error-details.show {
            display: block;
        }

        .error-info {
            margin-bottom: 15px;
        }

        .error-label {
            font-weight: 600;
            color: var(--text);
            margin-bottom: 5px;
            font-size: 14px;
        }

        .error-value {
            color: var(--text-light);
            background: white;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            word-break: break-all;
        }

        .toggle-details {
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            font-weight: 600;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
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
                font-size: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-header">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="error-code">{{ $statusCode }}</div>
            <h1 class="error-title">Oops! Algo deu errado</h1>
            <p class="error-subtitle">Ocorreu um erro inesperado no sistema</p>
        </div>

        <div class="error-content">
            <p class="error-message">
                @if($statusCode == 500)
                    Ocorreu um erro interno no servidor. Nossa equipe já foi notificada.
                @else
                    Ocorreu um erro inesperado. Tente novamente mais tarde.
                @endif
            </p>

            <div class="error-actions">
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                
                <a href="{{ url('/') }}" class="btn btn-primary">
                    <i class="fas fa-home"></i> Página Inicial
                </a>

                <button onclick="location.reload()" class="btn btn-primary">
                    <i class="fas fa-redo"></i> Recarregar
                </button>
            </div>

            @if($showDetails)
            <button class="toggle-details" onclick="toggleDetails()">
                <i class="fas fa-chevron-down" id="details-icon"></i>
                Detalhes Técnicos
            </button>

            <div class="error-details" id="error-details">
                <div class="error-info">
                    <div class="error-label">Mensagem:</div>
                    <div class="error-value">{{ $exception->getMessage() ?? 'N/A' }}</div>
                </div>

                <div class="error-info">
                    <div class="error-label">Tipo:</div>
                    <div class="error-value">{{ get_class($exception) }}</div>
                </div>

                <div class="error-info">
                    <div class="error-label">Arquivo:</div>
                    <div class="error-value">{{ $exception->getFile() ?? 'N/A' }}</div>
                </div>

                <div class="error-info">
                    <div class="error-label">Linha:</div>
                    <div class="error-value">{{ $exception->getLine() ?? 'N/A' }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        function toggleDetails() {
            const content = document.getElementById('error-details');
            const icon = document.getElementById('details-icon');
            
            content.classList.toggle('show');
            
            if (content.classList.contains('show')) {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }

        // Mostrar detalhes automaticamente em ambiente local
        @if($showDetails)
        document.addEventListener('DOMContentLoaded', function() {
            toggleDetails();
        });
        @endif
    </script>
</body>
</html>