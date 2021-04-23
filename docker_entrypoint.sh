#!/bin/bash
set -e
cd /app

## Download Z-BlogPHP
if ! [ -e /app/index.php ]; then
	echo Downloading Z-BlogPHP...
	wget https://update.zblogcn.com/zip/Z-BlogPHP_1_6_7_2160_Valyria.zip
	echo Unpacking Z-BlogPHP...
	unzip -oq Z-BlogPHP_1_6_7_2160_Valyria.zip
	echo Delete zip
	rm /app/Z-BlogPHP_1_6_7_2160_Valyria.zip
	chown -R www-data:www-data /app
fi

if ! [ -e /app/zb_users/c_option.php ]; then
	echo Installing Z-BlogPHP...
	php /app/install-docker.php
fi

if [ "$ZC_SKIP_TC_PLUGINS" -eq "0" ]; then
	echo Updating Plugins...
	php /app/install-docker-plugins.php
fi

if [ -e /app/zb_users/c_option.php ]; then
	rm -rf /app/zb_install
	rm /app/install-docker.php
	rm /app/install-docker-plugins.php
fi

# echo "$@"
# exec "$@"
