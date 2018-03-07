Установим пакеты.

```
yum install nginx munin munin-node spawn-fcgi perl-File-ReadBackwards
```

Запустим и добавим в автозагрузку.

```
systemctl start munin-node
systemctl start nginx
systemctl enable munin-node
systemctl enable nginx
```

Для nginx создадим виртуальный хост.

```
# nano /etc/nginx/conf.d/munin.conf
server { # munin
	listen 85;
	keepalive_timeout 30;
	root /var/www/html/munin;
	location /munin/static/ {
		alias /etc/munin/static/;
	}
	location ^~ /munin-cgi/munin-cgi-graph/ {
		access_log off;
		fastcgi_split_path_info ^(/munin-cgi/munin-cgi-graph)(.*);
		fastcgi_param PATH_INFO $fastcgi_path_info;
		fastcgi_pass unix:/var/run/munin/fcgi-graph.sock;
		include fastcgi_params;
	}
}
```

Запустим spawn-fcgi

```
# spawn-fcgi -s /var/run/munin/fcgi-graph.sock -U nginx -u nginx -g munin /var/www/cgi-bin/munin-cgi-graph
spawn-fcgi: child spawned successfully: PID: 1036
```

Проверим, что он действительно работает.

```
# ps uax | grep munin-cgi-graph
nginx     1036  0.8  0.1 288932 21416 ?        Ss   09:21   0:00 /usr/bin/perl -T /var/www/cgi-bin/munin-cgi-graph
root      1442  0.0  0.0 112664   976 pts/0    S+   09:22   0:00 grep --color=auto munin-cgi-graph
```

Добавим в автозапгрузку spawn-fcgi

```
# chmod +x /etc/rc.local
# nano /etc/rc.local
spawn-fcgi -s /var/run/munin/fcgi-graph.sock -U nginx -u nginx -g munin /var/www/cgi-bin/munin-cgi-graph
```

Перезагрузим сервисы.

```
systemctl restart munin-node
systemctl restart nginx
```
