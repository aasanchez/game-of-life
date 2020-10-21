FROM php:fpm-alpine

RUN apk --update --no-cache add git
COPY --from=composer /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
CMD composer install --prefer-dist --no-progress --no-suggest; php-fpm
ENTRYPOINT ["/usr/bin/composer --help"]
