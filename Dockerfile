# Use an official PHP image with an Apache web server
FROM php:8.1-apache

# Install dependencies and PHP extension in one step
RUN apt-get update && apt-get install -y libpq-dev && \
    docker-php-ext-install pdo_pgsql

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy all your project files from your repository into the container
COPY . .

# Create the uploads directory (in case it's empty and wasn't copied)
# and then set the correct permissions for it.
RUN mkdir -p uploads && \
    chown -R www-data:www-data uploads

# Enable Apache's .htaccess support
RUN a2enmod rewrite