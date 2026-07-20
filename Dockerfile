FROM php:8.2-cli

# Install PostgreSQL extensions
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pgsql pdo_pgsql

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Run PHP built-in server
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
