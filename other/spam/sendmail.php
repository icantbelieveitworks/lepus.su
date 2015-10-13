#!/usr/bin/php
<?php
// about sendmail flags => http://linux.die.net/man/8/sendmail.sendmail
// clamav scan php mail script => if get *virus* => don`t send mail

function log_in_file($data){
	file_put_contents('/home/test.log', $data, FILE_APPEND | LOCK_EX);
}

function send_debug($data){
	return var_export($data, true);
}

function clamav_check($script_path, $i = 'yes'){
	$data = shell_exec('clamdscan --multiscan --fdpass '.escapeshellarg($script_path));
	if(strpos($data, 'Infected files: 1') !== FALSE){
		$i = 'no';
	}
	return $i;
}

$pointer = fopen('php://stdin', 'r');
while ($line = fgets($pointer)) {
	$data[] = $line;
	$mail .= $line;
}

$script = explode(':', $data[2]);
$script_path = $_SERVER['PWD'].'/'.$script[2];

if(clamav_check($script_path) == 'yes'){ // let`s send mail
	log_in_file("valid mail\n");
	log_in_file($mail);
	$command = 'echo '.escapeshellarg($mail).' | /usr/sbin/sendmail -t -i -f '.escapeshellarg($_SERVER['argv']['4']);
	shell_exec($command);
}
