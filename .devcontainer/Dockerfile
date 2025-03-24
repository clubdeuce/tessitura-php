FROM mcr.microsoft.com/devcontainers/base:ubuntu-24.04

# use this Dockerfile to install additional tools you might need, e.g.
# RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
#     && apt-get -y install --no-install-recommends <your-package-list-here>
RUN apt update \
    && apt install -y \
    imagemagick \
    openssl \
    redis-server \
    php-cli \
    php-fpm \
    php-bcmath \
    php-common \
    php-curl \
    php-dev \
    php-gd \
    php-intl \
    php-mbstring \
    php-mysqli \
    php-phar \
    php-pspell \
    php-redis \
    php-xdebug \
    php-xml