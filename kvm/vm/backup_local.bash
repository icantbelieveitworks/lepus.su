#/bin/bash
# zfs create -o mountpoint=/backup sata/backup
# zfs set compression=off sata/backup
# zfs set primarycache=metadata sata
# zfs set secondarycache=metadata sata

BACKUP_DIR=/backup
DATE=$(date +%F)

declare -A array=( 
 [NAME]=1  [sata]=1  [ssd]=1  [sata/backup]=1  [sata/centos]=1  [sata/debian]=1  [sata/ubuntu]=1
)

if [ -f /tmp/backup ]; then
    echo "Already running!"
    exit
fi

echo '' > /tmp/backup
mkdir $BACKUP_DIR/$DATE

zfs list | while read line
do
	IFS=" " set -- $line
	if [ ! -n "${array[$1]}" ];
	then
		echo "$1 ${1##*/}"
		zfs destroy $1@backup
		zfs snapshot $1@backup
		zfs send $1@backup | nice -n 19 pigz > $BACKUP_DIR/$DATE/${1##*/}.gz
		zfs destroy $1@backup
	fi
done

cp -r /etc/libvirt/qemu/ $BACKUP_DIR/$DATE/
echo 'done' > $BACKUP_DIR/$DATE/done.txt
find $BACKUP_DIR/* -mtime +5 -delete
find $BACKUP_DIR/ -type d -empty -delete
rm /tmp/backup
