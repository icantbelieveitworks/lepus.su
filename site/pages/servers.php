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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Выделенные серверы</title>
		<meta name="description" content="Заказать мощный выделенный сервер" />
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
						<div class="page-title">Выделенные серверы</div>
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
									<th>Цена</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>E3-SAT-1</td>
									<td><a href="http://ark.intel.com/ru/products/65733/Intel-Xeon-Processor-E3-1225V2-(8M-Cache-3_20-GHz)" target="_blank">E3-1225</a></td>
									<td>16 GB</td>
									<td>2x 2TB SATA</td>
									<td>250 Mbps</td>
									<td>OVH</td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<th><?php echo lepus_getTariffPrice(17); ?></th>
									<td><?php echo lepus_orderLink(); ?></td>
								</tr>
								<tr>
									<td>E3-SAT-2</td>
									<td><a href="http://ark.intel.com/ru/products/65733/Intel-Xeon-Processor-E3-1225V2-(8M-Cache-3_20-GHz)" target="_blank">E3-1225</a></td>
									<td>32 GB</td>
									<td>2x 2TB SATA</td>
									<td>250 Mbps</td>
									<td>OVH</td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<th><?php echo lepus_getTariffPrice(18); ?></th>
									<td><?php echo lepus_orderLink(); ?></td>
								</tr>
								<tr>
									<td>E3-SAT-3</td>
									<td><a href="http://ark.intel.com/ru/products/65729/Intel-Xeon-Processor-E3-1245-v2-8M-Cache-3_40-GHz" target="_blank">E3-1245</a></td>
									<td>32 GB</td>
									<td>2x 2TB SATA</td>
									<td>250 Mbps</td>
									<td>OVH</td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<th><?php echo lepus_getTariffPrice(19); ?></th>
									<td><?php echo lepus_orderLink(); ?></td>
								</tr>
								<!--<tr>
									<td>E3-SSD-5</td>
									<td><a href="http://ark.intel.com/ru/products/65729/Intel-Xeon-Processor-E3-1245-v2-8M-Cache-3_40-GHz" target="_blank">E3-1245</a></td>
									<td>32 GB</td>
									<td>2x 240GB SSD</td>
									<td>250 Mbps</td>
									<td>OVH</td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<th><?php echo lepus_getTariffPrice(39); ?></th>
									<td><?php echo lepus_orderLink(); ?></td>
								</tr>-->
								<tr>
									<td>SP-32</td>
									<td><a href="http://ark.intel.com/ru/products/80910/Intel-Xeon-Processor-E3-1231-v3-8M-Cache-3_40-GHz" target="_blank">E3-1231</a></td>
									<td>32 GB</td>
									<td>2x 2TB SATA</td>
									<td>500 Mbps</td>
									<td>OVH</td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<th><?php echo lepus_getTariffPrice(20); ?></th>
									<td><?php echo lepus_orderLink(); ?></td>
								</tr>
								<tr>
									<td>SP-32-SSD</td>
									<td><a href="http://ark.intel.com/ru/products/80910/Intel-Xeon-Processor-E3-1231-v3-8M-Cache-3_40-GHz" target="_blank">E3-1231</a></td>
									<td>32 GB</td>
									<td>2x 480GB SSD</td>
									<td>500 Mbps</td>
									<td>OVH</td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<th><?php echo lepus_getTariffPrice(21); ?></th>
									<td><?php echo lepus_orderLink(); ?></td>
								</tr>
								<tr>
									<td>SP-64</td>
									<td><a href="http://ark.intel.com/ru/products/82764/Intel-Xeon-Processor-E5-1630-v3-10M-Cache-3_70-GHz" target="_blank">E5-1630</a></td>
									<td>64 GB</td>
									<td>2x 2TB SATA</td>
									<td>500 Mbps</td>
									<td>OVH</td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<th><?php echo lepus_getTariffPrice(22); ?></th>
									<td><?php echo lepus_orderLink(); ?></td>
								</tr>
								<tr>
									<td>SP-64-SSD</td>
									<td><a href="http://ark.intel.com/ru/products/82764/Intel-Xeon-Processor-E5-1630-v3-10M-Cache-3_70-GHz" target="_blank">E5-1630</a></td>
									<td>64 GB</td>
									<td>2x 480GB SSD</td>
									<td>500 Mbps</td>
									<td>OVH</td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<th><?php echo lepus_getTariffPrice(23); ?></th>
									<td><?php echo lepus_orderLink(); ?></td>
								</tr>
								<tr>
									<td>SP-128</td>
									<td><a href="http://ark.intel.com/ru/products/82765/Intel-Xeon-Processor-E5-1650-v3-15M-Cache-3_50-GHz" target="_blank">E5-1650</a></td>
									<td>128 GB</td>
									<td>2x 2TB SATA</td>
									<td>500 Mbps</td>
									<td>OVH</td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<th><?php echo lepus_getTariffPrice(40); ?></th>
									<td><?php echo lepus_orderLink(); ?></td>
								</tr>
								<tr>
									<td>SP-128-SSD</td>
									<td><a href="http://ark.intel.com/ru/products/82765/Intel-Xeon-Processor-E5-1650-v3-15M-Cache-3_50-GHz" target="_blank">E5-1650</a></td>
									<td>128 GB</td>
									<td>2x 480GB SSD</td>
									<td>500 Mbps</td>
									<td>OVH</td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<th><?php echo lepus_getTariffPrice(41); ?></th>
									<td><?php echo lepus_orderLink(); ?></td>
								</tr>
								<tr>
									<td>MG-128</td>
									<td>2x <a href="http://ark.intel.com/ru/products/83356/Intel-Xeon-Processor-E5-2630-v3-20M-Cache-2_40-GHz" target="_blank">E5-2630</a></td>
									<td>128 GB</td>
									<td>2x 2TB SATA</td>
									<td>1 Gbps</td>
									<td>OVH</td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<th><?php echo lepus_getTariffPrice(24); ?></th>
									<td><?php echo lepus_orderLink(); ?></td>
								</tr>
								<tr>
									<td>MG-128-SSD</td>
									<td>2x <a href="http://ark.intel.com/ru/products/83356/Intel-Xeon-Processor-E5-2630-v3-20M-Cache-2_40-GHz" target="_blank">E5-2630</a></td>
									<td>128 GB</td>
									<td>2x480GB SSD</td>
									<td>1 Gbps</td>
									<td>OVH</td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<th><?php echo lepus_getTariffPrice(25); ?></th>
									<td><?php echo lepus_orderLink(); ?></td>
								</tr>
								<tr>
									<td>MG-256</td>
									<td>2x <a href="http://ark.intel.com/ru/products/81705/Intel-Xeon-Processor-E5-2650-v3-25M-Cache-2_30-GHz" target="_blank">E5-2650</a></td>
									<td>256 GB</td>
									<td>2x 2TB SATA</td>
									<td>1 Gbps</td>
									<td>OVH</td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<th><?php echo lepus_getTariffPrice(26); ?></th>
									<td><?php echo lepus_orderLink(); ?></td>
								</tr>
								<tr>
									<td>MG-256-SSD</td>
									<td>2x <a href="http://ark.intel.com/ru/products/81705/Intel-Xeon-Processor-E5-2650-v3-25M-Cache-2_30-GHz" target="_blank">E5-2650</a></td>
									<td>256 GB</td>
									<td>2x480GB SSD</td>
									<td>1 Gbps</td>
									<td>OVH</td>
									<td><img src="https://lepus.su/images/flags/france-flag.png" title="France"></td>
									<th><?php echo lepus_getTariffPrice(27); ?></th>
									<td><?php echo lepus_orderLink(); ?></td>
								</tr>
							</tbody>
						</table>
						Бесплатно <a href="https://github.com/poiuty/lepus.su/wiki/%D0%9F%D0%B5%D1%80%D0%B5%D0%BD%D0%BE%D1%81-%D1%81%D0%B0%D0%B9%D1%82%D0%BE%D0%B2" target="_blank">перенесем ваши сайты</a>. В дата центре OVH работает фильтрация tcp/ udp DDOS.<br/>
						Обработка вашего заказа может занять время. Если вам срочно требуется сервер => обратитесь в поддержку.<br/>
						Дополнительный IP: 300 рублей/ один раз. E3-x-x => max 16 дополнительных IP. На всех остальных max 256.<br/>
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
