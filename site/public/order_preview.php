<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');
if(!is_login()) die("no_login");
if(empty($_POST['id'])) die('empty post');
$tmpData = lepus_order_preview($_POST['id'], lepus_check_discount(@$_POST['promo'], $_POST['id']));
echo "<center>{$tmpData["name"]} | <u><font color=\"green\">Cкидка {$tmpData["discont"]} RUR</font></u> | К оплате {$tmpData["price"]} RUR</center>";
if($tmpData['handler'] == 'KVM' || $tmpData['handler'] == 'OVH-DEDIC')
	echo "<br/><center><select id='ostype' style='width: 68%;' class='form-control'><option value='1'>Debian 7</option><option value='2'>Ubuntu 14.04</option><option value='3'>CentOS 7</option></select></center>";
