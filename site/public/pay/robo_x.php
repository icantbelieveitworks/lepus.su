<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');

$out_summ = $_REQUEST["OutSum"];
$inv_id = $_REQUEST["InvId"];
$shp_uid = $_REQUEST["shp_uid"];
$crc = strtoupper($_REQUEST["SignatureValue"]);
$my_crc = strtoupper(md5("$out_summ:$inv_id:{$conf['robokassa_pass2']}:shp_uid=$shp_uid"));
if ($crc != $my_crc) die("bad sign\n");
echo lepus_update_balance($inv_id, $shp_uid, $out_summ, 'robokassa');
