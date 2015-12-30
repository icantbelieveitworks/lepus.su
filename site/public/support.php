<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');
switch($_POST['do']){
	default: echo "wrong do"; break;
	case 'new': support_msg($user['id'], support_create($user['id'])); break;
	case 'send_msg':
		$tmpData = support_msg($user['id'], $_POST["tid"]);
		$tmpData = lepus_get_supportMsg($tmpData['tid'], $user['id'], $user['data']['access'], $tmpData['msgID']);
		echo $tmpData['msg'];
	break;
	case 'update_msg':
		$tmpData = lepus_get_supportMsg($_POST["tid"], $user['id'], $user['data']['access'], 0, $_POST["count"]);
		if(!is_array($tmpData)) echo "no_mes";
			else echo $tmpData['msg'];
	break;
}
