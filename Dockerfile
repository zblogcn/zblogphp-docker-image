FROM webdevops/php-nginx:7.4-alpine
# FROM webdevops/php-nginx:7.4

VOLUME /app

ADD install-docker.php /app/install-docker.php
ADD docker_entrypoint.sh /entrypoint.d
