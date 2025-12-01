# Dockerfile
FROM php:8.2-fpm-alpine

# Install ekstensi yang dibutuhkan
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

# Copy source code ke dalam container (untuk image build)
COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html \
    && find . -type f -exec chmod 644 {} \; \
    && find . -type d -exec chmod 755 {} \;

EXPOSE 9000

CMD ["php-fpm"]


