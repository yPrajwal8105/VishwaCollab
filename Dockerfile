FROM php:8.2-apache

# Install mysqli extension for MySQL database connection
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy application source code
COPY . /var/www/html/

# Set appropriate permissions for file uploads
RUN mkdir -p /var/www/html/uploads && chmod -R 777 /var/www/html/uploads || true

# Enable Apache mod_rewrite for .htaccess configuration
RUN a2enmod rewrite

# Set Apache to listen on port 80 (Railway will map the PORT env variable automatically)
EXPOSE 80
