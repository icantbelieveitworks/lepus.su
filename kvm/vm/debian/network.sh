#!/bin/bash
# Перед тем, как отправить данные от VPS клиенту => настроим сеть
if [ ! -f /root/lepus/tmp/network.lock ]; then
	ADDRESS=$(cat /root/lepus/tmp/network | head -1 | tail -n 1)
	SERVER=$(cat /root/lepus/tmp/network | head -2 | tail -n 1)
	sed -i -e "s/aaaa/$ADDRESS/" /etc/network/interfaces
	sed -i -e "s/bbbb/$SERVER/" /etc/network/interfaces
	/etc/init.d/networking restart
	echo "1" > /root/lepus/tmp/network.lock
fi
