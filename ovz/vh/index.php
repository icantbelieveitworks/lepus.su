<?php
// id, ip, passwd, memory, cpus, diskspace, command

$id = intval(@$_GET['id']);
if(empty($id) || empty($_GET['command'])) die('empty');
if($_GET['key'] != 'xxxx') die('error 2');

switch($_GET['command']){
	case 'changeTariff':
		if(empty($_GET['memory']) || empty($_GET['cpus']) || empty($_GET['diskspace'])) die('empty');
		$cpu = round($_GET['cpus']/(2200/100));
		shell_exec("sudo vzctl set $id --ram {$_GET['memory']} --cpus 1  --cpulimit $cpu --diskspace {$_GET['diskspace']} --save");
	break;
	case 'startServ':
		shell_exec("sudo vzctl start $id");
	break;
	case 'stopServ':
		shell_exec("sudo vzctl stop $id");
	break;
	case 'restartServer':
		shell_exec("sudo vzctl restart $id");
	break;
	case 'changePasswd':
		if(empty($_GET['passwd'])) die('empty');
		shell_exec("sudo vzctl set $id --userpasswd lepus:{$_GET['passwd']}");
	break;
}

$i = shell_exec("sudo vzctl status $id");
$arr = explode(" ", $i);
echo trim($arr[4]);
