<?php
function rehash($passwd, $hash = 0){	
	if(empty($hash) || password_needs_rehash($hash, PASSWORD_DEFAULT))
		return password_hash($passwd, PASSWORD_DEFAULT);
	else
		return 'no_hash';
}

function _exit(){
	global $db;
	$query = $db->prepare("UPDATE `users` SET `session` = :null where `session` = :sess");
	$query->bindValue(':null', null, PDO::PARAM_INT);
	$query->bindParam(':sess', $_SESSION["sess"], PDO::PARAM_STR);
	$query->execute();
	
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
	session_unset();
	session_destroy();
	header("Location: https://lepus.dev");
}

function is_lepus_user($login){
	global $db;
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
	_mail($_POST['email'], "Забыли пароль?", "Дорогой клиент,<br/>после того как вы перейдете <a href=\"http://lepus.dev/public/lost_passwd.php?hash=".urlencode(lepus_crypt(json_encode($arr)))."\">по этой ссылке</a> - вы получите второе письмо с паролем от вашего аккаунта.<br/>");
	return 'Мы отправили письмо с инструкцией на ваш email';
}

function login($login, $passwd){
	global $db;
	if(empty($login) || empty($passwd)) return 'empty_post_value';
	if(!filter_var($login, FILTER_VALIDATE_EMAIL)) return 'bad_email';
	$is_user = is_lepus_user($login);
	if($is_user['0'] != 1) return 'no_user';
	$row = $is_user['1'];
	if (password_verify($passwd, $row['passwd'])){
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
		return 'enter';
	} else return 'bad_passwd';
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
	return ["id" => $row['id'], "login" => $row['login'], "passwd" => $row['passwd'], "data" => $row['data']];
}

function error($message, $j = 0){
	if(!is_array($message)){
		$err = [
			"no_auth" => "Неудачная попытка входа",
			"no_user" => "Неправильный логин",
			"bad_passwd" => "Неправильный пароль",
			"block_user" => "Пользователь заблокирован",
			"empty_message" => "Пустое сообщение",
			"no_access" => "Нет доступа",
			"close_tiket" => "Тикет уже закрыт",
			"already_open" => "Тикет уже открыт",
			"empty_post_value" => "Пустой POST",
			"bad_email" => "Неправильный email",
			"user_exist" => "Такой пользователь уже существует",
			"captcha_fail" => "Проверка на бота не пройдена",
			"wrong_hash" => "Неправильный hash",
			"lost_passwd_time" => "Ссылка устарела, для восстановления пароля - получите новую ссылку",
			"no_auth_page" => "У вас нет доступа к этой странице",
			"wrong_phone" => "Неправильный телефонный номер",
			"only_numeric" => "Только цифры"
		];
		if (array_key_exists($message, $err)) $j = 1;
	}
	if($j == 1) $message = ['mes' => $err[$message], 'err' => $message];
	else $message = ['mes' => $message, 'err' => 'OK'];
	return $message;
}

function save_user_data($id, $data){
	global $db;
	$data = json_encode($data);
	$query = $db->prepare("UPDATE `users` SET `data` = :data WHERE `id` = :id");
	$query->bindParam(':data', $data, PDO::PARAM_STR);
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	return '1';
}

function _mail($email, $subject, $message){
	$headers  = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=utf-8\r\n";
	$subject  = "=?utf-8?B?".base64_encode($subject)."?=";
	$headers .= "From: Lepus Artifical Intelligence <support@lepus.su>\r\n";
	mail($email, $subject, $message, $headers);
}

