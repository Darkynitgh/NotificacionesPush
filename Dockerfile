FROM php:8.2-cli

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

RUN composer install --no-dev --optimize-autoloader || true

EXPOSE 10000

CMD ["sh", "-c", "php -S 0.0.0.0:$PORT"]
