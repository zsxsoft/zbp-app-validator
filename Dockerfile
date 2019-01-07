FROM ubuntu:18.04
MAINTAINER zsx<zsx@zsxsoft.com>
ENV NODEJS_VERSION v10.15.0
ENV DEBIAN_FRONTEND=noninteractive

ARG location
RUN export NODEJS_HOST=https://nodejs.org/dist/; if [ "x$location" = "xchina" ]; then echo "Changed Ubuntu source"; sed -i 's/http:\/\/archive\.ubuntu\.com\/ubuntu\//http:\/\/mirrors\.tuna\.tsinghua\.edu\.cn\/ubuntu\//g' /etc/apt/sources.list; export NPM_CONFIG_REGISTRY=https://registry.npm.taobao.org; export ELECTRON_MIRROR=http://npm.taobao.org/mirrors/electron/; export PUPPETEER_DOWNLOAD_HOST=https://storage.googleapis.com.cnpmjs.org; export NODEJS_HOST=https://npm.taobao.org/mirrors/node/; fi; \
    \
    mkdir /data/ /data/logs/ /data/logs/nginx /data/www/ /data/tools /www/ \
    && mkdir /zbp-app-validator \
    && apt-get update \
    && apt-get -y install software-properties-common \
# Base
    && apt-get -y install git curl wget iptables unzip sudo \
    && (echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list) \
    && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
# Fonts (Chinese, wqy-microhei)
    && apt-get install -y --force-yes --no-install-recommends fonts-noto fonts-wqy-microhei \
# nginx & PHP
    && LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php \
    && apt-get update \
    && apt-get -y install nginx php7.3-fpm php7.3-gd php7.3-curl php7.3-mysql php7.3-cli php7.3-xml php7.3-json php7.3-mbstring php7.3-cli php7.3-sqlite3 php7.3-zip \
    && rm -rf /etc/nginx/sites-enabled/default \
    && curl https://getcomposer.org/installer | php -- --filename=composer \
    && chmod a+x composer \
    && mv composer /usr/local/bin/composer \
# Nodejs
    && apt-get -y install yarn \
    && wget "$NODEJS_HOST/$NODEJS_VERSION/node-$NODEJS_VERSION-linux-x64.tar.xz" -O/tmp/node.tar.xz \
    && tar -C /usr/local/ -xvf /tmp/node.tar.xz --strip-components 1 \
# Java
    && apt-get -y install openjdk-8-jre \
# Chromium
    && apt-get -y install libnss3 libnss3-tools libasound2 libxss1 \
# MySQL
    && bash -c "debconf-set-selections <<< 'mysql-server mysql-server/root_password password rootpassword'" \
    && bash -c "debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password rootpassword'" \
    && apt-get -y install mysql-server \
    && mkdir /var/run/mysqld \
    && chown -R mysql:mysql /var/lib/mysql /var/run/mysqld \
# strace & tshark
    && apt-get -y install strace tshark \
# mitmproxy
    && wget https://github.com/mitmproxy/mitmproxy/releases/download/v4.0.1/mitmproxy-4.0.1-linux.tar.gz -O/tmp/mitmproxy.tar.gz \
    && tar -C /usr/local/bin -xvf /tmp/mitmproxy.tar.gz \
    && chmod 0777 /usr/local/bin/mitmdump /usr/local/bin/mitmproxy /usr/local/bin/mitmweb \
    && sysctl -w net.ipv4.ip_forward=1 \
# Clean rubbish
    && apt-get -y autoremove \
    && apt-get autoclean \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/*

RUN mkdir /data/certs \
    && ln -s /data/certs /root/.mitmproxy \
    && timeout 5s mitmdump || true \
    && cp /root/.mitmproxy/mitmproxy-ca-cert.pem /usr/local/share/ca-certificates/mitmproxy.crt \
    && update-ca-certificates --fresh

COPY docker-scripts/64-language-selector-prefer.conf /etc/fonts/conf.d/64-language-selector-prefer.conf
RUN fc-cache -fv

COPY package.json yarn.lock composer.json composer.lock /zbp-app-validator/
WORKDIR /zbp-app-validator/


RUN if [ "x$location" = "xchina" ]; then composer config -g repo.packagist composer https://packagist.phpcomposer.com; fi; \
    yarn && yarn cache clean --force && composer install && composer clearcache

COPY ./ /zbp-app-validator/
RUN chmod 0777 /zbp-app-validator/docker-scripts/* \
    && bash /zbp-app-validator/docker-scripts/docker-init.sh \
    && bash /zbp-app-validator/docker-scripts/create-site.sh local zblogphp.local

ENV HOME=/data/www/local/home

VOLUME ["/var/lib/mysql/", "/data/www/local/www/"]
EXPOSE 3000
CMD ["/zbp-app-validator/docker-scripts/run.sh"]
