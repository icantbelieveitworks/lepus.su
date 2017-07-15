<?php
if(empty($user)){ ?>
<div class="blocks">
	<div class="block-login box-shadow--2dp">
		<div class="block-body">
			<input class="form-control" id="login_email" placeholder="E-mail" name="email" type="email" value="" autofocus="">
			<input class="form-control" id="login_passwd" style="margin-top: 3px;" placeholder="Password" name="password" type="password" value="" autofocus="">
			<input class="form-control" id="login_2facode" style="margin-top: 3px;" placeholder="2FA code" name="2facode" type="text" maxlength="6" value="" autofocus="">
			<input id="check_auth" type="submit" name="login" class="btn btn-sm btn-success btn-block" style="margin-top: 15px;" data-do-login value="Войти">
		</div>
	</div>
	<div class="block-info1 box-shadow--2dp" style="margin-top: 15px;">
		<div class="block-body">
			<input type="submit" name="login" class="btn btn-sm btn-success btn-block" data-register-open value="Регистрация">
			<input type="submit" name="login" class="btn btn-sm btn-success btn-block" data-lost-passwd value="Забыл пароль">
		</div>
	</div>
</div>
<?php }else{ ?>
<div class="blocks">
	<div class="block-login box-shadow--2dp">
		<center style="padding-top:15px;">
			<a href="/public/logout.php" title="Выход"><b><?php echo $user['login']; ?></b></a>
		</center>
		<div class="block-body" style="margin-left: 15px; padding-top: 0px;">
			<ul>
				<?php if($user['data']['access'] > 1){ ?>
				<hr/>
				<li><a href="/pages/admin/income.php">Доход</a></li>
				<li><a href="/pages/admin/send.php">Рассылка</a></li>
				<li><a href="/pages/admin/ipmanager.php">IPmanager</a></li>
				<li><a href="/pages/admin/service.php">Список услуг</a></li>
				<li><a href="/pages/admin/servers.php">Список серверов</a></li>
				<?php } ?> 
				<hr/>
				<li><a href="/pages/cp.php">Настройки</a></li>
				<li><a href="/pages/archive.php">Архив услуг</a></li>
				<li><a href="/pages/cp.php">Пополнить счет</a></li>
				<li><a href="/pages/cron.php">Планировщик задач</a></li>
				<li><a href="/pages/2fa.php">2FA аутентификация</a></li>
				<li><a href="https://github.com/poiuty/lepus.su/issues" target="_blank">Пожелания и ошибки</a></li>
				<hr>
				<li><a href="/pages/service.php">Все услуги</a></li>
				<li><a href="/pages/service.php?q=2">VPS хостинг</a></li>
				<li><a href="/pages/dns.php">DNS хостинг</a></li>
				<li><a href="https://my.lepus.su/billmgr?func=showroom.redirect&redirect_to=desktop&startform=service.order.itemtype&newwindow=yes" target="_blank">Домены и лицензии</a></li>
				<li><a href="/pages/service.php?q=4">Администрирование</a></li>
				<li><a href="/pages/service.php?q=5">Виртуальный xостинг</a></li>
				<li><a href="/pages/service.php?q=3">Выделенные серверы</a></li>
				<hr/>
				<li><a href="/pages/support.php">Тех. поддержка</a></li>
				<li><a href="/pages/spend.php">Логи оплаты услуг</a></li>
				<li><a href="/pages/income.php">Логи пополнения счета</a></li>
				<hr/>
				<li><b>Баланс</b>: <?php echo $user['data']['balance']; ?> рублей</li>
				<hr/>
			</ul>
		</div>
	</div>
</div>
<?php } ?>
