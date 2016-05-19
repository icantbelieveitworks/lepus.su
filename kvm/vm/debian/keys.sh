#!/bin/bash
if [ ! -f /root/lepus/tmp/keys.lock ]; then
	rm /etc/ssh/ssh_host_*
	if [ -f /etc/debian_version ]; then
		dpkg-reconfigure openssh-server
	fi
	if [ -f /etc/centos-release ]; then
		ssh-keygen -N "" -t rsa -f /etc/ssh/ssh_host_rsa_key
		ssh-keygen -N "" -t dsa -f /etc/ssh/ssh_host_dsa_key
		ssh-keygen -N "" -t ecdsa -f /etc/ssh/ssh_host_ecdsa_key
		ssh-keygen -N "" -t ed25519 -f /etc/ssh/ssh_host_ed25519_key
		service sshd restart
	fi
	echo "1" > /root/lepus/tmp/keys.lock
fi
