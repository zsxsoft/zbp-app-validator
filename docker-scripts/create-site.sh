#!/bin/bash
# @require /data
# @require /www
# @require acme.sh
# @require /data/logs
# @require /data/www
# @require /data/certs

if [ $# -ne 2 ]
then
  echo "Usage: create_site.sh SITENAME URL"
  exit 1
fi

sitename=$1
url=$2
data=/data/www/${sitename}

# User permissions
groupadd ${sitename}
useradd -g ${sitename} ${sitename}
whoami | xargs usermod -a -G ${sitename}

# Logs
mkdir /data/logs/nginx/${sitename}
chown -R www-data:${sitename} /data/logs/nginx/${sitename}
# Website
mkdir ${data}
mkdir -p ${data}/www ${data}/www/blog ${data}/bin ${data}/dev ${data}/tmp ${data}/lib ${data}/etc/ ${data}/home/
mkdir -p ${data}/usr/sbin/ ${data}/usr/share/zoneinfo/ ${data}/var/run/nscd/
mkdir -p ${data}/var/lib/php/sessions
cp -a /dev/zero /dev/urandom /dev/null ${data}/dev/
chmod --reference=/tmp ${data}/tmp/
chmod --reference=/var/lib/php/sessions ${data}/var/lib/php/sessions
chown -R root:root ${data}/
chown -R ${sitename}:${sitename} ${data}/www
cp /etc/resolv.conf /etc/hosts /etc/nsswitch.conf ${data}/etc
cp /etc/ssl/certs/ca-certificates.crt ${data} --parents
cp /lib/x86_64-linux-gnu/{libc.so.6,libdl.so.2,libnss_dns.so.2,libnss_files.so.2,libresolv.so.2}  ${data}/lib/
cp -R /usr/share/zoneinfo ${data}/usr/share
chmod g+r -R ${data}/www
ln -s /data/www/${sitename}/www/ /www/${sitename}

# Cert
mkdir /data/certs/${sitename}
echo "authorityKeyIdentifier=keyid,issuer
basicConstraints=CA:FALSE
keyUsage = digitalSignature, nonRepudiation, keyEncipherment, dataEncipherment
subjectAltName = @alt_names

[alt_names]
DNS.1 = ${url}" > /data/certs/${sitename}/v3.ext
openssl genrsa -out /data/certs/${sitename}/ssl-key.pem 2048
openssl rsa -in /data/certs/${sitename}/ssl-key.pem -out /data/certs/${sitename}/ssl-key-unsecure.pem
openssl req -newkey rsa:2048 -keyout /data/certs/${sitename}/ssl-key-unsecure.pem -out /data/certs/${sitename}/ssl-key.req -nodes -subj "/C=US/ST=Denial/L=Springfield/O=Dis/CN=${url}"

HOME=${data}/home openssl x509 -req -in /data/certs/${sitename}/ssl-key.req -CA /data/certs/mitmproxy-ca-cert.pem -CAkey /data/certs/mitmproxy-ca.pem -CAcreateserial -days 10 -out /data/certs/${sitename}/ssl.pem -extfile /data/certs/${sitename}/v3.ext
cat /data/certs/${sitename}/ssl.pem /data/certs/mitmproxy-ca-cert.pem > /data/certs/${sitename}/ssl-fullchain.pem
mkdir -p ${data}/home/.pki/nssdb
certutil -d ${data}/home/.pki/nssdb -N --empty-password
certutil -d sql:${data}/home/.pki/nssdb -A -t "P,," -n /data/certs/${sitename}/ssl-fullchain.pem -i /data/certs/${sitename}/ssl-fullchain.pem
chown -R ${sitename}:${sitename} ${data}/home

ln -s /data/certs ${data}/home/.mitmproxy

# Conf files
bash -c "echo \"[${sitename}]
user = ${sitename}
group = ${sitename}
listen = /var/run/php72-fpm-${sitename}.sock
listen.owner = www-data
listen.group = www-data
php_admin_value[disable_functions] = exec,passthru,shell_exec,system
php_admin_flag[allow_url_fopen] = off
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
chroot = /data/www/${sitename}
chdir = /www\" > /etc/php/7.2/fpm/pool.d/${sitename}.conf"
bash -c "echo \"server {
    listen 80 default_server;
    listen 443 ssl http2 default_server;
    ssl on;
    ssl_ciphers EECDH+CHACHA20:EECDH+CHACHA20-draft:EECDH+ECDSA+AES128:EECDH+aRSA+AES128:RSA+AES128:EECDH+ECDSA+AES256:EECDH+aRSA+AES256:RSA+AES256:EECDH+ECDSA+3DES:EECDH+aRSA+3DES:RSA+3DES:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
    ssl_session_cache          shared:SSL:50m;
    ssl_session_timeout        1d;
    ssl_session_tickets        on;

    index index.php index.html index.htm;

    root /data/www/${sitename}/www;
    index index.php index.html index.htm;
    access_log /data/logs/nginx/${sitename}/access.log;
    error_log /data/logs/nginx/${sitename}/error.log;

    location / {
        try_files \\\$uri \\\$uri/ =404;
    }

    # Custom Tag Here


    location ~ \.php\\\$ {
        try_files \\\$uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)\\\$;
        fastcgi_pass unix:/var/run/php72-fpm-${sitename}.sock;
        fastcgi_index index.php;
        fastcgi_param DOCUMENT_ROOT  /www;
        fastcgi_param SCRIPT_FILENAME  /www\\\$fastcgi_script_name;
#        fastcgi_param SCRIPT_FILENAME \\\$document_root\\\$fastcgi_script_name;
        include fastcgi_params;
    }

    ssl_certificate      /data/certs/${sitename}/ssl-fullchain.pem;
    ssl_certificate_key  /data/certs/${sitename}/ssl-key-unsecure.pem;
    ssl_trusted_certificate    /data/certs/mitmproxy-ca.pem;
    ssl_dhparam          /data/certs/mitmproxy-dhparam.pem;


    include gzip.conf;

}\" > /etc/nginx/sites-available/${sitename}"
ln -s /etc/nginx/sites-available/${sitename} /etc/nginx/sites-enabled/${sitename}

# Certificate
#service php7.1-fpm restart
#service nginx restart
