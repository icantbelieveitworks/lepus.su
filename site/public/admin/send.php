<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');
if(!is_login()) die("no_login");
if($user['data']['access'] < 2)
	 $tmpData = error('no_access');
else
	$tmpData = error(lepus_admin_send_emails(htmlentities($_POST['title'], ENT_QUOTES, 'UTF-8'), htmlentities($_POST['text'], ENT_QUOTES, 'UTF-8'), intval($_POST['server'])));
echo json_encode($tmpData);
