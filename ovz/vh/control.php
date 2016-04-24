<?php
// id, ip, passwd, memory, cpus, diskspace, command
function genRandStr($length = 10) {
    return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
}

$json = json_decode(file_get_contents('https://lepus.su/public/api/create.php'), true);
$id = intval($json['id']);
if(empty($id)) die('empty');
var_dump($json);

if(file_exists("/etc/vz/conf/$id.conf")) die("ovz create\n");
if(empty($json['ip']) || empty($json['passwd']) || empty($json['memory']) || empty($json['cpus']) || empty($json['diskspace'])) die('empty');
$cpu = round($json['cpus']/(2200/100));
shell_exec("vzctl create $id --ostemplate debian-8 --config main --ipadd {$json['ip']}");
shell_exec("vzctl start $id");
shell_exec("vzctl set $id --userpasswd lepus:{$json['passwd']}");
shell_exec("vzctl set $id --userpasswd root:{$json['passwd']}";
shell_exec("vzctl set $id --diskspace {$json['diskspace']} --cpus 1 --cpulimit $cpu --ram {$json['memory']} --swap 0 --save");
