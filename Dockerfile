FROM php:8.3-fpm-alpine

ENV FOP_HOME=/usr/share/fop-2.1 \
    COMPOSER_ALLOW_SUPERUSER=1 \
    LD_PRELOAD=/usr/lib/preloadable_libiconv.so

RUN set -xe \
    && apk add --no-cache --virtual .phpext-builddeps \
      gettext-dev \
      libxslt-dev \
      zlib-dev \
      libmemcached-dev \
      libzip-dev \
      oniguruma-dev \
      autoconf \
      build-base \
      openldap-dev \
      linux-headers \
    && docker-php-ext-install \
      calendar \
      gettext \
      mbstring \
      mysqli \
      opcache \
      pcntl \
      pdo_mysql \
      sockets \
      xsl \
      zip \
      ldap \
    && pecl install apcu pcov xdebug \
    && curl -Ls https://github.com/websupport-sk/pecl-memcache/archive/refs/tags/8.2.tar.gz | tar xz -C / \
    && cd /pecl-memcache-8.2 \
    && phpize && ./configure && make && make install \
    && cd / && rm -rf /pecl-memcache-8.2 \
    && docker-php-ext-enable apcu memcache pcov xdebug \
    && apk add --no-cache --virtual .phpext-rundeps \
      gettext \
      libxslt \
      libmemcached-libs \
      libzip \
      openldap-dev \
    && apk del .phpext-builddeps \
    && pecl clear-cache \
    && apk add --no-cache --virtual .atom-deps \
      openjdk8-jre-base \
      ffmpeg \
      imagemagick \
      ghostscript \
      poppler-utils \
      npm \
      make \
      bash \
      gnu-libiconv \
      fcgi \
    && curl -Ls https://archive.apache.org/dist/xmlgraphics/fop/binaries/fop-2.1-bin.tar.gz | tar xz -C /usr/share \
    && ln -sf /usr/share/fop-2.1/fop /usr/local/bin/fop \
    && echo "extension=ldap.so" > /usr/local/etc/php/conf.d/docker-php-ext-ldap.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.* /atom/build/

RUN set -xe && composer install -d /atom/build

COPY package* /atom/build/

RUN set -xe && npm install --prefix /atom/build

COPY . /atom/src

WORKDIR /atom/src

RUN set -xe \
    && mv /atom/build/vendor/composer vendor/ \
    && mv /atom/build/node_modules . \
    && npm run build \
    && rm -rf /atom/build

ENTRYPOINT ["docker/entrypoint.sh"]

CMD ["fpm"]
