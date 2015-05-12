#!/bin/bash
# Перед тем, как отправить данные от VPS клиенту => увеличим lvm раздел контейнера
if [ ! -f /root/lepus/tmp/resize.lock ]; then
	apt-get install -y parted
	echo -e "p\nd\n5\nd\n2\nn\ne\n2\n\n\nn\nl\n\n\nt\n5\n8e\nw" | fdisk /dev/vda
	partprobe
	pvresize /dev/vda5
	lvextend -l +100%FREE /dev/debian-vg/root
	resize2fs /dev/debian-vg/root
	echo "1" > /root/lepus/tmp/resize.lock
fi
