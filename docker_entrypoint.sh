#!/bin/bash
set -e
## Download Z-BlogPHP
if ! [ -e /app/index.php ]; then
	echo Downloading Z-BlogPHP...
	cd /app
	wget https://update.zblogcn.com/zip/Z-BlogPHP_1_6_7_2160_Valyria.zip
	unzip Z-BlogPHP_1_6_7_2160_Valyria.zip
	rm Z-BlogPHP_1_6_7_2160_Valyria.zip
	## Modify permissions
	chown -R www-data:www-data /app
	php /app/install-docker.php
fi
# echo "$@"
# exec "$@"
