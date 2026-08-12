FROM php:8.3-apache

# 1. Dependencias del sistema y extensiones de PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache \
    && rm -rf /var/lib/apt/lists/*

# 2. Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# 3. Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4. Node.js y npm
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# 5. Copiar archivos del proyecto
COPY . .

# 6. Instalar dependencias PHP sin ejecutar scripts que dependan del .env aún
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

# 7. Instalar dependencias JS y compilar assets
RUN npm install && npm run build

# 8. Permisos de Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# 9. Configurar Apache VirtualHost para apuntar a /public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' \
    /etc/apache2/sites-available/000-default.conf

RUN printf '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' > /etc/apache2/conf-available/laravel.conf

RUN a2enconf laravel

EXPOSE 80

CMD ["apache2-foreground"]