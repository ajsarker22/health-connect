# Use an official PHP image with an Apache web server
FROM php:8.1-apache

# Install the PostgreSQL client library FIRST, then install the PHP extension
RUN apt-get update && apt-get install -y libpq-dev && \
    docker-php-ext-install pdo_pgsql

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy all your project files from your repository into the container
COPY . .

# Enable Apache's .htaccess support
RUN a2enmod rewrite

# Set correct permissions for the uploads folder
RUN chown -R www-data:www-data /var/www/html/uploads