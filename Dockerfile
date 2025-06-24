# Dockerfile
# Specifies the environment for the CodeIgniter application.

FROM php:7.4-apache

# Install necessary PHP extensions for CodeIgniter and MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache's mod_rewrite for clean URLs (.htaccess)
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# The default command is to start the Apache server in the foreground
CMD ["apache2-foreground"]
