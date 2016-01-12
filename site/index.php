<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');
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
		<script src="/js/jquery.min.js"></script>
		<script src="/js/bootstrap.min.js"></script>
		<script src="/js/alertify.js"></script>
		<script src="/js/lepus.js"></script>
	</head>
	<body>
		<div class="wrapper">
			<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/menu.php'); ?>
			<div class="content-box">
				<div class="content-info box-shadow--2dp">
					<div class="content-text">
						<div class="page-title">Lepus хостинг</div>
						<p>
							— <a href="/domains.html" target="_blank" rel="nofollow">Регистрация доменов</a>.<br/>
							— Moneyback по первому требованию.<br/>
							— <a href="/ispmanager.html" rel="nofollow">Лицензии ISPmanager</a> по доступным ценам.<br/>
							— Мощные <a href="/vps.html">администрируемые VPS</a> на SSD дисках.<br/>
							— Бесплатный перенос ваших сайтов на наш хостинг.<br/>
							— <a href="/shared.html">Виртуальный хостинг</a> и <a href="/vip.html" rel="nofollow">VIP хостинг</a> для ваших сайтов.<br/>
							— Быстрые администрируемые <a href="/dedicated.html">выделенные серверы</a> по выгодной цене.<br/><br/>
							Наша миссия - предоставление качественных услуг хостинга. 
							Находим индивидуальный подход к каждому клиенту.<br/>
							Оказываем профессиональные услуги по технической поддержке, администрированию и сопровождению проектов.<br/>
							Самое ценное - это вы, наши клиенты. Мы болеем за вас, радуемся вашим победам и успехам! Добро пожаловать!<br/>
						</p>
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
