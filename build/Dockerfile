FROM nginx:1.15.12-alpine

LABEL maintainer="Pham Ba Trung Thanh <phambatrungthanh@gmail.com>"

ARG WWW_ID=33
ARG WWW_GROUP=33

ENV USER_ID=${WWW_ID}
ENV GROUP_ID=${WWW_GROUP}

# IMAGE ARGUMENTS WITH DEFAULTS.
ARG PHP_VERSION=7.3
ARG ALPINE_VERSION=3.9
ARG COMPOSER_HASH=a5c698ffe4b8e849a443b120cd5ba38043260d5c4023dbf93e1558871f1f07f58274fc6f4c93bcfd858c6bd0775cd8d1

USER root
RUN apk add --update --no-cache curl ca-certificates \
    bash \
    supervisor \
    shadow \
    sudo

RUN if [ -z "`getent group $GROUP_ID`" ]; then \
    addgroup -S -g $GROUP_ID www-data; \
    else \
    groupmod -n www-data `getent group $GROUP_ID | cut -d: -f1`; \
    fi && \
    if [ -z "`getent passwd $USER_ID`" ]; then \
    adduser -S -u $USER_ID -G www-data -s /bin/sh www-data; \
    else \
    usermod -l www-data -g $GROUP_ID -d /home/www-data -m `getent passwd $USER_ID | cut -d: -f1`; \
    fi && \
    echo "www-data ALL=(root) NOPASSWD:ALL" > /etc/sudoers.d/www-data && \
    chmod 0440 /etc/sudoers.d/www-data




# trust this project public key to trust the packages.
ADD https://dl.bintray.com/php-alpine/key/php-alpine.rsa.pub /etc/apk/keys/php-alpine.rsa.pub
# make sure you can use HTTPS


# CONFIGURE ALPINE REPOSITORIES AND PHP BUILD DIR.
RUN echo "http://dl-cdn.alpinelinux.org/alpine/v${ALPINE_VERSION}/main" > /etc/apk/repositories && \
    echo "http://dl-cdn.alpinelinux.org/alpine/v${ALPINE_VERSION}/community" >> /etc/apk/repositories && \
    echo "@php https://dl.bintray.com/php-alpine/v${ALPINE_VERSION}/php-${PHP_VERSION}" >> /etc/apk/repositories

# INSTALL PHP AND SOME EXTENSIONS. SEE: https://github.com/codecasts/php-alpine
RUN apk add --no-cache --update php-fpm@php \
    php@php \
    php-bcmath@php \
    php-curl@php \
    php-exif@php \
    php-pcntl@php \
    php-openssl@php \
    php-pdo@php \
    php-gd@php \
    php-iconv@php \
    php-mysqli@php \
    php-mysqlnd@php \
    php-pdo_mysql@php \
    php-mbstring@php \
    php-phar@php \
    php-session@php \
    php-dom@php \
    php-ctype@php \
    php-zlib@php \
    php-json@php \
    php-posix@php \
    php-xmlreader@php \
    php-zip@php \
    php-xml@php && \
    ln -s /usr/bin/php7 /usr/bin/php
# CONFIGURE WEB SERVER.
RUN mkdir -p /var/www && \
    mkdir -p /run/php && \
    mkdir -p /run/nginx && \
    mkdir -p /var/log/supervisor && \
    mkdir -p /etc/nginx/sites-enabled && \
    mkdir -p /etc/nginx/sites-available && \
    rm /etc/nginx/nginx.conf && \
    rm /etc/php7/php-fpm.d/www.conf && \
    rm /etc/php7/php.ini

# INSTALL COMPOSER.
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '${COMPOSER_HASH}') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php --install-dir=/usr/bin --filename=composer && \
    php -r "unlink('composer-setup.php');"

# ADD START SCRIPT, SUPERVISOR CONFIG, NGINX CONFIG AND RUN SCRIPTS.
ADD start.sh /start.sh
ADD supervisord/supervisord.conf /etc/supervisord.conf
ADD php/php.ini /etc/php7/php.ini
ADD php-fpm/www.conf /etc/php7/php-fpm.d/www.conf

ADD nginx/nginx.conf /etc/nginx/nginx.conf
ADD nginx/site.conf /etc/nginx/sites-available/default.conf

RUN chmod 755 /start.sh
# EXPOSE PORTS!
EXPOSE 80

# SET THE WORK DIRECTORY.

WORKDIR /var/www
RUN chown --changes --silent --no-dereference --recursive \
    ${USER_ID}:${GROUP_ID} \
    # /home/www-data \
    /var/log/php7 \
    /var/log/nginx/error.log \
    /var/log/nginx/access.log;


CMD ["/start.sh"]