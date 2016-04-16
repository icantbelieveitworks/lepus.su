#!/bin/bash
# Перед тем, как отправить данные от VPS клиенту => поменяем пароль
if [ ! -f /root/lepus/tmp/passwd.lock ]; then
	PASSWD=$(cat /root/lepus/tmp/passwd)
	echo "root:$PASSWD" | chpasswd
	echo "1" > /root/lepus/tmp/passwd.lock
	rm /root/lepus/tmp/passwd
fi
