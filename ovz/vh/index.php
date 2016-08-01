<?php
// id, ip, passwd, memory, cpus, diskspace, command

$id = intval(@$_GET['id']);
if(empty($id) || empty($_GET['command'])) die('empty');
if($_GET['key'] != 'xxxx') die('error 2');

switch($_GET['command']){
	case 'changeTariff':
		$memory = intval(@$_GET['memory']);  $cpus = intval(@$_GET['cpus']); $diskspace = intval(@$_GET['diskspace']);
		if(empty($memory) || empty($cpus) || empty($diskspace)) die('empty');
		$cpu = round($_GET['cpus']/(2200/100));
		shell_exec('sudo vzctl set '.escapeshellarg($id).' --ram '.escapeshellarg($memory).' --cpus 1 --cpulimit '.escapeshellarg($cpu).' --diskspace '.escapeshellarg($diskspace).' --save');
	break;
	case 'startServer':
		shell_exec('sudo vzctl start '.escapeshellarg($id));
	break;
	case 'stopServer':
		shell_exec('sudo vzctl stop '.escapeshellarg($id));
	break;
	case 'restartServer':
		shell_exec('sudo vzctl restart '.escapeshellarg($id));
	break;
	case 'changePasswd':
		if(empty($_GET['passwd'])) die('empty');
		shell_exec('sudo vzctl set '.escapeshellarg($id).' --userpasswd lepus: '.escapeshellarg($_GET['passwd']));
	break;
}

$i = shell_exec('sudo vzctl status '.escapeshellarg($id));
$arr = explode(" ", $i);
echo trim($arr[4]);
