<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');
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
						<div class="page-title">Администрируемые OpenVZ/ KVM VPS</div>
						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th>Тарифы</th>
									<th>OVZ1</th>
									<th><font color="IndianRed">OVZ2</font></th>
									<th>OVZ3</th>
									<th>OVZ4</th>
									<th>KVM1</th>
									<th>KVM2</th>
									<th>KVM3</th>
									<th>KVM4</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>CPU (MHz)</td>
									<td>1000</td>
									<td>2000</td>
									<td>3000</td>
									<td>3400</td>
									<td>3400</td>
									<td>2x3400</td>
									<td>2x3400</td>
									<td>4x3400</td>
								</tr>
								<tr>
									<td>Диск (Gb)</td>
									<td>5</td>
									<td>10</td>
									<td>15</td>
									<td>20</td>
									<td>30</td>
									<td>40</td>
									<td>50</td>
									<td>60</td>
								</tr>
								<tr>
									<td>RAM (Mb)</td>
									<td>1024</td>
									<td>2048</td>
									<td>3072</td>
									<td>4096</td>
									<td>6144</td>
									<td>8192</td>
									<td>10240</td>
									<td>16384</td>
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
						Заказывая у нас <strong>VPS</strong>, вы получите быстрый и надежный <strong>виртуальный сервер</strong> с root доступом по выгодной цене.<br/>
						Идеально подходит для крупных проектов, сайтов с высокой посещаемостью.<br/><br/>
						В качестве панели управления, мы предлагаем использовать <a href="http://www.ispsystem.ru/software/ispmanager" target="_blank">ISPmanager</a> [200 руб/ месяц] или <a href="http://vestacp.com/" target="_blank">VestaCP</a> [бесплатная].
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
