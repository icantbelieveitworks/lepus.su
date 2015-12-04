<?php
function rehash($passwd, $hash){
	if (password_needs_rehash($hash, PASSWORD_DEFAULT))
		return password_hash($passwd, PASSWORD_DEFAULT);
	else
		return 'no_hash';
}

function _exit(){
	session_unset();
	session_destroy();
	header("Location: http://lepus.dev");
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
	$row = $is_user['1'];
	$real_hash = hash('sha512' ,$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].$row['passwd'].$row['login']);
	if($data[1] != $real_hash) return 'wrong_hash';
		else return ['id' => $row['id'], 'email' => $data[0]];
}

function lost_passwd($login){
	$is_user = is_lepus_user($login);
	if($is_user['0'] != 1) return 'no_user';
	$row = $is_user['1'];
	return hash('sha512' ,$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].$row['passwd'].$row['login']);
}

function login($login, $passwd){
	global $db;
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
			"no_auth" => "Неудачная попытка входа.",
			"no_user" => "Неправильный логин.",
			"bad_passwd" => "Неправильный пароль.",
			"block_user" => "Пользователь заблокирован"
		];
		if (array_key_exists($message, $err)) $j = 1;
	}
	
	if($j == 1){
		$message = ['mess' => $message, 'err' => $err[$message]];
	}else{
		$message = ['mess' => $message, 'err' => 'OK'];
	}
	
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

function change_passwd($passwd, $id){
	global $db;
	$query = $db->prepare("UPDATE `users` SET `passwd` = :passwd WHERE `id` = :id");
	$query->bindParam(':passwd', $passwd, PDO::PARAM_STR);
	$query->bindParam(':id', $id, PDO::PARAM_STR);
	$query->execute();
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
