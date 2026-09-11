# bAI — Laravel (PHP). Netlify cannot run this stack.
FROM php:8.4-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip curl ca-certificates libsqlite3-dev libzip-dev \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && docker-php-ext-install pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/* \
    && node -v && npm -v

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN mkdir -p bootstrap/cache storage/framework/{cache,sessions,views} storage/logs database \
    && COMPOSER_ALLOW_SUPERUSER=1 composer install \
        --no-dev \
        --optimize-autoloader \
        --no-interaction \
        --no-scripts \
        --prefer-dist

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

# Build assets + install a valid key for artisan during image build.
RUN cp .env.example .env \
    && php -r "file_put_contents('.env', preg_replace('/^APP_KEY=.*/m', 'APP_KEY=base64:'.base64_encode(random_bytes(32)), file_get_contents('.env')));" \
    && COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --optimize --no-interaction \
    && php artisan package:discover --ansi \
    && npm run build \
    && rm -f public/hot \
    && test -f public/build/manifest.json \
    && if [ -f database/demo.sqlite ]; then \
         cp database/demo.sqlite database/database.sqlite; \
       else \
         touch database/database.sqlite \
         && php artisan migrate --force --no-interaction \
         && php artisan db:seed --force --no-interaction; \
       fi \
    && chmod -R 777 storage bootstrap/cache database \
    && test -s database/database.sqlite

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr
ENV LOG_LEVEL=warning
ENV SESSION_DRIVER=file
ENV CACHE_STORE=file
ENV QUEUE_CONNECTION=sync
ENV DB_CONNECTION=sqlite
ENV DB_DATABASE=/app/database/database.sqlite

EXPOSE 8080

COPY docker/start.sh /usr/local/bin/start-bai
RUN chmod +x /usr/local/bin/start-bai

CMD ["start-bai"]
