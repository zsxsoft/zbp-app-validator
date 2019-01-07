#!/bin/bash
service mysql stop
chown -R mysql:mysql /var/lib/mysql /var/run/mysqld
echo 'slow-query-log-file=/var/log/mysql/mysql-slow.log' >> /etc/mysql/mysql.conf.d/mysqld.cnf
service mysql start

mysql -u root --password=rootpassword --execute="create database userdb;"
mysql -u root --password=rootpassword --execute="CREATE USER 'user'@'localhost' IDENTIFIED BY 'userpassword';"
mysql -u root --password=rootpassword --execute="grant all privileges on userdb.* to user@localhost ;"
mysql -u user --password=userpassword -Duserdb < /zbp-app-validator/docker-scripts/data.sql
bash -c "echo \"gzip on;
gzip_disable \\\"msie6\\\";
gzip_vary on;
gzip_proxied any;
gzip_comp_level 6;
gzip_buffers 16 8k;
gzip_http_version 1.1;
gzip_min_length 256;
gzip_types text/plain text/css application/json application/x-javascript text/xml application/xml application/xml+rss text/javascript application/vnd.ms-fontobject application/x-font-ttf font/opentype image/svg+xml image/x-icon;
\" > /etc/nginx/gzip.conf"
