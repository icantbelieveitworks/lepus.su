<?php
function rehash($passwd, $hash = 0, $a = 'no_hash'){
	if(empty($hash) || password_needs_rehash($hash, PASSWORD_DEFAULT))
		$a = password_hash($passwd, PASSWORD_DEFAULT);
	return $a;
}

function _exit(){
	global $db;
	$query = $db->prepare("UPDATE `users` SET `session` = :null where `session` = :sess");
	$query->bindValue(':null', null, PDO::PARAM_INT);
	$query->bindParam(':sess', $_SESSION["sess"], PDO::PARAM_STR);
	$query->execute();
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
	session_unset();
	session_destroy();
	header("Location: https://".$_SERVER['SERVER_NAME']);
}

function is_lepus_user($login){
	global $db; $login = mb_strtolower($login);
	$query = $db->prepare("SELECT * FROM `users` WHERE `login` =:login");
	$query->bindParam(':login', $login, PDO::PARAM_STR);
	$query->execute();
	return ['0' => $query->rowCount(), '1' => $query->fetch()];
}

function lost_passwd_change($arr){
	$data = json_decode(lepus_crypt($arr, 'decode'), true);
	$is_user = is_lepus_user($data[0]);
	if($is_user['0'] != 1) return 'no_user';
	$real_hash = hash('sha512' ,$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].$is_user['1']['passwd'].$is_user['1']['login']);
	if($data[1] != $real_hash) return 'wrong_hash';
	if(time() > $data[2]) return 'lost_passwd_time';
	$new_passwd = change_passwd($is_user['1']['id']);
	_mail($is_user['1']['login'], "Новый пароль", "Дорогой клиент,<br/>по-вашему запросу, мы поменяли пароль.<br/>Ваш новый пароль: $new_passwd");
	return 'Мы отправили новый пароль на ваш email';
}

function lost_passwd($login){
	if(empty($login)) return 'empty_post_value';
	if(!filter_var($login, FILTER_VALIDATE_EMAIL)) return 'bad_email';
	$is_user = is_lepus_user($login);
	if($is_user['0'] != 1) return 'no_user';
	$arr = [$_POST['email'], hash('sha512' ,$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].$is_user['1']['passwd'].$is_user['1']['login']), time()+60*60*24];
	_mail($_POST['email'], "Забыли пароль?", "Дорогой клиент,<br/>после того как вы перейдете <a href=\"http://".$_SERVER['SERVER_NAME']."/public/lost_passwd.php?hash=".urlencode(lepus_crypt(json_encode($arr)))."\">по этой ссылке</a> - вы получите второе письмо с паролем от вашего аккаунта.<br/>");
	return 'Мы отправили письмо с инструкцией на ваш email';
}

