<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');
$start_order = null;
for($i=0; $i < 5; $i++){
	if(!is_login())
		$start_order .= "<td><a class=\"btn btn-danger btn-xs\" data-register-open rel=\"nofollow\">заказать</a></td>";
	else
		$start_order .= "<td><a class=\"btn btn-danger btn-xs\" rel=\"nofollow\" href=\"/pages/order.php\">заказать</a></td>";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>KVM VPS</title>
		<meta name="description" content="Заказать быстрый KVM VPS на SSD дисках" />
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
		<?php if(!empty($conf['beard_stats'])){ echo "<script async src=\"https://stats.vboro.de/code/code/{$conf['beard_stats']}/\"></script>"; } ?>
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
						<div class="page-title">KVM VPS на SSD дисках</div>
						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th>Тарифы</th>
									<th>KVM1</th>
									<th><font color="IndianRed">KVM2</font></th>
									<th>KVM3</th>
									<th>KVM4</th>
									<th>KVM5</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>CPU (MHz)</td>
									<td>2200</td>
									<td>2200</td>
									<td>2200</td>
									<td>2x2200</td>
									<td>2x2200</td>
								</tr>
								<tr>
									<td>Диск (Gb)</td>
									<td>10</td>
									<td>20</td>
									<td>30</td>
									<td>40</td>
									<td>50</td>
								</tr>
								<tr>
									<td>RAM (Mb)</td>
									<td>1024</td>
									<td>2048</td>
									<td>3072</td>
									<td>4096</td>
									<td>5120</td>
								</tr>
								<tr>
									<td style="line-height: 35px;">ОС</td>
									<td class="text-center" colspan="5"><img src="https://lepus.su/images/flags/debian.png" title="Debian"> <img src="https://lepus.su/images/flags/ubuntu.png" style="margin-left: 55px;" title="Ubuntu"> <img src="https://lepus.su/images/flags/centos.png" style="margin-left: 55px;" title="CentOS"></td>
								</tr>
								<tr>
									<td>Дата-центр</td>
									<td>OVH</td>
									<td>OVH</td>
									<td>OVH</td>
									<td>OVH</td>
									<td>OVH</td>
								</tr>
								<tr>
									<td style="line-height: 25px;">Страна</td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
								</tr>
								<tr>
									<th>Цена</th>
									<?php echo lepus_getTariffPrices(2); ?>
								</tr>
								<tfoot>
									<tr>
										<td>&nbsp;</td>
										<?php echo $start_order;?>
									</tr>
								</tfoot>
							</tbody>
						</table>
						Предлагаем решение на базе виртуализации KVM. Бесплатно <a href="https://github.com/poiuty/lepus.su/wiki/%D0%9F%D0%B5%D1%80%D0%B5%D0%BD%D0%BE%D1%81-%D1%81%D0%B0%D0%B9%D1%82%D0%BE%D0%B2" target="_blank">перенесем ваши сайты</a>. <u><i>Новым клиентам 50% скидка!</i></u><br/>
						Подключение к сети 100Mb/s, защита от DDoS (tcp/udp) на уровне дата-центра, SSD диск, низкий пинг (~40ms MSK).<br/>
						В качестве панели управления, мы предлагаем использовать <a href="https://my.lepus.su/billmgr?func=showroom.redirect&redirect_to=desktop&startform=service.order.itemtype&newwindow=yes" target="_blank">ISPmanager</a> [<?php echo lepus_getBillprice(17, 0); ?> руб/ месяц] или <a href="http://vestacp.com/" target="_blank">VestaCP</a> [бесплатная].
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
