<?php
$cache = new Memcache();
$cache->connect('unix:///tmp/memcached.socket', 0);
if($cache === FALSE ){
	 die("memcached down");
}

try {
	$db = new PDO("mysql:host=localhost;dbname=spam", 'spam', 'KN7hqUGGHraet3Kq');
	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$db->exec("set names utf8");
}
catch(PDOException $e) {
	die('MySQL ERROR');
}

function clamav_check($file, $hash){
	global $cache;
	$data = shell_exec('clamdscan --multiscan --fdpass '.escapeshellarg($file));
	$a = ['send' => 'yes', 'info' => 'none'];
	if(strpos($data, 'Infected files: 1') !== FALSE){
		$i = explode("\n", $data);
		$j = explode(":", $i[0]);
		$a = ['send' => 'no', 'info' => trim($j[1])];		
	}else{
		$data = file_get_contents($file);
		if(count(file($file)) < 10 && strlen($data) > 10000){
			$a = ['send' => 'no', 'info' => 'possible spam bot'];	
		}
	}
	$cache->set($hash, json_encode($a), MEMCACHE_COMPRESSED, 3600);
	unlink($file);
	return json_encode($a);
}

function clamav_memcacheStat($send){
	global $cache;
	$stat = $cache->get('stat');
	if(empty($stat))
		$stat = ['no' => 0, 'yes' => 0];
	else
		$stat = json_decode($stat, true);

	$stat[$send]++;
	$cache->set('stat', json_encode($stat), MEMCACHE_COMPRESSED, 86400);
	return;
}

function clamav_mysqlStat($hash, $info){
	global $db;
	$query = $db->prepare("SELECT * FROM `hash` WHERE `hash` = :hash");
	$query->bindParam(':hash', $hash, PDO::PARAM_STR);
	$query->execute();
	if($query->rowCount() != 1){
		$query = $db->prepare("INSERT INTO `hash` (`hash`, `info`, `last`) VALUES (:hash, :info, :time)");
		$query->bindParam(':hash', $hash, PDO::PARAM_STR);
		$query->bindParam(':info', $info, PDO::PARAM_STR);
		$query->bindParam(':time', time(), PDO::PARAM_STR);
		$query->execute();
	}else{
		$query = $db->prepare("UPDATE `hash` SET `count` = `count` +1, `info` = :info WHERE `hash` = :hash");
		$query->bindParam(':hash', $hash, PDO::PARAM_STR);
		$query->bindParam(':info', $info, PDO::PARAM_STR);
		$query->execute();
	}
}

function clamav_start(){
	global $cache, $hash;
	$base = '/var/www/spam/root/tmp';
	$hash = hash_file('sha256', $_FILES["file"]["tmp_name"]);
	$fake = hash('md5', $hash.mt_rand(0,100).time());
	if($cache->get($hash) === FALSE){
		move_uploaded_file($_FILES["file"]["tmp_name"], "$base/$fake");
		return clamav_check("$base/$fake", $hash);
	}else{
		return $cache->get($hash);
	}
}

function get_tableData($i = 0, $data = ''){
	global $db;
	$query = $db->prepare("SELECT * FROM `hash`");
	$query->execute();
	while($row = $query->fetch()){
		$i++; $data .= "<tr><td>#$i</a></td><td>{$row['hash']}</td><td>{$row['info']}</td><td>{$row['count']}</td><td>".date("H:i", $row['last'])."</td></tr>";
	}
	return $data;
}

if(!empty($_POST) && !empty($_FILES)){
	$json = clamav_start();
	$x = json_decode($json, true);
	if($x['send'] == 'no') clamav_mysqlStat($hash, $x['info']);
	clamav_memcacheStat($x['send']);
	echo $json;
}else{
	$stat = json_decode($cache->get('stat'), true);
	$table = get_tableData();
	require_once('./page.php');
}

$cache->close();
