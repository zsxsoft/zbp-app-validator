#!/bin/bash
# mitmproxy, for mmp-server
#(echo '--gid-owner' ; id -g local;) | tr "\n" " " | xargs iptables -t nat -A OUTPUT -p tcp ! -s 127.0.0.1 -j DNAT --to-destination 127.0.0.1:8080 -m owner
#sysctl -w net.ipv4.ip_forward=1

export DISPLAY=:99
Xvfb :99 -screen 0 1920x1080x24 &
service php7.2-fpm start && service nginx start && service mysql start
curl https://zblogphp.local --max-time 1 > /dev/null
if [ $? -ne 0 ]; then
  echo '127.0.0.1 zblogphp.local' >> /etc/hosts;
fi

cd /zbp-app-validator
mkdir tmp && chmod -R 0777 tmp
exec sudo -u local php checker $@
