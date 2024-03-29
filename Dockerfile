ARG WEB_SERVER=nginx
ARG PHP_VER=7.4

FROM webdevops/php-${WEB_SERVER}:${PHP_VER}

VOLUME /app

ADD nginx/vhost.common.d/10-location-root.conf /opt/docker/etc/nginx/vhost.common.d/10-location-root.conf

ENV ZC_SKIP_TC_PLUGINS 0
ENV ZC_SKIP_CHMOD 0
ENV ZC_INSTALL_NAME Z-BlogPHP_1_7_3_3290_Finch

# ADD install-docker.php /app/install-docker.php
# ADD install-docker-plugins.php /app/install-docker-plugins.php

ADD install-docker.php /root/install-docker.php
ADD install-docker-plugins.php /root/install-docker-plugins.php

ADD docker_entrypoint.sh /entrypoint.d
