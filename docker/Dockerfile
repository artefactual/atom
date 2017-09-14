FROM php:7-fpm-alpine

ARG GIT_BRANCH=qa/2.5.x

ENV FOP_HOME /usr/share/fop-2.1

RUN set -xe \
		&& apk add --no-cache --virtual .phpext-builddeps \
			gettext-dev \
			libmcrypt-dev \
			libxslt-dev \
			zlib-dev \
			libmemcached-dev \
			autoconf \
			build-base \
		&& docker-php-ext-install \
			gettext \
			mbstring \
			mcrypt \
			mysqli \
			opcache \
			pdo_mysql \
			sockets \
			xsl \
			zip \
		# https://bugs.php.net/bug.php?id=70751
		&& curl -Ls https://github.com/websupport-sk/pecl-memcache/archive/NON_BLOCKING_IO_php7.tar.gz | tar xz -C / \
		&& cd /pecl-memcache-NON_BLOCKING_IO_php7 \
		&& phpize && ./configure && make && make install \
		&& docker-php-ext-enable memcache \
		&& rm -rf /pecl-memcache-NON_BLOCKING_IO_php7 \
		&& apk add --virtual .phpext-rundeps \
			gettext \
			libmcrypt \
			libxslt \
			libmemcached-libs \
		&& apk del .phpext-builddeps

RUN set -xe \
		&& apk add --no-cache --virtual .atom-deps \
			openjdk8-jre-base \
			ffmpeg \
			imagemagick \
			ghostscript \
			poppler-utils \
			nodejs \
			make \
			bash \
		&& npm install -g "less@<2.0.0" \
		&& curl -Ls https://archive.apache.org/dist/xmlgraphics/fop/binaries/fop-2.1-bin.tar.gz | tar xz -C /usr/share \
		&& ln -sf /usr/share/fop-2.1/fop /usr/local/bin/fop

RUN set -xe \
		&& mkdir /atom \
		&& curl -Ls https://github.com/artefactual/atom/archive/$GIT_BRANCH.tar.gz | tar xz -C /atom \
		&& cd /atom \
		&& mv * src \
		&& make -C /atom/src/plugins/arDominionPlugin \
		&& make -C /atom/src/plugins/arArchivesCanadaPlugin

WORKDIR /atom/src
ENTRYPOINT ["/atom/src/docker/entrypoint.sh"]
CMD ["fpm"]
