FROM php:7.3-fpm-alpine

ENV FOP_HOME=/usr/share/fop-2.1 COMPOSER_ALLOW_SUPERUSER=1

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY . /atom/src

RUN set -xe \
		&& apk add --no-cache --virtual .phpext-builddeps \
			gettext-dev \
			libxslt-dev \
			zlib-dev \
			libmemcached-dev \
			libzip-dev \
			autoconf \
			build-base \
		&& docker-php-ext-install \
			calendar \
			gettext \
			mbstring \
			mysqli \
			opcache \
			pdo_mysql \
			sockets \
			xsl \
			zip \
		&& curl -Ls https://github.com/websupport-sk/pecl-memcache/archive/NON_BLOCKING_IO_php7.tar.gz | tar xz -C / \
		&& cd /pecl-memcache-NON_BLOCKING_IO_php7 \
		&& phpize && ./configure && make && make install \
		&& docker-php-ext-enable memcache \
		&& cd / && rm -rf /pecl-memcache-NON_BLOCKING_IO_php7 \
		&& apk add --no-cache --virtual .phpext-rundeps \
			gettext \
			libxslt \
			libmemcached-libs \
			libzip \
		&& apk del .phpext-builddeps \
		&& apk add --no-cache --virtual .atom-deps \
			openjdk8-jre-base \
			ffmpeg \
			imagemagick \
			ghostscript \
			poppler-utils \
			npm \
			make \
			bash \
		&& npm install -g "less@<2.0.0" \
		&& curl -Ls https://archive.apache.org/dist/xmlgraphics/fop/binaries/fop-2.1-bin.tar.gz | tar xz -C /usr/share \
		&& ln -sf /usr/share/fop-2.1/fop /usr/local/bin/fop \
		&& make -C /atom/src/plugins/arDominionPlugin \
		&& make -C /atom/src/plugins/arArchivesCanadaPlugin \
		&& composer install -d /atom/src

WORKDIR /atom/src

ENTRYPOINT ["/atom/src/docker/entrypoint.sh"]

CMD ["fpm"]
