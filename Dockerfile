# ---- Base Image ----
    FROM php:8.3-fpm AS base

    # Install system dependencies
    RUN apt-get update && apt-get install -y \
        zip unzip curl git libpng-dev libjpeg-dev libfreetype6-dev \
        npm nodejs \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install gd pdo pdo_mysql \
        && rm -rf /var/lib/apt/lists/*
    
    # Install Composer globally
    RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    
    # Set working directory
    WORKDIR /var/www/html
    
    # Copy the entire Laravel application (including artisan)
    COPY . .
    
    # Set correct permissions
    RUN chown -R www-data:www-data /var/www/html
    
    # Install PHP dependencies **AFTER copying all files**
    RUN composer install --no-dev --optimize-autoloader
    
    # ---- Frontend ----
    FROM base AS frontend
    
    # Set working directory for frontend build
    WORKDIR /var/www/html
    
    # Install frontend dependencies
    RUN npm install
    
    # Build frontend assets
    RUN npm run build
    
    # ---- Final Laravel Image ----
    FROM base AS final
    
    # Set working directory
    WORKDIR /var/www/html
    
    # Copy built frontend assets from frontend stage
    COPY --from=frontend /var/www/html/public /var/www/html/public
    
    # Expose port
    EXPOSE 9000
    
    # Start PHP-FPM
    CMD ["php-fpm"]
    