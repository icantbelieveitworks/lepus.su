#!/usr/bin/php5
<?php
/*
<name>debian</name>
<uuid>d2f3050c-f97e-49ac-a682-92bee50a0424</uuid>
<memory unit='KiB'>1048576</memory>
<currentMemory unit='KiB'>1048576</currentMemory>
<vcpu placement='static'>1</vcpu>
<devices>
...
	<disk type='block' device='disk'>
		...
		<source dev='/dev/zvol/ssd/debian'/>
		...
	</disk>

	<interface type='bridge'>
		...
		<mac address='00:00:00:00:00:00'/>
		...
	</interface>
	<graphics type='vnc' port='-1' autoport='yes' listen='0.0.0.0' keymap='en-us' passwd='000000'>
...
</devices>
*/

function kvm_exec($command, $id){
	shell_exec("virsh -c qemu:///system $command $id");
}

function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
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

$conf_dir = '/root/lepus/config';
$credentials = [VIR_CRED_AUTHNAME => "", VIR_CRED_PASSPHRASE => ""];
$res = libvirt_connect("qemu+unix:///system", false, $credentials);

// id, ip, mac, root passwd, memory, cpus, diskspace, node, os
$json = json_decode(file_get_contents('https://lepus.su/public/api/create.php'), true);

if(empty(intval($json['id'])) || empty($json['ip']) || empty($json['mac']) || empty($json['passwd']) || empty($json['memory']) || empty($json['cpus']) || empty($json['diskspace']) || empty($json['node']) || empty($json['os'])) die("empty\n");
if($json['os'] != 'debian' && $json['os'] != 'ubuntu' && $json['os'] != 'centos') die("wrong os\n");
var_dump($json);

$arr = explode(".", $json['node']);
$gateway = "{$arr[0]}.{$arr[1]}.{$arr[2]}.254";
$vm_id = "kvm".intval($json['id']);
echo $vm_id."\n";
if(file_exists("/etc/libvirt/qemu/$vm_id.xml")) die("kvm create\n");

$xml = file_get_contents("$conf_dir/conf.xml");
$config = new SimpleXMLElement($xml);
lepus_editKVM('name', $vm_id);
lepus_editKVM('uuid', gen_uuid());
lepus_editKVM('memory', $json['memory']);
lepus_editKVM('currentMemory', $json['memory']);
lepus_editKVM('vcpu', $json['cpus']);
lepus_editKVM('disk', "/dev/zvol/ssd/$vm_id");
lepus_editKVM('mac', $json['mac']);
file_put_contents("/etc/libvirt/qemu/$vm_id.xml", $config->saveXML());
shell_exec("zfs create -s -V {$json['diskspace']}g ssd/$vm_id");
shell_exec("zfs set compression=lz4 ssd/$vm_id");
sleep(10);
shell_exec("cp /dev/zvol/ssd/{$json['os']} /dev/zvol/ssd/$vm_id");
shell_exec("virsh define /etc/libvirt/qemu/$vm_id.xml");
shell_exec("mkdir /mnt/$vm_id");
shell_exec("mount /dev/zvol/ssd/$vm_id-part1 /mnt/$vm_id");
switch($json['os']){
	case 'debian':
	case 'ubuntu':
		$network = file_get_contents("$conf_dir/interfaces");
		$network = str_replace("VMIP", $json['ip'], $network);
		$network = str_replace("NODE", $gateway, $network);
		file_put_contents("/mnt/$vm_id/etc/network/interfaces", $network);
	break;
	case 'centos':
		$network = file_get_contents("$conf_dir/ifcfg-eth0");
		$network = str_replace("VMIP", $json['ip'], $network);
		$network = str_replace("NODE", $gateway, $network);
		$network = str_replace("MAC", $json['mac'], $network);
		file_put_contents("/mnt/$vm_id/etc/sysconfig/network-scripts/ifcfg-eth0", $network);
		$network = file_get_contents("$conf_dir/route-eth0");
		$network = str_replace("NODE", $gateway, $network);
		file_put_contents("/mnt/$vm_id/etc/sysconfig/network-scripts/route-eth0", $network);
	break;
}
file_put_contents("/mnt/$vm_id/root/lepus/tmp/passwd", $json['passwd']);
@unlink("/mnt/$vm_id/root/lepus/tmp/passwd.lock");
@unlink("/mnt/$vm_id/root/lepus/tmp/resize.lock");
@unlink("/mnt/$vm_id/root/lepus/tmp/keys.lock");
shell_exec("umount /mnt/$vm_id");
shell_exec("virsh start $vm_id");