function login($login, $passwd, $a = 'bad_passwd'){
	global $db; $login = mb_strtolower($login);
	if(IsTorExitPoint()) return 'deny_tor';
	if(empty($login) || empty($passwd)) return 'empty_post_value';
	if(!filter_var($login, FILTER_VALIDATE_EMAIL)) return 'bad_email';
	$is_user = is_lepus_user($login);
	if($is_user['0'] != 1) return 'no_user';
	$row = $is_user['1'];
	if(password_verify($passwd, $row['passwd'])){
		$new_passwd = rehash($passwd, $row['passwd']);
		$_SESSION['id'] = $row['id'];
		$_SESSION['sess'] = hash('sha512' ,$login.$passwd.$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
		if($new_passwd != 'no_hash'){
			$query = $db->prepare("UPDATE `users` SET `passwd` = :passwd WHERE `id` = :id");
			$query->bindParam(':passwd', $new_passwd, PDO::PARAM_STR);
			$query->bindParam(':id', $row['id'], PDO::PARAM_STR);
			$query->execute();
		}
		$query = $db->prepare("UPDATE `users` SET `session` = :sess WHERE `id` = :id");
		$query->bindParam(':id', $row['id'], PDO::PARAM_STR);
		$query->bindParam(':sess', $_SESSION['sess'], PDO::PARAM_STR);
		$query->execute();
		lepus_log_ip($row['id'], ip2long($_SERVER["REMOTE_ADDR"]));
		$a = 'enter';
	}
	return $a;
}

function auth($id, $session){
	global $db;
	$query = $db->prepare("SELECT * FROM `users` WHERE `id` = :id AND `session` = :session");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->bindParam(':session', $session, PDO::PARAM_STR);
	$query->execute();	
	if($query->rowCount() != 1){
		$query = $db->prepare("UPDATE `users` SET `session` = NULL WHERE `login` = :login AND `session` = :session");
		$query->bindParam(':login', $login, PDO::PARAM_STR);
		$query->bindParam(':session', $session, PDO::PARAM_STR);
		$query->execute();
		
		session_unset();
		session_destroy();
		return 'no_auth';
	}
	$row = $query->fetch();
	return ["id" => $row['id'], "login" => $row['login'], "passwd" => $row['passwd'], "bitcoin" => $row['bitcoin'], "api" => $row['api'], "data" => $row['data']];
}

function error($message, $j = 0){
	global $db;
	$result = ['mes' => $message, 'err' => 'OK'];
	if(!is_array($message)){
		$query = $db->prepare("SELECT * FROM `error` WHERE `name` = :name");
		$query->bindParam(':name', $message, PDO::PARAM_STR);
		$query->execute();
		if($query->rowCount() == 1){
			$row = $query->fetch();
			$result = ['mes' => $row['mes'], 'err' => $message];
		}
	}
	return $result;
}

function save_user_data($id, $data){
	global $db;
	$data = json_encode($data);
	$query = $db->prepare("UPDATE `users` SET `data` = :data WHERE `id` = :id");
	$query->bindParam(':data', $data, PDO::PARAM_STR);
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
}

function _mail($email, $subject, $message){
	$headers  = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=utf-8\r\n";
	$headers .= "Content-Transfer-Encoding: base64\r\n";
	$subject  = "=?utf-8?B?".base64_encode($subject)."?=";
	$headers .= "From: Lepus Artifical Intelligence <support@lepus.su>\r\n";
	mail($email, $subject, rtrim(chunk_split(base64_encode($message))), $headers);
}

function genRandStr($length = 10) {
    return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
}

function change_passwd($id){
	global $db;
	$passwd = genRandStr(8);
	$hash = rehash($passwd);
	$query = $db->prepare("UPDATE `users` SET `passwd` = :passwd WHERE `id` = :id");
	$query->bindParam(':passwd', $hash, PDO::PARAM_STR);
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	return $passwd;
}

function lepus_crypt($input, $do = 'encode'){
	global $conf;
	$algo = MCRYPT_RIJNDAEL_256;
	$mode = MCRYPT_MODE_CBC;
	$iv_size = mcrypt_get_iv_size($algo, $mode);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
	switch($do){
		case 'encode':	
		$ciphertext = mcrypt_encrypt($algo, $conf['lepus_crypt'], $input, $mode, $iv);
		$ciphertext = $iv . $ciphertext;
		$result = base64_encode($ciphertext);
		break;
		case 'decode':
		$ciphertext_dec = base64_decode($input);
		$iv_dec = substr($ciphertext_dec, 0, $iv_size);
		$ciphertext_dec = substr($ciphertext_dec, $iv_size);
		$result = mcrypt_decrypt($algo, $conf['lepus_crypt'], $ciphertext_dec, $mode, $iv_dec);
		break;
	}
	return $result;
}

function lepus_new_account($login){
	global $db; $login =  mb_strtolower($login);
	if(empty($login)) return 'empty_post_value';
	if(!filter_var($login, FILTER_VALIDATE_EMAIL)) return 'bad_email';
	$is_user = is_lepus_user($login);
	if($is_user['0'] != 0) return 'user_exist';
	$passwd = genRandStr(8);
	$data = ['balance' => 0, 'phone' => NULL, 'regDate' => time(), 'access' => 1, 'lastIP' => NULL];
	$json = json_encode($data);
	$query = $db->prepare("INSERT INTO `users` (`login`, `passwd`, `api`, `data`) VALUES (:login, :passwd, :api, :data)");
	$query->bindParam(':login', $login, PDO::PARAM_STR);
	$query->bindParam(':passwd', rehash($passwd), PDO::PARAM_STR);
	$query->bindParam(':api', genRandStr(32), PDO::PARAM_STR);
	$query->bindParam(':data', $json, PDO::PARAM_STR);
	$query->execute();
	_mail($login, "Регистрация нового аккаунта", "Дорогой клиент, ваш аккаунт готов.<br/>Ваш логин: $login<br/>Ваш пароль: $passwd<br/>Для активации, пожалуйста, авторизуйтесь на нашем сайте.<br/>В противном случае аккаунт будет автоматически удален через 7 дней.");
	return 'Мы отправили пароль на ваш email';
}

function lepus_log_ip($id, $ip){
	global $db; $info = get_browser(null, true);
	if(preg_match('/[^0-9A-Za-z.]/', $info['platform'])) $info['platform'] = "unknown";
	if(preg_match('/[^0-9A-Za-z.]/', $info['browser'])) $info['browser'] = "unknown";
	$query = $db->prepare("INSERT INTO `log_ip` (`uid`, `ip`, `platform`, `browser`, `time`) VALUES (:id, :ip, :platform, :browser, unix_timestamp(now()))");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->bindParam(':ip', $ip, PDO::PARAM_STR);
	$query->bindParam(':platform', $info['platform'], PDO::PARAM_STR);
	$query->bindParam(':browser', $info['browser'], PDO::PARAM_STR);
	$query->execute();
}

function lepus_get_logip($id, $i = 0){
	global $db; $data = null;
	$query = $db->prepare("SELECT * FROM `log_ip` WHERE `uid` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	if($query->rowCount() == 0) return "no_data";
	while($row = $query->fetch()){
		$i++; $data .= "<tr><td>$i</td><td>".long2ip($row['ip'])."</td><td><img src=\"/images/flags16/".mb_strtolower(geoip_country_code_by_name(long2ip($row['ip']))).".png\" style=\"margin-bottom:-3px;\"> ".geoip_country_name_by_name(long2ip($row['ip']))."</td><td>".$row['platform']."</td><td>".$row['browser']."</td><td>".date('Y-m-d H:i', $row['time'])."</td></tr>";
	}
	return $data;
}

function lepus_addDNSDomain($domain, $type, $master, $id){
	global $pdns; $arr = array_reverse(explode(".", $domain));
	$lvl = ['kiev.ua', 'com.ua', 'pp.ua', 'ru.com', 'com.kz', 'org.kz', 'co.am', 'com.am', 'net.am', 'msk.ru', 'org.ru',
			'org.am', 'co.in', 'net.in', 'org.in', 'gen.in', 'firm.in', 'ind.in', 'za.com', 'uy.com', 'br.com', 'msk.su',
			'spb.su', 'spb.ru', 'com.ru', 'ru.net', 'co.ua', 'od.ua', 'in.ua', 'net.ua', 'kh.ua', 'kharkov.ua', 'co.uk', 'vn.ua', 'org.ua'];
	if(count($arr) > 2){
		if(!in_array("$arr[1].$arr[0]", $lvl)) return "wrong_domain";
		$domain = "$arr[2].$arr[1].$arr[0]"; 
	}
	$query = $pdns->prepare("SELECT * FROM `domains` WHERE `name` = :domain");
	$query->bindParam(':domain', $domain, PDO::PARAM_STR);
	$query->execute();
	if($query->rowCount() != 0) return 'already_add';
	switch($type){
		default: die("Something wrong"); break;
		case 'master':
			$query = $pdns->prepare("INSERT INTO `domains` (`name`, `type`, `account`) VALUES ( :domain, 'MASTER', :uid)");
			$query->bindParam(':domain', $domain, PDO::PARAM_STR);
			$query->bindParam(':uid', $id, PDO::PARAM_STR);
			$query->execute();
		break;
		case 'slave':
			$query = $pdns->prepare("INSERT INTO `domains` (`name`, `master`, `type`, `account`) VALUES ( :domain, :master, 'SLAVE', :uid)");
			$query->bindParam(':domain', $domain, PDO::PARAM_STR);
			$query->bindParam(':master', $master, PDO::PARAM_STR);
			$query->bindParam(':uid', $id, PDO::PARAM_STR);
			$query->execute();
		break;
	}
	return $pdns->lastInsertId();
}

function lepus_get_dnsDomains($id, $i = 0){
	global $pdns; $data = null;
	$query = $pdns->prepare("SELECT * FROM `domains` WHERE `account` = :uid");
	$query->bindParam(':uid', $id, PDO::PARAM_STR);
	$query->execute();
	while($row = $query->fetch()){
		if($row['type'] == 'MASTER') $row['master'] = '-';
		$i++; $data .= "<tr id=\"".$row['id']."\"> <td>$i</td> <td>".htmlspecialchars(idn_to_utf8($row['name']))."</td> <td>".$row['type']."</td> <td>".$row['master']."</td> <td><a href=\"/pages/edit-domain.php?id=".$row['id']."\"><i class=\"glyphicon glyphicon-pencil\"></i></a> &nbsp; <a href=\"nourl\" data-dns-delete-id=".$row['id']."><i class=\"glyphicon glyphicon-remove\"></i></a></td> </tr>";
	}
	return $data;
}

function lepus_get_dnsAccess($id, $uid, $slave = 'no_check'){
	global $pdns;
	$query = $pdns->prepare("SELECT * FROM `domains` WHERE `id` = :id AND `account` =:uid");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->bindParam(':uid', $uid, PDO::PARAM_STR);
	$query->execute();
	if($query->rowCount() != 1) return 'deny';
	$row = $query->fetch();
	if($slave == 'check') return $row['type'];
	return htmlspecialchars(idn_to_utf8($row['name']));
}

function lepus_add_dnsRecord($zone, $type, $data, $prio, $domain_id){
	global $pdns;	
	$types = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SRV', 'PTR', 'SOA'];
	if(!in_array($type, $types)) return "wrong type record";
	$query = $pdns->prepare("INSERT INTO `records` (`domain_id`, `name`, `type`, `content`, `ttl`, `prio`) VALUES (:id, :name, :type, :content, 3600, :prio)");
	$query->bindParam(':id', $domain_id, PDO::PARAM_STR);
	$query->bindParam(':name', $zone, PDO::PARAM_STR);
	$query->bindParam(':type', $type, PDO::PARAM_STR);
	$query->bindParam(':content', $data, PDO::PARAM_STR);
	$query->bindParam(':prio', $prio, PDO::PARAM_STR);
	$query->execute();
	return $pdns->lastInsertId();
}

function lepus_delete_dnsDomain($id){
	global $pdns;
	$query = $pdns->prepare("DELETE FROM `domains` WHERE `id` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	$query = $pdns->prepare("DELETE FROM `records` WHERE `domain_id` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
}

function lepus_get_dnsRecordAccess($id, $uid){
	global $pdns;
	$query = $pdns->prepare("SELECT * FROM `records` WHERE `id` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	if($query->rowCount() != 1) return "no_record";
	$row = $query->fetch();
	$tmpData = lepus_get_dnsAccess($row['domain_id'], $uid, 'check');
	if($tmpData == 'deny') return 'deny';
	if($tmpData == 'SLAVE') return 'SLAVE';
	return 'ok';
}

function lepus_get_dnsRecord($type, $id){
	global $pdns;
	$query = $pdns->prepare("SELECT * FROM `records` WHERE `id` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	$row = $query->fetch();
	return idn_to_utf8($row[$type]);
}

function lepus_get_dnsRecords($id, $i = 0){
	global $pdns; $data = null;
	$query = $pdns->prepare("SELECT * FROM `records` WHERE `domain_id` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	while($row = $query->fetch()){
		$i++; $data .= "<tr id=\"".$row['id']."\"><td>".$i."</td><td class=\"edit\" id=\"name_".$row['id']."\">".htmlspecialchars(idn_to_utf8($row['name']))."</td><td class=\"edit_type\" id=\"type_".$row['id']."\">".$row['type']."</td><td class=\"edit\" id=\"content_".$row['id']."\">".htmlspecialchars($row['content'])."</td><td class=\"edit\" id=\"prio_".$row['id']."\">".$row['prio']."</td><td><a href=\"nourl\" data-dns-zone-id=\"".$row['id']."\"><i class=\"glyphicon glyphicon-remove\"></i></a></td></tr>";
	}
	return $data;
}

function lepus_edit_dnsRecord($type, $id, $value){
	global $pdns;
	$tmpTest = lepus_dnsValid($type, $value);
	if($tmpTest != 'ok') return $tmpTest;
	if($type == 'type' && lepus_dnsValidType($value) != 'ok') return "wrong type record";	
	if($type == 'name') $value = idn_to_ascii(mb_strtolower($value));
	$query = $pdns->prepare("UPDATE `records` SET `$type` = :value WHERE `id` = :id");
	$query->bindParam(':value', $value, PDO::PARAM_STR);
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	return htmlspecialchars(idn_to_utf8($value));
}

function lepus_delete_dnsRecord($id){
	global $pdns;
	$query = $pdns->prepare("DELETE FROM `records` WHERE `id` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
}

function lepus_dnsValidType($value, $j = 'ok'){
	$types = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SRV', 'PTR', 'SOA'];
	if(!in_array($value, $types)) $j = "wrong type record";
	return $j;
}

function lepus_dnsValid($type, $value, $j = 'ok'){
	$i = ['prio' => 3, 'type' => 6, 'master' => 128, 'name' => 255, 'content' => 4096];
	if(isset($i[$type])){
		if(strlen($value) > $i[$type]) $j = "max $type strlen max $i[$type]";
	}else{
		$j = 'no_exist';
	}
	if(strlen($value) == 0) $j = 'empty value';
	if($type == 'master' && !filter_var($value, FILTER_VALIDATE_IP)) $j = 'Wrong master IP';
	if($type == 'prio' && !ctype_digit($value)) $j = 'prio only number';
	return $j;
}

function lepus_get_tiketLabel($id, $uid, $tid, $access){
	global $db;
	$query = $db->prepare("SELECT * FROM `support_msg` WHERE `tid` = :tid ORDER BY `time` DESC LIMIT 1");
	$query->bindParam(':tid', $tid, PDO::PARAM_STR);
	$query->execute();
	$row = $query->fetch();
	if($row['uid'] != $uid){
		$info = [1 => 'Ответ получен', 2 => 'Закрыт'];
		$label = [1 => 'success', 2 => 'danger'];
	}else{
		$info = [1 => 'В обработке', 2 => 'Закрыт'];
		$label = [1 => 'warning', 2 => 'danger'];
		if($access > 1) $info[1] = 'Обработан';
	}
	return  ['info' => $info[$id], 'label' => $label[$id]];
}

function lepus_get_supportList($uid, $access, $id = 0){
	global $db; $data = null;
	if($access > 1){
		if($id == 0){
			$query = $db->prepare("SELECT * FROM `support`");
		}else{
			$query = $db->prepare("SELECT * FROM `support` WHERE `id` = :id");
			$query->bindParam(':id', $id, PDO::PARAM_STR);
		}
	}else{
		if($id == 0){
			$query = $db->prepare("SELECT * FROM `support` WHERE `uid` = :uid");
			$query->bindParam(':uid', $uid, PDO::PARAM_STR);
		}else{
			$query = $db->prepare("SELECT * FROM `support` WHERE `uid` = :uid AND `id` = :id");
			$query->bindParam(':uid', $uid, PDO::PARAM_STR);
			$query->bindParam(':id', $id, PDO::PARAM_STR);
		}
	}
	$query->execute();
	while($row = $query->fetch()){
		if(!empty($row['open'])) $row['open'] = date("Y-m-d H:i", $row['open']); else $row['open'] = '-';
		if(!empty($row['last'])) $row['last'] = date("Y-m-d H:i", $row['last']); else $row['last'] = '-';
		$ldata = lepus_get_tiketLabel($row['status'], $uid, $row['id'], $access);
		if(strlen($row['title']) > 23){
			$tmpTitle = "title='{$row['title']}'";
			$row['title'] = mb_substr($row['title'], 0, 23,'utf-8')."...";	 
		}
		if($id == 0){
			$data .= "<tr><td><a href=\"/pages/tiket.php?id={$row['id']}\" title=\"Открыть\">".$row['id']."</a></td><td ".@$tmpTitle.">".$row['title']."</td><td>".$row['open']."</td><td>".$row['last']."</td><td style=\"padding-top: 11px;\"><span class=\"label label-pill label-".$ldata['label']." myLabel\">".$ldata['info']."</span></td></tr>";
		}else{
			$data = ['a' => "<a href=\"/pages/tiket.php?id={$row['id']}\" title=\"Открыть\">".$row['id']."</a>",
					 'b' => $row['title'],
					 'c' => $row['open'],
					 'd' => $row['last'],
					 'e' => "<span class=\"label label-pill label-".$ldata['label']." myLabel\">".$ldata['info']."</span>"];
		}
		$tmpTitle = '';
	}
	return $data;
}

function support_create($uid, $title, $access){
	global $db;
	if(strlen($_POST['msg']) < 1) return 'empty_message';
	if(empty(trim($title)) || empty($uid)) return('empty_post_value');
	if($access < 2){
		$tmpTime = time()-60*60*24;
		$query = $db->prepare("SELECT * FROM `support` WHERE `uid` = :uid AND `open` > :time");
		$query->bindParam(':uid', $uid, PDO::PARAM_STR);
		$query->bindParam(':time', $tmpTime, PDO::PARAM_STR);
		$query->execute();
		if($query->rowCount() > 10) return 'max_limit';
	}
	$title = filter_var($title, FILTER_SANITIZE_STRING);
	$query = $db->prepare("INSERT INTO `support` (`uid`, `title`, `open`, `status`) VALUES (:uid, :title, unix_timestamp(now()), 1)");
	$query->bindParam(':uid', $uid, PDO::PARAM_STR);
	$query->bindParam(':title', $title, PDO::PARAM_STR);
	$query->execute();
	$id = $db->lastInsertId();

	$query = $db->prepare("SELECT * FROM `users` WHERE `id` = :id");
	$query->bindParam(':id', $uid, PDO::PARAM_STR);
	$query->execute();
	$row = $query->fetch();
	_mail($row['login'], "[Lepus Support] Заявка №[$id]", "Благодарим за общение в службу технической поддержки.<br/>Наши специалисты постараются как можно скорее ответить вам.<br/>Ваш тикет доступен в личном кабинете  <a href=\"https://".$_SERVER['SERVER_NAME']."/pages/tiket.php?id=$id\">по ссылке</a>.<br/><br/>Это сообщение отправлено автоматически. Пожалуйста, не отвечайте на него.<br/>------------------------<br/>Lepus Support<br/><a href=\"http://lepus.su\">https://lepus.su</a>");
	return $id;
}

function lepus_get_supportAccess($tid){
	global $db;
	$query = $db->prepare("SELECT * FROM `support` WHERE `id` = :id");
	$query->bindParam(':id', $tid, PDO::PARAM_STR);
	$query->execute();
	return $query->fetch();
}

function lepus_get_supportMsg($tid, $uid, $access, $msgID = 0, $update = 0, $data = null, $j = 0){
	global $db;
	$row = lepus_get_supportAccess($tid);	
	if($row['uid'] != $uid && $access < 2) return 'no_access';
	$tstatus = $row['status'];
	if($msgID == 0){
		if($update != 0){
			$query = $db->prepare("SELECT * FROM `support_msg` WHERE `tid` = :tid ORDER BY `time` ASC");
		}else{
			$query = $db->prepare("SELECT * FROM `support_msg` WHERE `tid` = :tid ORDER BY `time` DESC");
		}
		$query->bindParam(':tid', $tid, PDO::PARAM_STR);
	}else{
		$query = $db->prepare("SELECT * FROM `support_msg` WHERE `id` = :id AND `tid` = :tid");
		$query->bindParam(':id', $msgID, PDO::PARAM_STR);
		$query->bindParam(':tid', $tid, PDO::PARAM_STR);
	}
	$query->execute();
	$countMSG = $query->rowCount();
	if($update != 0 && $countMSG <= $update ) return 'no_mes';
	while($msg = $query->fetch()){
		$j++;
		if($update != 0 && $update+1 > $j) continue;
		$tmpQuery = $db->prepare("SELECT * FROM `users` WHERE `id` =:id");
		$tmpQuery->bindParam(':id', $msg['uid'], PDO::PARAM_STR);
		$tmpQuery->execute();
		$tmpRow = $tmpQuery->fetch();
		$tmpData = json_decode($tmpRow['data'], true);
		if($tmpData['access'] > 1){
			$panel = 'panel-danger';
			$who = "Ответ службы поддержки";
		}else{
			$panel = 'panel-info';
			$who = "Пользователь написал ({$tmpRow['login']})";
		}
		$msg['time'] = date("Y-m-d H:i", $msg['time']);
		$data .= "<div class=\"panel $panel panelbg\"><div class=\"panel-heading\"><span class=\"label label-pill label-default myColor myLabel\">{$msg['time']}</span><font color=\"black\"> $who</font></div><div class=\"panel-body\">{$msg['msg']}</div></div>";
		if($update != 0 && strlen($data) > 10) break 1;
	}
	return ['title' => $row['title'], 'msg' => $data, 'countMSG' => $countMSG, 'status' => $tstatus];
}

function support_msg($uid, $tid, $access, $no_last = 0){
	global $db;
	$tiket = lepus_get_supportAccess($tid);
	if($tiket['uid'] != $uid && $access < 2) return 'no_access';
	if(strlen($_POST['msg']) < 1) return 'empty_message';
	if($access > 1 && $_POST['msg'] != 'END' && $_POST['msg'] != 'OPEN') $_POST['msg'] .= "\n\n\n[i]С уважением, команда технической поддержки.\nДокументация: [url=https://github.com/poiuty/lepus.su/wiki/%D0%92%D0%B8%D1%80%D1%82%D1%83%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9-%D1%85%D0%BE%D1%81%D1%82%D0%B8%D0%BD%D0%B3]виртуальный хостинг[/url], [url=https://github.com/poiuty/lepus.su/wiki/KVM-VPS]vps[/url], [url=https://wiki.lepus.su]wiki[/url].[/i]";
	if($tiket['status'] == 2 && $_POST['msg'] != 'OPEN') return 'close_tiket'; // if tiket close => we need first open it
	if($tiket['status'] == 1 && $_POST['msg'] == 'OPEN') return 'already_open'; // dont open - open tiket
	$msg = parse_bb_code(nl2br(htmlentities($_POST['msg'], ENT_QUOTES, 'UTF-8')));
	if($access < 2 && $msg != 'END'){
		$tmpTime = time()-5;
		$query = $db->prepare("SELECT * FROM `support_msg` WHERE `uid` = :uid AND `time` > :time");
		$query->bindParam(':uid', $uid, PDO::PARAM_STR);
		$query->bindParam(':time', $tmpTime, PDO::PARAM_STR);
		$query->execute();
		if($query->rowCount() != 0) return 'max_limit';
	}
	if($msg == 'END'){
		$msg = '<span class="label label-pill label-danger myLabel">Тикет закрыт</span>';
		$query = $db->prepare("UPDATE `support` SET `status` = 2 WHERE `id` = :tid");
		$query->bindParam(':tid', $tid, PDO::PARAM_STR);
		$query->execute();
	}
	if($msg == 'OPEN'){
		$msg = '<span class="label label-pill label-success myLabel">Тикет открыт</span>';
		$query = $db->prepare("UPDATE `support` SET `status` = 1 WHERE `id` = :tid");
		$query->bindParam(':tid', $tid, PDO::PARAM_STR);
		$query->execute();
	}
	if($no_last == 0){
		$query = $db->prepare("UPDATE `support` SET `last` = unix_timestamp(now()) WHERE `id` = :tid");
		$query->bindParam(':tid', $tid, PDO::PARAM_STR);
		$query->execute();
	}
	$query = $db->prepare("INSERT INTO `support_msg` (`tid`, `msg`, `uid`, `time`) VALUES (:tid, :msg, :uid, unix_timestamp(now()))");
	$query->bindParam(':tid', $tid, PDO::PARAM_STR);
	$query->bindParam(':msg', $msg, PDO::PARAM_STR);
	$query->bindParam(':uid', $uid, PDO::PARAM_STR);
	$query->execute();
	$lastID = $db->lastInsertId();
	if($tiket['uid'] == $uid){
			telegram_send("Заявка №[$tid]\nНовый ответ от клиента.\nhttps://".$_SERVER['SERVER_NAME']."/pages/tiket.php?id=$tid");
	}else{
		$query = $db->prepare("SELECT * FROM `users` WHERE `id` = :id");
		$query->bindParam(':id', $tiket['uid'], PDO::PARAM_STR);
		$query->execute();
		$row = $query->fetch();
		_mail($row['login'], "[Lepus Support] Заявка №[$tid]", "Новое сообщение от службы технической поддержки.<br/>Ваш тикет доступен в личном кабинете  <a href=\"https://".$_SERVER['SERVER_NAME']."/pages/tiket.php?id=$tid\">по ссылке</a>.<br/><br/>Это сообщение отправлено автоматически. Пожалуйста, не отвечайте на него.<br/>------------------------<br/>Lepus Support<br/><a href=\"http://lepus.su\">https://lepus.su</a>");
	}
	return ['tid' => $tid, 'msgID' => $lastID];
}

function parse_bb_code($text) {
	$find = [
		'~\[b\](.*?)\[/b\]~s',
		'~\[i\](.*?)\[/i\]~s',
		'~\[u\](.*?)\[/u\]~s',
		'~\[s\](.*?)\[/s\]~s',
		'~\[url\]((?:http|https?)://.*?)\[/url\]~s',
		'~\[url=((?:http|https?)://.*?)\](.*?)\[/url\]~s'
		//'~\[quote\](.*?)\[/quote\]~s',
		//'~\[size=(.*?)\](.*?)\[/size\]~s',
		//'~\[color=(.*?)\](.*?)\[/color\]~s',
		//'~\[img\](https?://.*?\.(?:jpg|jpeg|gif|png|bmp))\[/img\]~s'
	];
	$replace = [
		'<b>$1</b>',
		'<i>$1</i>',
		'<u>$1</u>',
		'<s>$1</s>',
		'<a href="$1" target="_blank">$1</a>',
		'<a href="$1" target="_blank">$2</a>',
		//'<span style="text-decoration:underline;">$1</span>',
		//'<pre>$1</'.'pre>',
		//'<span style="font-size:$1px;">$2</span>',
		//'<span style="color:$1;">$2</span>',
		//'<img src="$1" alt="" />'
	];
	return preg_replace($find, $replace, $text);
}

function lepus_error_page($mes){
	return "<html><head><title>Lepus info page</title><meta http-equiv=\"refresh\" content=\"5;url=https://".$_SERVER['SERVER_NAME']."\"><style>.boxInfo{width: 80%;max-width: 600px;margin: 2em auto;padding: 1em;box-shadow: 0 0 10px 5px rgba(221, 221, 221, 1);word-wrap: break-word;}</style><head><body><div class=\"boxInfo\">$mes<br/>Через 5 секунд вы будете перенаправлены на главную страницу сайта.</div></body></html>";
}

function lepus_change_phone($num, $user){
	if(empty($num)) return 'empty_post_value';
	if(!ctype_digit($num)) return 'only_numeric';
	if(strlen($num) > 15) return 'wrong_phone';
	$user['data']['phone'] = $num;
	save_user_data($user['id'], $user['data']);
	return 'Мы сохранили ваш новый номер телефона';
}

function lepus_update_balance($pid, $uid, $amount, $system){
	global $db; $uid = intval($uid); $amount = intval($amount);
	if($system != 'lepus'){
		$query = $db->prepare("SELECT * FROM `log_income` WHERE `payment_id` =:pid AND `system` = :system");
		$query->bindParam(':pid', $pid, PDO::PARAM_STR);
		$query->bindParam(':system', $system, PDO::PARAM_STR);
		$query->execute();
		if($query->rowCount() == 1) return 'already';
	}
	if($system == 'bitcoin'){
		$select = $db->prepare("SELECT * FROM `users` WHERE `bitcoin` =:address");
		$select->bindParam(':address', $uid, PDO::PARAM_STR);
		$select->execute();
		if($select_query->rowCount() != 1) continue;
		$row = $select->fetch();
		$uid = $row['id'];
	}
	$query = $db->prepare("SELECT * FROM `users` WHERE `id` = :uid");
	$query->bindParam(':uid', $uid, PDO::PARAM_STR);
	$query->execute();
	if($query->rowCount() != 1) return 'no_user';
	$row = $query->fetch();
	$tmp['data'] = json_decode($row['data'], true);
	$query = $db->prepare("INSERT INTO `log_income` (`payment_id`, `user_id`, `amount`, `system`, `time`) VALUES (:pid, :uid, :amount, :system, unix_timestamp(now()))");
	$query->bindParam(':pid', $pid, PDO::PARAM_STR);
	$query->bindParam(':uid', $uid, PDO::PARAM_STR);
	$query->bindParam(':amount', $amount, PDO::PARAM_STR);
	$query->bindParam(':system', $system, PDO::PARAM_STR);
	$query->execute();
	$tmp['data']['balance'] += $amount;
	save_user_data($row['id'], $tmp['data']);
	if($system != 'lepus'){
		_mail($row['login'], "Пополнение счета", "Дорогой клиент,<br/>ваш баланс увеличен на $amount RUR.<br/>Благодарим за оплату.");
	}else{
		_mail($row['login'], "Возврат денежных средств", "Дорогой клиент,<br/>ваш баланс увеличен на $amount RUR.<br/>$pid");
	}
	lepus_AutoExtend($row['id']);
	return "OK$pid\n";
}

function lepus_getLogIncome($uid, $i = 0){
	global $db; $data = null;
	$query = $db->prepare("SELECT * FROM `log_income` WHERE `user_id` = :uid");
	$query->bindParam(':uid', $uid, PDO::PARAM_STR);
	$query->execute();
	while($row = $query->fetch()){
		if(strlen($row['payment_id']) > 28){
			$tmpPID = $row['payment_id'];	
			$row['payment_id'] = mb_substr($row['payment_id'], 0, 28,'utf-8')."...";
		}
		if($row['system'] == 'bitcoin'){
			$row['payment_id'] = "<a href=\"https://blockchain.info/tx/$tmpPID\" target=\"_blank\">{$row['payment_id']}</a>";
		}
		$row['time'] = date("[Y-m-d] h:i:s", $row['time']);
		$i++; $data .= "<tr><td>$i</td><td>{$row['system']}</td><td>{$row['payment_id']}</td><td> {$row['amount']} </td><td>{$row['time']}</td></tr>";
	}
	return $data;
}

function lepus_validCron($time, $url){
	if(strlen($url) > 128) return 'not_valid';
	if(preg_match('/[^0-9a-zA-Z.=_\&\-\?\:\/]/', $url)) return 'not_valid'; // only 0-9a-zA-Z.=_&-?:/
	$arr = explode(" ", $time);
	if(count($arr) != 5) return 'not_valid';
	foreach($arr as $val){
		if(strlen($val) > 4) return 'not_valid';
		if(preg_match('/[^0-9\*\/]/', $val)) return 'not_valid';
	}
	return 'valid';
}

function lepus_getCronList($uid){
	global $db; $data = null;
	$query = $db->prepare("SELECT * FROM `cron` WHERE `uid` = :uid");
	$query->bindParam(':uid', $uid, PDO::PARAM_STR);
	$query->execute();
	while($row = $query->fetch()){
		$tmpURL = '';
		if(strlen($row['url']) > 50){
			$tmpURL = $row['url'];	
			$row['url'] = mb_substr($row['url'], 0, 50,'utf-8')."...";
		}
		$data .= "<tr><td>{$row['id']}</td><td>{$row['time']}</td><td><lu title=\"$tmpURL\">{$row['url']}</lu></td><td><a href=\"nourl\" data-cron-task-id=\"{$row['id']}\"><i class=\"glyphicon glyphicon-remove\"></i></a></td></tr>";
	}
	return $data;
}

function lepus_getCronAccess($id, $uid){
	global $db;
	if(empty($id) || empty($uid)) return 'empty_post_value';
	$query = $db->prepare("SELECT * FROM `cron` WHERE `id` = :id AND `uid` = :uid");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->bindParam(':uid', $uid, PDO::PARAM_STR);
	$query->execute();
	return $query->rowCount();
}

function lepus_addCron($uid, $time, $url, $do, $id = 0){
	global $db;
	switch($do){
		default: return 'wrong_action'; break;
		case 'remove':
			if(empty($id)) return 'empty_post_value';
			if(lepus_getCronAccess($id, $uid) != 1) return 'no_access';
			$query = $db->prepare("DELETE FROM `cron` WHERE `id` = :id");
			$query->bindParam(':id', $id, PDO::PARAM_STR);
			$query->execute();
			$data = 'Мы удалили это задание';			
		break;
		case 'add':
			if(empty($time) || empty($url)) return 'empty_post_value';
			if(lepus_validCron($time, $url) != 'valid') return 'not_valid';
			$query = $db->prepare("SELECT * FROM `cron` WHERE `uid` = :uid");
			$query->bindParam(':uid', $uid, PDO::PARAM_STR);
			$query->execute();
			if($query->rowCount() > 100) return 'max_limit';
			$query = $db->prepare("INSERT INTO `cron` (`uid`, `time`, `url`, `date`) VALUES (:uid, :time, :url, unix_timestamp(now()))");
			$query->bindParam(':uid', $uid, PDO::PARAM_STR);
			$query->bindParam(':time', $time, PDO::PARAM_STR);
			$query->bindParam(':url', $url, PDO::PARAM_STR);
			$query->execute();
			$lastId = $db->lastInsertId();
			$tmpURL = '';
			if(strlen($url) > 50){
				$tmpURL = $url;	
				$url = mb_substr($url, 0, 50,'utf-8')."...";
			}
			$data = ['a' => $time, 'b' => "<lu title=\"$tmpURL\">$url</lu>", 'c' => "<a href=\"nourl\" data-cron-task-id=\"$lastId\"><i class=\"glyphicon glyphicon-remove\"></i></a>", 'd' => $lastId];
		break;
	}
	return $data;
}

function telegram_send($msg){
	global $conf;
	file_get_contents("https://api.telegram.org/{$conf['telegram']}/sendMessage?chat_id=160138276&text=".urlencode($msg));
}

function admin_lepus_getIPlist(){
	global $db; $data = null;
	$query = $db->prepare("SELECT * FROM `ipmanager`");
	$query->execute();
	while($row = $query->fetch()){
		$tmpQuery = $db->prepare("SELECT * FROM `users` WHERE `id` =:id");
		$tmpQuery->bindParam(':id', $row['owner'], PDO::PARAM_STR);
		$tmpQuery->execute();
		$tmpRow = $tmpQuery->fetch();
		$row['owner'] = $tmpRow['login'];
		if(strlen($row['owner']) > 16)
			$row['owner'] = mb_substr($row['owner'], 0, 16,'utf-8')."...";
		if(!empty($row['sid'])){
			$tmpQuery = $db->prepare("SELECT * FROM `servers` WHERE `id` =:id");
			$tmpQuery->bindParam(':id', $row['sid'], PDO::PARAM_STR);
			$tmpQuery->execute();
			$tmpRow = $tmpQuery->fetch();
			$row['sid'] = $tmpRow['domain'];
		}else{
			$row['sid'] = 'empty';
		}
		$row['ip'] = long2ip($row['ip']);
		$data .= "<tr><td>{$row['id']}</td><td>{$row['ip']}</td><td>{$row['sid']}</td><td>{$row['service']}</td><td>{$row['owner']}</td><td>{$row['mac']}</td><td>{$row['domain']}</td><td><a href=\"nourl\" data-adminip-delete-id=\"{$row['id']}\"><i class=\"glyphicon glyphicon-remove\"></i></a></td></tr>";
	}
	return $data;
}

function lepus_getHTMLSelect($table, $column){
	global $db; $data = null;
	$query = $db->prepare("SELECT * FROM `$table`");
	$query->execute();
	while($row=$query->fetch()){
		$data .= "<option value=\"{$row['id']}\">{$row[$column]}</option>";
	}
	return $data;
}

function lepus_admin_addIP($ip, $mac, $host, $server, $user){
	global $db;
	$query = $db->prepare("SELECT * FROM `ipmanager` WHERE `ip` = :ip");
	$query->bindParam(':ip', $ip, PDO::PARAM_STR);
	$query->execute();
	if($query->rowCount() > 0) return "already_get_it";
	$query = $db->prepare("INSERT INTO `ipmanager` (`ip`, `sid`, `service`, `owner`, `mac`, `domain`) VALUES (:ip, :server, 0, :user, :mac, :host)");
	$query->bindParam(':ip', $ip, PDO::PARAM_STR);
	$query->bindParam(':server', $server, PDO::PARAM_STR);
	$query->bindParam(':user', $user, PDO::PARAM_STR);
	$query->bindParam(':mac', $mac, PDO::PARAM_STR);
	$query->bindParam(':host', $host, PDO::PARAM_STR);
	$query->execute();
	$lastID = $db->lastInsertId();
	return ['a' => $lastID, 'b' => "<a href=\"nourl\" data-adminip-delete-id=\"$lastID\"><i class=\"glyphicon glyphicon-remove\"></i></a>"];
}

function lepus_admin_removeIP($id){
	global $db;
	if(empty($id)) return 'empty_post_value';
	$query = $db->prepare("DELETE FROM `ipmanager` WHERE `id` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	return 'OK';
}

function is_login($j = TRUE){
	global $user; if(empty($user)) $j = FALSE; return $j;
}

function lepus_getTariffList($id = null){
	global $db; $data = null;
	if($id === null){
		$query = $db->prepare("SELECT * FROM `tariff` WHERE `status` = 1 ORDER BY `name`");
	}else{
		$query = $db->prepare("SELECT * FROM `tariff` WHERE `id` = :id");
		$query->bindParam(':id', $id, PDO::PARAM_STR);
		$query->execute();
		$row = $query->fetch();
		
		$query = $db->prepare("SELECT * FROM `tariff` WHERE `gid` = :gid AND `id` != :id AND `status` != 0 ORDER BY `point`");
		$query->bindParam(':gid', $row['gid'], PDO::PARAM_STR);
		$query->bindParam(':id', $id, PDO::PARAM_STR);
	}
	$query->execute();
	while($row=$query->fetch()){
		$data .= "<option value=\"{$row["id"]}\">{$row["name"]} - ".lepus_price($row["price"], $row["currency"])." рублей</option>";
	}
	return $data;
}

function lepus_getTariffPrices($g){
	global $db; $data = null;
	$query = $db->prepare("SELECT * FROM `tariff` WHERE `gid` = :gid AND `status` = 1 ORDER BY `point`");
	$query->bindParam(':gid', $g, PDO::PARAM_STR);
	$query->execute();
	while($row = $query->fetch()){
		$data .= '<th>'.lepus_price($row["price"], $row["currency"]).'</th>';
	}
	return $data;
}

function lepus_getTariffPrice($id){
	global $db;
	$query = $db->prepare("SELECT * FROM `tariff` WHERE `id` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	$row = $query->fetch();
	return lepus_price($row["price"], $row["currency"]);
}

function lepus_price($val, $currency){
	switch($currency){
		case 'EUR':
		case 'EUR1':
			$val = $val*90;
		break;
		case 'EUR2':
			$val = $val*80;
		break;
	}
	return round($val);
}

function lepus_order_preview($sid, $promo = 0){
	global $db; $discont = 0;
	$query = $db->prepare("SELECT * FROM `tariff` WHERE `id` = :id");
	$query->bindParam(':id', $sid, PDO::PARAM_STR);
	$query->execute();
	if($query->rowCount() != 1) return 'no_service';
	$row = $query->fetch();
	$price = lepus_price($row["price"], $row["currency"]);
	if(is_numeric($promo)){
		$discont = $price-($price*$promo/100);
		$price = $price*$promo/100;
	}
	return ['name' => $row["name"], 'price' => round($price), 'discont' => round($discont), 'handler' => $row['handler']];
}

function lepus_check_discount($promo, $sid){
	global $db, $user; $discont = 0;
	$query = $db->prepare("SELECT * FROM `discounts` WHERE `name` = :name");
	$query->bindParam(':name', $promo, PDO::PARAM_STR);
	$query->execute();
	if($query->rowCount() != 1) return 'no_promo';
	$row = $query->fetch();
	$data = json_decode($row['data'], true);
	$allow = explode(",", $data['tariff']);
	if(!in_array($sid, $allow)) return 'no_promo';
	switch($data['handler']){
		case 'only_new':
			if(time() > $user['data']['regDate']+60*60*24*7) return 'old_promo';
			$select = $db->prepare("SELECT * FROM `services` WHERE `uid` = :id");
			$select->bindParam(':id', $user['id'], PDO::PARAM_STR);
			$select->execute();
			if($select->rowCount() != 0) return 'no_promo';
			$discont = $data['percent'];
		break;
	}
	return $discont;
}

function lepus_create_order($sid, $promo = 0, $os){
	global $db, $user; $os_info['full'] ='';
	$info = lepus_order_preview($sid, lepus_check_discount($promo, $sid));
	if(!is_array($info)) return $info; 
	if($info['price'] > $user['data']['balance']) return 'no_money';
	$user['data']['balance'] -= $info['price'];
	save_user_data($user['id'], $user['data']);
	$time1 = time(); $time2 = strtotime("+1 month", $time1);
	$query = $db->prepare("INSERT INTO `services` (`sid`, `uid`, `time1`, `time2`) VALUES (:sid, :uid, :time1, :time2)");
	$query->bindParam(':sid', $sid, PDO::PARAM_STR);
	$query->bindParam(':uid', $user['id'], PDO::PARAM_STR);
	$query->bindParam(':time1', $time1, PDO::PARAM_STR);
	$query->bindParam(':time2', $time2, PDO::PARAM_STR);
	$query->execute();
	$order_id = $db->lastInsertId();
	lepus_log_spend($user['id'], $order_id, $time1, $time2, $info['price'], "{$info['name']} [заказ]");
	if(!ctype_digit($os) && $os != 'no') $os = 'no';
	if($os > 0 && $os < 4) $os_info = lepus_osType($os, 'full');
	if($os == 'no'){
		$_POST['msg'] = "Дорогой клиент, благодарим за оплату.\nКак только ваш заказ будет готов - мы свяжемся с вами в этом тикете.";
	}else{
		$_POST['msg'] = "Дорогой клиент, благодарим за оплату.\nКак только ваш заказ будет готов - мы свяжемся с вами в этом тикете.\nДополнение к заказу: операционная система {$os_info['full']}";
	}
	$tmpData = support_create($user['id'], $info['name'], 2);
	support_msg(5, $tmpData, 2, 1);
	telegram_send("Заявка №[$tmpData]\nКлиент сделал новый заказ.\nhttps://".$_SERVER['SERVER_NAME']."/pages/tiket.php?id=$tmpData");
	lepus_addTask($user['id'], $info['handler'], ['do' => 'create', 'tiket' => $tmpData, 'tariff' => $sid, 'email' => $user['login'], 'order' => $order_id, 'os' => $os_info['name']]);
	return $tmpData;
}

function lepus_log_spend($uid, $oid, $time1, $time2, $money, $info){
	global $db;
	$query = $db->prepare("INSERT INTO `log_spend` (`uid`, `oid`, `time1`, `time2`, `money`, `info`) VALUES (:uid, :oid, :time1, :time2, :money, :info)");
	$query->bindParam(':uid', $uid, PDO::PARAM_STR);
	$query->bindParam(':oid', $oid, PDO::PARAM_STR);
	$query->bindParam(':time1', $time1, PDO::PARAM_STR);
	$query->bindParam(':time2', $time2, PDO::PARAM_STR);
	$query->bindParam(':money', $money, PDO::PARAM_STR);
	$query->bindParam(':info', $info, PDO::PARAM_STR);
	$query->execute();
}

function lepus_getLogSpend($id, $i = 0){
	global $db; $data = null;
	$query = $db->prepare("SELECT * FROM `log_spend` WHERE `uid` = :uid");
	$query->bindParam(':uid', $id, PDO::PARAM_STR);
	$query->execute();
	while($row=$query->fetch()){
		$row['time1'] = date("Y-m-d", $row['time1']);
		$row['time2'] = date("Y-m-d", $row['time2']);
		$i++; $data .= "<tr><td>$i</td><td>{$row['time1']}</td><td>{$row['info']} [{$row['oid']}]</td><td>{$row['money']}</td><td>{$row['time2']}</td></tr>";
	}
	return $data;
}

function lepus_getPageNavi(){
	$navi = ''; $pages = ['/' => 'Главная', '/pages/ovz.php' => 'Хостинг', '/pages/vps.php' => 'VPS', '/pages/servers.php' => 'Серверы', '/pages/domains.php' => 'Домены', '/pages/license.php' => 'Лицензии', '/pages/doc.php' => 'Документы', '/pages/contacts.php' => 'Контакты'];
	foreach($pages as $key => $val){
		if($_SERVER["REQUEST_URI"] == $key)
			$navi .= "<li class=\"active\"><a href=\"$key\">$val</a></li>";
		else
			$navi .= "<li><a href=\"$key\">$val</a></li>";
	}
	return $navi;
}

function lepus_getListServices($sid, $uid){
	global $db; $data = null;
	$query = $db->prepare("SELECT * FROM `services` WHERE `uid` = :uid");
	$query->bindParam(':uid', $uid, PDO::PARAM_STR);
	$query->execute();
	while($row=$query->fetch()){
		$tmpQuery = $db->prepare("SELECT * FROM `tariff` WHERE `id` =:sid");
		$tmpQuery->bindParam(':sid', $row['sid'], PDO::PARAM_STR);
		$tmpQuery->execute();
		$tmpRow = $tmpQuery->fetch();
		if($tmpRow['gid'] != $sid && $sid != 'all') continue;
		$row['time2'] = date("Y-m-d", $row['time2']);
		if($row['auto'] != 1)
			$i = '<input class="btn btn-danger btn-xs" style="width: 30px;" data-autoextend-id='.$row['id'].' value="off">';
		else
			$i = '<input class="btn btn-success btn-xs" style="width: 30px;" data-autoextend-id='.$row['id'].' value="on">';
		$arr = json_decode($row['data'], true);
		$price = lepus_price($tmpRow['price'], $tmpRow['currency'])+lepus_price($arr['extra'], $arr['extra_currency']);
		$data .= "<tr><td><a href='/pages/view.php?id={$row['id']}'>{$row['id']}</a></td><td>{$tmpRow['name']}</td><td>$price</td><td>{$row['time2']}</td><td>$i</td></tr>";
	}
	return $data;
}

function lepus_adminGetListServices(){
	global $db; $data = null; $all = 0;
	$query = $db->prepare("SELECT * FROM `services`");
	$query->execute();
	while($row=$query->fetch()){
		$tmpQuery = $db->prepare("SELECT * FROM `users` WHERE `id` =:uid");
		$tmpQuery->bindParam(':uid', $row['uid'], PDO::PARAM_STR);
		$tmpQuery->execute();
		$tmpRow = $tmpQuery->fetch();
		$email = $tmpRow['login'];
		if(strlen($email) > 15)
			$email = mb_substr($email, 0, 15,'utf-8')."...";
		$tmpQuery = $db->prepare("SELECT * FROM `tariff` WHERE `id` =:sid");
		$tmpQuery->bindParam(':sid', $row['sid'], PDO::PARAM_STR);
		$tmpQuery->execute();
		$tmpRow = $tmpQuery->fetch();
		if(time() > $row['time2']) $status = 'timeout'; else $status = 'paid';
		$row['time2'] = date("Y-m-d", $row['time2']);
		$arr = json_decode($row['data'], true);
		$price = lepus_price($tmpRow['price'], $tmpRow['currency'])+lepus_price($arr['extra'], $arr['extra_currency']);
		$data .= "<tr><td>{$row['id']}</td><td>{$tmpRow['name']}</td><td>{$email}</td><td>$price</td><td>{$row['time2']}</td><td>{$status}</td><td><a href=\"nourl\" data-move-archive-id=\"{$row['id']}\"><i class=\"glyphicon glyphicon-remove\"></i></a></td></tr>";
		$all += $price;
	}
	return ['table' => $data, 'all' => $all];
}

function lepus_getServiceAccess($id){
	global $db, $user; $row = 'no_access';
	$query = $db->prepare("SELECT * FROM `services` WHERE `id` = :id AND `uid` = :uid");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->bindParam(':uid', $user['id'], PDO::PARAM_STR);
	$query->execute();
	if($query->rowCount() == 1) $row = $query->fetch();
	return $row;
}

function lepus_changeAutoExtend($id){
	global $db;
	$row = lepus_getServiceAccess($id);
	if(!is_array($row)) return $row;
	if($row['auto'] == 1) $row['auto'] = 0;
		else $row['auto'] = 1;
	$query = $db->prepare("UPDATE `services` SET `auto` = :i WHERE `id` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->bindParam(':i', $row['auto'], PDO::PARAM_STR);
	$query->execute();
}

function lepus_AutoExtend($uid = 0){
	global $db;
	$time = time()+60*60*24*3;
	if($uid == 0){
		$query = $db->prepare("SELECT * FROM `services` WHERE `time2` < :time");
	}else{
		$query = $db->prepare("SELECT * FROM `services` WHERE `time2` < :time AND `uid` = :uid");
		$query->bindParam(':uid', $uid, PDO::PARAM_STR);
	}
	$query->bindParam(':time', $time, PDO::PARAM_STR);
	$query->execute();
	while($row=$query->fetch()){
		$tmpQuery = $db->prepare("SELECT * FROM `users` WHERE `id` = :uid");
		$tmpQuery->bindParam(':uid', $row['uid'], PDO::PARAM_STR);
		$tmpQuery->execute();
		$tmpRow = $tmpQuery->fetch();
		$user['id'] = $tmpRow['id'];
		$user['login'] = $tmpRow['login'];
		$user['data'] = json_decode($tmpRow['data'], true);
		$tmpQuery = $db->prepare("SELECT * FROM `tariff` WHERE `id` = :sid");
		$tmpQuery->bindParam(':sid', $row['sid'], PDO::PARAM_STR);
		$tmpQuery->execute();
		$tmpRow = $tmpQuery->fetch();
		$arr = json_decode($row['data'], true);
		$price = lepus_price($tmpRow['price'], $tmpRow['currency'])+lepus_price($arr['extra'], $arr['extra_currency']);
		switch($tmpRow['handler']){
			case 'ISPmanagerV4':
				$toTask['order'] = $row['id'];
				$toTask['user'] = $arr['user'];
			break;
			case 'VH':
			case 'KVM':
			case 'OpenVZ':
				$toTask['order'] = $row['id'];
			break;
		}
		if(!empty($toTask) && time() > $row['time2']+60*60*24){
			if($user['data']['balance'] >= $price && $row['auto'] == 1){
				$toTask['do'] = 'start';
			}else{
				if(time() > $row['time3'] && $uid == 0) $toTask['do'] = 'stop';
			}
			if(!empty($toTask['do']))
				lepus_addTask(0, $tmpRow['handler'], $toTask);
		}
		unset($toTask);
		if($user['data']['balance'] < $price || $row['auto'] != 1){
			_mail($user['login'], "Автоматическое продление #{$row['id']}", "Дорогой клиент, мы не смогли продлить услугу {$tmpRow['name']} #{$row['id']}<br/>Так как вы или выключили автопродление или на вашем счете недостаточно средств.");
		}else{
			$row['time1'] = $row['time2'];
			$row['time2'] = strtotime("+1 month", $row['time2']);
			$user['data']['balance'] -= $price;
			save_user_data($user['id'], $user['data']);
			lepus_log_spend($user['id'], $row['id'], $row['time1'], $row['time2'], $price, "{$tmpRow['name']} [продление]");
			_mail($user['login'], "Автоматическое продление #{$row['id']}", "Дорогой клиент, услуга {$tmpRow["name"]} #{$row['id']} оплачена до ".date("Y-m-d", $row['time2'])."<br/>Расход: $price, остаток: {$user['data']['balance']} рублей");
			$tmpQuery = $db->prepare("UPDATE `services` SET `time2` = :time2 WHERE `id` = :id");
			$tmpQuery->bindParam(':id', $row['id'], PDO::PARAM_STR);
			$tmpQuery->bindParam(':time2', $row['time2'], PDO::PARAM_STR);
			$tmpQuery->execute();
		}
	}
}

function lepus_getService($id){
	global $db; $top = null; $bottom = null;
	$row = lepus_getServiceAccess($id);
	if(!is_array($row)) return $row;
	$tmpQuery = $db->prepare("SELECT * FROM `tariff` WHERE `id` = :id");
	$tmpQuery->bindParam(':id', $row['sid'], PDO::PARAM_STR);
	$tmpQuery->execute();
	$tmpRow = $tmpQuery->fetch();
	$arr = lepus_getExtra($id);
	$price = lepus_price($tmpRow['price'], $tmpRow['currency'])+lepus_price($arr['extra'], $arr['extra_currency']);
	switch($tmpRow['handler']){
		case 'ISPmanagerV4':
			if($row['server'] != 0){
				$select = $db->prepare("SELECT * FROM `servers` WHERE `id` =:id");
				$select->bindParam(':id', $row['server'], PDO::PARAM_STR);
				$select->execute();
				$tmp = $select->fetch();
				$data = json_decode($row['data'], true);
				$top = "<br/><a href=\"https://{$tmp['domain']}\" target=\"_blank\">Панель управления</a> виртуальным хостингом.<br/>Пользователь <u>{$data['user']}</u> [<a href=\"https://{$tmp['domain']}/ispmgr?func=recovery\" target=\"_blank\">восстановить пароль</a>].";
				$bottom = null;
			}
		break;
		case 'VH':
		case 'KVM':
		case 'OpenVZ':
			if($row['server'] != 0){
				$bottom = "<input class=\"btn btn-sm btn-danger btn-block\" style=\"margin-top: 4px;\" data-vm-restart={$id} type=\"submit\" value=\"Перезагрузить\">
						   <hr/><table id=\"IPList\" class=\"table table-striped table-bordered\" cellspacing=\"0\" width=\"100%\"><thead><tr><th>ID</th><th>IP</th><th>Domain</th><th>MAC</th></tr></thead>".lepus_getListIP($id)."<tbody></tbody></table>";
			}
		break;
		case 'OVH-DEDIC':
		case 'HETZNER-DEDIC':
			$bottom = "<hr/><table id=\"IPList\" class=\"table table-striped table-bordered\" cellspacing=\"0\" width=\"100%\"><thead><tr><th>ID</th><th>IP</th><th>Domain</th><th>MAC</th></tr></thead>".lepus_getListIP($id)."<tbody></tbody></table>";
		break;
	}
	return ['id' => $row['id'], 'gid' => $tmpRow['gid'], 'sid' => $row['sid'], 'name' => $tmpRow['name'], 'time' => date("Y-m-d", $row['time2']), 'price' => $price, 'extra' => $arr['extra_text'], 'top' => $top, 'bottom' => $bottom];
}

function lepus_moneyback($id, $sid){
	global $db, $user;
	$query = $db->prepare("SELECT * FROM `log_spend` WHERE `oid` = :id AND `time2` > unix_timestamp(now())");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	if($query->rowCount() != 1) return 'no_moneyback';
	$row = $query->fetch();
	$tmpQuery = $db->prepare("SELECT * FROM `services` WHERE `id` = :id");
	$tmpQuery->bindParam(':id', $id, PDO::PARAM_STR);
	$tmpQuery->execute();
	$tmpRow = $tmpQuery->fetch();
	$old_tariff_id = $tmpRow['sid'];
	if($sid == $old_tariff_id) return 'already_tariff';
	$tmpQuery = $db->prepare("SELECT * FROM `tariff` WHERE `id` = :id");
	$tmpQuery->bindParam(':id', $old_tariff_id, PDO::PARAM_STR);
	$tmpQuery->execute();
	$tmpRow = $tmpQuery->fetch();
	$old_tariff_poins = $tmpRow['point'];
	if($tmpRow['gid'] == 3 || $tmpRow['gid'] == 4) return 'no_gid_change_tariff';	
	$time_moneyback = ($row['time2'] - time())/(60*60*24);
	$day = $row['money']/(($row['time2']-strtotime("-1 month", $row['time2']))/(60*60*24));
	$moneyback = floor($day*$time_moneyback);
	$money_use = $row['money'] - $moneyback;
	$log_id = $row['id'];
	$query = $db->prepare("SELECT * FROM `tariff` WHERE `id` = :sid");
	$query->bindParam(':sid', $sid, PDO::PARAM_STR);
	$query->execute();
	if($query->rowCount() != 1) return 'wrong_tariff';
	$row = $query->fetch();
	if($tmpRow['gid'] == '2' && $old_tariff_poins > $row['point']) return 'cant_tariff_lower';
	if($tmpRow['handler'] != $tmpRow['handler']) return 'different_handler';
	$arr = lepus_getExtra($id);
	$pay = lepus_price($row["price"], $row["currency"])+lepus_price($arr['extra'], $arr['extra_currency']);
	$total = $user['data']['balance'] + $moneyback - $pay;	
	return ['moneyback' => $moneyback, 'pay' => $pay, 'total' => $total, 'log_id' => $log_id, 'use' => $money_use, 'name' => $row['name'], 'status' => $row['status'], 'status2' => $tmpRow['status']];
}

function lepus_changeTariff_preview($id, $sid){
	global $db, $user; $data = null; $show = 1;
	$info = lepus_getServiceAccess($id);
	if(!is_array($info)) return $info;
	$info = lepus_moneyback($id, $sid);
	if(!is_array($info)) return $info;	
	if($info['status'] != 0 && $info['status2']){
		$data .= "Возврат средств => {$info['moneyback']}, к оплате => {$user['data']['balance']} + {$info['moneyback']} - {$info['pay']}, остаток на счете => {$info['total']} рублей.";
		if($info['total'] < 0){
			$data .="<br/><font color='red'>Для смены тарифа, пожалуйста, пополните счет на ".abs($info['total'])." рублей.</font>";
			$show = '0';
		}
	}else{
			$data .="<br/><font color='red'>Вы используете устаревший тариф.<br/>Чтобы перейти на новый => пожалуйста, обратитесь в техподдержку.</font>";
			$show = '0';
	}
	return ['text' => "<center>$data</center>", 'show' => $show];
}

function lepus_changeTariff($id, $sid){
	global $db, $user; $data = null; $toTask = null;
	$data = lepus_getServiceAccess($id);	
	if(!is_array($data)) return $data;
	$info = lepus_moneyback($id, $sid);
	if(!is_array($info)) return $info;
	if($info['status'] == 0) die("error");
	if($info['total'] < 0)
		return "<center><font color='red'>Для смены тарифа, пожалуйста, пополните счет на ".abs($info['total'])." рублей.</font></center>";
	$query = $db->prepare("UPDATE `log_spend` SET `time2` = unix_timestamp(now()), `money` = :money WHERE `id` = :id");
	$query->bindParam(':id', $info['log_id'], PDO::PARAM_STR);
	$query->bindParam(':money', $info['use'], PDO::PARAM_STR);
	$query->execute();
	lepus_update_balance("moneyback service #$id", $user['id'], $info['moneyback'], 'lepus');
	$user['data']['balance'] = $info['total'];
	save_user_data($user['id'], $user['data']);
	$time2 = time()+60*60*24*30;
	$query = $db->prepare("UPDATE `services` SET `time2` =:time, `sid` =:sid WHERE `id` =:id");
	$query->bindParam(':time', $time2, PDO::PARAM_STR);
	$query->bindParam(':sid', $sid, PDO::PARAM_STR);
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	lepus_log_spend($user['id'], $id, time(), $time2, $info['pay'], "{$info['name']} [изменение]");
	$query = $db->prepare("SELECT * FROM `tariff` WHERE `id` = :id");
	$query->bindParam(':id', $data['sid'], PDO::PARAM_STR);
	$query->execute();
	$row = $query->fetch();
	$arr = json_decode($data['data'], true);
	switch($row['handler']){
		case 'ISPmanagerV4':
			$toTask = ['do' => 'change', 'tariff' => $sid, 'user' => $arr['user'], 'email' => $user['login'], 'order' => $data['id']];
		break;
		case 'VH':
		case 'KVM':
			$query = $db->prepare("SELECT * FROM `tariff` WHERE `id` = :id");
			$query->bindParam(':id', $sid, PDO::PARAM_STR);
			$query->execute();
			$tmp = $query->fetch();
			$x = json_decode($tmp['data'], true);
			$toTask = ['do' => 'change', 'memory' => $x['memory'], 'cpus' => $x['cpus'], 'diskspace' => $x['diskspace'], 'order' => $data['id']];
		break;
	}
	if(!empty($toTask))
		lepus_addTask($user['id'], $row['handler'], $toTask);
	return "OK";
}

function lepus_getArchiveList($id = null){
	global $db, $user; $data = null; $i = 0;
	if(empty($id)){
		if($user['data']['access'] < 2){
			$query = $db->prepare("SELECT * FROM `archive` WHERE `uid` =:uid");
			$query->bindParam(':uid', $user['id'], PDO::PARAM_STR);
		}else{
			$query = $db->prepare("SELECT * FROM `archive`");
		}
	}else{
		if($user['data']['access'] < 2){
			$query = $db->prepare("SELECT * FROM `archive` WHERE `oid` =:id AND `uid` =:uid");
			$query->bindParam(':uid', $user['id'], PDO::PARAM_STR);
		}else{
			$query = $db->prepare("SELECT * FROM `archive` WHERE `oid` =:id");
		}
		$query->bindParam(':id', $id, PDO::PARAM_STR);
	}
	$query->execute();
	if(empty($id)){
		while($row=$query->fetch()){
			$row['time1'] = date("Y-m-d", $row['time1']);
			$row['time2'] = date("Y-m-d", $row['time2']);
			$tmpQuery = $db->prepare("SELECT * FROM `tariff` WHERE `id` =:id");
			$tmpQuery->bindParam(':id', $row['sid'], PDO::PARAM_STR);
			$tmpQuery->execute();
			$tmpRow=$tmpQuery->fetch();
			$name = $tmpRow['name'];
			$tmpQuery = $db->prepare("SELECT SUM(money) FROM `log_spend` WHERE `oid` = :oid");
			$tmpQuery->bindParam(':oid', $row['oid'], PDO::PARAM_STR);
			$tmpQuery->execute();
			$tmpRow=$tmpQuery->fetch();
			$all = $tmpRow['SUM(money)'];
			$i++; $data .= "<tr><td>$i</td><td>$name [{$row['oid']}]</td><td>{$row['time1']}</td><td>{$row['time2']}</td><td>$all RUR</td><td><a href='#' data-archive-show={$row['oid']}><i class='glyphicon glyphicon-paperclip'></i></a></td></tr>";
		}
	}else{
		if($query->rowCount() != 1) return 'no_access';
		$row = $query->fetch();
		$arr = json_decode($row['data'], true);
		foreach($arr as $key => $value){
			$data .= "$key: $value<br/>";
		}
	}
	return $data;
}

function lepus_moveToArchive($id){
	global $db; $ips = null;
	$query = $db->prepare("SELECT * FROM `services` WHERE `id` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	if($query->rowCount() != 1) return 'no_info';
	$row = $query->fetch();
	if($row['time2'] > time()-60*60*24*7) return 'no_time';
	if($row['time3'] > time()-60*60*24*2) return 'no_time';
	$getips = $db->prepare("SELECT * FROM `ipmanager` WHERE `service` = :id");
	$getips->bindParam(':id', $id, PDO::PARAM_STR);
	$getips->execute();
	while($row2 = $getips->fetch()){
		$ips .= long2ip($row2['ip']).", ";
	}
	if(!empty($ips)){
		$row['data'] = json_decode($row['data'], true);
		$row['data']['ips'] = substr($ips, 0, -2);
		$row['data'] = json_encode($row['data']);
	}
	$query = $db->prepare("INSERT INTO `archive` (`oid`, `sid`, `uid`, `time1`, `time2`, `data`) VALUES (:oid, :sid, :uid, :time1, :time2, :data)");
	$query->bindParam(':oid', $row['id'], PDO::PARAM_STR);
	$query->bindParam(':sid', $row['sid'], PDO::PARAM_STR);
	$query->bindParam(':uid', $row['uid'], PDO::PARAM_STR);
	$query->bindParam(':time1', $row['time1'], PDO::PARAM_STR);
	$query->bindParam(':time2', $row['time2'], PDO::PARAM_STR);
	$query->bindParam(':data', $row['data'], PDO::PARAM_STR);
	$query->execute();
	$query = $db->prepare("DELETE FROM `services` WHERE `id` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	$query = $db->prepare("UPDATE `ipmanager` SET `owner` = 5, `service` = 0 WHERE `service` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	$query = $db->prepare("SELECT * FROM `users` WHERE `id` = :id");
	$query->bindParam(':id', $row['uid'], PDO::PARAM_STR);
	$query->execute();
	$row = $query->fetch();
	_mail($row['login'], "Перенос в архив # $id", "Дорогой клиент, через семь дней, неоплаченные попадают в архив.<br/>
						Вы  не продлили услугу # $id => данные удалены, <a href=\"https://lepus.su/pages/archive.php\">услуга перенесена в архив</a>.<br/>
						Если вы хотите восстановить услугу - напишите в техническую поддержку. И возможно мы поможем восстановить ваши данные.");				
	return 'OK';
}

function lepus_getExtra($id){
	global $db;
	$query = $db->prepare("SELECT * FROM `services` WHERE `id` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	$row = $query->fetch();
	return json_decode($row['data'], true);
}

function lepus_getPayLink($system, $val, $uid){
	global $conf;
	$val = intval($val);
	$pay_desc = base64_encode('Пополнение счета');
	if(empty($val) || empty($system)) return 'empty_post_value';
	if($val > 50000) return 'Максимальная сумма 50000 рублей.';
	if($system != 'paypal' && $system != 'paymaster' && $system != 'robokassa' && $system != 'webmoney' && $system != 'unitpay') $system = 'paypal';
	$i = "<center>Вы хотите пополнить счет на сумму <font color=green>$val</font> рублей через $system?</center>";
	switch($system){
		case 'paypal':
			$i .= "<center>
				<form method=\"post\" action= \"http://www.paypal.com/cgi-bin/webscr\" id=\"gotopay\"> 
				<input type=\"hidden\" name=\"cmd\" value=\"_xclick\"> 
				<input type=\"hidden\" name=\"business\" value=\"poiuty@lepus.su\"> 
				<input type=\"hidden\" name=\"item_name\" value=\"Account replenishment\"> 
				<input type=\"hidden\" name=\"custom\" value=\"$uid\">
				<input type=\"hidden\" name=\"amount\" value=\"$val \"> 
				<input type=\"hidden\" name=\"return\" value=\"http://lepus.su\"> 
				<input type=\"hidden\" name=\"cancel_return\" value=\"https://lepus.su\">
				<input type=\"hidden\" name=\"currency_code\" value=\"RUB\"> 
				<input type=submit value=\"Перейти на страницу оплаты\" class=\"btn btn-danger\" style=\"width: 100%; margin-top: 2px;\"> 
				</form>
				<script>//document.getElementById(\"gotopay\").submit()</script>
				</center>";
				if($val < 100) $i = "Минимальная сумма пополнения счета через PayPal - 100 рублей.";
		break;
		case 'robokassa':
			$crc  = md5("{$conf['robokassa_login']}:$val:0:{$conf['robokassa_pass1']}:shp_uid=$uid");
			$i .= "<center>Вы хотите пополнить счет на сумму <font color=green>$val</font> рублей?<form action='https://merchant.roboxchange.com/Index.aspx' method=POST id=\"gotopay\">".
				"<input type=hidden name=MrchLogin value={$conf['robokassa_login']}>".
				"<input type=hidden name=OutSum value=$val>".
				"<input type=hidden name=InvId value=0>".
				"<input type=hidden name=Desc value='Пополнение счета'>".
				"<input type=hidden name=SignatureValue value=$crc>".
				"<input type=hidden name=shp_uid value='$uid'>".
				"<input type=hidden name=IncCurrLabel value=WMR>".
				"<input type=hidden name=Culture value=ru>".
				"<input type=submit value='Подтверждаю, перейти на страницу оплаты' class='btn btn-danger'>".
				"</form>
				<script>//document.getElementById(\"gotopay\").submit()</script>
				</center>";
		break;
		case 'paymaster':
			$i .= "<center>".
				"<form action='https://paymaster.ru/Payment/Init' method=POST id=\"gotopay\">".
				"<input type=hidden name=LMI_MERCHANT_ID value=720f46c1-c6fa-4047-977c-f76396f2b3ce>".
				"<input type=hidden name=LMI_PAYMENT_AMOUNT value=$val>".
				"<input type=hidden name=LMI_CURRENCY value=RUR>".
				"<input type=hidden name=LMI_PAYMENT_DESC_BASE64 value='$pay_desc'>".
				"<input type=hidden name=LEPUS_USER value='$uid'>".
				"<input type=submit value='Подтверждаю, перейти на страницу оплаты' class='btn btn-danger'>".
				"</form>
				<script>//document.getElementById(\"gotopay\").submit()</script>
				</center>";
		break;
		case 'webmoney':
			$i .= "<center>".
				"<form action='https://merchant.webmoney.ru/lmi/payment.asp' method=POST>".
				"<input type=hidden name=LMI_PAYMENT_AMOUNT value=$val>".
				"<input type=hidden name=LMI_PAYMENT_DESC_BASE64 value='$pay_desc'>".
				"<input type=hidden name=LMI_PAYEE_PURSE value=R000000000000>".
				"<input type=hidden name=LEPUS_USER value='$uid'>".
				"<input type=submit value='Подтверждаю, перейти на страницу оплаты' class='btn btn-danger'>".
				"</form></center>";
		break;
		case 'unitpay':
			$i .= "<center>".
				"<form action='https://unitpay.ru/pay/{$conf['unitpay_public']}'>
				<input type=hidden name=account value=$uid>
				<input type=hidden name=sum value=$val>
				<input type=hidden name=desc value='[Lepus Hosting] пополнение счета'>
				<input type=submit value='Подтверждаю, перейти на страницу оплаты' class='btn btn-danger'>
				</form></center>";
		break;
	}
	return $i;
}

function lepus_addTask($uid, $handler, $data){
	global $db;
	$data = json_encode($data);
	if($uid != 0){
		$query = $db->prepare("SELECT * FROM `task` WHERE `uid` = :uid AND `handler` = :handler AND `data` = :data AND `status` IN (0,1)");
		$query->bindParam(':uid', $uid, PDO::PARAM_STR);
		$query->bindParam(':handler', $handler, PDO::PARAM_STR);
		$query->bindParam(':data', $data, PDO::PARAM_STR);
		$query->execute();
		if($query->rowCount() != 0) return 'task_already';
	}
	$query = $db->prepare("INSERT INTO `task` (`uid`, `handler`, `data`) VALUES (:uid, :handler, :data)");
	$query->bindParam(':uid', $uid, PDO::PARAM_STR);
	$query->bindParam(':handler', $handler, PDO::PARAM_STR);
	$query->bindParam(':data', $data, PDO::PARAM_STR);
	$query->execute();
}

function lepus_doTask(){
	global $db; $err = null;
	$query = $db->prepare("SELECT * FROM `task` WHERE `status` = '1'");
	$query->execute();
	if($query->rowCount() > 0) return 'progress...';
	$query = $db->prepare("SELECT * FROM `task` WHERE `status` = '0' LIMIT 1");
	$query->execute();
	$row = $query->fetch();
	if($query->rowCount() == 0) return 'no_task';
	$data = json_decode($row['data'], true);
	$update = $db->prepare("UPDATE `task` SET `status` = 1 WHERE `id` = :id");
	$update->bindParam(':id', $row['id'], PDO::PARAM_STR);
	$update->execute();
	if($data['do'] == 'create'){
		if(empty($data['tiket']) || empty($data['tariff']) || empty($data['order'])) $err = 'wrong data params';
	}
	if($row['handler'] == 'ISPmanagerV4' || $row['handler'] == 'OpenVZ' || $row['handler'] == 'KVM' || $row['handler'] = 'VH'){
		if($data['do'] == 'create'){
			$server = lepus_searchFree($row['handler'], $data['tariff'], $data['order']);
		}else{
			$server = lepus_searchFree($row['handler'], 0, $data['order']);
		}
		if(!is_array($server)) $err = 'no_free_server';
		if(empty($err) && $data['do'] == 'create'){
			$update = $db->prepare("UPDATE `services` SET `server` = :sid WHERE `id` = :id");
			$update->bindParam(':sid', $server['id'], PDO::PARAM_STR);
			$update->bindParam(':id', $data['order'], PDO::PARAM_STR);
			$update->execute();
			if($row['handler'] == 'KVM' || $row['handler'] == 'VH'){
				$update = $db->prepare("UPDATE `ipmanager` SET `service` = :service WHERE `ip` = :ip");
				$update->bindParam(':service', $data['order'], PDO::PARAM_STR);
				$update->bindParam(':ip', $server['ipvm'], PDO::PARAM_STR);
				$update->execute();
				$tmp = $db->prepare("SELECT * FROM `users` WHERE `login` = :login");
				$tmp->bindParam(':login', $data['email'], PDO::PARAM_STR);
				$tmp->execute();
				$v = $tmp->fetch();
				$tmp = $db->prepare("UPDATE `ipmanager` SET `owner` = :owner WHERE `ip` = :ip");
				$tmp->bindParam(':owner', $v['id'], PDO::PARAM_STR);
				$tmp->bindParam(':ip', $server['ipvm'], PDO::PARAM_STR);
				$tmp->execute();
			}
		}
	}
	if(empty($err)){
		switch($row['handler']){
			default: $info = 'no_handler'; break;
			case 'ISPmanagerV4':
				$presets = [1 => 'basic', 2 => 'standart', 3 => 'pro', 4 => 'super', 5 => 'vip1', 6 => 'vip2', 7 => 'vip3', 8 => 'vip4'];
				$commands = ['create' => 'createUser', 'stop' => 'blockUser', 'start' => 'unblockUser', 'change' => 'changeService'];
				$disks = [1 => 1000, 2 => 2500, 3 => 4000, 4 => 6000, 5 => 10000, 6 => 12500, 7 => 15000, 8 => 20000];		
				if(!empty($data['tariff'])){
					$preset = $presets[$data['tariff']];
					$disk = $disks[$data['tariff']];
				}
				switch($commands[$data['do']]){
					default: $info = 'no_action'; break;
					case 'createUser': // params: email, preset, ip, login, password
						$login = mb_strtolower(genRandStr(7));
						$passwd = genRandStr(9);
						$info = lepus_sendToPythonAPI($server['ip'], $server['port'], $server['access'], $commands[$data['do']], "{$data['email']}/{$preset}/{$server['ip']}/{$login}/{$passwd}", $row['id']);
						$xml = simplexml_load_string($info);
						if(empty($xml->error['code']) && !empty($info)){
							lepus_editServiceData($data['order'], 'edit', 'user', $login);
							$_POST['msg'] = "Дорогой клиент, виртуальный хостинг готов.\nLogin: $login\nPassword: $passwd\nПожалуйста, поменяйте пароль.\nВы можете посмотреть более подробную информацию об услуге [url=https://lepus.su/pages/view.php?id={$data['order']}]на этой странице[/url].";
							support_msg(5, $data['tiket'], 2, 1);
						}
					break;
					case 'changeService': // params: login, preset, email, disk
						$info = lepus_sendToPythonAPI($server['ip'], $server['port'], $server['access'], $commands[$data['do']], "{$data['user']}/{$preset}/{$data['email']}/{$disk}", $row['id']);
					break;
					case 'blockUser': // params: user
					case 'unblockUser':
						$info = lepus_sendToPythonAPI($server['ip'], $server['port'], $server['access'], $commands[$data['do']], $data['user'], $row['id']);
					break;
				}
			break;
			case 'VH':
			case 'KVM':
			case 'OpenVZ':
				$commands = ['stop' => 'stopServer', 'start' => 'startServer', 'restart' => 'restartServer', 'create' => 'createServer', 'change' => 'changeTariff'];
				switch($commands[$data['do']]){
					default: $info = 'no_action'; break;
					case 'changeTariff':
						if($row['handler'] == 'VH' || $row['handler'] == 'KVM'){
							$info = send_changeTariff($row['id'], $commands[$data['do']], $server['ip'], $server['access'], $data['order']+100, "memory={$data['memory']}&cpus={$data['cpus']}&diskspace={$data['diskspace']}");
						}
					break;
					case 'startServer':
					case 'stopServer':
					case 'restartServer':
						if($row['handler'] == 'OpenVZ'){
							$info = lepus_sendToPythonAPI($server['ip'], $server['port'], $server['access'], $commands[$data['do']], $data['order']+100, $row['id']);
						}
						if($row['handler'] == 'KVM' || $row['handler'] == 'VH'){
							$info = send_kvm($row['id'], $commands[$data['do']], $server['ip'], $server['access'], $data['order']+100);
						}
					break;
					case 'createServer':
						$info = send_kvm($row['id'], 'getStatus', $server['ip'], $server['access'], $data['order']+100);
						var_dump($info);
						if($info == 'running'){
							if($row['handler'] == 'KVM'){
								$s = 'VPS готова';
								$s1 = 'root';
							}
							if($row['handler'] == 'VH'){
								$s = "виртуальный хостинг готов";
								$s1 = 'root или lepus [доступ от SFTP/ FTP/ SSH/ MySQL]';
							}
							$_POST['msg'] = "Дорогой клиент, $s.\nLogin: $s1\nPassword: {$data['passwd']}\nПожалуйста, поменяйте пароль.\nВы можете посмотреть более подробную информацию об услуге [url=https://lepus.su/pages/view.php?id={$data['order']}]на этой странице[/url].";
							support_msg(5, $data['tiket'], 2, 1);
						}else{
							$update = $db->prepare("UPDATE `task` SET `status` = 0 WHERE `id` = :id");
							$update->bindParam(':id', $row['id'], PDO::PARAM_STR);
							$update->execute();
							return;
						}
					break;
				}
			break;
		}
		$query = $db->prepare("UPDATE `task` SET `info` = :info, `status` = 2 WHERE `id` = :id");
		$query->bindParam(':info', $info, PDO::PARAM_STR);
		$query->bindParam(':id', $row['id'], PDO::PARAM_STR);
		$query->execute();
	}
}

function lepus_editServiceData($id, $do, $key, $val){
	global $db;
	$query = $db->prepare("SELECT * FROM `services` WHERE `id` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	$row = $query->fetch();
	$arr = json_decode($row['data'], true);
	if($do == 'remove')
		unset($arr[$key]);
	else	
		$arr[$key] = $val;
	$json = json_encode($arr);
	$query = $db->prepare("UPDATE `services` SET `data` = :data WHERE `id` = :id");
	$query->bindParam(':data', $json, PDO::PARAM_STR);
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
}

function lepus_searchFree($handler, $tariff, $id){
	global $db; $server = null; $need = null;
	$query = $db->prepare("SELECT * FROM `services` WHERE `id` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	$row = $query->fetch();
	if($row['server'] != 0){
		$query = $db->prepare("SELECT * FROM `servers` WHERE `id` = :id");
		$query->bindParam(':id', $row['server'], PDO::PARAM_STR);
		$query->execute();
		$row = $query->fetch();
		if($handler == 'KVM'){
			$query = $db->prepare("SELECT * FROM `ipmanager` WHERE `service` = :id");
			$query->bindParam(':id', $row['server'], PDO::PARAM_STR);
			$query->execute();
			$tmpData = $query->fetch();
			return ['id' => $row['id'], 'ip' => long2ip($row['ip']), 'port' => $row['port'], 'access' => $row['access'], 'ipvm' => $tmpData['ip']];
		}
		return ['id' => $row['id'], 'ip' => long2ip($row['ip']), 'port' => $row['port'], 'access' => $row['access']];
	}
	if(!empty($tariff)){
		$query = $db->prepare("SELECT * FROM `tariff` WHERE `handler` = :handler");
		$query->bindParam(':handler', $handler, PDO::PARAM_STR);
		$query->execute();
		while($row = $query->fetch()){
			if($tariff == $row['id']) $need = $row['point'];
			$data[$row['id']] = $row['point'];
		}	
		$query = $db->prepare("SELECT * FROM `servers` WHERE `handler` = :handler AND `status` = '1'");
		$query->bindParam(':handler', $handler, PDO::PARAM_STR);
		$query->execute();
		while($row = $query->fetch()){
			$points = 0;
			$tmp = $db->prepare("SELECT * FROM `services` WHERE `server` = :server");
			$tmp->bindParam(':server', $row['id'], PDO::PARAM_STR);
			$tmp->execute();
			while($tmpRow = $tmp->fetch()){
				$points += $data[$tmpRow['sid']];
			}
			if($points+$need < $row['points']){
				if($handler == 'KVM' || $handler == 'VH'){
					$select_ip = $db->prepare("SELECT * FROM `ipmanager` WHERE `sid` = :server AND `service` = 0 LIMIT 1");
					$select_ip->bindParam(':server', $row['id'], PDO::PARAM_STR);
					$select_ip->execute();
					if($select_ip->rowCount() == 0) continue;
					$x = $select_ip->fetch();
				}
				$j = $points+$need;
				$server = ['id' => $row['id'], 'ip' => long2ip($row['ip']), 'port' => $row['port'], 'access' => $row['access'], 'points' => $j];
				if($handler == 'KVM' || $handler == 'VH'){
					$server['ipvm'] = $x['ip'];
				}
				break;
			}
		}
	}
	return $server;
}

function send_changeTariff($cid, $command, $host, $key, $id, $get){
	return file_get_contents("http://$host/index.php?id=$id&command=$command&key=$key&$get");
}

function send_kvm($cid, $command, $host, $key, $id){
	return file_get_contents("http://$host/index.php?id=$id&command=$command&key=$key");
}

function lepus_sendToPythonAPI($host, $port, $access, $action, $data, $id){
	return file_get_contents("http://$host:$port/".md5($action.$access)."/$action/$data");
}

function lepus_getListIP($id){
	global $db; $data = null; $i = 0;
	$query = $db->prepare("SELECT * FROM `ipmanager` WHERE `service` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	while($row = $query->fetch()){
		$row['ip'] = long2ip($row['ip']);
		$i++; $data .= "<tr><td>$i</td> <td>{$row['ip']}</td> <td>{$row['domain']}</td> <td>{$row['mac']}</td> </tr>";
	}
	return $data;
}

function lepus_userAddTask($id, $command){
	global $db, $user; $i = 'error_task';
	$info = lepus_getServiceAccess($id);
	if(!is_array($info)) return $info;
	if(time() > $info['time2']) return 'not_paid';
	$query = $db->prepare("SELECT * FROM `tariff` WHERE `id` = :id");
	$query->bindParam(':id', $info['sid'], PDO::PARAM_STR);
	$query->execute();
	$row = $query->fetch();
	switch($row['handler']){
		case 'VH':
		case 'KVM':
		case 'OpenVZ':
			if(time() < $info['time1']+60*60) return 'wait_60min'; // time to install vps
			if($command == 'restart'){
				$j = lepus_addTask($user['id'], $row['handler'], ['do' => $command, 'order' => $id]);
				if(!empty($j)) return $j;
				$i = 'Мы скоро перезагрузим VM';
			}
		break;
	}
	return $i;
}

function lepus_admin_getMoneyLog($time = 'day'){
	global $db; $data = array();
	if($time != 'day')
		$query = $db->prepare("SELECT date(from_unixtime(time)) as stat_day, sum(amount) from `log_income` WHERE `time` >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 YEAR)) GROUP BY month(from_unixtime(time)),year(from_unixtime(time)) ORDER by stat_day");
	else
		$query = $db->prepare("SELECT date(from_unixtime(time)) as stat_day, sum(amount) from `log_income` WHERE `time` >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH)) GROUP BY date(from_unixtime(time)) ORDER by stat_day");
	$query->execute();
	while($row = $query->fetch()){
		$row['stat_day'] = strtotime($row['stat_day']) * 1000;
		$data[] = [$row['stat_day'], intval($row['sum(amount)'])];
	}
	return $data;
}

function IsTorExitPoint(){
	global $cache;
	$i = $cache->get($_SERVER['REMOTE_ADDR']);
	if($i === FALSE){
		if(gethostbyname(ReverseIPOctets($_SERVER['REMOTE_ADDR']).".".$_SERVER['SERVER_PORT'].".".ReverseIPOctets($_SERVER['SERVER_ADDR']).'.ip-port.exitlist.torproject.org') == '127.0.0.2') $i = 1; else $i = 2;
		$cache->set($_SERVER['REMOTE_ADDR'], $i, MEMCACHE_COMPRESSED, 0);
	}
	if($i == 1) return true; else return false;
}

function ReverseIPOctets($inputip){
		$ipoc = explode(".",$inputip);
		return $ipoc[3].".".$ipoc[2].".".$ipoc[1].".".$ipoc[0];
}

function lepus_changeAPIkey(){
	global $db, $user;
	$a = genRandStr(32);
	$b = substr_replace($a, str_repeat('*', 20), -20, -6);
	$query = $db->prepare("UPDATE `users` SET `api` = :api WHERE `id` = :id");
	$query->bindParam(':api', $a, PDO::PARAM_STR);
	$query->bindParam(':id', $user['id'], PDO::PARAM_STR);
	$query->execute();
	return "<center><a href=# data-change-api-key><i class='glyphicon glyphicon-refresh'></i></a> API: <a data-show-api=$a>$b</a></center>";
}

function lepus_closeTikets(){
	global $db; $time = time()-60*60*24*7;
	$query = $db->prepare("UPDATE `support` SET `status` = '2' WHERE `open` < :time AND `last` < :time");
	$query->bindParam(':time', $time, PDO::PARAM_STR);
	$query->execute();
}

function lepus_getBillprice($id, $period, $j = 0){
	global $conf, $cache;
	$price = $cache->get("billprice.$id.$period");
	if($price === FALSE || $j == 1){
		$ctx = stream_context_create(['http'=> ['timeout' => 30]]);
		$arr = json_decode(@file_get_contents("https://my.lepus.su/billmgr?authinfo={$conf['billmgr_user']}:{$conf['billmgr_pass']}&out=json&func=pricelist.export&onlyavailable=on&pricelist=$id", false, $ctx), true);
		$price = $arr["doc"]["pricelist"]["price"]["period"][$period]['$cost'];
		$cache->set("billprice.$id.$period", $price, MEMCACHE_COMPRESSED, 3600);
	}
	return intval($price);
}

function lepus_getServStat(){
	global $db; $i = null;
	$query = $db->prepare("SELECT * FROM `servers`");
	$query->execute();
	while($row=$query->fetch()){
		$percent = $percent2 = $points = 0;
		$row['ip'] = long2ip($row['ip']);
		$tmp_query = $db->prepare("SELECT count(*) FROM `ipmanager` WHERE `sid` = :id");
		$tmp_query->bindParam(':id', $row['id'], PDO::PARAM_STR);
		$tmp_query->execute();
		$ips = $tmp_query->fetchColumn();
		$tmp_query = $db->prepare("SELECT count(*) FROM `ipmanager` WHERE `sid` = :id AND `service` != 0");
		$tmp_query->bindParam(':id', $row['id'], PDO::PARAM_STR);
		$tmp_query->execute();
		$use = $tmp_query->fetchColumn();
		$tmp_query = $db->prepare("SELECT * FROM `services` WHERE `server` = :id");
		$tmp_query->bindParam(':id', $row['id'], PDO::PARAM_STR);
		$tmp_query->execute();
		while($tmp_row=$tmp_query->fetch()){
			$tmp2_query = $db->prepare("SELECT * FROM `tariff` WHERE `id` = :id");
			$tmp2_query->bindParam(':id', $tmp_row['sid'], PDO::PARAM_STR);
			$tmp2_query->execute();
			$tmp2_row = $tmp2_query->fetch();
			$points += $tmp2_row['point'];
		}
		$percent = round($points*100/$row['points']);
		if($ips == 0) $percent2 = 100;
			else $percent2 =  round($use*100/$ips);
		if($percent < 25) $width = 25; else $width = $percent;
		if($percent2 < 25) $width2 = 25; else $width2 = $percent2;
		$i .= "<tr><td>{$row['id']}</td><td>{$row['ip']}</td><td>{$row['domain']}</td><td><div class=\"progress\"><div class=\"progress-bar\" role=\"progressbar\" style=\"width:$width%\">$percent%</div></div></td><td><div class=\"progress\"><div class=\"progress-bar\" role=\"progressbar\" style=\"width:$width2%\">$percent2%</div></div></td><td>{$row['status']}</td></tr>";
	}
	return $i;
}

function lepus_osType($id){
	$arr = [1 => ['name' => 'debian', 'version' => 7, 'full' => 'Debian 7'],
			2 => ['name' => 'ubuntu', 'version' => '14.04', 'full' => 'Ubuntu 14.04'],
			3 => ['name' => 'centos', 'version' => 7, 'full' => 'CentOS 7']];
	if($id == 0) return $arr;
	return $arr[$id];
}
