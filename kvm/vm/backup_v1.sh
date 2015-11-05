#!/bin/bash
#zfs snapshot kvm@backup
#mount -t zfs kvm@backup /var/backups/vm
#umount /var/backups/vm
#zfs list -t snapshot
#zfs destroy kvm@backup

DAY=$(date +%d)
DIR="/var/backups/vm"
BACKUP_DIR=/backup/ovh2
DATE=$(date +%F)
IP=x.x.x.x
USER=test

ssh $USER@$IP "mkdir $BACKUP_DIR/$DATE"

zfs destroy kvm@backup
zfs snapshot kvm@backup
mount -t zfs kvm@backup /var/backups/vm

for variable in `find $DIR -mindepth 1 -type d`
do
#if [ "$variable" != "/var/backups/vm/p2pool" ] && [ "$variable" != "/var/backups/vm/ns2" ];
if [ "$variable" != "/var/backups/vm/p2pool" ];
then
	echo "$variable"
	NAME=$(basename $variable)
	echo $NAME
	nice -n 19 ionice -c3 tar --use-compress-program=pigz -cpf - $variable | ssh $USER@$IP  dd of=$BACKUP_DIR/$DATE/$NAME.tar.gz
fi
done

umount /var/backups/vm
zfs destroy kvm@backup
