Debian 9, сеть через `systemd-network`, необходимо сделать bridge.

```
# nano /etc/systemd/network/br0.netdev
[NetDev]
Name=br0
Kind=bridge
MACAddress=0c:c4:7a:d9:c8:b8
```

```
# nano /etc/systemd/network/uplink.network
[Match]
Name=eno1
MACAddress=0c:c4:7a:d9:c8:b8
 
[Network]
Bridge=br0
```

```
# nano /etc/systemd/network/br0.network

# This file sets the IP configuration of the primary (public) network device.
# You can also see this as "OSI Layer 3" config.
# It was created by the OVH installer, please be careful with modifications.
# Documentation: man systemd.network or https://www.freedesktop.org/software/systemd/man/systemd.network.html

[Match]
Name=br0

[Network]
Description=network interface on public network, with default route
DHCP=no
Address=149.202.222.76/24
Gateway=149.202.222.254
#IPv6AcceptRA=false
NTP=ntp.ovh.net
DNS=127.0.0.1
DNS=213.186.33.99
DNS=2001:41d0:3:163::1
Gateway=2001:41d0:1000:21ff:ff:ff:ff:ff

[Address]
Address=2001:41d0:1000:214c::/64

[Route]
Destination=2001:41d0:1000:21ff:ff:ff:ff:ff
Scope=link
```
