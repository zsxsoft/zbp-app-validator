#!/bin/bash
# mitmproxy, for mmp-server
#(echo '--gid-owner' ; id -g local;) | tr "\n" " " | xargs iptables -t nat -A OUTPUT -p tcp ! -s 127.0.0.1 -j DNAT --to-destination 127.0.0.1:8080 -m owner
#sysctl -w net.ipv4.ip_forward=1

cd /zbp-app-validator
if [ ! -d tmp ]; then
  mkdir tmp
fi
chmod -R 0777 tmp
chown -R mysql:mysql /var/lib/mysql /var/run/mysqld

service php7.4-fpm start
service nginx start
service mysql start

curl https://zblogphp.local --max-time 1 > /dev/null
if [ $? -ne 0 ]; then
  echo '127.0.0.1 zblogphp.local' >> /etc/hosts;
fi

if [ -f tmp/config.json ]; then
  echo "Found custom config.json"
  cp tmp/config.json .
fi

exec sudo -u local php checker $@
