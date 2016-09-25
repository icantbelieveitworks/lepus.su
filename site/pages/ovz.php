<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');
$start_order = null;
for($i=0; $i < 4; $i++){
	if(!is_login())
		$start_order .= "<td><a class=\"btn btn-danger btn-xs\" data-register-open rel=\"nofollow\">заказать</a></td>";
	else
		$start_order .= "<td><a class=\"btn btn-danger btn-xs\" rel=\"nofollow\" href=\"/pages/order.php\">заказать</a></td>";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Виртуальный хостинг</title>
		<meta name="description" content="Купить надежный виртуальный хостинг" />
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
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>Сайтов</td>
										<td>unlim</td>
										<td>unlim</td>
										<td>unlim</td>
										<td>unlim</td>
									</tr>
									<tr>
										<td>CPU (MHz)</td>
										<td>1000</td>
										<td>1000</td>
										<td>1500</td>
										<td>1500</td>
									</tr>
									<tr>
										<td>RAM (Mb)</td>
										<td>512</td>
										<td>1024</td>
										<td>1536</td>
										<td>2048</td>
									</tr>
									<tr>
										<td>Диск (Mb)</td>
										<td>3000</td>
										<td>6000</td>
										<td>9000</td>
										<td>12000</td>
									</tr>
									<tr>
										<td>MySQL</td>
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
									</tr>
									<tr>
										<td>SSH</td>
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
									</tr>
									<tr>
										<td>PHP</td>
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
									</tr>
								<tr>
									<td>Дата-центр</td>
									<td>OVH</td>
									<td>OVH</td>
									<td>OVH</td>
									<td>OVH</td>
								</tr>
								<tr>
									<td>Страна</td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
								</tr>
									<tr>
										<th>Цена</th>
										<?php echo lepus_getTariffPrices(5); ?>
									</tr>
									<tfoot>
										<tr>
											<td>&nbsp;</td>
											<?php echo $start_order;?>
										</tr>
									</tfoot>
								</tbody>
							</table>
							Предлагаем решение на базе OpenVZ контейнеров. Бесплатно <a href="https://github.com/poiuty/lepus.su/wiki/%D0%9F%D0%B5%D1%80%D0%B5%D0%BD%D0%BE%D1%81-%D1%81%D0%B0%D0%B9%D1%82%D0%BE%D0%B2" target="_blank">перенесем ваши сайты</a>. <u><i>Новым клиентам 50% скидка!</i></u><br/>
							Уникальный IP, хорошая изоляция от соседей, SSD диск, Anti-DDoS (tcp/udp) на уровне дата-центра, и полный root доступ.<br/><br/>
							Чтобы разместить сайт на хостинге (<a href="https://wiki.lepus.su/index.php?title=%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%81%D1%82%D0%B8%D0%BC_%D1%81%D0%B0%D0%B9%D1%82_%D0%BD%D0%B0_%D0%B2%D0%B8%D1%80%D1%82%D1%83%D0%B0%D0%BB%D1%8C%D0%BD%D0%BE%D0%BC_%D1%85%D0%BE%D1%81%D1%82%D0%B8%D0%BD%D0%B3%D0%B5" target="_blank">подробная инструкция</a> + <a href="https://github.com/poiuty/lepus.su/wiki/%D0%92%D0%B8%D1%80%D1%82%D1%83%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9-%D1%85%D0%BE%D1%81%D1%82%D0%B8%D0%BD%D0%B3" target="_blank">ответы на вопросы</a>).<br/>
							1. Делегируйте домен на наши NS + создаем записи.<br/>
							2. Зайдите на FTP, создайте папку (название домена, например lepus.su) и загрузите в нее файлы сайта.<br/>
							3. Зайдите в phpmyadmin, создайте базу+пользователя и загрузите дамп базы.<br/><br/>

							В стандартной поставке => это виртуальный хостинг: apache24 + apache mod_vhost_alias, php56-fpm, proftpd, mysql.<br/>
							Если же вы умеете работать с linux, то для вас => это еще и недорогая VPS (где вы можете запустить любое приложение).
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
