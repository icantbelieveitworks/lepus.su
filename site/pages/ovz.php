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
										<td>420</td>
										<td>840</td>
										<td>1260</td>
										<td>1680</td>
									</tr>
									<tr>
										<td>RAM (Мб)</td>
										<td>512</td>
										<td>1024</td>
										<td>1536</td>
										<td>2048</td>
									</tr>
									<tr>
										<td>Диск (Мб)</td>
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
										<td>SSH (root)</td>
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
										<td>PHP (5.6)</td>
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
							<a href="https://poiuty.com/img/64/007a5008eb2bd61ffd5bfc06fe8ba064.png" target="_blank">Подробное описание услуги</a>, <a href="https://github.com/poiuty/lepus.su/issues/21">github</a>.<br/>
							В стандартной поставке => это виртуальный хостинг (<a href="https://poiuty.com/index.php?title=%D0%92%D0%B8%D1%80%D1%82%D1%83%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9_%D1%85%D0%BE%D1%81%D1%82%D0%B8%D0%BD%D0%B3_-_%D0%B4%D0%BE%D0%B1%D0%B0%D0%B2%D0%B8%D1%82%D1%8C_%D1%81%D0%B0%D0%B9%D1%82" target="_blank">инструкция как разместить сайт</a> + <a href="https://github.com/poiuty/lepus.su/wiki/%D0%92%D0%B8%D1%80%D1%82%D1%83%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9-%D1%85%D0%BE%D1%81%D1%82%D0%B8%D0%BD%D0%B3">ответы на вопросы</a>) .<br/>
							Если же вы умеете работать с linux => то для вас => это еще и дешевая OpenVZ VPS c полным root доступом.
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