<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');

if(empty($_POST['id'])) die('empty post');
$tmpData = lepus_order_preview($_POST['id'], lepus_check_discount(@$_POST['promo']));
echo "<center>{$tmpData["name"]} | <u><font color=\"green\">Cкидка {$tmpData["discont"]} RUR</font></u> | К оплате {$tmpData["price"]} RUR</center>";
