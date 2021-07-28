ARG PHP_VERSION=7.4
ARG WORDPRESS_VERSION=5.8

FROM wordpress:cli-php${PHP_VERSION} AS wp-cli
FROM wordpress:${WORDPRESS_VERSION}-php${PHP_VERSION}-apache

# Needed for Codeception WPDB test integration.
RUN docker-php-ext-install pdo pdo_mysql

# Add wp-cli to the main container.
COPY --from=wp-cli /usr/local/bin/wp /usr/local/bin/wp

RUN set -e \
    ; apt-get -qq update \
    ; apt-get install \
        default-mysql-client \
        less \
        openssh-server \
        sudo \
        vim \
        -qq --no-install-recommends \
    ; sudo adduser www-data sudo \
    ; chmod +x /usr/local/bin/wp \
    ; chown -R www-data:www-data /var/www \
    ; rm -rf /var/lib/apt/lists/*
