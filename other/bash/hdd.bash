#!/bin/bash
# apt-get install smartmontools hdparm curl -y
# yum install smartmontools hdparm curl -y
# SHELL=/bin/sh
# PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
# MAILTO=""
# HOME=/
# 01 12 * * * root cd /root/lepus && ./hdd.bash

lepusHelpMe() {
	curl -4 -X POST -F "info=$INFO" https://lepus.su/public/api/hdd.php
	exit 1
}

MDSTAT=$(cat /proc/mdstat)
MESATA=$(cat /var/log/messages | grep "ATA")
SYSATA=$(cat /var/log/syslog | grep "ATA")
ZFSLIST=$(zpool list)
ZFSRAID=$(zpool status)

re='^[a-z/]+$'
while read -r DISK; do
	if [[ $DISK =~ $re ]] ; then
		SerialNo=$(hdparm -i $DISK | grep SerialNo | awk '{print $NF}')
		SerialNo=${SerialNo//=/ }
		LINE="\n================ $DISK $SerialNo ================\n\n"
		SMART="$SMART$LINE$LINE2$(smartctl -A $DISK)\n"
	fi
done < <(ls /dev/sd* )

INFO=$(echo -e "$MDSTAT\n\n $SMART $MESATA\n\n $SYSATA\n\n $ZFSRAID\n\n $ZFSLIST\n\n")

LOG1=$(cat /proc/mdstat | grep _)
LOG2=$(cat /var/log/messages | grep "EH complete")
LOG3=$(cat /var/log/syslog | grep "EH complete")

if [[ !  -z  "$LOG1"  ]] || [[ !  -z  "$LOG2"  ]] || [[ !  -z  "$LOG3"  ]] ; then
	lepusHelpMe
	echo "log or mdstat"
	exit 1
fi

ls /dev/sd*| while read DISK; do
	if [[ $DISK =~ $re ]] ; then
		SMARTCTL=$(smartctl --all $DISK | grep "SMART Error Log not supported")
		if [[  -z  "$SMARTCTL"  ]] ; then
			SMARTCTL=$(smartctl --all $DISK | grep "No Errors Logged")
			if [[  -z  "$SMARTCTL"  ]] ; then
				echo "SMART $DISK"
				lepusHelpMe
			fi
		fi
		
		SMARTCTL=$(smartctl --all $DISK | grep Reallocated_Sector_Ct | awk '{print $10}')
		if [[ "$SMARTCTL" > "50" ]] ; then
			echo "Reallocated $DISK"
			lepusHelpMe
		fi
	fi
done

# thx https://gist.github.com/petervanderdoes/bd6660302404ed5b094d
zfs_state=$(zpool status | egrep -i '(DEGRADED|FAULTED|OFFLINE|UNAVAIL|REMOVED|FAIL|DESTROYED|corrupt|cannot|unrecover)')
if [ "${condition}" ]; then
	echo "ZFS $zfs_state"
	lepusHelpMe
fi

errors=$(zpool status | grep ONLINE | grep -v state | awk '{print $3 $4 $5}' | grep -v 000)
if [ "${errors}" ]; then
	echo "ZFS errors $errors"
	lepusHelpMe
fi

capacity=$(zpool list -H -o capacity)
for line in ${capacity//%/}
	do
	if [ $line -ge 90 ]; then
		echo "ZFS capacity $line"
		lepusHelpMe
	fi
done
