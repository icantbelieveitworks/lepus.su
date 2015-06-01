#!/bin/bash
losetup /dev/loop0 /vm/kvm/1/root.hdd
kpartx -a /dev/loop0
vgscan
vgchange -ay debian-vg
mount /dev/debian-vg/root /mnt
