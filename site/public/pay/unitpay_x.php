<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');

$allowips = ['31.186.100.49', '178.132.203.105', '52.29.152.23', '52.19.56.234']; // http://help.unitpay.ru/article/35-confirmation-payment
if(!in_array($_SERVER['REMOTE_ADDR'], $allowips)) die;

$arr = null; list($method, $params) = array($_GET['method'], $_GET['params']);
$sign = $params['sign']; unset($params['sign']); ksort($params);
if(md5(implode(null, $params).$conf['unitpay_secret']) != $sign)
	$arr = ['error' => ['message' => 'wrong sign']];

if($params['orderCurrency'] != $conf['unitpay_currency'] || $params['projectId'] != $conf['unitpay_id'])
	$arr = ['error' => ['message' => 'wrong order']];

if(empty($arr)){
	switch($method){
		default:
			$arr = ['error' => ['message' => 'wrong method']];
		break;
		case 'check':
			$arr = ['result' => ['message' => 'Check Success. Ready to pay.']];
		break;
		case 'pay':
			lepus_update_balance($params['unitpayId'], $params['account'], $params['orderSum'], 'unitpay');
			$arr = ['result' => ['message' => 'Pay Success.']];
		break;
	}
}

echo json_encode($arr);
