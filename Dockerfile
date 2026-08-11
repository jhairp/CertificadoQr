FROM php:8.3-apache

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Apache
RUN a2enmod rewrite

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node.js y npm
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias PHP
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# Instalar dependencias JS y compilar Vite
RUN npm install
RUN npm run build

# Permisos de Laravel
RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache

# Configurar Apache para Laravel
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' \
    /etc/apache2/sites-available/000-default.conf

RUN printf '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' > /etc/apache2/conf-available/laravel.conf

RUN a2enconf laravel

EXPOSE 80

CMD ["apache2-foreground"]