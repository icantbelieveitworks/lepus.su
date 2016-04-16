#!/bin/bash
if [ ! -f /root/lepus/tmp/keys.lock ]; then
	rm /etc/ssh/ssh_host_*
	dpkg-reconfigure openssh-server
	echo "1" > /root/lepus/tmp/keys.lock
fi
