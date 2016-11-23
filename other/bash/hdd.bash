#!/bin/bash
# apt-get install smartmontools hdparm curl -y
# SHELL=/bin/sh
# PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
# MAILTO=""
# HOME=/
# 01 12 * * * root cd /root/lepus && ./hdd.bash

lepusHelpMe() {
	curl -4 https://lepus.su/public/api/hdd.php
	exit 1
}

MDSTAT=$(cat /proc/mdstat | grep _)
LOG1=$(cat /var/log/messages | grep "EH complete")
LOG2=$(cat /var/log/syslog | grep "EH complete")

if [[ !  -z  "$LOG1"  ]] || [[ !  -z  "$LOG2"  ]] || [[ !  -z  "$MDSTAT"  ]] ; then
	lepusHelpMe
	echo "log or mdstat"
	exit 1
fi

re='^[a-z/]+$'
ls /dev/sd*| while read DISK; do
	if [[ $DISK =~ $re ]] ; then
		SMARTCTL=$(smartctl --all $DISK | grep "No Errors Logged")
		if [[  -z  "$SMARTCTL"  ]] ; then
			echo "SMART $DISK"
			lepusHelpMe
		fi
		
		SMARTCTL=$(smartctl --all $DISK | grep Reallocated_Sector_Ct | awk '{print $10}')
		if [[ "$SMARTCTL" > "50" ]] ; then
			echo "Reallocated $DISK "
			lepusHelpMe
		fi
	fi
done
