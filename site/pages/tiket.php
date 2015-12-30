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
$tmpData = lepus_get_supportMsg($_GET['id'], $user['id'], $user['data']['access']);
if(!is_array($tmpData)) die('no accsess');
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
			.panelbg { background: #FAFAFA; }
			.myLabel { font-size: 85%; background: #999999;}
		</style>
		<script src="/js/jquery.min.js"></script>
		<script src="//cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js"></script>
		<script src="//cdn.datatables.net/plug-ins/1.10.10/integration/bootstrap/3/dataTables.bootstrap.js"></script>
		<script src="/js/bootstrap.min.js"></script>
		<script type="text/javascript" charset="utf-8"> $(document).ready(function() { $('#supportList').dataTable({ "order": [[ 0, "desc" ]] }); }); </script>
		<script src="/js/alertify.js"></script>
		<script src="/js/lepus.js"></script>
		<script>
			function f(){
				count = parseInt($('input[id=countMSG]').val());
				tid = $('input[id=tiketID]').val();
				$.post("//"+document.domain+"/public/support.php", {do: 'update_msg', tid: tid, count: count}, function( data ){
					if(data != 'no_mes'){
						snd.play();
						$('input[id=countMSG]').val(count+1);
						$("#messageList").prepend(data);		
					}else{
						alertify.error(data);
					}
				});
				setTimeout(f, 10000);
			}
			$(document).ready(function(){
				f();
			});
		</script>
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
							<div class="page-title"><?php echo $tmpData['title']; ?></div>
							<div class="row">					
								<div class="col-lg-12">							
									<textarea id="tiketMsg" class="form-control" rows="5" id="comment" style="resize:vertical;" placeholder=""></textarea>
									<input id="tiketID" type="hidden" value="<?php echo $_GET['id']; ?>">
									<input id="countMSG" type="hidden" value="<?php echo $tmpData['countMSG']; ?>">
									
										<div class="form-inline" style="padding-top: 7px;">
											<center>
												<input class="form-control btn btn-sm btn-danger" style="width: 49%;" data-tiket-send-msg type="submit" value="Написать ответ в тикет">
												<input class="form-control btn btn-sm btn-danger" style="width: 49%;" type="submit" value="Проблема решена">
											</center>
										</div>
									
									<hr>
									<div id="messageList" style="word-wrap: break-word;">
										<?php echo $tmpData['msg']; ?>
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
