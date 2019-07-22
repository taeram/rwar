FROM php:7.3-apache

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
# https://pecl.php.net/package/imagick
ENV IMAGEMAGICK_VERSION=3.4.3

# Enable the Apache modules
RUN a2enmod rewrite

# Set the Apache path
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install ImageMagick
RUN apt-get update && apt-get install -y --no-install-recommends libmagickwand-dev imagemagick && \
  mkdir -p /usr/src/php/ext/imagick && \
  curl --location https://pecl.php.net/get/imagick-${IMAGEMAGICK_VERSION}.tgz | tar zx --strip-components=1 -C /usr/src/php/ext/imagick && \
  export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS" && \
  docker-php-ext-install imagick
