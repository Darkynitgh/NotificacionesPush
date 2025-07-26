# Imagen oficial de PHP con servidor integrado
FROM php:8.2-cli

# Instalar composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar tu código al contenedor
WORKDIR /app
COPY . /app

# Instalar dependencias con composer (si tienes)
RUN composer install

# Exponer el puerto (Render usa $PORT, pero en Docker lo mapeas)
EXPOSE 10000

# Comando para arrancar el servidor PHP en el puerto 10000
CMD ["php", "-S", "0.0.0.0:10000"]
