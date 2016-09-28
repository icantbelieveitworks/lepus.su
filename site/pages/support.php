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
		<link rel="stylesheet" type="text/css" href="/css/chosen.css">
		<link rel="stylesheet" href="/css/dataTables.bootstrap.css">
		<style>
			.myLabel { font-size: 85%; }
			td,th { text-align: center; vertical-align: middle !important; }
		</style>
		<script src="/js/jquery.min.js"></script>
		<script src="/js/jquery.dataTables.min.js"></script>
		<script src="/js/dataTables.bootstrap.js"></script>
		<script src="/js/bootstrap.min.js"></script>
		<script type="text/javascript" charset="utf-8">
			$(document).ready(function() {
				$('#supportList').dataTable({
					"processing": true,
					"serverSide": true,
					"ajax": { "url":"/public/support.php", "type":"POST" },
					"order": [[ 0, "desc" ]]
				});
			});
			setInterval( function () { $('#supportList').DataTable().ajax.reload( null, false ); }, 30000 );
		</script>
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
							<div class="page-title">Техническая поддержка</div>
							<div class="row">					
								<div class="col-lg-12">
									За день вы можете открыть не более 10 тикетов.<br/>
									Дорогие пользователи, мы работаем без выходных и праздников с 10.00 до 20.00<br/>
									<hr>
									<div class="form-inline">
										<center>
											<?php if($user['data']['access'] < 2){ ?>
												<input class="form-control" id="tiketTitle" style="width: 44%;" type="text" name="count" value="" required="" placeholder="Заголовок">
												<input class="form-control btn btn-sm btn-danger btn-block" data-open-new-tiket style="width: 55%;" type="submit" value="Открыть новый тикет">
											<?php }else{ ?>
												<select id="tiketUser" data-placeholder="Выберите пользователя..." class="chosen-select" style="width: 29%;">
													<option value="no"></option>
													<?php echo lepus_getHTMLSelect('users', 'login'); ?>
												</select>	
												<input class="form-control" id="tiketTitle" style="width: 39%;" type="text" name="count" value="" required="" placeholder="Заголовок">
												<input class="form-control btn btn-sm btn-danger btn-block" data-open-new-tiket style="width: 30%;" type="submit" value="Открыть новый тикет">
											<?php } ?>
										</center>
									</div>
									<div style="padding-top: 5px;">
										<textarea id="tiketMsg" class="form-control" rows="5" id="comment" style="resize:vertical;" placeholder="Подробное описание вашей проблемы."></textarea>
									</div>
									<hr>	
									<table id="supportList" class="table table-striped table-bordered" cellspacing="0" width="100%">
										<thead>
											<tr>
												<th>ID</th>
												<th>Тема</th>
												<th>Создан</th>
												<th>Последний ответ</th>
												<th>Статус</th>
											</tr>
										</thead>
										<tbody>
										</tbody>
									</table>
								</div>
							</div>
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
