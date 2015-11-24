<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Lepus - интернет хостинг</title>
		<meta name="description" content="Виртуальный хостинг, быстрые VPS, выделенные серверы по привлекательной цене." />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="yandex-verification" content="6940b644b3235f76" />
		<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
		<link rel="stylesheet" type="text/css" href="css/reset.css"/>
		<link rel="stylesheet" type="text/css" href="css/style.css"/>
		<link rel="stylesheet" type="text/css" href="css/alertify.core.css" />
		<link rel="stylesheet" type="text/css" href="css/alertify.bootstrap.css" />
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/alertify.js"></script>
		<script src="js/lepus.js"></script>
	</head>
	<body>
		<div class="wrapper">
			<div class="top-menu">
				<ul>
					<li><a href="/" class="menu-home" rel="nofollow"></a></li>
					<li><a href="/outsourcing.html" class="menu-about" rel="nofollow"></a></li>
					<li><a href="/domains.html" class="menu-prices" rel="nofollow"></a></li>
					<li><a href="/stock.html" class="menu-partner" rel="nofollow"></a></li>
					<li><a href="/ispmanager.html" class="menu-online" rel="nofollow"></a></li>
					<li><a href="/contacts.html" class="menu-contacts" rel="nofollow"></a></li>
				</ul>
			</div>
			<div class="logo"></div>
			<div class="information">
				<div class="news-box">
					<div class="news">
						<div class="body">
							<div class="title">
								<span class='date'>2014-08-01</span>
								<a role="button" tabindex="0">PayPal</a>
							</div>
							Теперь вы можете пополнить счет в личном кабинете через PayPal.
						</div>					
						<div class="body">
							<div class="title">
								<span class='date'>2014-04-12</span>
								<a role="button" tabindex="0">Антивирусная проверка</a>
							</div>
							Сделали автоматическое еженедельное сканирование VPS, виртуального хостинга с отправкой отчета на почту клиента.
						</div>					
						<div class="body">
							<div class="title">
								<span class='date'>2014-04-09</span>
								<a role="button" tabindex="0">Обновление тарифов</a>
							</div>
							Еще больше места на виртуальном хостинге!<br/>
							Добавлена возможность использовать SSL для сайтов.<br/>
							Бесплатно помогаем получить SSL сертификаты StartCom Class 1
						</div>					
						<b>Техническая поддержка, icq: 450420625</b><br/>
						Наши DNS серверы: ns1.lepus.su и ns2.poiuty.com<br/>
					</div>
				</div>
			</div>
			<div class="container">
				<div class="content-box">
					<div class="content-info">
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
								Находим индивидуальный подход к каждому клиенту.  <br/>
								Оказываем профессиональные услуги по технической поддержке, администрированию и сопровождению проектов.<br/>
								Самое ценное - это вы, наши клиенты. Мы болеем за вас, радуемся вашим победам и успехам! Добро пожаловать!<br/>
							</p>
						</div>
					</div>
				</div>
				<div class="blocks">
					<div class="block-login">
						<div class="block-body">
							<input class="form-control" id="login_email" placeholder="E-mail" name="email" type="email" value="" autofocus="">
							<input class="form-control" id="login_passwd" style="margin-top: 3px;" placeholder="Password" name="password" type="password" value="" autofocus="">
							<input type="submit" name="login" class="btn btn-sm btn-success btn-block" style="margin-top: 15px;" data-do-login value="Войти">
						</div>
					</div>
					<div class="block-info1" style="margin-top: 15px;">
						<div class="block-body">
							<input type="submit" name="login" class="btn btn-sm btn-success btn-block" data-register-open value="Регистрация">
							<input type="submit" name="login" class="btn btn-sm btn-success btn-block" data-lost-passwd value="Забыл пароль">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="footer-box">
			<div class="footer">
				<p class="author">
					<del>Дизайн — <a href="http://www.o-kvadrat.ru" target="_blank" rel="nofollow">Веб-студия «КРУГЛЫЙ КВАДРАТ»</a></del>
				</p>
				<p class="copyright">
					&copy; Lepus Hosting (<a href="https://lepus.su/oferta.html">публичный договор оферты</a>)
				</p>
				<div class="banners">
					<div>
						<a href="https://money.yandex.ru" target="_blank"> <img src="/images/other/yamoney_logo88x31.gif" alt="Я принимаю Яндекс.Деньги" title="Я принимаю Яндекс.Деньги" border="0" width="88" height="31"/></a>
						<a href="http://www.webmoney.ru/" target="_blank"><img src="/images/other/88x31_wm_blue_on_transparent_ru.png" border="0" width="88" height="31"/></a>
						<a href="https://qiwi.ru/" target="_blank" rel="nofollow"><img width="88" height="31" border="0" src="/images/ico/qiwi_b.png" /></a>
					</div>
				</div>
			</div>
		</div>
		<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/pages/modal.php'); ?>
		<script src="//www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
	</body>
</html>
