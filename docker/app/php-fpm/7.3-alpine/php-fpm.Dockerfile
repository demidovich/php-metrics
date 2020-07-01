FROM demidovich/php-fpm:7.3-alpine

ARG UID=82
ARG GID=82
ENV UID=${UID:-82} \
    GID=${GID:-82}

COPY ./docker/app/php-fpm/7.3-alpine/conf/www.conf /etc/php7/php-fpm.d/
COPY ./docker/app/php-fpm/7.3-alpine/conf/50-options.ini /etc/php7/conf.d/

RUN set -eux \
    # && sed -i "s#%USER_ID%#${UID}#g" "/etc/php7/php-fpm.d/www.conf" \
    # && sed -i "s#%GROUP_ID%#${GID}#g" "/etc/php7/php-fpm.d/www.conf"
    && if [ $UID -ne 82 ]; then \
        usermod -u $UID www-data; \
    fi \
    && if [ $GID -ne 82 ]; then \
        groupmod -g $GID www-data; \
    fi \
    && chown \
        --changes \
        --silent \
        --no-dereference \
        --recursive \
        ${UID}:${GID} \
        /composer \
        /var/log/php7

USER $UID

WORKDIR /app

EXPOSE 9000

ENTRYPOINT []
CMD ["php-fpm7", "-F"]

