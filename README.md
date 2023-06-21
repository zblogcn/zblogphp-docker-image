# zblogphp-docker

基于 `webdevops/php-nginx`，默认支持伪静态。

项目地址：[https://github.com/zblogcn/zblogphp-tencent-openapp-docker](https://github.com/zblogcn/zblogphp-tencent-openapp-docker "GitHub - zblogcn/zblogphp-tencent-openapp-docker")

## 使用 Docker Compose 部署「推荐」

wdssmq/zbp-docker-compose: 使用 Docker Compose 快捷部署 Z-BlogPHP + MySQL；：

[https://github.com/wdssmq/zbp-docker-compose](https://github.com/wdssmq/zbp-docker-compose "wdssmq/zbp-docker-compose: 使用 Docker Compose 快捷部署 Z-BlogPHP + MySQL；")

## 实际可用镜像及 TAG

- `wdssmq/zblogphp`
    - `wdssmq/zblogphp:latest` - Nginx + PHP 7.4
- `wdssmq/zblogphp-nginx`
    - `wdssmq/zblogphp-nginx:latest` - 同 8.2
    - `wdssmq/zblogphp-nginx:8.2`    - Nginx + PHP 8.2
    - `wdssmq/zblogphp-nginx:8.1`    - Nginx + PHP 8.1
    - `wdssmq/zblogphp-nginx:8.0`    - Nginx + PHP 8.0
    - `wdssmq/zblogphp-nginx:7.4`    - Nginx + PHP 7.4

## 构建和调试

构建镜像

```bash
# 克隆项目并进入目录
git clone git@github.com:zblogcn/zblogphp-tencent-openapp-docker.git zbp-docker
cd zbp-docker

# Build
docker build -t zblogcn/zblogphp .

# 指定 PHP 版本
docker build -t zblogcn/zblogphp:8.2 --build-arg PHP_VER="8.2" .

```
运行：

```bash
# docker rm --force zbp
docker run --rm --name zbp \
  -e ZC_DB_HOST=host.docker.internal \
  -e ZC_DB_NAME=zblog_docker \
  -e ZC_DB_USER=root \
  -e ZC_DB_PWDD= \
  -e ZC_BLOG_USER=admin \
  -e ZC_BLOG_PWDD=shezhidemima \
  -p 8288:80 zblogcn/zblogphp
#exit
```
正式运行将`--rm`参数改为`-d`

## 注意事项

镜像内不包含数据库；通过`host.docker.internal`可访问宿主环境。

以下变量可选，使用`-e ZC_DB_PREFIX=pre_`指定：

```php
// 可选
define('DB_PREFIX', getenv_docker('ZC_DB_PREFIX', 'zbp_'));

define('DB_ENGINE', getenv_docker('ZC_DB_ENGINE', 'MyISAM'));

define('DB_TYPE', getenv_docker('ZC_DB_TYPE', 'mysqli'));
```

「可选」`-e ZC_INSTALL_NAME=Z-BlogPHP_1_7_2_3050_Tenet`可指定 Z-BlogPHP 版本；

「可选」默认会安装腾讯云相关插件，可使用`-e ZC_SKIP_TC_PLUGINS=1`跳过；

> 腾讯云服务插件：[https://app.zblogcn.com/circle/?id=18117](https://app.zblogcn.com/circle/?id=18117 "腾讯云服务插件 - Z-Blog 应用中心")

「可选」`-e ZC_SKIP_CHMOD=1`——跳过文件权限变更，适用于插件开发等文件较多的场景，此时需要自行确保具体文件的写入权限；

「可选」如需使用`-v /root/zbp:/app`映射站点目录，请在首次运行时复制`install-docker.php`、`install-docker-plugins.php`到`/root/zbp`文件夹内。
