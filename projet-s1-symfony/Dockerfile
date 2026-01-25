FROM php:8.3-cli-alpine

# ARG APP_ENV=prod

RUN apk add --no-cache \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    postgresql-dev \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql zip gd intl opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

COPY . .

RUN composer install --no-scripts

RUN npm install && npm run build

RUN mkdir -p var/cache var/log \
    && chmod -R 777 var

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]