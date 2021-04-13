#!/bin/bash
set -e
cd /app
## Download Z-BlogPHP
if ! [ -e /app/index.php ]; then
	echo Downloading Z-BlogPHP...
	wget https://update.zblogcn.com/zip/Z-BlogPHP_1_6_7_2160_Valyria.zip
	echo Unpacking Z-BlogPHP...
	unzip -oq Z-BlogPHP_1_6_7_2160_Valyria.zip
	## Modify permissions
	chown -R www-data:www-data /app
fi
if ! [ -e /app/zb_users/c_option.php ]; then
	echo Installing Z-BlogPHP...
	php /app/install-docker.php
fi
if [ -e /app/zb_users/c_option.php ]; then
	rm Z-BlogPHP_1_6_7_2160_Valyria.zip
	rm /app/install-docker.php
	rm -rf /app/zb_install
fi
# echo "$@"
# exec "$@"
