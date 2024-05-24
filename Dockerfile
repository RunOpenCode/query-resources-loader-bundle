FROM php:8-fpm

RUN apt-get update
RUN apt-get install -y --no-install-recommends wget
RUN apt-get install -y --allow-unauthenticated gnupg
RUN apt-get install -y --allow-unauthenticated xsltproc
RUN apt-get install -y --allow-unauthenticated git
RUN apt-get install -y --allow-unauthenticated zip
RUN apt-get install -y --allow-unauthenticated unzip
RUN apt-get install -y --allow-unauthenticated libzip-dev

RUN docker-php-ext-install pcntl
RUN docker-php-ext-install posix
RUN docker-php-ext-install zip

RUN pecl install xdebug

RUN echo "zend_extension=xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.mode=debug" > /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.discover_client_host=0" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.idekey=\"PHPSTORM\"" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.max_nesting_level=700" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.client_port=9000" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.client_host=\"host.docker.internal\"" >> /usr/local/etc/php/conf.d/xdebug.ini

#####################################################################################
#                                                                                   #
#                                 Setup Composer                                    #
#                                                                                   #
#####################################################################################

WORKDIR /tmp

ENV COMPOSER_HOME /composer

# Add global binary directory to PATH and make sure to re-export it
ENV PATH /composer/vendor/bin:$PATH

# Allow Composer to be run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

# Setup the Composer installer
RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
    && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
    && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }"

RUN php /tmp/composer-setup.php

RUN mv /tmp/composer.phar /usr/local/bin/composer.phar && \
    ln -s /usr/local/bin/composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

WORKDIR /var/www/html
