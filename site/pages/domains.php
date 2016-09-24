<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');
$start_order = null;
for($i=0; $i < 9; $i++)
		$start_order .= "<td><a id=\"noclick\" class=\"btn btn-danger btn-xs\" rel=\"nofollow\" href=\"https://my.lepus.su/billmgr?func=showroom.redirect&redirect_to=desktop&startform=service.order.itemtype&newwindow=yes\" target=\"_blank\">заказать</a></td>";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Домены</title>
		<meta name="description" content="Купить домен ru, рф, su, name, com, net, org, biz, info" />
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
						<div class="page-title">Домены</div>
						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th>Зона</th>
									<th>.RU</th>
									<th>.РФ</th>
									<th>.SU</th>
									<th>.NAME</th>
									<th>.COM</th>
									<th>.NET</th>
									<th>.ORG</th>
									<th>.BIZ</th>
									<th>.INFO</th>
								</tr>
							</thead>
							<tbody>
								<th>Цена</th>
									<th><?php echo lepus_getBillprice(1, 1); ?></th>
									<th><?php echo lepus_getBillprice(13, 1); ?></th>
									<th><?php echo lepus_getBillprice(5, 1); ?></th>
									<th><?php echo lepus_getBillprice(19, 1); ?></th>
									<th><?php echo lepus_getBillprice(11, 1); ?></th>
									<th><?php echo lepus_getBillprice(15, 1); ?></th>
									<th><?php echo lepus_getBillprice(9, 1); ?></th>
									<th><?php echo lepus_getBillprice(21, 1); ?></th>
									<th><?php echo lepus_getBillprice(7, 1); ?></th>
								</tr>
								<tfoot>
									<tr>
										<td>&nbsp;</td>
										<?php echo $start_order;?>
									</tr>
								</tfoot>
							</tbody>
						</table>
						Хотите перенести к нам домены? Будем рады принять их на обслуживание.<br/>
						<a href="https://lepus.su/pages/support.php">Создайте тикет</a> [необходимо авторизоваться на сайте] и мы поможем выполнить перенос.
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
