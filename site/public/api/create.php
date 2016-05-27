<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');

$main = array();
$query = $db->prepare("SELECT * FROM `task` WHERE (`handler` = 'KVM' OR `handler` = 'VH') AND `status` = '0'");
$query->execute();
while($row=$query->fetch()){
	$arr = json_decode($row['data'], true);
	if($arr['do'] != 'create') continue;
	$select = $db->prepare("SELECT * FROM `tariff` WHERE `id` = :id");
	$select->bindParam(':id', $arr['tariff'], PDO::PARAM_STR);
	$select->execute();
	if($select->rowCount() == 0) continue;
	$info = $select->fetch();
	$info = json_decode($info['data'], true);
	if(empty($arr['os'])) $arr['os'] = 'debian';
	$select = $db->prepare("SELECT * FROM `ipmanager` WHERE `service` = :service");
	$select->bindParam(':service', $arr['order'], PDO::PARAM_STR);
	$select->execute();
	if($select->rowCount() == 0) continue;
	$data = $select->fetch();
	$select = $db->prepare("SELECT * FROM `servers` WHERE `id` = :id");
	$select->bindParam(':id', $data['sid'], PDO::PARAM_STR);
	$select->execute();
	if($select->rowCount() == 0) continue;
	$server = $select->fetch();
	if(empty($arr['passwd'])){
		$arr['passwd'] = genRandStr(10);
		$json = json_encode($arr);
		$update = $db->prepare("UPDATE `task` SET `data` = :data WHERE `id` = :id");
		$update->bindParam(':data', $json, PDO::PARAM_STR);
		$update->bindParam(':id', $row['id'], PDO::PARAM_STR);
		$update->execute();
	}
	$arr['order'] += 100;
	$data['ip'] = long2ip($data['ip']);
	$server['ip'] = long2ip($server['ip']);
	$main[] = ['id' => $arr['order'], 'ip' => $data['ip'], 'mac' => $data['mac'], 'passwd' => $arr['passwd'], 'memory' => $info['memory'], 'cpus' => $info['cpus'], 'diskspace' => $info['diskspace'], 'node' => $server['ip'], 'os' => $arr['os']];
}

foreach($main as $val){
	if($val['node'] != $_SERVER['REMOTE_ADDR']) continue;
	echo json_encode(['id' => $val['id'], 'ip' => $val['ip'], 'mac' => $val['mac'], 'passwd' => $val['passwd'], 'memory' => $val['memory'], 'cpus' => $val['cpus'], 'diskspace' => $val['diskspace'], 'node' => $val['node'], 'os' => $val['os']]);
	break;
}
