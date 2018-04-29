Debian 9, сеть через `systemd-network`, необходимо сделать bridge.

```
# nano /etc/systemd/network/br0.netdev
[NetDev]
Name=br0
Kind=bridge
MACAddress=0c:c4:7a:d9:7b:cc
```

```
# nano /etc/systemd/network/uplink.network
[Match]
Name=eno1
MACAddress=0c:c4:7a:d9:7b:cc
 
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
Address=37.187.155.229/24
Gateway=37.187.155.254
#IPv6AcceptRA=false
NTP=ntp.ovh.net
DNS=127.0.0.1
DNS=213.186.33.99
DNS=2001:41d0:3:163::1
Gateway=2001:41d0:000a:5bff:ff:ff:ff:ff

[Address]
Address=2001:41d0:000a:5be5::/64

[Route]
Destination=2001:41d0:000a:5bff:ff:ff:ff:ff
Scope=link
```

```
# nano /etc/systemd/network/50-public-interface.link
# This file configures the relation between network device and device name.
# You can also see this as "OSI Layer 2" config.
# It was created by the OVH installer, please be careful with modifications.
# Documentation: man systemd.link or https://www.freedesktop.org/software/systemd/man/systemd.link.html

[Match]
MACAddress=0c:c4:7a:d9:7b:cc

[Link]
Description=network interface on public network, with default route
MACAddressPolicy=persistent
NamePolicy=kernel database onboard slot path mac
#Name=eth0	# name under which this interface is known under OVH rescue system
#Name=eno1	# name under which this interface is probably known by systemd
```
```
# nano /etc/sysctl.conf
# Allow forward
net.ipv4.ip_forward=1
net.ipv6.conf.all.forwarding=1

# http://wiki.libvirt.org/page/Net.bridge.bridge-nf-call_and_sysctl.conf
net.bridge.bridge-nf-call-ip6tables = 0
net.bridge.bridge-nf-call-iptables = 0
net.bridge.bridge-nf-call-arptables = 0

# sysctl -p 
```

<hr/>

Внутри vm (debian 9).
```
# nano /etc/network/interfaces

auto lo ens3
iface lo inet loopback
iface ens3 inet static
	address 92.222.108.232
	netmask 255.255.255.255
	broadcast 92.222.108.232
	post-up ip route add 37.187.155.254 dev ens3
	post-up ip route add default via 37.187.155.254
	pre-down ip route del 37.187.155.254 dev ens3
	pre-down ip route del default via 37.187.155.254

iface ens3 inet6 static
	address 2001:41d0:a:5be5::1
	netmask 64
	post-up /sbin/ip -f inet6 route add 2001:41d0:a:5bff:ff:ff:ff:ff dev ens3
	post-up /sbin/ip -f inet6 route add default gw 2001:41d0:a:5bff:ff:ff:ff:ff
	pre-down /sbin/ip -f inet6 route del 2001:41d0:a:5bff:ff:ff:ff:ff dev ens3
	pre-down /sbin/ip -f inet6 route del default gw 2001:41d0:a:5bff:ff:ff:ff:ff
```
