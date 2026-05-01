FROM php:8.2-cli

# Instalar dependências
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev zip nodejs npm \
    && docker-php-ext-install zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir diretório
WORKDIR /app

# Copiar arquivos
COPY . .

# Instalar dependências do Laravel
RUN composer install

# Instalar front (Vite + Tailwind)
RUN npm install && npm run build

# Gerar chave Laravel
RUN php artisan key:generate

# Expor porta do Render
EXPOSE 10000

# Rodar servidor
CMD php artisan serve --host=0.0.0.0 --port=10000