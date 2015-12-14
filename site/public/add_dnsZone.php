<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');

$tmpPOST = ['name', 'type', 'content', 'prio', 'domain_id'];
foreach($tmpPOST as $val){
	if(!isset($_POST[$val])) die("Empty POST value");
	if($val == 'name'){
		$_POST[$val] = idn_to_ascii(mb_strtolower($_POST[$val]));
	}
	if($val == 'type' || $val == 'domain_id'){
		if(empty($_POST[$val])) die("Empty value $val");
	}
	if($val == 'domain_id'){
		if(!ctype_digit($_POST[$val])) die("only_num");
	}
	if($val != 'domain_id'){
		$tmpTest = lepus_dnsValid($val, $_POST[$val]);
		if($tmpTest != 'ok') die($tmpTest);
	}
}

$tmpData = lepus_get_dnsAccess($_POST['domain_id'], $user['id'], 'check');
if($tmpData == 'deny' || $tmpData == 'SLAVE') die("deny or no_record or slave ".$tmpData);
echo lepus_add_dnsRecord($_POST['name'], $_POST['type'], $_POST['content'], $_POST['prio'], $_POST['domain_id']);
