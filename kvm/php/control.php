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

function lepus_editKVM($key, $val){
	global $config;
	switch($key){
		default: $config->$key = $val; break;
		case 'disk': $config->devices->disk->source['dev'] = $val; break;
		case 'mac': $config->devices->interface->mac['address'] = $val; break;
		case 'vnc': $config->devices->graphics['passwd'] = $val; break;
	}
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
	case 'changeTariff':
	if(empty($_GET['memory']) || empty($_GET['cpus']) || empty($_GET['diskspace'])) die("error 2");
		$conf_dir = '/etc/libvirt/qemu';
		shell_exec("sudo zfs set volsize=".$_GET['diskspace']."G ssd/$vm_id");
		shell_exec("sudo chmod 777 $conf_dir/$vm_id.xml");
		$xml = file_get_contents("$conf_dir/$vm_id.xml");
		$config = new SimpleXMLElement($xml);
		lepus_editKVM('memory', $_GET['memory']);
		lepus_editKVM('currentMemory', $_GET['memory']);
		lepus_editKVM('vcpu', $_GET['cpus']);
		file_put_contents("$conf_dir/$vm_id.xml", $config->saveXML());
		shell_exec("sudo virsh define $conf_dir/$vm_id.xml");
		kvm_exec("destroy", $vm_id);
		sleep(3);
		kvm_exec("start", $vm_id);
	break;
}

if($status['state'] == 1) echo 'running';
	else echo 'down';
