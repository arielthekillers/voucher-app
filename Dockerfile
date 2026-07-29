FROM php:8.2-apache

# Mengaktifkan mod_rewrite Apache (biasanya dibutuhkan untuk routing di PHP)
RUN a2enmod rewrite

# Install dependencies sistem yang sering dibutuhkan dan ekstensi PHP
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mysqli \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set direktori kerja
WORKDIR /var/www/html

# Copy seluruh file project ke dalam container
COPY . .

# Install dependencies PHP menggunakan composer dan generate file autoload
RUN composer install --no-dev --optimize-autoloader

# Mengubah DocumentRoot Apache agar mengarah ke folder public/
RUN sed -i -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
RUN sed -i -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Atur permission yang sesuai, berikan akses tulis untuk folder storage (jika ada upload atau cache/log)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage
