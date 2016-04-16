#!/bin/bash
if [ ! -f /root/lepus/tmp/resize.lock ]; then
	apt-get install -y parted
	echo -e "p\nd\nn\np\n1\n\n\np\nw" | fdisk /dev/vda
	partprobe
	resize2fs /dev/vda1
	echo "1" > /root/lepus/tmp/resize.lock
	reboot
fi

# Иногда срабатывает сразу, иногда после reboot
if [ -f /root/lepus/tmp/resize.lock ]; then
	NUM=$(cat /root/lepus/tmp/resize.lock)
	if [ "$NUM" -eq "1" ]; then
		sleep 10
		echo -e "p\nd\nn\np\n1\n\n\np\nw" | fdisk /dev/vda
		partprobe
		resize2fs /dev/vda1
		echo "2" > /root/lepus/tmp/resize.lock
	fi
fi
