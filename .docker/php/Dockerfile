FROM php:7.2-fpm
# Update system
RUN apt-get update && apt-get install git libicu-dev g++ zlib1g-dev zip procps -y
# Install xDebug
RUN pecl install xdebug
# Install extensions
RUN docker-php-ext-install zip
# Install OPcache
RUN docker-php-ext-configure opcache --enable-opcache \
   && docker-php-ext-install opcache
# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
   && php -r "if (hash_file('sha384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
   && php composer-setup.php --install-dir=/bin --filename=composer \
   && php -r "unlink('composer-setup.php');"
RUN apt-get autoremove --purge -y && apt-get clean -s
COPY opcache.ini ${PHP_INI_DIR}/conf.d/opcache.ini
COPY xdebug.ini ${PHP_INI_DIR}/conf.d/xdebug.ini
COPY worker.pool.conf /usr/local/etc/php-fpm.d/worker.conf
WORKDIR /repo