function genRandStr($length){
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';	
	for ($i = 0; $i < $length; $i++)
		$randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
	return $randomString;
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

function lepus_crypt($input, $do = 'encode', $key = 'Jml*Zwde4a#%ix$m'){
	$algo = MCRYPT_RIJNDAEL_256;
	$mode = MCRYPT_MODE_CBC;
	$iv_size = mcrypt_get_iv_size($algo, $mode);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
	switch($do){
		case 'encode':	
		$ciphertext = mcrypt_encrypt($algo, $key, $input, $mode, $iv);
		$ciphertext = $iv . $ciphertext;
		$result = base64_encode($ciphertext);
		break;
			
		case 'decode':
		$ciphertext_dec = base64_decode($input);
		$iv_dec = substr($ciphertext_dec, 0, $iv_size);
		$ciphertext_dec = substr($ciphertext_dec, $iv_size);
		$result = mcrypt_decrypt($algo, $key, $ciphertext_dec, $mode, $iv_dec);
		break;
	}
	return $result;
}

function lepus_new_account($login){
	global $db;
	if(empty($login)) return 'empty_post_value';
	if(!filter_var($login, FILTER_VALIDATE_EMAIL)) return 'bad_email';
	$is_user = is_lepus_user($login);
	if($is_user['0'] != 0) return 'user_exist';
	$passwd = genRandStr(8);
	$data = ['balance' => 0, 'phone' => NULL, 'regDate' => time(), 'access' => 1, 'lastIP' => NULL, 'apiKey' => genRandStr(32)];
	$json = json_encode($data);
	$query = $db->prepare("INSERT INTO `users` (`login`, `passwd`, `data`) VALUES (:login, :passwd, :data)");
	$query->bindParam(':login', $login, PDO::PARAM_STR);
	$query->bindParam(':passwd', rehash($passwd), PDO::PARAM_STR);
	$query->bindParam(':data', $json, PDO::PARAM_STR);
	$query->execute();
	_mail($login, "Регистрация нового аккаунта", "Дорогой клиент, ваш аккаунт готов.<br/>Ваш логин: $login<br/>Ваш пароль: $passwd<br/>Для активации, пожалуйста, авторизуйтесь на нашем сайте.<br/>В противном случае аккаунт будет автоматически удален через 7 дней.");
	return 'Мы отправили пароль на ваш email';
}

function lepus_log_ip($id, $ip){
	global $db; $info = get_browser(null, true);
	if(preg_match('/[^0-9A-Za-z.]/', $info['platform'])) $info['platform'] = "unknown";
	if(preg_match('/[^0-9A-Za-z.]/', $info['browser'])) $info['browser'] = "unknown";
	$query = $db->prepare("INSERT INTO `log_ip` (`uid`, `ip`, `platform`, `browser`, `time`) VALUES (:id, :ip, :platform, :browser, :time)");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->bindParam(':ip', $ip, PDO::PARAM_STR);
	$query->bindParam(':platform', $info['platform'], PDO::PARAM_STR);
	$query->bindParam(':browser', $info['browser'], PDO::PARAM_STR);
	$query->bindParam(':time', time(), PDO::PARAM_STR);
	$query->execute();
}

function lepus_get_logip($id, $i = 0){
	global $db;
	$query = $db->prepare("SELECT * FROM `log_ip` WHERE `uid` = :id");
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
	if($query->rowCount() == 0) return "no_data";
	while($row = $query->fetch()){
		$i++; $data .= "<tr><td>$i</td><td>".long2ip($row['ip'])."</td><td><img src=\"/images/flags16/".mb_strtolower(geoip_country_code_by_name('136.243.79.123')).".png\" style=\"margin-bottom:-3px;\"> ".geoip_country_name_by_name('136.243.79.123')."</td><td>".$row['platform']."</td><td>".$row['browser']."</td><td>".date('Y-m-d H:i', $row['time'])."</td></tr>";
	}
	return $data;
}

function lepus_addDNSDomain($domain, $type, $master, $id){
	global $pdns;
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
	global $pdns;
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
	global $pdns;
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
	$query = $db->prepare("SELECT * FROM `support_msg` WHERE `tid` = :tid ORDER BY `time` DESC LIMIT 1"); //
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
	global $db;
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
		if(strlen($row['title']) > 25){
			$tmpTitle = "title='{$row['title']}'";
			$row['title'] = mb_substr($row['title'], 0, 23,'utf-8')."...";	 
		}
		if($id == 0){
			$data .= "<tr><td><a href=\"/pages/tiket.php?id={$row['id']}\" title=\"Открыть\">".$row['id']."</a></td><td $tmpTitle>".$row['title']."</td><td>".$row['open']."</td><td>".$row['last']."</td><td style=\"padding-top: 11px;\"><span class=\"label label-pill label-".$ldata['label']." myLabel\">".$ldata['info']."</span></td></tr>";
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

function support_create($uid, $title, $msg){
	global $db;
	if(empty(trim($_POST['title'])) || empty(trim($_POST['msg']))) return('empty_post_value');
	$title = filter_var($_POST["title"], FILTER_SANITIZE_STRING);	
	$msg = nl2br(htmlentities($_POST["msg"], ENT_QUOTES, 'UTF-8'));
	$query = $db->prepare("INSERT INTO `support` (`uid`, `title`, `open`, `status`) VALUES (:uid, :title, :open, 1)");
	$query->bindParam(':uid', $uid, PDO::PARAM_STR);
	$query->bindParam(':title', $title, PDO::PARAM_STR);
	$query->bindParam(':open', time(), PDO::PARAM_STR);
	$query->execute();
	return $db->lastInsertId();
}

function lepus_get_supportAccess($tid){
	global $db;
	$query = $db->prepare("SELECT * FROM `support` WHERE `id` = :id");
	$query->bindParam(':id', $tid, PDO::PARAM_STR);
	$query->execute();
	return $query->fetch();
}

function lepus_get_supportMsg($tid, $uid, $access, $msgID = 0, $update = 0, $data = '', $j = 0){
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
			$who = "Ответ службы поддержи";
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
	if(strlen($_POST['msg']) < 1) return "empty_message";
	if($access > 1 && $_POST['msg'] != 'END' && $_POST['msg'] != 'OPEN') $_POST['msg'] .= "\n\n\n[i]С уважением, команда технической поддержки.[/i]";
	if($tiket['status'] == 2 && $_POST['msg'] != 'OPEN') return 'close_tiket'; // if tiket close => we need first open it
	if($tiket['status'] == 1 && $_POST['msg'] == 'OPEN') return 'already_open'; // dont open - open tiket
	$msg = parse_bb_code(nl2br(htmlentities($_POST['msg'], ENT_QUOTES, 'UTF-8')));
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
		$query = $db->prepare("UPDATE `support` SET `last` = :time WHERE `id` = :tid");
		$query->bindParam(':time', time(), PDO::PARAM_STR);
		$query->bindParam(':tid', $tid, PDO::PARAM_STR);
		$query->execute();
	}
	$query = $db->prepare("INSERT INTO `support_msg` (`tid`, `msg`, `uid`, `time`) VALUES (:tid, :msg, :uid, :time)");
	$query->bindParam(':tid', $tid, PDO::PARAM_STR);
	$query->bindParam(':msg', $msg, PDO::PARAM_STR);
	$query->bindParam(':uid', $uid, PDO::PARAM_STR);
	$query->bindParam(':time', time(), PDO::PARAM_STR);
	$query->execute();
	return ['tid' => $tid, 'msgID' => $db->lastInsertId()];
}

function parse_bb_code($text){
	$text = preg_replace('/\[(\/?)(b|i|u|s)\s*\]/', "<$1$2>", $text);
	$text = preg_replace('/\[url\](?:http:\/\/)?(.+)\[\/url\]/', "<a href=\"http://$1\" target=\"_blank\">$1</a>", $text);
	$text = preg_replace('/\[url\s?=\s?([\'"]?)(?:http:\/\/)?(.+)\1\](.*?)\[\/url\]/', "<a href=\"http://$2\" target=\"_blank\">$3</a>", $text);
	$text = preg_replace('/\[urls\](?:https:\/\/)?(.+)\[\/urls\]/', "<a href=\"https://$1\" target=\"_blank\">$1</a>", $text);
	$text = preg_replace('/\[urls\s?=\s?([\'"]?)(?:https:\/\/)?(.+)\1\](.*?)\[\/urls\]/', "<a href=\"https://$2\" target=\"_blank\">$3</a>", $text);
	return $text;
}

function lepus_error_page($mes){
	return "<html><head><title>Lepus info page</title><meta http-equiv=\"refresh\" content=\"5;url=https://lepus.dev\"><style>.boxInfo{width: 80%;max-width: 600px;margin: 2em auto;padding: 1em;box-shadow: 0 0 10px 5px rgba(221, 221, 221, 1);word-wrap: break-word;}</style><head><body><div class=\"boxInfo\">$mes<br/>Через 5 секунд вы будете перенаправлены на главную страницу сайта.</div></body></html>";
}

function lepus_change_phone($num, $user){
	if(empty($num)) return 'empty_post_value';
	if(!ctype_digit($num)) return 'only_numeric';
	if(strlen($num) > 15) return 'wrong_phone';
	$user['data']['phone'] = $num;
	save_user_data($user['id'], $user['data']);
	return 'Мы сохранили ваш новый номер телефона';
}
