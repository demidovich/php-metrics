FROM demidovich/php-fpm:7.4-ubuntu

ARG UID=82
ARG GID=82
ENV UID=${UID:-82} \
    GID=${GID:-82}

RUN set -eux \
    && if [ $UID -ne 82 ]; then \
        usermod -u ${UID} www-data; \
    fi \
    && if [ $GID -ne 82 ]; then \
        groupmod -g ${GID} www-data; \
    fi \
    && chown -R www-data:www-data /composer \
    && phpenmod -v 7.4 xdebug

RUN chmod u+s /usr/sbin/php-fpm7.4

USER "www-data"

WORKDIR /app

CMD ["/usr/sbin/php-fpm7.4", "-F" ]

EXPOSE 9000
