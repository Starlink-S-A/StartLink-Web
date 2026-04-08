# Usamos la imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalamos dependencias del sistema y extensiones de PHP necesarias para TiDB (MySQL compatible)
RUN apt-get update && apt-get install -y \
    libmariadb-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql zip

# Habilitamos el módulo rewrite de Apache (necesario para el .htaccess)
RUN a2enmod rewrite

# Configuramos el directorio de trabajo
WORKDIR /var/www/html

# Copiamos los archivos del proyecto al contenedor
COPY . .

# Instalamos Composer de manera global
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Ejecutamos la instalación de dependencias de PHP
RUN composer install --no-interaction --optimize-autoloader

# Ajustamos permisos para carpetas que requieran escritura (aunque sea efímero)
RUN chown -R www-data:www-data /var/www/html/assets /var/www/html/src
RUN chmod -R 755 /var/www/html

# Exponemos el puerto 80 (Apache por defecto)
EXPOSE 80

# El comando de inicio ya está definido en la imagen base (apache2-foreground)
