FROM php:7.4.5-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    libzip-dev && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    curl -sL https://deb.nodesource.com/setup_12.x | bash - && \
    apt-get install -y nodejs && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
      xml \
      pgsql \
      pdo_pgsql \
      mbstring \
      exif pcntl \
      bcmath \
      gd \
      zip && \
      mkdir -p /home/root/.composer

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
EXPOSE 9000
