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
		case 'boot': $config->os->boot['dev'] = $val; break;
		case 'cdfile': $config->devices->disk[1]->source['file'] = $val; break;
	}
}

$credentials = [VIR_CRED_AUTHNAME => "", VIR_CRED_PASSPHRASE => ""];
$res = libvirt_connect("qemu+unix:///system", false, $credentials);

if(empty(intval($_GET['id'])) || empty($_GET['command']) || empty($_GET['key'])) die("error 1");
if($_GET['key'] != 'xxx') die("error 2");

$vm_id = "kvm".intval($_GET['id']);
$status = get_status($vm_id);
$conf_dir = '/etc/libvirt/qemu';

switch($_GET['command']){
	case 'stopServer':
		kvm_exec("destroy", $vm_id);
	break;
	case 'startServer':
		kvm_exec("start", $vm_id);
	break;
	case 'restartServer':
	case 'hardrestartServer':
	case 'stopANDstart':
		if(empty($_GET['boot'])) die("error 2");
		switch($_GET['boot']){
			default: $file = '/home/debian.iso'; $boot = 'hd'; break;
			case '2': $file = '/home/debian.iso'; $boot = 'cdrom'; break;
			case '3': $file = '/home/ubuntu.iso'; $boot = 'cdrom'; break;
			case '4': $file = '/home/centos.iso'; $boot = 'cdrom'; break;
		}
		shell_exec("sudo chmod 777 $conf_dir/$vm_id.xml");
		$xml = file_get_contents("$conf_dir/$vm_id.xml");
		$config = new SimpleXMLElement($xml);
		lepus_editKVM('boot', $boot);
		lepus_editKVM('cdfile', $file);
		file_put_contents("$conf_dir/$vm_id.xml", $config->saveXML());
		shell_exec("sudo virsh define $conf_dir/$vm_id.xml");
		if($_GET['command'] == 'hardrestartServer'){
			kvm_exec("destroy", $vm_id);
			sleep(3);
			kvm_exec("start", $vm_id);
		}
		if($_GET['command'] == 'restartServer'){
			kvm_exec("reboot", $vm_id);
		}
		if($_GET['command'] == 'stopANDstart'){
			kvm_exec("shutdown", $vm_id);
			sleep(3);
			kvm_exec("start", $vm_id);
		}
	break;
	case 'changeTariff':
		if(empty($_GET['memory']) || empty($_GET['cpus']) || empty($_GET['diskspace'])) die("error 2");
		if(!is_numeric($_GET['memory']) || !is_numeric($_GET['cpus']) || !is_numeric($_GET['diskspace'])) die("error 3");
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
	case 'changeVNC':
		if(empty($_GET['vnc'])) die("error 2");
		shell_exec("sudo chmod 777 $conf_dir/$vm_id.xml");
		$xml = file_get_contents("$conf_dir/$vm_id.xml");
		$config = new SimpleXMLElement($xml);
		lepus_editKVM('vnc', md5($_GET['vnc']));
		file_put_contents("$conf_dir/$vm_id.xml", $config->saveXML());
		shell_exec("sudo virsh define $conf_dir/$vm_id.xml");
	break;
	case 'portVNC':
		$pid = intval(trim(shell_exec("sudo ps uax | grep 'qemu-system-x86_64 -enable-kvm -name $vm_id ' | grep -v grep | awk '{print $2}'")));
		die(shell_exec("sudo netstat -tupan | grep LISTEN | grep $pid | head -n1 | awk '{print $4}'"));
	break;
	
}

if($status['state'] == 1) echo 'running';
	else echo 'down';
