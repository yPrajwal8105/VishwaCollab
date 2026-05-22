FROM php:8.2-cli-alpine

# Install mysqli extension for MySQL database connection
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy application source code
COPY . /var/www/html/

# Set appropriate permissions for file uploads
RUN mkdir -p /var/www/html/uploads && chmod -R 777 /var/www/html/uploads || true

# Set working directory
WORKDIR /var/www/html

# Expose port 80 (Railway will map the PORT env variable automatically)
EXPOSE 80

# Start the lightweight built-in PHP web server
CMD ["php", "-S", "0.0.0.0:80"]
