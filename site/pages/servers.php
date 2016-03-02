<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');
function lepus_orderLink(){
	$j = "<a class=\"btn btn-danger btn-xs\" rel=\"nofollow\" href=\"/pages/order.php\">заказать</a>";
	if(!is_login()) $j = "<a class=\"btn btn-danger btn-xs\" data-register-open rel=\"nofollow\">заказать</a>";
	return $j;
}
$start_order = null;
for($i=0; $i < 8; $i++){
	if(!is_login())
		$start_order .= "<td><a class=\"btn btn-danger btn-xs\" data-register-open rel=\"nofollow\">заказать</a></td>";
	else
		$start_order .= "<td><a class=\"btn btn-danger btn-xs\" rel=\"nofollow\" href=\"/pages/order.php\">заказать</a></td>";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Lepus - интернет хостинг</title>
		<meta name="description" content="Виртуальный хостинг, быстрые VPS, выделенные серверы по привлекательной цене." />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="yandex-verification" content="6940b644b3235f76" />
		<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
		<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="/css/font-awesome.min.css">
		<link rel="stylesheet" type="text/css" href="/css/reset.css"/>
		<link rel="stylesheet" type="text/css" href="/css/style.css"/>
		<link rel="stylesheet" type="text/css" href="/css/alertify.core.css" />
		<link rel="stylesheet" type="text/css" href="/css/alertify.bootstrap.css" />
		<script src="/js/jquery.min.js"></script>
		<script src="/js/bootstrap.min.js"></script>
		<script src="/js/alertify.js"></script>
		<script src="/js/lepus.js"></script>
		<style>
			td,th { text-align: center; vertical-align: middle; }
		</style>
	</head>
	<body>
		<div class="wrapper">
			<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/menu.php'); ?>
			<div class="content-box">
				<div class="content-info box-shadow--2dp">
					<div class="content-text">
						<div class="page-title">Администрируемые выделенные серверы</div>
						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th>Тариф</th>
									<th>CPU</th>
									<th>RAM</th>
									<th>DISK</th>
									<th>Сеть</th>
									<th>DC</th>
									<th>Страна</th>
									<th>Установка</th>
									<th>Цена</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>FR1</td>
									<td><a href="http://ark.intel.com/ru/products/65733/Intel-Xeon-Processor-E3-1225V2-(8M-Cache-3_20-GHz)" target="_blank">E3 1225v2</a></td>
									<td>32 GB</td>
									<td>2x 2TB SATA</td>
									<td>250 Mbps</td>
									<td>OVH</td>
									<td><img src="https://lepus.su/images/flags/germany-flag.png" title="Germany"></td>
									<th>FREE</th>
									<th>5000</th>
									<td><?php echo lepus_orderLink(); ?></td>
								</tr>
							</tbody>
						</table>
						В базовое администрирование входит установка и настройка: ISPmanager, nginx, apache, php, mysql, proftpd, bind9, phpmyadmin, munin, memcache. Специальная настройка сервера именно под ваш проект.<br/>
						Мы позаботимся о том, чтобы ваши сайты были всегда доступны и работали максимально быстро.
						<hr/>
					</div>
				</div>
			</div>
			<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/navi.php'); ?>
		</div>
		<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/footer.php'); ?>
		<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/modal.php'); ?>
		<script src="//www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
	</body>
</html>
