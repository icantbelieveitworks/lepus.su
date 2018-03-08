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
			td,th { text-align: center; vertical-align: middle !important; }
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
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>Сайтов</td>
										<td>unlim</td>
										<td>unlim</td>
										<td>unlim</td>
									</tr>
									<tr>
										<td>Диск [GB]</td>
										<td>10</td>
										<td>20</td>
										<td>30</td>
									</tr>
									<tr>
										<td>MySQL [GB]</td>
										<td>2</td>
										<td>4</td>
										<td>6</td>
									</tr>
									<tr>
										<td>FTP</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
									</tr>
									<tr>
										<td>Cron</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
									</tr>
									<tr>
										<td>PHP</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
									</tr>
									<tr>
										<td>phpMyAdmin</td>
										<td>+</td>
										<td>+</td>
										<td>+</td>
									</tr>
									<tr>
										<td style="line-height: 35px;">Панель управления</td>
										<td class="text-center" colspan="3">ISPmanager 5</td>
									</tr>
									<tr>
										<td>Дата-центр</td>
										<td>OVH</td>
										<td>OVH</td>
										<td>OVH</td>
									</tr>
									<tr>
										<td>Страна</td>
										<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
										<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
										<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									</tr>
									<tr>
										<th>Цена</th>
										<td><b><?php echo lepus_getBillprice(45, 0); ?></b></td>
										<td><b><?php echo lepus_getBillprice(49, 0); ?></b></td>
										<td><b><?php echo lepus_getBillprice(53, 0); ?></b></td>
									</tr>
									<tfoot>
										<tr>
											<td>&nbsp;</td>
											<td><a id="noclick" class="btn btn-danger btn-xs" rel="nofollow" href="https://my.lepus.su/billmgr?func=showroom.redirect&amp;redirect_to=desktop&amp;startform=service.order.itemtype&amp;newwindow=yes" target="_blank">заказать</a></td>
											<td><a id="noclick" class="btn btn-danger btn-xs" rel="nofollow" href="https://my.lepus.su/billmgr?func=showroom.redirect&amp;redirect_to=desktop&amp;startform=service.order.itemtype&amp;newwindow=yes" target="_blank">заказать</a></td>
											<td><a id="noclick" class="btn btn-danger btn-xs" rel="nofollow" href="https://my.lepus.su/billmgr?func=showroom.redirect&amp;redirect_to=desktop&amp;startform=service.order.itemtype&amp;newwindow=yes" target="_blank">заказать</a></td>
										</tr>
									</tfoot>
								</tbody>
							</table>
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
