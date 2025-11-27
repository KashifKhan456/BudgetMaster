FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    libpq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Install dependencies
RUN composer install --optimize-autoloader --no-dev
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 80 (Render expects this or we configure Nginx, but for simplicity we can use `php artisan serve` for dev-like or use a proper Nginx setup. 
# For production on Render, it's best to use Nginx + PHP-FPM. 
# Let's use a simpler approach for the "free" tier: just run the PHP server or use a combined Nginx+PHP image.
# Actually, the best way for Render is to use a script that starts Nginx and PHP-FPM.
# Let's stick to a simple `php artisan serve` for the absolute easiest setup, 
# BUT `php artisan serve` is single-threaded. 
# Better: Use `heroku/heroku:php` buildpack logic or just install Nginx in this Dockerfile.

# Let's install Nginx inside this container to make it a self-contained web server.
RUN apt-get update && apt-get install -y nginx
COPY .docker/nginx.conf /etc/nginx/sites-available/default

# Create a startup script
RUN echo "#!/bin/sh\nnginx\nphp-fpm" > /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
