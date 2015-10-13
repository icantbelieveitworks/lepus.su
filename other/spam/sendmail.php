#!/usr/bin/php
<?php
// about sendmail flags => http://linux.die.net/man/8/sendmail.sendmail
// clamav scan php mail script => if get *virus* => don`t send mail
setlocale(LC_CTYPE, "en_US.UTF-8");

function log_in_file($data){
	file_put_contents('/home/mail-clamav.log', $data, FILE_APPEND | LOCK_EX);
}

function send_debug($data){
	return var_export($data, true);
}

function clamav_check($script_path, $i = 'yes'){
	$data = shell_exec('clamdscan --multiscan --fdpass '.escapeshellarg($script_path));
	if(strpos($data, 'Infected files: 1') !== FALSE){
		$i = 'no';
		log_in_file("clamav => $script_path");
	}
	return $i;
}

function check_spambot($script_path, $i = 'yes'){
	$data = file_get_contents($script_path);
	if(count($data) < 10 && strlen($data) > 10000){ // обычно такой скрипт это 1-10 строчек и много много символов.
		$i = 'no';
		log_in_file("spam_bot => $script_path");
	}
	
	// Часто в этих скриптах используют $GLOBALS и $_SERVER
	// if(strpos($file, '$GLOBALS') !== FALSE)
	// if(strpos($file, '$_SERVER') !== FALSE)
	
	return $i;
}

$pointer = fopen('php://stdin', 'r');
while ($line = fgets($pointer)) {
	$data[] = $line;
	$mail .= $line;
}
$script = explode(':', $data[2]);
$script_path = trim($_SERVER['PWD'].'/'.$script[2]);
if(clamav_check($script_path) == 'yes' && check_spambot($script_path) == 'yes'){ // let`s send mail
	//log_in_file("valid mail\n");
	//log_in_file($mail);
	$command = 'echo '.escapeshellarg($mail).' | /usr/sbin/sendmail -t -i -f '.escapeshellarg($_SERVER['argv']['4']);
	shell_exec($command);
}
