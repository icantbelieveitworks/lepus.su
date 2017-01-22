#!/bin/bash
if [ "$(id -u)" != "0" ]; then
	echo "This script must be run as root" 2>&1
	exit 1
fi

echo 'deb http://ftp.debian.org/debian jessie-backports main' > /etc/apt/sources.list.d/backports.list

apt-get -y update
apt-get -y upgrade

apt-get -y install mysql-server-core-5.5 mysql-common mysql-client-5.5
apt-get -y install bind9 mysql-server-5.5 apache2-mpm-prefork apache2-utils
apt-get -y install mtr htop bwm-ng strace lsof nano fail2ban curl ca-certificates proftpd-basic screen
apt-get -y install php5-cli php5-common php5-curl php5-fpm php5-gd php5-geoip php5-intl php5-json php5-mcrypt php5-memcache php5-mysqlnd php5-readline php5-xsl phpmyadmin
apt-get -y install python-certbot-apache -t jessie-backports

sed -i -e 's/# DefaultRoot/DefaultRoot/' /etc/proftpd/proftpd.conf
service proftpd restart

a2enmod ssl proxy_fcgi vhost_alias proxy proxy_fcgi proxy_http rewrite
a2dismod autoindex -f
service apache2 restart

adduser --no-create-home --home /var/www --gecos "" lepus
usermod -a -G www-data lepus

mkdir -p /etc/apache2/ssl/
openssl req -new -newkey rsa:2048 -days 9999 -nodes -x509 -subj /C=RU/ST=Moscow/L=Moscow/O=Lepus/CN=lepuscp -keyout /etc/apache2/ssl/server.key -out /etc/apache2/ssl/server.crt

mkdir -p /var/www
mkdir -p /var/www/logs
mkdir -p /var/www/public
chown -R lepus:www-data /var/www

echo '' > /var/www/logs/.keep
chattr +i /var/www/logs/.keep
echo '' > /var/www/public/.keep
chattr +i /var/www/public/.keep

# install deb

service cron restart
service apache2 restart
service fail2ban restart

