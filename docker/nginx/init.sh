#!/usr/bin/env sh
set -e

CONFIG_PATH="/etc/nginx/nginx.conf"

if [[ $NODE_ENV eq "local" ]]; then
    ln -sf /bin/bash /bin/sh
    useradd -ms /bin/bash ${USER_ID:-1000}
else
    USER_ID="www-data"
fi

sed -i "s#%USER_ID%#${USER_ID}#g" "$CONFIG_PATH"

exec "$@";
