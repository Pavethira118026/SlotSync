FROM php:8.2-apache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Install MySQLi extension
RUN docker-php-ext-install mysqli

# Copy SlotSync project into Apache web directory
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Apache uses port 80
EXPOSE 80