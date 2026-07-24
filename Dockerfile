FROM php:8.2-apache

# --- 1. Dépendances système (Ajout de libicu-dev et libonig-dev) ---
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libzip-dev \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    libicu-dev \
    libonig-dev \
    dos2unix \
    && rm -rf /var/lib/apt/lists/*

# --- 2. Extensions PHP ---
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    bcmath \
    ctype \
    fileinfo \
    gd \
    intl \
    mbstring \
    xml \
    zip \
    opcache \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# --- 3. Composer + Apache ---
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN a2enmod rewrite
RUN cat > /etc/apache2/sites-available/000-default.conf <<'EOF'
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        Options +FollowSymLinks
        AllowOverride All
        Require all granted
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ /index.php [QSA,L]
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html && chmod -R 775 /var/www/html

EXPOSE 80