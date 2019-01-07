#!/bin/bash
(echo '--gid-owner' ; id -g local;) | tr "\n" " " | xargs iptables -t nat -A OUTPUT -p tcp ! -s 127.0.0.1 -j DNAT --to-destination 127.0.0.1:8080 -m owner
service php7.2-fpm start && service nginx start && service mysql start
sysctl -w net.ipv4.ip_forward=1
cd /zbp-app-validator
exec sudo -u local php checker $@
