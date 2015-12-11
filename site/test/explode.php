<pre>
<?php
$check = ['name', 'type', 'content', 'prio'];

$i = 'prio_1421';
$data = explode("_", $i);
//var_dump($data);
if(!in_array($data[0], $check)) die("error");
echo $data[0];
