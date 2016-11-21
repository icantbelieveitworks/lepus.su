#!/bin/bash
# cron: 01 12 * * * root cd /root/lepus && ./hdd.bash
# apt-get install smartmontools hdparm curl -y

function lepusHelpMe {
	curl -4 https://lepus.su/public/api/hdd.php
	exit 1
}

MDSTAT=$(cat /proc/mdstat | grep _)
LOG1=$(cat /var/log/messages.1 | grep "EH complete")
LOG2=$(cat /var/log/syslog | grep "EH complete")

if [[ !  -z  "$LOG1"  ]] || [[ !  -z  "$LOG2"  ]] || [[ !  -z  "$MDSTAT"  ]] ; then
	lepusHelpMe
fi

re='^[a-z/]+$'
ls /dev/sd*| while read DISK; do
	if [[ $DISK =~ $re ]] ; then
		SMARTCTL=$(smartctl --all $DISK | grep "No Errors Logged")
		if [[  -z  "$SMARTCTL"  ]] ; then
			lepusHelpMe
		fi
		
		SMARTCTL=$(smartctl --all $DISK | grep Reallocated_Sector_Ct | awk '{print $10}')
		if [ "$SMARTCTL" > 5 ] ; then
			lepusHelpMe
		fi
	fi
done
