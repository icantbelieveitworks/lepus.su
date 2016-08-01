#!/usr/bin/php
<?php
// id, ip, passwd, memory, cpus, diskspace, command
function genRandStr($length = 10) {
    return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
}

$json = json_decode(file_get_contents('https://lepus.su/public/api/create.php'), true);
$id = intval($json['id']);
if(empty($id)) die('empty\n');
var_dump($json);

if(file_exists("/etc/sysconfig/vz-scripts/$id.conf")) die("ovz create\n");
if(empty($json['ip']) || empty($json['passwd']) || empty($json['memory']) || empty($json['cpus']) || empty($json['diskspace'])) die('empty');
$cpu = round($json['cpus']/(2200/100));
shell_exec('vzctl create '.escapeshellarg($id).' --ostemplate debian-8 --config main --ipadd '.escapeshellarg($json['ip']));
shell_exec('vzctl start '.escapeshellarg($id));
shell_exec('vzctl set '.escapeshellarg($id).' --userpasswd lepus:'.escapeshellarg($json['passwd']));
shell_exec('vzctl set '.escapeshellarg($id).' --userpasswd root:'.escapeshellarg($json['passwd']));
shell_exec('vzctl set '.escapeshellarg($id).' --diskspace '.escapeshellarg($json['diskspace']).' --cpus 1 --cpulimit '.escapeshellarg($cpu).' --ram '.escapeshellarg($json['memory']).' --swap 0 --save');
sleep(30);
shell_exec('vzctl exec '.escapeshellarg($id).' mysqladmin -uroot -p92d20b1d8bd0b50 password '.escapeshellarg($json['passwd']));
