

```
   KVM NODE                                   LEPUS NODE  
vnc websoket   [localhost]                 nginx https [0.0.0.0]
      |                                             |
      |                                             |
      |                                             |
nginx wss proxy [0.0.0.0] ----- SSL ----- novnc (html/ js) ----- SSL ----- CLIENT (web browser)

```

```
# virsh shutdown kvm999
# nano /etc/libvirt/qemu/kvm999.xml
<graphics type='vnc' autoport='yes' websocket='-1' listen='127.0.0.1' keymap='en-us' passwd='secret'>
   <listen type='address' address='127.0.0.1'/>
</graphics>

# virsh define /etc/libvirt/qemu/kvm999.xml
# virsh start kvm999
```

```
# nano /etc/nginx/nginx.conf
map $arg_port $proxyport { # filter ports
		default 5700;
		5701 5701;
		5702 5702;
}

```

```
# nano /etc/nginx/sites-available/default
location /ws {		
	proxy_pass http://127.0.0.1:$proxyport/;
	proxy_http_version 1.1;
	proxy_set_header Upgrade $http_upgrade;
	proxy_set_header Connection "upgrade";
	proxy_read_timeout 86400;
}
```

В настройках подключения websocket => указываем порт vm.
```
Host: sparrow.lepus.su
Port: 443
Path: ws/?port=5700
```

<img src="https://img.poiuty.com/img/f8/41a699b1f6c7b2a3cf82872aac6477f8.png">
