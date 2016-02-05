<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');

for($i=0; $i < 8; $i++){
	if(!is_login())
		$start_order .= "<td><a class=\"btn btn-danger btn-xs\" data-register-open rel=\"nofollow\">заказать</a></td>";
	else
		$start_order .= "<td><a class=\"btn btn-danger btn-xs\" rel=\"nofollow\">заказать</a></td>";
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
						<div class="page-title">Виртуальный хостинг</div>
							<table class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>Тарифы</th>
										<th>Basic</th>
										<th><font color="IndianRed">Standard</font></th>
										<th>Pro</th>
										<th>Super</th>
										<th>VIP1</th>
										<th>VIP2</th>
										<th>VIP3</th>
										<th>VIP4</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>Сайтов</td>
										<td>unlim</td>
										<td>unlim</td>
										<td>unlim</td>
										<td>unlim</td>
										<td>unlim</td>
										<td>unlim</td>
										<td>unlim</td>
										<td>unlim</td>
									</tr>
									<tr>
										<td>Диск (Мб)</td>
										<td>1000</td>
										<td>2000</td>
										<td>4000</td>
										<td>6000</td>
										<td>10000</td>
										<td>12500</td>
										<td>15000</td>
										<td>20000</td>
									</tr>
									<tr>
										<td>MySQL</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
									</tr>
									<tr>
										<td>FTP</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
									</tr>
									<tr>
										<td>ISPmanager</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
									</tr>
									<tr>
										<td>Cron</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
									</tr>	
									<tr>
										<td>SSL</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
									</tr>
									<tr>
										<td>PHP</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
									</tr>
									<tr>
										<td>MySQL</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
									</tr>
									<tr>
										<td>FTP-доступ</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
									</tr>
									<tr>
										<td>ionCube</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
									</tr>
									<tr>
										<td>Zend Guard</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
									</tr>
									<tr>
										<td>phpMyAdmin</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
									</tr>	
									<tr>
										<td>Расположение</td>
										<th><img src="https://lepus.su/images/flags/germany-flag.png" title="Germany"></th>
										<th><img src="https://lepus.su/images/flags/germany-flag.png" title="Germany"></th>
										<th><img src="https://lepus.su/images/flags/germany-flag.png" title="Germany"></th>
										<th><img src="https://lepus.su/images/flags/germany-flag.png" title="Germany"></th>
										<th><img src="https://lepus.su/images/flags/germany-flag.png" title="Germany"></th>
										<th><img src="https://lepus.su/images/flags/germany-flag.png" title="Germany"></th>
										<th><img src="https://lepus.su/images/flags/germany-flag.png" title="Germany"></th>
										<th><img src="https://lepus.su/images/flags/germany-flag.png" title="Germany"></th>
									</tr>
									<tr>
										<td><font color="green"><i>Тест. период</i></font></td>
										<td><font color="green">+</font></td>
										<td><font color="green">+</font></td>
										<td><font color="green">+</font></td>
										<td><font color="green">+</font></td>
										<td><font color="green">+</font></td>
										<td><font color="green">+</font></td>
										<td><font color="green">+</font></td>
										<td><font color="green">+</font></td>
									</tr>
									<tr>
										<th>Цена</th>
										<th>50</th>
										<th>100</th>
										<th>200</th>
										<th>300</th>
										<th>400</th>
										<th>500</th>
										<th>600</th>
										<th>700</th>
									</tr>
									<tfoot>
										<tr>
											<td>&nbsp;</td>
											<?php echo $start_order;?>
										</tr>
									</tfoot>
								</tbody>
							</table>
							Наш виртуальный хостинг отлично подходит для размещения небольших сайтов: визитки, личные блоги.<br/>
							Вы сможете разместить сайты на таких популярных CMS как Wordpress, Joomla, Drupal, DLE и других PHP движках.<br/>
							Для новых клиентов мы предоставляем бесплатный тестовый период! Попробуйте наш php хостинг прямо сейчас!
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
