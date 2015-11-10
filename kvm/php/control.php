<?php
// https://www.linux.org.ru/forum/admin/12097297 (start non-root)
// https://poiuty.com/index.php?title=%D0%9A%D0%BE%D0%BC%D0%BF%D0%B8%D0%BB%D0%B8%D1%80%D1%83%D0%B5%D0%BC_libvirt-php (build libvirt-php)
$uri = "qemu+unix:///system"; $credentials = [VIR_CRED_AUTHNAME => "", VIR_CRED_PASSPHRASE => ""]; $res = libvirt_connect($uri,false,$credentials);

function get_status($name){
	global $url, $credentials, $res;
	$id = libvirt_domain_lookup_by_name($res, $name);
	return libvirt_domain_get_info($id); // 1 => online, 5 => offline
}

function kvm_exec($command, $id){
	shell_exec("virsh $command $id");
}

if(empty(intval($_GET['id'])) || empty($_GET['command'])) die("error 1");
$vm_id = "kvm".intval($_GET['id']); $status = get_status($vm_id);

var_dump($status);

if($_GET['command'] == 'stop'){
	kvm_exec("shutdown", $vm_id);
}

if($_GET['command'] == 'start'){
	kvm_exec("start", $vm_id);
}

if($_GET['command'] == 'reboot'){
	kvm_exec("reboot", $vm_id);
}

echo $status['state'];
