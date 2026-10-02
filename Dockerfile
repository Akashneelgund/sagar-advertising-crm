FROM php:8.2-apache

# Install MariaDB, client, and required PHP extensions
RUN apt-get update && apt-get install -y \
    mariadb-server \
    mariadb-client \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Copy and setup entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Ensure storage and upload permissions
RUN mkdir -p /var/www/html/storage/pdf /var/www/html/storage/backups /var/www/html/storage/mail_logs /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/storage /var/www/html/uploads

# Expose HTTP port
EXPOSE 80 10000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]