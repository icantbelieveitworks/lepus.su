#!/bin/bash
mkdir -p /root/lepuscp/deb/lepusvh/DEBIAN
mkdir -p /root/lepuscp/deb/lepusvh/etc
mkdir -p /root/lepuscp/deb/lepusvh/etc/php5
mkdir -p /root/lepuscp/deb/lepusvh/etc/php5/mods-available

mkdir -p /root/lepuscp/deb/lepusvh/usr
mkdir -p /root/lepuscp/deb/lepusvh/usr/local
mkdir -p /root/lepuscp/deb/lepusvh/usr/local/lepuscp
mkdir -p /root/lepuscp/deb/lepusvh/usr/local/lepuscp/sess
mkdir -p /root/lepuscp/deb/lepusvh/usr/local/lepuscp/logs

mkdir -p /root/lepuscp/deb/lepusvh/usr/lib
mkdir -p /root/lepuscp/deb/lepusvh/usr/lib/php5
mkdir -p /root/lepuscp/deb/lepusvh/usr/lib/php5/20131226

cp -rp /root/lepuscp/conf/php5/20131226/* /root/lepuscp/deb/lepusvh/usr/lib/php5/20131226
cp -rp /root/lepuscp/conf/php5/mods-available/* /root/lepuscp/deb/lepusvh/etc/php5/mods-available

cp -rp /root/lepuscp/php /root/lepuscp/deb/lepusvh/usr/local/lepuscp
cp -rp /root/lepuscp/files /root/lepuscp/deb/lepusvh/usr/local/lepuscp
cp -rp /root/lepuscp/lepus /root/lepuscp/deb/lepusvh/usr/local/lepuscp
cp -rp /root/lepuscp/start.bash /root/lepuscp/deb/lepusvh/usr/local/lepuscp

chmod 700 /root/lepuscp/deb/lepusvh/usr/local/lepuscp

fakeroot dpkg-deb --build lepusvh
mv lepusvh.deb lepusvh_1.0-x.deb
