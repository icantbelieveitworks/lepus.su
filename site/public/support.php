<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');
if(!is_login()) die("no_login");
switch(@$_POST['do']){
	default:
		$tmpData = lepus_get_supportList($user['id'], $user['data']['access']);
	break;
	case 'new':
		if($user['data']['access'] > 1 && $_POST['user'] != 'no')
			$tmpData = error(support_create(intval($_POST['user']), $_POST['title'], $user['data']['access']));
		else
			$tmpData = error(support_create($user['id'], $_POST['title'], $user['data']['access']));
		if($tmpData['err'] == 'OK')
			$tmpData = error(support_msg($user['id'], $tmpData['mes'], $user['data']['access'], 1));
	break;
	case 'send_msg':
		$tmpData = error(support_msg($user['id'], $_POST['tid'], $user['data']['access']));
		if($tmpData['err'] == 'OK')
			$tmpData = error(lepus_get_supportMsg($tmpData['mes']['tid'], $user['id'], $user['data']['access'], $tmpData['mes']['msgID']));
	break;
	case 'update_msg':
		$tmpData = error(lepus_get_supportMsg($_POST['tid'], $user['id'], $user['data']['access'], 0, $_POST['count']));
	break;
    
}
echo json_encode($tmpData);
