FROM redis:5.0.5-alpine3.10

ARG UID=1000
ARG GID=1000
ENV UID=${UID:-1000} \
    GID=${GID:-1000}

RUN set -eux \
    && apk add --update --no-cache shadow \
    && rm -rf /var/cache/apk/* \
    && rm -rf /tmp/* \
    && useradd -u ${UID} www-data

USER $UID

EXPOSE 6379


