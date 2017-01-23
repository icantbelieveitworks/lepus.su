#!/bin/bash
mkdir -p /root/lepuscp/deb/lepusvh/DEBIAN
mkdir -p /root/lepuscp/deb/lepusvh/etc
mkdir -p /root/lepuscp/deb/lepusvh/etc/bind
mkdir -p /root/lepuscp/deb/lepusvh/etc/bind/zone
mkdir -p /root/lepuscp/deb/lepusvh/etc/bind/domain
mkdir -p /root/lepuscp/deb/lepusvh/etc/cron.d
mkdir -p /root/lepuscp/deb/lepusvh/etc/logrotate.d
mkdir -p /root/lepuscp/deb/lepusvh/etc/fail2ban
mkdir -p /root/lepuscp/deb/lepusvh/etc/fail2ban/jail.d
mkdir -p /root/lepuscp/deb/lepusvh/etc/fail2ban/filter.d

mkdir -p /root/lepuscp/deb/lepusvh/usr
mkdir -p /root/lepuscp/deb/lepusvh/usr/local
mkdir -p /root/lepuscp/deb/lepusvh/usr/local/lepuscp
mkdir -p /root/lepuscp/deb/lepusvh/usr/local/lepuscp/ssl
mkdir -p /root/lepuscp/deb/lepusvh/usr/local/lepuscp/sess
mkdir -p /root/lepuscp/deb/lepusvh/usr/local/lepuscp/logs

mkdir -p /root/lepuscp/deb/lepusvh/etc/proftpd
mkdir -p /root/lepuscp/deb/lepusvh/etc/apache2
mkdir -p /root/lepuscp/deb/lepusvh/etc/apache2/conf-enabled

mkdir -p /root/lepuscp/deb/lepusvh/etc/php5
mkdir -p /root/lepuscp/deb/lepusvh/etc/php5/fpm
mkdir -p /root/lepuscp/deb/lepusvh/etc/php5/fpm/pool.d
mkdir -p /root/lepuscp/deb/lepusvh/etc/php5/mods-available

mkdir -p /root/lepuscp/deb/lepusvh/usr/lib
mkdir -p /root/lepuscp/deb/lepusvh/usr/lib/php5
mkdir -p /root/lepuscp/deb/lepusvh/usr/lib/php5/20131226

cp -rp /root/lepuscp/conf/bind/* /root/lepuscp/deb/lepusvh/etc/bind
cp -rp /root/lepuscp/conf/php5/fpm/* /root/lepuscp/deb/lepusvh/etc/php5/fpm
cp -rp /root/lepuscp/conf/cron.d/* /root/lepuscp/deb/lepusvh/etc/cron.d
cp -rp /root/lepuscp/conf/proftpd/* /root/lepuscp/deb/lepusvh/etc/proftpd
cp -rp /root/lepuscp/conf/apache2/* /root/lepuscp/deb/lepusvh/etc/apache2
cp -rp /root/lepuscp/conf/fail2ban/* /root/lepuscp/deb/lepusvh/etc/fail2ban
cp -rp /root/lepuscp/conf/logrotate.d/* /root/lepuscp/deb/lepusvh/etc/logrotate.d

cp -rp /root/lepuscp/conf/php5/20131226/* /root/lepuscp/deb/lepusvh/usr/lib/php5/20131226
cp -rp /root/lepuscp/conf/php5/mods-available/* /root/lepuscp/deb/lepusvh/etc/php5/mods-available

cp -rp /root/lepuscp/php /root/lepuscp/deb/lepusvh/usr/local/lepuscp
cp -rp /root/lepuscp/files /root/lepuscp/deb/lepusvh/usr/local/lepuscp
cp -rp /root/lepuscp/lepus /root/lepuscp/deb/lepusvh/usr/local/lepuscp
cp -rp /root/lepuscp/main.conf /root/lepuscp/deb/lepusvh/usr/local/lepuscp
cp -rp /root/lepuscp/start.bash /root/lepuscp/deb/lepusvh/usr/local/lepuscp

chmod 700 /root/lepuscp/deb/lepusvh/usr/local/lepuscp

fakeroot dpkg-deb --build lepusvh
mv lepusvh.deb lepusvh_1.0-x.deb
