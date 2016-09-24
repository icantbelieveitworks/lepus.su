<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');
if(empty($user)){
	$tmpData = error('no_auth_page');
	die(lepus_error_page($tmpData['mes']));
}
$tmpData = lepus_get_dnsAccess($_GET['id'], $user['id']);
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
		<style>
			.col-centered{ float: none; margin: 0 auto; }
			td,th { text-align: center; vertical-align: middle; }
			table td { word-wrap: break-word; overflow-wrap: break-word; }
		</style>
		<script src="/js/jquery.min.js"></script>
		<script src="/js/jquery.jeditable.mini.js"></script>
		<script src="/js/bootstrap.min.js"></script>
		<script src="/js/alertify.js"></script>
		<script src="/js/lepus.js"></script>
		<?=$head_code?>
	</head>
	<body>
		<div class="wrapper">
			<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/menu.php'); ?>
				<div class="content-box">
					<div class="content-info box-shadow--2dp">
						<div class="content-text">
							<div class="page-title">Управление доменом <?php echo $tmpData; ?></div>
							<div class="row">					
								<div class="col-lg-14">
									<div class="col-lg-12">
										На этой странице - вы можете управлять записями на DNS.<br/>
										Про типы записей можно прочитать <a href="https://ru.wikipedia.org/wiki/%D0%A2%D0%B8%D0%BF%D1%8B_%D1%80%D0%B5%D1%81%D1%83%D1%80%D1%81%D0%BD%D1%8B%D1%85_%D0%B7%D0%B0%D0%BF%D0%B8%D1%81%D0%B5%D0%B9_DNS" target="_blank">по этой ссылке</a>.<br/>
										Для slave можно только просматривать записи.
										<hr/>
									</div>
									<div class="col-lg-11 col-centered">
										<div class="form-inline">
											<input class="form-control" id="dnsZone" style="width: 200px;" type="text" name="count" value="" required="" placeholder="test.example.com">
												<select class="form-control" id="dnsZoneType" name="type">
													<option value="A" selected="">A</option>
													<option value="AAAA">AAAA</option>
													<option value="CNAME">CNAME</option>
													<option value="MX">MX</option>
													<option value="NS">NS</option>
													<option value="TXT">TXT</option>
													<option value="SRV">SRV</option>
													<option value="PTR">PTR</option>
													<option value="SOA">SOA</option>
												</select>
												<input class="form-control" id="dnsZoneData"  style="width: 266px;"  type="text" name="count" value="" required="" placeholder="127.0.0.1">
												<input class="form-control" id="dnsZonePrio"  style="width: 120px;"  type="text" name="count" value="" required="" placeholder="10 [приоритет]">
												<input id="dnsDomainZoneID" type="hidden" value=<?php echo intval($_GET['id']);?>>
											<input class="btn btn-sm btn-danger btn-block" data-dns-zone-add style="margin-top: 2px;" type="submit" value="Добавить запись">
										</div>
									</div>
									<div class="col-lg-12">
										<hr/>
										<table id="dnsZone" class="table table-striped table-bordered" cellspacing="0" style="width: 100%; table-layout: fixed;">
											<thead>
												<tr>
													<th style="width: 5%;">ID</th>
													<th style="width: 25%;">Запись</th>
													<th style="width: 12%;">Тип</th>
													<th style="width: 35%;">Данные</th>
													<th style="width: 12%;">Приоритет</th>
													<th style="width: 11%;">Действия</th>
												</tr>
											</thead>
											<tbody>
												<?php	if($tmpData == 'deny') echo "<center><b><font color=\"Brown\">!!! Access denied !!!</font></b></center>";
															else echo lepus_get_dnsRecords($_GET['id']);
												?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/navi.php'); ?>
		</div>
		<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/footer.php'); ?>
		<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/modal.php'); ?>
		<script src="//www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
		<script>
			$(document).on('click', '.edit', function(){
				$('.edit').editable('/public/change_dnsRecords.php', {
					loadurl  : '//'+document.domain+'/public/change_dnsRecords.php?load',
					indicator : 'Сохранение...',
					event     : "dblclick",
					style: "inherit",
					height: 'none'
				});
			});
			$(document).on('click', '.edit_type', function(){
				$('.edit_type').editable('/public/change_dnsRecords.php', {
					indicator : 'Сохранение...',
					data   : " {'A':'A','AAAA':'AAAA','CNAME':'CNAME','MX':'MX','NS':'NS','TXT':'TXT','SRV':'SRV','PTR':'PTR', 'SOA':'SOA'}",
					type   : 'select',
					submit: 'OK',
					event     : "dblclick"
				});
			});
		</script>
	</body>
</html>
