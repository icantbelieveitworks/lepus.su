#/bin/bash

BACKUP_DIR=/backup/test/vm
DATE=$(date +%F)
IP=x.x.x.x
USER=zzz

ssh $USER@$IP "mkdir $BACKUP_DIR/$DATE"

zfs list | while read line
do
IFS=" " set -- $line
if [ "$1" != "NAME" ] && [ "$1" != "sata" ] && [ "$1" != "ssd" ];
then
	echo "$1 ${1##*/}"
	zfs destroy $1@backup
	zfs snapshot $1@backup
	zfs send $1@backup | nice -n 19 pigz | ssh $USER@$IP dd of=$BACKUP_DIR/$DATE/${1##*/}.gz
	zfs destroy $1@backup
fi
done
