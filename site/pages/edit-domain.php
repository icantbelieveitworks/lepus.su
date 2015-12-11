<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');
if(empty($user)){
	header('refresh: 3; url=http://lepus.dev');
	die;
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
		<style>
			.col-centered{ float: none; margin: 0 auto; }
			td,th { text-align: center; vertical-align: middle; }
		</style>
		<script src="/js/jquery.min.js"></script>
		<script src="/js/jquery.jeditable.mini.js"></script>
		<script src="/js/bootstrap.min.js"></script>
		<script src="/js/alertify.js"></script>
		<script src="/js/lepus.js"></script>
	</head>
	<body>
		<div class="wrapper">
			<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/menu.php'); ?>
			<div class="logo"></div>
			<div class="information">
			<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/news.php'); ?>
			</div>
			<div class="container">
				<div class="content-box">
					<div class="content-info">
						<div class="content-text">
							<div class="page-title">Управление доменом lolka.ru</div>
							<div class="row">					
								<div class="col-lg-14">
									<div class="col-lg-12">
										На этой странице - вы можете управлять записями на DNS.<br/>
										<hr/>
									</div>
									<div class="col-lg-9 col-centered">
										<div class="form-inline">
											<input class="form-control" id="dnsDomain" style="width: 204px;" type="text" name="count" value="" required="" placeholder="example.com">
												<select class="form-control" id="dnsDomainType" name="type">
													<option value="master" selected="">MASTER</option>
													<option value="slave">SLAVE</option>
												</select>
												<input class="form-control" id="dnsDomainMaster"  style="width: 208px;"  type="text" name="count" value="" required="" placeholder="8.8.8.8 (только для SLAVE)">
											<input class="btn btn-sm btn-danger btn-block" data-dns-domain-add style="margin-top: 2px;" type="submit" value="Пополнить счет">
										</div>
									</div>
									<div class="col-lg-12">
										<hr/>
										<table class="table table-striped table-bordered" cellspacing="0" width="100%">
											<thead>
												<tr>
													<th>ID</th>
													<th>Запись</th>
													<th>Тип</th>
													<th>Данные</th>
													<th>Приоритет</th>
													<th>Действия</th>
												</tr>
											</thead>
											<tbody>
												<?php	$tmpData = lepus_get_dnsAccess($_GET['id'], $user['id']);
														if($tmpData == 'deny') echo "<center><b><font color=\"Brown\">!!! Access denied !!!</font></b></center>";
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
		</div>
		<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/footer.php'); ?>
		<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/modal.php'); ?>
		<script src="//www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
		<script>
			$(document).on('click', '.edit', function(){
				$('.edit').editable('/public/handler/handler_user_actions.php?action=edit_domain', {
					indicator : 'Сохранение...',
					tooltip   : 'Кликните чтобы изменить...',
					event     : "dblclick",
					style: "inherit",
					height: 'none'
				});
			});
			$(document).on('click', '.edit_type', function(){
				$('.edit_type').editable('/public/handler/handler_user_actions.php?action=edit_domain', {
					indicator : 'Сохранение...',
					tooltip   : 'Кликните чтобы изменить...',
					data   : " {'A':'A','AAAA':'AAAA','CNAME':'CNAME','MX':'MX','NS':'NS','TXT':'TXT','SRV':'SRV','PTR':'PTR',}",
					type   : 'select',
					submit: 'OK',
					event     : "dblclick"
				});
			});
		</script>
	</body>
</html>
