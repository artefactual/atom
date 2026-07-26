FROM composer:2@sha256:5946476338742b200bb9ff88f8be56275ddae4b3949c72305cb0dbf10cfcb760 AS composer-base

WORKDIR /atom/build

COPY composer.* ./
COPY vendor/FreeBeerIso639Map.php vendor/FreeBeerIso639Map.php

FROM composer-base AS composer-development

RUN composer install \
      --no-interaction \
      --no-progress \
      --prefer-dist

FROM composer-base AS composer-production

COPY src src

RUN composer install \
      --classmap-authoritative \
      --no-dev \
      --no-interaction \
      --no-progress \
      --prefer-dist

FROM node:22-alpine@sha256:16e22a550f3863206a3f701448c45f7912c6896a62de43add43bb9c86130c3e2 AS frontend

WORKDIR /atom/src

COPY package*.json ./

RUN npm ci

COPY . .

RUN NODE_ENV=production npm run build

FROM frontend AS frontend-production

RUN set -eux \
    && rm -rf node_modules \
    && rm -f package.json package-lock.json webpack.config.js

FROM php:8.3-fpm-alpine@sha256:9fcec48321d890240d700ccdc2b475420c87d398826e68c3d8830b8fca663e5c AS php-runtime

ARG FOP_SHA256=a93b59aa4d0b6d573c9090d8f21dee6c7d0c449a4bd2d48a1723e233dfb423ea
ARG MEMCACHE_SHA256=4dc6ddf7a4c4f5996c585b93d5159d56a9e9c49c08e29b04b4b97da5fec1ddc7

ENV FOP_HOME=/usr/share/fop-2.1 \
    LD_PRELOAD=/usr/lib/preloadable_libiconv.so

RUN set -eux \
    && apk add --no-cache --virtual .phpext-builddeps \
      autoconf \
      build-base \
      gettext-dev \
      icu-dev \
      imagemagick-dev \
      libmemcached-dev \
      libzip-dev \
      linux-headers \
      libxslt-dev \
      oniguruma-dev \
      openldap-dev \
      zlib-dev \
    && docker-php-ext-install \
      calendar \
      gettext \
      intl \
      ldap \
      mbstring \
      mysqli \
      opcache \
      pcntl \
      pdo_mysql \
      sockets \
      xsl \
      zip \
    && pecl install apcu-5.1.28 imagick-3.8.0 \
    && curl -LfsS \
      https://github.com/websupport-sk/pecl-memcache/archive/refs/tags/8.2.tar.gz \
      -o /tmp/memcache.tar.gz \
    && echo "${MEMCACHE_SHA256}  /tmp/memcache.tar.gz" \
      | sha256sum -c - \
    && tar xzf /tmp/memcache.tar.gz -C /tmp \
    && cd /tmp/pecl-memcache-8.2 \
    && phpize \
    && ./configure \
    && make -j"$(getconf _NPROCESSORS_ONLN)" \
    && make install \
    && cd / \
    && docker-php-ext-enable apcu imagick memcache \
    && apk add --no-cache --virtual .phpext-rundeps \
      gettext \
      icu-libs \
      libmemcached-libs \
      libzip \
      libxslt \
      openldap \
    && apk del .phpext-builddeps \
    && pecl clear-cache \
    && rm -rf /tmp/memcache.tar.gz /tmp/pecl-memcache-8.2 \
    && apk add --no-cache --virtual .atom-runtime \
      bash \
      fcgi \
      ffmpeg \
      ghostscript \
      gnu-libiconv \
      imagemagick \
      openjdk8-jre-base \
      poppler-utils \
    && curl -LfsS \
      https://archive.apache.org/dist/xmlgraphics/fop/binaries/fop-2.1-bin.tar.gz \
      -o /tmp/fop.tar.gz \
    && echo "${FOP_SHA256}  /tmp/fop.tar.gz" \
      | sha256sum -c - \
    && tar xzf /tmp/fop.tar.gz -C /usr/share \
    && rm /tmp/fop.tar.gz \
    && ln -s /usr/share/fop-2.1/fop /usr/local/bin/fop \
    && addgroup -S -g 1000 atom \
    && adduser -S -D -H -u 1000 -G atom atom \
    && chown -R atom:atom \
      /usr/local/etc/php \
      /usr/local/etc/php-fpm.d

WORKDIR /atom/src

FROM php-runtime AS development

USER root

RUN set -eux \
    && apk add --no-cache --virtual .devext-builddeps \
      autoconf \
      build-base \
      linux-headers \
    && pecl install pcov-1.0.12 xdebug-3.5.3 \
    && docker-php-ext-enable pcov \
    && apk del .devext-builddeps \
    && pecl clear-cache

RUN apk add --no-cache make

COPY --from=composer-base /usr/bin/composer /usr/bin/composer
COPY --from=frontend /usr/local/bin/node /usr/local/bin/node
COPY --from=frontend /usr/local/lib/node_modules /usr/local/lib/node_modules

RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s ../lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

COPY --from=frontend /atom/src /atom/src
COPY --from=composer-development /atom/build/vendor/composer /atom/src/vendor/composer

ENTRYPOINT ["docker/entrypoint.sh"]

CMD ["fpm"]

FROM php-runtime AS production

ARG VCS_REF=unknown

LABEL org.opencontainers.image.revision="${VCS_REF}" \
      org.opencontainers.image.source="https://github.com/artefactual/atom"

COPY --from=frontend-production /atom/src /atom/src
COPY --from=composer-production /atom/build/vendor/composer /atom/src/vendor/composer

RUN set -eux \
    && mkdir -p \
      cache \
      downloads \
      log \
      uploads \
      var/sessions \
    && chown -R atom:atom \
      apps/qubit/config \
      cache \
      config \
      downloads \
      log \
      uploads \
      var \
    && test ! -d node_modules \
    && test ! -d output \
    && test ! -d playwright \
    && test ! -e playwright.config.js \
    && test ! -e vendor/bin/phpunit \
    && test ! -d vendor/symfony \
    && test -e vendor/propel1/runtime/propel/Propel.php

USER atom

ENTRYPOINT ["docker/entrypoint.sh"]

HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
  CMD ["php", "docker/healthcheck.php", "ready"]

CMD ["fpm"]
