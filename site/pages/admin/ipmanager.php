<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');
if($user['data']['access'] < 2) die('no_access');
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
		<link rel="stylesheet" type="text/css" href="/css/chosen.css">
		<link rel="stylesheet" href="/css/dataTables.bootstrap.css">
		<style>
			.col-centered{ float: none; margin: 0 auto; }
			td,th { text-align: center; vertical-align: middle; }
			blockquote { background: #f9f9f9; border-left: 10px solid #ccc; margin: 1.5em 10px; padding: 0.5em 10px; }
		</style>
		<script src="/js/jquery.min.js"></script>
		<script src="/js/jquery.dataTables.min.js"></script>
		<script src="/js/dataTables.bootstrap.js"></script>
		<script src="/js/bootstrap.min.js"></script>
		<script src="/js/alertify.js"></script>
		<script src="/js/lepus.js"></script>
		<script type="text/javascript" charset="utf-8"> $(document).ready(function() { $('#IPmanagerList').dataTable({ "order": [[ 0, "desc" ]] }); }); </script>
	</head>
	<body>
		<div class="wrapper">
			<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/menu.php'); ?>
			<div class="content-box">
				<div class="content-info box-shadow--2dp">
					<div class="content-text">
						<div class="page-title">Управление IP адресами</div>
						На этой странице вы можете выполнить следующие действия с IP адресами.<br/>
						Просмотр, поиск свободных адресов, редактирование, добавление, удаление.<br/>
						<hr/>
						<div class="form-inline">
							<center><input class="form-control" id="ipAddress" style="width: 24%;" type="text" name="count" value="" required="" placeholder="127.0.0.1">
							<input class="form-control" id="ipMAC"  style="width: 24%;"  type="text" name="count" value="" required="" placeholder="00:0a:95:9d:68:16">
							<select id="ipServer" data-placeholder="Сервер..." class="chosen-select" style="width: 20%;">
								<option value=""></option>
									<option value=0>empty</option>
									<?php echo lepus_getHTMLSelect('servers', 'domain'); ?>
							</select>
							<select id="ipUser" data-placeholder="Пользователь..." class="chosen-select" style="width: 29.9%;">
								<option value=""></option>
									<?php echo lepus_getHTMLSelect('users', 'login'); ?>
							</select></center>
							<input class="btn btn-sm btn-danger btn-block" data-admin-addip style="margin-top: 2px;" type="submit" value="Добавить IP адрес">
						</div>		
						<hr/>
						<table id="IPmanagerList" class="table table-striped table-bordered" cellspacing="0" width="100%">
							<thead>
								<tr>
									<th>ID</th>
									<th>IP</th>
									<th>Server</th>
									<th>SID</th>
									<th>Owner</th>
									<th>MAC</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php echo admin_lepus_getIPlist(); ?>
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
		<script src="/js/chosen.jquery.min.js" type="text/javascript"></script>
		<script type="text/javascript">
			var config = {
				'.chosen-select'           : {},
				'.chosen-select-deselect'  : {allow_single_deselect:true},
				'.chosen-select-no-single' : {disable_search_threshold:10},
				'.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
				'.chosen-select-width'     : {width:"95%"}
			}
			for (var selector in config) {
				$(selector).chosen(config[selector]);
			}
		</script>
		<script src="//www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
	</body>
</html>
