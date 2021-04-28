#!/bin/bash
set -e
cd /app

## Download Z-BlogPHP
if ! [ -e /app/index.php ]; then
    echo Downloading Z-BlogPHP...
    wget https://update.zblogcn.com/zip/Z-BlogPHP_1_6_8_2210_Valyria.zip
    echo Unpacking Z-BlogPHP...
    unzip -oq Z-BlogPHP_1_6_8_2210_Valyria.zip
    echo Delete zip
    rm /app/Z-BlogPHP_1_6_8_2210_Valyria.zip
fi

# # cd /app
# chown -R www-data:www-data /app
# find ./ -type d -print|xargs chmod 777
# find ./ -type f -print|xargs chmod 777

if [ ! -e /app/zb_users/c_option.php ] && [ -e /app/install-docker.php ]; then
    echo Installing Z-BlogPHP...
    php /app/install-docker.php
fi

if [ "$ZC_SKIP_TC_PLUGINS" -eq "0" ] && [ -e /app/install-docker-plugins.php ]; then
    echo Updating Plugins...
    php /app/install-docker-plugins.php
fi

if [ -e /app/zb_users/c_option.php ]; then
    if [ -d /app/zb_install ]; then
        rm -rf /app/zb_install
    fi
    if [ -e /app/install-docker.php ]; then
        rm /app/install-docker.php
    fi
    if [ -e /app/install-docker-plugins.php ]; then
        rm /app/install-docker-plugins.php
    fi
fi

if [ "$ZC_SKIP_CHMOD" -eq "0" ]; then
    # cd /app
    chown -R www-data:www-data /app
    find ./ -type d -print|xargs chmod 777
    find ./ -type f -print|xargs chmod 777
fi

# echo "$@"
# exec "$@"
