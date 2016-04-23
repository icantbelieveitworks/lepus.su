<?php
// id, ip, passwd, memory, cpus, diskspace, command
$json = json_decode(file_get_contents('https://lepus.su/public/api/create.php'), true);
if(empty(intval($json['id'])) || empty($json['command'])) die('empty');
var_dump($json);

switch($json['command']){
	default: echo "wrong command"; break;
	case 'createServ'
		if(empty($json['ip']) || empty($json['passwd']) || empty($json['memory']) || empty($json['cpus']) || empty($json['diskspace'])) die('empty');
		shell_exec("vzctl create {$json['id']} --ostemplate debian-8 --config main --ipadd {$json['ip']}");
		shell_exec("vzctl start {$json['id']}");
		shell_exec("vzctl set {$json['id']} --userpasswd lepus:{$json['passwd']}");
	break;
}
