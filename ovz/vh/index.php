<?php
// id, ip, passwd, memory, cpus, diskspace, command
//if(empty(intval($_GET['id'])) || empty($_GET['command']) || empty($_GET['key'])) die("error 1");
//if($_GET['key'] != 'xxx') die("error 2");

$id = intval(@$_GET['id']);
if(empty($id) || empty($_GET['command'])) die('empty');

switch($_GET['command']){
	default:
		$i = shell_exec("sudo vzctl status $id");
		$arr = explode(" ", $i);
		echo $arr[4];
	break;
	case 'changeTariff':
		if(empty($_GET['memory']) || empty($_GET['cpus']) || empty($_GET['diskspace'])) die('empty');
		shell_exec("sudo vzctl set $id --ram {$_GET['memory']} --cpus {$_GET['cpus']}  --cpulimit {$_GET['cpulimit']} --diskspace {$_GET['diskspace']} --save");
	break;
	case 'startServ':
		shell_exec("sudo vzctl start $id");
	break;
	case 'stopServ':
		shell_exec("sudo vzctl stop $id");
	break;
	case 'changePasswd':
		if(empty($_GET['passwd'])) die('empty');
		shell_exec("sudo vzctl set $id --userpasswd lepus:{$_GET['passwd']}");
	break;
}
