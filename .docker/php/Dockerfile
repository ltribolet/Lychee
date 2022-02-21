FROM php:8.1.3-fpm

# Set version label
LABEL maintainer="luctribolet"

# Environment variables
ENV PUID='1000'
ENV PGID='1000'
ENV USER='www-data'
ENV PHP_TZ=UTC

# Install base dependencies, clone the repo and install php libraries
RUN \
    set -ev && \
    apt-get update && \
    apt-get install -y --no-install-recommends \
    curl \
    locales \
    libicu-dev \
    libmagickwand-dev \
    libimage-exiftool-perl \
    zlib1g-dev \
    libjpeg-dev \
    libonig-dev \
    libmcrypt-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    ffmpeg \
    ufraw \
    ufraw-batch \
    git \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    webp \
    unzip \
    zip

RUN pecl install imagick mcrypt

RUN docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-jpeg=/usr

RUN docker-php-ext-enable \
    imagick \
    mcrypt

RUN docker-php-ext-install -j$(nproc) \
      bcmath \
      gd \
      gettext \
      intl \
      exif \
      mysqli \
      pcntl \
      pdo \
      pdo_mysql \
      sockets \
      opcache \
      xml \
      zip

RUN pecl install xdebug
COPY .docker/php/conf/15-xdebug.ini /usr/local/etc/php/conf.d/15-xdebug.ini
COPY .docker/php/conf/lychee-10-opcache.ini /usr/local/etc/php/conf.d/sycle-10-opcache.ini

# clean up to reduce image size
RUN  apt-get clean \
    && apt-get autoremove \
    && rm -rf /var/lib/apt/lists/* \
    # remove documentation to reduce image size
    && rm -rf /usr/share/man/man1

RUN curl --silent --show-error https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Change user to be matching the one from host.
RUN usermod -u "$PUID" "$USER" \
    && groupmod -g "$PGID" "$USER"

WORKDIR /var/www/lychee
RUN mkdir /var/www/.composer
COPY --chown="$USER":"$USER" . /var/www/lychee
RUN chown -R "$USER":"$USER" /var/www
USER "$USER":"$USER"

# Speed up composer install
RUN composer global require hirak/prestissimo

CMD ["php-fpm", "-F"]