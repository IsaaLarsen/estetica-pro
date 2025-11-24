# Estética PRO – LARAVEL

# Como rodar o projeto

# 1. Clonar o repositório
git clone https://github.com/IsaaLarsen/estetica-pro.git
cd estetica-pro
git checkout develop

# 2. Instalar dependências
composer install

# 3. Configurar o ambiente
cp .env.example .env  
Edite o arquivo `.env` com seu banco de dados MySQL.

# 4. Gerar key da aplicação
php artisan key:generate

# 5. Rodar as migrations
php artisan migrate

# 6. Subir o servidor
php artisan serve

# Acessar o sistema
http://127.0.0.1:8000


