<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estética PRO - Mudar Senha</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #ec4899;
            --secondary: #7e22ce;
            --text: #1f2937;
            --text-light: #6b7280;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fdf2f8 0%, #f3e8ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 500px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .login-visual {
            flex: 1;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 40px;
            position: relative;
        }

        .login-visual::before {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -50px;
            left: -50px;
        }

        .login-visual::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -100px;
            right: -100px;
        }

        .visual-content { text-align: center; z-index: 1; }

        /* ===== ANIMAÇÕES DA LOGO ===== */
        @keyframes float {
            0%   { transform: translateY(0); }
            50%  { transform: translateY(-8px); }
            100% { transform: translateY(0); }
        }

        @keyframes glow {
            0% {
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            }
            50% {
                box-shadow: 0 15px 40px rgba(236, 72, 153, 0.6);
            }
            100% {
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            }
        }

        .logo-ep {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 25px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: float 4s ease-in-out infinite, glow 4s ease-in-out infinite;
        }

        .logo-ep img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .visual-content h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .visual-content p {
            font-size: 1rem;
            opacity: 0.9;
        }

        .login-form {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .brand {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand h1 {
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }

        .brand p { color: var(--text-light); font-size: 0.9rem; }

        .input-group {
            position: relative;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .input-group input {
            width: 100%;
            padding: 15px 60px 15px 45px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
        }

        .input-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.2);
            outline: none;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-light);
            background: transparent;
            border: none;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 14px rgba(236, 72, 153, 0.4);
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(236, 72, 153, 0.5);
        }

        .alert {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .alert.error { background: #fee2e2; color: #dc2626; }
        .alert.success { background: #ecfdf5; color: #059669; }
    </style>
</head>

<body>

    <div class="login-container">
        
        <!-- ===== LADO VISUAL (LOGO ANIMADA) ===== -->
        <div class="login-visual">
            <div class="visual-content">
                <div class="logo-ep">
                    <img src="{{ asset('image/logoEP.png') }}" alt="Logo Estética PRO">
                </div>
                <h2>Bem-vindo ao Estética PRO</h2>
                <p>Antes de continuar, defina uma nova senha para garantir a segurança do seu acesso.</p>
            </div>
        </div>

        <!-- ===== FORMULÁRIO DE MUDANÇA DE SENHA ===== -->
        <div class="login-form">
            <div class="brand">
                <h1>Estética PRO</h1>
                <p>Mudar senha</p>
            </div>

            @if (session('error'))
                <div class="alert error">
                    <i class="fas fa-exclamation-circle" style="margin-right: 10px;"></i>
                    {{ session('error') }}
                </div>
            @elseif ($errors->any())
                <div class="alert error">
                    <i class="fas fa-exclamation-circle" style="margin-right: 10px;"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert success">
                    <i class="fas fa-check-circle" style="margin-right: 10px;"></i>
                    {{ session('success') }}
                </div>
            @endif

            <form id="changePasswordForm" method="POST" action="{{ route('me.senha.update') }}">
                @csrf

                {{-- Nova senha --}}
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input
                        type="password"
                        id="senha"
                        name="nova_senha"
                        placeholder="Nova senha"
                        required
                        minlength="8"
                        pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,72}"
                    >
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>

                {{-- Confirmar nova senha --}}
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input
                        type="password"
                        name="confirmar_senha"
                        placeholder="Confirmar nova senha"
                        required
                        minlength="8"
                    >
                </div>

                <p style="font-size:13px; color:#6b7280; margin-top:-10px; margin-bottom:15px;">
                    A senha deve ter pelo menos <strong>8 caracteres</strong>, incluindo:
                    <strong>1 letra maiúscula</strong>, <strong>1 letra minúscula</strong>,
                    <strong>1 número</strong> e <strong>1 caractere especial</strong>.
                </p>

                <button type="submit" class="btn-login">Salvar e entrar</button>
            </form>

        </div>
    </div>

    <script>
        // mantém o mesmo toggle da tela de login
        document.getElementById('togglePassword').addEventListener('click', function() {
            const input = document.getElementById('senha');
            const icon = this.querySelector('i');

            input.type = input.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    </script>

    @include('partials.change_password_modal')
    @include('partials.toast')

</body>
</html>
