<?php
$target_url = 'http://136.243.79.74/scan/index.php';
$filename = '/var/www/tmp/utf96.php';

$postdata = array(
    'method'    => 'post', 
    'file'      => new CurlFile($filename)
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$target_url);
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
$result = curl_exec($ch);
curl_close($ch);
echo $result;
