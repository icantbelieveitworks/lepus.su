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
		<link rel="stylesheet" href="//cdn.datatables.net/plug-ins/1.10.10/integration/bootstrap/3/dataTables.bootstrap.css">
		<style>
			.col-centered{ float: none; margin: 0 auto; }
			td,th { text-align: center; vertical-align: middle; }
			blockquote { background: #f9f9f9; border-left: 10px solid #ccc; margin: 1.5em 10px; padding: 0.5em 10px; }
		</style>
		<script src="/js/jquery.min.js"></script>
		<script src="//cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js"></script>
		<script src="//cdn.datatables.net/plug-ins/1.10.10/integration/bootstrap/3/dataTables.bootstrap.js"></script>
		<script src="/js/bootstrap.min.js"></script>
		<script src="/js/alertify.js"></script>
		<script src="/js/lepus.js"></script>
		<script type="text/javascript" charset="utf-8"> $(document).ready(function() { $('#dnsDomainsList').dataTable({ "order": [[ 0, "desc" ]] }); }); </script>
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
							<div class="page-title">DNS хостинг</div>
							<div class="row">					
								<div class="col-lg-14">
									<div class="col-lg-12">
										На этой странице - вы можете добавить домен и управлять записями на DNS.<br/>
										Если вы выбираете MASTER => то все записи нужно добавить через личный кабинет.<br/>
										Если вы выбираете SLAVE => записи будут автоматически скопированы с MASTER DNS (который вы укажите).<br/><br/>
										Для SLAVE => отключена возможность редактировать записи. Можно только просматривать их.<br/>
										Для SLAVE => на MASTER DNS (для наших IP) нужно разрешить allow-transfer и also-notify.<br/>
										Пример для bind9 => /etc/bind/named.conf.options
										<blockquote>
<pre>
options {
	directory "/var/cache/bind";
	
	dnssec-validation auto;

	recursion no;        # http://habrahabr.ru/post/235197/
	auth-nxdomain no;    # conform to RFC1035
	listen-on-v6 { any; };
	notify yes;
	listen-on-v6 { any; };
	allow-transfer { 5.9.164.59; };
	also-notify { 5.9.164.59; };
};
</pre>
										</blockquote>
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
											<input class="btn btn-sm btn-danger btn-block" data-dns-domain-add style="margin-top: 2px;" type="submit" value="Добавить домен">
										</div>
									</div>
									<div class="col-lg-12">
										<hr/>
										<table id="dnsDomainsList" class="table table-striped table-bordered" cellspacing="0" width="100%">
											<thead>
												<tr>
													<th>ID</th>
													<th>Домен</th>
													<th>Тип</th>
													<th>Мастер</th>
													<th>Действия</th>
												</tr>
											</thead>
											<tbody>
												<?php echo lepus_get_dnsDomains($user['id']); ?>
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
	</body>
</html>
