FROM php:7.4.9-apache

ARG ENV

RUN apt-get update; \
    apt-get install -y --no-install-recommends \
    libzip-dev \
    unzip \
    zip \
    vim \
    git \
    autoconf g++ make openssl libssl-dev libcurl4-openssl-dev pkg-config libsasl2-dev libpcre3-dev

RUN pecl install mongodb; \
    docker-php-ext-configure zip; \
    docker-php-ext-install zip; \
    docker-php-ext-enable mongodb; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
    rm -rf /var/lib/apt/lists/*;

RUN sed -i 's/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/public/g' /etc/apache2/sites-available/000-default.conf

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

COPY ./composer.json /var/www/composer.json
COPY ./public /var/www/public
COPY ./src /var/www/src
COPY ./config /var/www/config
COPY ./phpunit.xml /var/www/phpunit.xml
COPY ./phpcs.xml /var/www/phpcs.xml

RUN if [ "$ENV" != "production" ] ; then \
    pecl install xdebug; \
    docker-php-ext-enable xdebug; \
    echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    fi ;

RUN if [ "$ENV" != "production" ] ; then \
    composer install --prefer-source --no-interaction --no-suggest \
    && composer dump-autoload; \
    fi ;

RUN if [ "$ENV" = "production" ] ; then \
    composer install --prefer-source --no-interaction --no-dev --no-suggest -o \
    && composer dump-autoload -o; \
    fi ;

RUN a2enmod rewrite && \
    service apache2 restart && \
    chown -R www-data /var/www;
