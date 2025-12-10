FROM php:8.0-apache

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar arquivos do projeto
COPY . /var/www/html

# Definir permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Configurar Apache para usar /var/www/html/public como DocumentRoot
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Instalar dependências do PHP
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader

# Expor porta 80
EXPOSE 80

# Comando para iniciar Apache
CMD ["apache2-foreground"]