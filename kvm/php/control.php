<?php
// https://www.linux.org.ru/forum/admin/12097297 (start non-root)
// apt-get install php-libvirt-php

function safeExec($command){
	$exec = escapeshellcmd($command);
	return shell_exec($exec);
}

function get_vncPasswd($name){
	global $conf_dir;
	$config = safeExec("sudo cat $conf_dir/$name.xml");
	$xml = new SimpleXMLElement($config);
	return $xml->devices->graphics['passwd'];
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

function kvmControl($command, $name){
	global $url, $credentials, $res;
	$id = libvirt_domain_lookup_by_name($res, $name);
	switch($command){
		default:
			$status = libvirt_domain_get_info($id); // status
			$result = $status['state'];
		break;
		case 'stop':
			$result = libvirt_domain_shutdown($id); // start
		break;
		case 'start':
			$result = libvirt_domain_create($id);  // stop
		break;
		case 'reboot':
			$result = libvirt_domain_reboot($id);  // reboot
		break;
		case 'hardstop':
			$result = libvirt_domain_destroy($id); // hard reboot
		break;
		case 'autostart':
			$result = libvirt_domain_set_autostart($id, 1);
		break;
		case 'disableautostart':
			$result = libvirt_domain_set_autostart($id, 0);
		break;
		case 'vncport':
			$xml = simplexml_load_string(libvirt_domain_get_xml_desc($id, ''));
			$data = json_decode(json_encode($xml), true);
			$result = intval($data["devices"]["graphics"]["@attributes"]["port"]);
		break;
	}	
	return $result;
}

$credentials = [VIR_CRED_AUTHNAME => "", VIR_CRED_PASSPHRASE => ""];
$res = libvirt_connect("qemu+unix:///system", false, $credentials);

if(empty(intval($_POST['id'])) || empty($_POST['command']) || empty($_POST['key'])) die("error 1");
if($_POST['key'] != hash('sha256', 'secret'.$_SERVER['REMOTE_ADDR'])) die("error 2");

$vm_id = "kvm".intval($_POST['id']);
$conf_dir = '/etc/libvirt/qemu';

switch($_POST['command']){
	case 'stopServer':
		kvmControl('hardstop', $vm_id);
	break;
	case 'startServer':
		kvmControl('start', $vm_id);
	break;
	case 'restartServer':
	case 'hardrestartServer':
	case 'stopANDstart':
		if(empty($_POST['boot'])) die("error 2");
		switch($_POST['boot']){
			default: $file = '/home/debian.iso'; $boot = 'hd'; break;
			case '2': $file = '/home/debian.iso'; $boot = 'cdrom'; break;
			case '3': $file = '/home/ubuntu.iso'; $boot = 'cdrom'; break;
			case '4': $file = '/home/centos.iso'; $boot = 'cdrom'; break;
		}
		safeExec("sudo chmod 777 $conf_dir/$vm_id.xml");
		$xml = file_get_contents("$conf_dir/$vm_id.xml");
		$config = new SimpleXMLElement($xml);
		lepus_editKVM('boot', $boot);
		lepus_editKVM('cdfile', $file);
		file_put_contents("$conf_dir/$vm_id.xml", $config->saveXML());
		safeExec("sudo virsh define $conf_dir/$vm_id.xml");
		if($_POST['command'] == 'hardrestartServer'){
			kvmControl('hardstop', $vm_id);
			sleep(3);
			kvmControl('start', $vm_id);
		}
		if($_POST['command'] == 'restartServer'){
			kvmControl('reboot', $vm_id);
		}
		if($_POST['command'] == 'stopANDstart'){
			kvmControl('stop', $vm_id);
			sleep(8);
			kvmControl('start', $vm_id);
		}
	break;
	case 'changeTariff':
		if(empty($_POST['memory']) || empty($_POST['cpus']) || empty($_POST['diskspace'])) die("error 2");
		if(!is_numeric($_POST['memory']) || !is_numeric($_POST['cpus']) || !is_numeric($_POST['diskspace'])) die("error 3");
		safeExec("sudo zfs set volsize=".$_POST['diskspace']."G ssd/$vm_id");
		safeExec("sudo chmod 777 $conf_dir/$vm_id.xml");
		$xml = file_get_contents("$conf_dir/$vm_id.xml");
		$config = new SimpleXMLElement($xml);
		lepus_editKVM('memory', $_POST['memory']);
		lepus_editKVM('currentMemory', $_POST['memory']);
		lepus_editKVM('vcpu', $_POST['cpus']);
		file_put_contents("$conf_dir/$vm_id.xml", $config->saveXML());
		safeExec("sudo virsh define $conf_dir/$vm_id.xml");
		kvmControl('hardstop', $vm_id);
		sleep(3);
		kvmControl('start', $vm_id);
	break;
	case 'changeVNC':
		if(empty($_POST['vnc'])) die("error 2");
		safeExec("sudo chmod 777 $conf_dir/$vm_id.xml");
		$xml = file_get_contents("$conf_dir/$vm_id.xml");
		$config = new SimpleXMLElement($xml);
		lepus_editKVM('vnc', md5($_POST['vnc']));
		file_put_contents("$conf_dir/$vm_id.xml", $config->saveXML());
		safeExec("sudo virsh define $conf_dir/$vm_id.xml");
	break;
	case 'portVNC':
		echo json_encode(['port' => kvmControl('vncport', $vm_id), 'passwd' => get_vncPasswd($vm_id)]);
		die;
	break;
	
	case 'getStatus':
	
	break;
	
}

if(kvmControl('status', $vm_id) == 1){
	echo 'running';
}else{
	echo 'down';
}
