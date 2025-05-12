FROM php:8.2-cli

# Instalação de dependências
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip
# Instalar extensões PHP
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Diretório de trabalho
WORKDIR /var/www

# Copiar aplicação
COPY . .

# Instalar dependências
RUN composer install --no-interaction

# Copiar o .env.example para .env
COPY .env.example .env

RUN touch database/database.sqlite

# Expor a porta 8000
EXPOSE 8000

RUN php artisan migrate:fresh --seed

RUN php artisan jwt:secret

RUN php artisan key:generate

RUN php artisan storage:link

# Definir o entrypoint
ENTRYPOINT ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "8000"]
