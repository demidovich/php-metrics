FROM demidovich/nginx:1.17-alpine

ARG UID=82
ARG GID=82
ENV UID=${UID:-82} \
    GID=${GID:-82}

COPY ./docker/app/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/app/nginx/host.conf /etc/nginx/hosts/host.conf

RUN set -eux \
    && if [ $UID -ne 82 ]; then \
        usermod -u $UID www-data; \
    fi \
    && if [ $GID -ne 82 ]; then \
        groupmod -g $GID www-data; \
    fi \
    && touch /run/nginx.pid \
    && chown \
        --changes \
        --silent \
        --no-dereference \
        --recursive \
        ${UID}:${GID} \
        /run/nginx.pid \
        /var/cache/nginx

USER $UID

WORKDIR /app

EXPOSE 8080


