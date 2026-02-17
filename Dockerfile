# Step 1: Use an official PHP image with an Apache web server
FROM php:8.1-apache

# Step 2: Install the PostgreSQL driver for PHP, which your app needs to connect to the database
RUN docker-php-ext-install pdo_pgsql

# Step 3: Set the working directory inside the container
WORKDIR /var/www/html

# Step 4: Copy all your project files from your repository into the container
COPY . .

# Step 5: Enable Apache's .htaccess support (very important for your routing)
RUN a2enmod rewrite

# Step 6: Set correct permissions for the uploads folder
RUN chown -R www-data:www-data /var/www/html/uploads