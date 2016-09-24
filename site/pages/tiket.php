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
$_GET['id'] = intval($_GET['id']);
$tmpData = lepus_get_supportMsg($_GET['id'], $user['id'], $user['data']['access']);
if(!is_array($tmpData)) die('no accsess');
if($tmpData["title"] === NULL){
	header("refresh: 3; url=https://".$_SERVER['SERVER_NAME']."/pages/support.php");
	die;
}
$tmpTitle = '';
if(strlen($tmpData['title']) > 30){
	$tmpTitle = "title='{$tmpData['title']}'";
	$tmpData['title'] = mb_substr($tmpData['title'], 0, 28,'utf-8')."...";	 
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
			.panelbg { background: #FAFAFA; }
			.myLabel { font-size: 85%; }
			.myColor { background: #999999; }
			blockquote { background: #f9f9f9; border-left: 10px solid #ccc; margin: 1.5em 10px; padding: 0.5em 10px; font-size: 100%; }
		</style>
		<script src="/js/jquery.min.js"></script>
		<script src="/js/jquery.dataTables.min.js"></script>
		<script src="/js/dataTables.bootstrap.js"></script>
		<script src="/js/bootstrap.min.js"></script>
		<script type="text/javascript" charset="utf-8"> $(document).ready(function() { $('#supportList').dataTable({ "order": [[ 0, "desc" ]] }); }); </script>
		<script src="/js/alertify.js"></script>
		<script src="/js/lepus.js"></script>
		<?=$head_code?>
		<script>
			function f(){
				count = parseInt($('input[id=countMSG]').val());
				tid = $('input[id=tiketID]').val();
				$.post("//"+document.domain+"/public/support.php", {do: 'update_msg', tid: tid, count: count}, function(json){
					data = JSON.parse(json);
					if(data.err == 'OK'){
						if(data.mes != 'no_mes'){
							snd.play();
							alertify.success('Новое сообщение');
							$('input[id=countMSG]').val(count+1);
							$("#messageList").prepend(data.mes.msg);		
						}
						if(data.mes.status){
							if(data.mes.status == 1){
								$("#tiketStatus1").show();
								$("#tiketStatus2").hide();
							}else{
								$("#tiketStatus1").hide();
								$("#tiketStatus2").show();
							}
						}
					}else{
						alertify.error(data.err);
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
				<div class="content-box">
					<div class="content-info box-shadow--2dp">
						<div class="content-text">
							<div class="page-title" <?php echo $tmpTitle; ?>><?php echo "Тикет #".$_GET['id'].": {$tmpData['title']}"; ?></div>
							<div class="row">					
								<div class="col-lg-12">
									<div id="tiketStatus1" <?php if($tmpData['status'] == 2) echo 'style="display: none;"'; ?>>
									<blockquote>
										[b]жирный шрифт[/b] => <b>жирный шрифт</b><br/>
										[i]наклонный шрифт[/i] => <i>наклонный шрифт</i><br/>
										[u]подчеркнутый текст[/u] => <u>подчеркнутый текст</u><br/>
										[s]зачеркнутый шрифт[/s] => <s>зачеркнутый шрифт</s><br/><br/>
										[url]http://google.ru[/url] => <a href="http://google.ru" target="_blank">http://google.ru</a><br>
										[url]https://google.ru[/url] => <a href="https://google.ru" target="_blank">https://google.ru</a><br><br>
										[url=http://google.ru]google google google![/url] => <a href="http://google.ru" target="_blank"> google google google!</a><br>
										[url=https://google.ru]google google google![/url] => <a href="https://google.ru" target="_blank">google google google!</a><br>	
									</blockquote>
									<hr>
									<textarea id="tiketMsg" class="form-control" rows="5" id="comment" style="resize:vertical;" placeholder=""></textarea>
									<input id="tiketID" type="hidden" value="<?php echo $_GET['id']; ?>">
									<input id="countMSG" type="hidden" value="<?php echo $tmpData['countMSG']; ?>">
										<div class="form-inline" style="padding-top: 7px;">
											<center>
												<input id="sendMSG" class="form-control btn btn-sm btn-danger" style="width: 49%;" data-tiket-send-msg type="submit" value="Написать ответ в тикет">
												<input id="endTiket" class="form-control btn btn-sm btn-danger" style="width: 49%;" data-tiket-send-close type="submit" value="Проблема решена">
											</center>
										</div>
										</div>
									<div id="tiketStatus2" <?php if($tmpData['status'] == 2) echo 'style="display: inline;"'; else echo 'style="display: none;"'; ?>>
										<center><input id="reopenTiket" class="form-control btn btn-sm btn-danger" style="width: 49%;" data-tiket-send-reopen type="submit" value="Проблема не решена?"></center>
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
		<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/footer.php'); ?>
		<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/modal.php'); ?>
		<script src="//www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
	</body>
</html>
