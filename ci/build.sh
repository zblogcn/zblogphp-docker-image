#!/bin/bash

# 设置变量
web_server=nginx
php_vers=(7.4 8.0 8.1 8.2)

# 错误检查
set -e

# 构建镜像并推送到 Docker Hub
for php_ver in "${php_vers[@]}"
do
    docker build -t "wdssmq/zblogphp-${web_server}:${php_ver}" \
    --build-arg WEB_SERVER="${web_server}" \
    --build-arg PHP_VER="${php_ver}" \
    .

    echo "Image wdssmq/zblogphp-${web_server}:${php_ver} built successfully!"
    docker push "wdssmq/zblogphp-${web_server}:${php_ver}"
done

# 默认版本
docker build -t "wdssmq/zblogphp-${web_server}:latest" \
    --build-arg WEB_SERVER="${web_server}" \
    --build-arg PHP_VER="8.2" \
    .
docker push "wdssmq/zblogphp-${web_server}:latest"

# 这里默认 PHP 7.4，Nginx
docker build -t "wdssmq/zblogphp:latest" .
docker push "wdssmq/zblogphp:latest"

# # 一个年月版本的 tag
# docker build -t "wdssmq/zblogphp:$(date +%y.%m)" .
# docker push "wdssmq/zblogphp:$(date +%y.%m)"

# 清理无用的镜像和容器
docker image prune -f
docker container prune -f
