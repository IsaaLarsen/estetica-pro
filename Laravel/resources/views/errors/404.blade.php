<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Não Encontrada - Estética PRO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ec4899;
            --primary-dark: #db2777;
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
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
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

        .search-box {
            margin: 25px 0;
            padding: 20px;
            background: #f9fafb;
            border-radius: 12px;
            text-align: left;
        }

        .search-box h4 {
            color: var(--text);
            margin-bottom: 10px;
            font-size: 16px;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-family: inherit;
        }

        .search-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.2);
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
                <i class="fas fa-search"></i>
            </div>
            <div class="error-code">404</div>
            <h1 class="error-title">Página Não Encontrada</h1>
            <p class="error-subtitle">Oops! A página que você procura não existe.</p>
        </div>

        <div class="error-content">
            <p class="error-message">
                A URL que você tentou acessar não foi encontrada em nosso servidor. 
                Verifique se digitou corretamente ou utilize os links abaixo para navegar.
            </p>

            <div class="search-box">
                <h4>Encontrar o que precisa:</h4>
                <input type="text" class="search-input" placeholder="O que você está procurando?" id="searchInput">
            </div>

            <div class="error-actions">
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                
                <a href="{{ url('/') }}" class="btn btn-primary">
                    <i class="fas fa-home"></i> Página Inicial
                </a>

                <a href="{{ route('servicos.index') }}" class="btn btn-primary">
                    <i class="fas fa-spa"></i> Nossos Serviços
                </a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = this.value.trim();
                if (searchTerm) {
                    // Redirecionar para busca ou mostrar mensagem
                    alert('Buscar por: ' + searchTerm);
                    // window.location.href = '/buscar?q=' + encodeURIComponent(searchTerm);
                }
            }
        });
    </script>
</body>
</html>