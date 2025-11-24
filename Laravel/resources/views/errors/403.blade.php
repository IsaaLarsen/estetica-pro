<!DOCTYPE html>
<html>
<head>
    <title>Acesso Negado</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .error-card {
            background: white;
            padding: 3rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 450px;
            width: 100%;
            border-left: 4px solid #dc3545;
        }
        
        .error-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: #dc3545;
        }
        
        .error-code {
            font-size: 1.5rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .error-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 1rem;
        }
        
        .error-message {
            color: #6c757d;
            margin-bottom: 2rem;
            font-size: 1rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #545b62;
        }
        
        .btn-outline {
            background-color: transparent;
            color: #007bff;
            border: 1px solid #007bff;
        }
        
        .btn-outline:hover {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">⛔</div>
        
        <div class="error-code">ERRO 403</div>
        <h1 class="error-title">Acesso Negado</h1>
        
        <p class="error-message">
            Você não tem permissão para acessar este recurso. 
            Verifique suas credenciais ou entre em contato com o administrador do sistema.
        </p>
        
        <div class="action-buttons">
            <a href="javascript:history.back()" class="btn btn-secondary">
                ← Voltar
            </a>
            <a href="/" class="btn btn-primary">
                Página Inicial
            </a>
            <a href="/login" class="btn btn-outline">
                Fazer Login
            </a>
        </div>
    </div>
</body>
</html>