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
		<link rel="stylesheet" href="/css/dataTables.bootstrap.css">
		<style>
			td,th { text-align: center; vertical-align: middle; }
			.col-centered{ float: none; margin: 0 auto; }
			blockquote { background: #f9f9f9; border-left: 10px solid #ccc; margin: 1.5em 10px; padding: 0.5em 10px; }
		</style>
		<script src="/js/jquery.min.js"></script>
		<script src="/js/jquery.dataTables.min.js"></script>
		<script src="/js/dataTables.bootstrap.js"></script>
		<script src="/js/bootstrap.min.js"></script>
		<script src="/js/alertify.js"></script>
		<script src="/js/lepus.js"></script>
	<script type="text/javascript" charset="utf-8"> $(document).ready(function() { $('#cronTable').dataTable({ "order": [[ 0, "desc" ]] }); }); </script>
	</head>
	<body>
		<div class="wrapper">
			<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/menu.php'); ?>
				<div class="content-box">
					<div class="content-info box-shadow--2dp">
						<div class="content-text">
							<div class="page-title">Планировщик задач</div>
							<div class="row">					
								<div class="col-lg-14">
									<div class="col-lg-12">
										Время на сервере GTM+4:00 (Москва).<br/>
										Вы можете добавить не более 100 заданий.<br/>
										Допустимые символы в поле время: 0-9 и *<br/>
										Допустимые символы в поле URL: 0-9a-zA-Z.=_&-?:/<br/>
										Формат времени: минута (0-59), час (0-23), день месяца (1-31), месяц (1-12), день_недели (0-6).<br/><br/>

										Например мы хотим, чтобы скрипт http://mysite.ru/cron.php запускался раз в две минуты.
										<blockquote>
											*/2 * * * * http://mysite.ru/cron.php
										</blockquote>
										Или чтобы скрипт запускался каждый день в 12 часов.
										<blockquote>
											0 12 * * * http://mysite.ru/cron.php
										</blockquote>
										<hr/>
									</div>
									<div class="col-lg-12 ">
										<div class="form-inline col-centered" style="width: 74%">
											<input class="form-control" id="cronTime" style="width: 120px;" type="text" name="count" value="" required="" placeholder="*/2 * * * *">
											<input class="form-control" id="cronURL"  style="width: 440px;"  type="text" name="count" value="" required="" placeholder="http://mysite.ru/cron.php">
											<input class="btn btn-sm btn-danger btn-block" data-cron-add style="margin-top: 2px;" type="submit" value="Сохранить задание">
										</div>
									</div>
									<div class="col-lg-12">
										<hr/>
										<table id="cronTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
											<thead>
												<tr>
													<th>ID</th>
													<th>Время</th>
													<th>URL</th>
													<th>Действия</th>
												</tr>
											</thead>
											<tbody>
												<?php echo lepus_getCronList($user['id']); ?>
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
	</body>
</html>
