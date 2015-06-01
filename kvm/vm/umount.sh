#!/bin/bash
umount /mnt
vgchange -an debian-vg
kpartx -dv /dev/loop0
losetup -d /dev/loop0
vgscan
