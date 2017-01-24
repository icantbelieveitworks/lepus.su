#!/bin/bash
if [ "$(id -u)" != "0" ]; then
	echo "This script must be run as root" 2>&1
	exit 1
fi

echo 'deb http://ftp.debian.org/debian jessie-backports main' > /etc/apt/sources.list.d/backports.list
echo 'deb http://apt.lepus.su/ jessie main' > /etc/apt/sources.list.d/lepus.list

wget http://apt.lepus.su/lepus.gpg
apt-key add lepus.gpg

apt-get -y update
apt-get -y upgrade

apt-get -y install mysql-server-core-5.5 mysql-common mysql-client-5.5
apt-get -y install bind9 mysql-server-5.5 apache2-mpm-prefork apache2-utils
apt-get -y install mtr htop bwm-ng strace lsof nano fail2ban curl ca-certificates proftpd-basic screen exim4
apt-get -y install php5-cli php5-common php5-curl php5-fpm php5-gd php5-geoip php5-intl php5-json php5-mcrypt php5-memcache php5-mysqlnd php5-readline php5-xsl phpmyadmin
apt-get -y install python-certbot-apache -t jessie-backports

wget -O /etc/mysql/my.cnf https://raw.githubusercontent.com/poiuty/lepus.su/master/cp/conf/mysql/my.cnf
wget -O /etc/cron.d/lepuscp https://raw.githubusercontent.com/poiuty/lepus.su/master/cp/conf/cron.d/lepuscp
wget -O /etc/logrotate.d/lepuscp https://raw.githubusercontent.com/poiuty/lepus.su/master/cp/conf/logrotate.d/lepuscp
wget -O /etc/bind/named.conf.options https://raw.githubusercontent.com/poiuty/lepus.su/master/cp/conf/bind/named.conf.options
wget -O /etc/php5/fpm/pool.d/lepus.conf https://raw.githubusercontent.com/poiuty/lepus.su/master/cp/conf/php5/fpm/pool.d/lepus.conf
wget -O /etc/fail2ban/jail.d/lepuscp.conf https://raw.githubusercontent.com/poiuty/lepus.su/master/cp/conf/fail2ban/jail.d/lepuscp.conf
wget -O /etc/fail2ban/jail.d/proftpd.conf https://raw.githubusercontent.com/poiuty/lepus.su/master/cp/conf/fail2ban/jail.d/proftpd.conf
wget -O /etc/fail2ban/filter.d/lepuscp.conf https://raw.githubusercontent.com/poiuty/lepus.su/master/cp/conf/fail2ban/filter.d/lepuscp.conf
wget -O /etc/apache2/conf-enabled/lepuscp.conf https://raw.githubusercontent.com/poiuty/lepus.su/master/cp/conf/apache2/conf-enabled/lepuscp.conf

dpkg-reconfigure exim4-config

a2enmod ssl proxy_fcgi vhost_alias proxy proxy_fcgi proxy_http rewrite
a2dismod autoindex -f

adduser --no-create-home --home /var/www --gecos "" lepus
usermod -a -G www-data lepus

sed -i -e 's/begin acl/disable_ipv6 = true\nacl_not_smtp = acl_not_smtp\nbegin acl\nacl_not_smtp:\n\tdeny message = Sender rate overlimit - $sender_rate \/ $sender_rate_period\n\tratelimit = 50 \/ 1h \/ leaky\n\taccept/' /etc/exim4/exim4.conf.template
sed -i -e 's/# DefaultRoot/DefaultRoot/' /etc/proftpd/proftpd.conf
sed -i -e 's/post_max_size = 8M/post_max_size = 100M/' /etc/php5/apache2/php.ini
sed -i -e 's/;date.timezone =/date.timezone = "Europe\/Moscow"/' /etc/php5/apache2/php.ini
sed -i -e 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' /etc/php5/apache2/php.ini

mkdir -p /etc/bind/zone
mkdir -p /etc/bind/domain
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

mkdir -p /usr/local/lepuscp
mkdir -p /usr/local/lepuscp/ssl
chmod 700 /usr/local/lepuscp
openssl req -new -newkey rsa:2048 -days 9999 -nodes -x509 -subj /C=RU/ST=Moscow/L=Moscow/O=Lepus/CN=lepuscp -keyout /usr/local/lepuscp/ssl/server.key -out /usr/local/lepuscp/ssl/server.crt

wget -O /usr/local/lepuscp/main.conf https://raw.githubusercontent.com/poiuty/lepus.su/master/cp/main.conf
sesskey="$(date | md5sum | awk '{print $1}')"
sed -i -e 's/7e6dad20cc6a9f1666d6dff91b8ffd90/$sesskey/' /usr/local/lepuscp/main.conf

apt-get install lepusvh

service cron restart
service exim4 restart
service mysql restart
service proftpd restart
service apache2 restart
service fail2ban restart
