```
# apt-get install sshfs
# ssh-keygen -t rsa
# scp ~/.ssh/id_rsa.pub x@192.168.0.1:~/.ssh/authorized_keys
# mkdir /backup

# nano /etc/fstab
x@192.168.0.1:/backup/x  /backup  fuse.sshfs _netdev,reconnect  0  0

# mount -a
```
