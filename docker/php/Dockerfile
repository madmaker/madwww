#FROM php:5.6-apache
FROM php:5.6-fpm

# database
RUN docker-php-ext-install -j$(nproc) \
	mysqli \
	pdo \
	pdo_mysql

## IMAGIC
RUN apt-get update && apt-get install -y
#RUN    libmagickwand-dev --no-install-recommends \
#    && pecl install imagick

RUN docker-php-ext-enable imagick

## XDEBUG
#RUN pecl install xdebug-2.2.7

#RUN docker-php-ext-enable mbstring
#RUN docker-php-ext-enable pdo pdo_mysql mysqli
#RUN apt-get install php5.6-mbstring \
#RUN apt-get update && apt-get install -y \
#        git \
#        curl \
#        wget \
#        libfreetype6-dev \
#        libjpeg62-turbo-dev \
#        libmcrypt-dev \
#        libpng-dev zlib1g-dev libicu-dev g++ libmagickwand-dev --no-install-recommends libxml2-dev \
    #&& docker-php-ext-configure intl \
    #&& docker-php-ext-install intl \
    #&& docker-php-ext-install mbstring zip xml gd \
    #&& pecl install imagick \
    #&& docker-php-ext-enable imagick \
    #&& pecl install xdebug-2.5.0 \
    #&& docker-php-ext-enable xdebug





#RUN apk add --no-cache \
#    autoconf \
#    curl \
#    g++ \
#    gcc \
#    git \
#    libtool \
#    make \
#    tar \
#    freetype-dev \
#    libjpeg-turbo-dev \
#    libmcrypt-dev \
#    libpng-dev \
#    postgresql-dev \
#    zip \
#    unzip \
#    wget

#RUN mkdir /var/log/php

#RUN
#docker-php-ext-configure gd \
#        -with-freetype-dir=/usr/include/ \
#        -with-png-dir=/usr/png/ \
#        -with-jpeg-dir=/usr/jpeg/ \
#    && docker-php-ext-configure pgsql \
#        -with-pgsql=/usr/include/postgresql/ \

    # Finally, install php compiled extensions
#    && docker-php-ext-install \
#        gd \
#        mcrypt \
#        mysqli \
#        pdo \
#        pdo_pgsql \
#        pdo_mysql \
#        mbstring \
#        tokenizer \
#        opcache \
#        exif \
#        pgsql

# Intl
#RUN apk add --no-cache icu-dev \
#    && docker-php-ext-install intl


# Imagick
#RUN apk add --no-cache --virtual .imagick-build-dependencies \
#    imagemagick-dev \
#    && apk add --virtual .imagick-runtime-dependencies \
#    imagemagick \
#
#    && IMAGICK_TAG="3.4.2" \
#    && git clone -o ${IMAGICK_TAG} --depth 1 https://github.com/mkoppanen/imagick.git /tmp/imagick \
#    && cd /tmp/imagick \
#
#    && phpize \
#    && ./configure \
#    && make \
#    && make install \
#
#    && echo "extension=imagick.so" > /usr/local/etc/php/conf.d/ext-imagick.ini \
#
#    && apk del .imagick-build-dependencies
