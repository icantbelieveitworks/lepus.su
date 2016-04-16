<?php
// https://www.linux.org.ru/forum/admin/12097297 (start non-root)
// https://poiuty.com/index.php?title=%D0%9A%D0%BE%D0%BC%D0%BF%D0%B8%D0%BB%D0%B8%D1%80%D1%83%D0%B5%D0%BC_libvirt-php (build libvirt-php)
function kvm_exec($command, $id){
	shell_exec("virsh -c qemu:///system $command $id");
}

function get_status($name){
	global $url, $credentials, $res;
	$id = libvirt_domain_lookup_by_name($res, $name);
	return libvirt_domain_get_info($id); // 1 => online, 5 => offline
}

$credentials = [VIR_CRED_AUTHNAME => "", VIR_CRED_PASSPHRASE => ""];
$res = libvirt_connect("qemu+unix:///system", false, $credentials);

if(empty(intval($_GET['id'])) || empty($_GET['command']) || empty($_GET['key'])) die("error 1");
if($_GET['key'] != 'xxx') die("error 2");

$vm_id = "kvm".intval($_GET['id']);
$status = get_status($vm_id);

switch($_GET['command']){
	case 'stopServer':
		kvm_exec("destroy", $vm_id);
	break;
	case 'startServer':
		kvm_exec("start", $vm_id);
	break;
	case 'restartServer':
		kvm_exec("reboot", $vm_id);
	break;
}

if($status['state'] == 1) echo 'running';
	else echo 'down';
