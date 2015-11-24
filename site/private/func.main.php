<?php
function rehash($passwd, $hash){
	if (password_needs_rehash($hash, PASSWORD_DEFAULT))
		return password_hash($passwd, PASSWORD_DEFAULT);
	else
		return 'no_hash';
}

function login($login, $passwd){
	global $db;
	$query = $db->prepare("SELECT * FROM `users` WHERE `login` =:login");
	$query->bindParam(':login', $login, PDO::PARAM_STR);
	$query->execute();
	if($query->rowCount() != 1) return 'no_user';
	$row = $query->fetch();
	if (password_verify($passwd, $row['passwd'])){
		if($row['block'] == 1) return 'block_user';
		$new_passwd = rehash($passwd, $row['passwd']);
		$_SESSION['id'] = $row['id'];
		$_SESSION['login'] = $login;
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

