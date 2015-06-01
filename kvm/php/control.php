<pre>
<?php
/* such dangerous code [need root access]*/
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
?>
