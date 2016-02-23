<?php
if(empty($user)){ ?>
<div class="blocks">
	<div class="block-login box-shadow--2dp">
		<div class="block-body">
			<input class="form-control" id="login_email" placeholder="E-mail" name="email" type="email" value="" autofocus="">
			<input class="form-control" id="login_passwd" style="margin-top: 3px;" placeholder="Password" name="password" type="password" value="" autofocus="">
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
				<?php if($user['data']['access'] > 0){ ?>
				<hr/>
				<li><a href="/pages/cp.php">Услуги</a></li>
				<li><a href="/pages/cp.php">Статистика</a></li>
				<li><a href="/pages/ipmanager.php">Управление IP</a></li>
				<?php } ?> 
				<hr/>
				<li><a href="/pages/cp.php">Настройки</a></li>
				<li><a href="/pages/cp.php">Пополнить счет</a></li>
				<li><a href="/pages/cron.php">Планировщик задач</a></li>
				<hr>
				<li><a href="/cp-vps.html">VPS хостинг</a></li>
				<li><a href="/pages/dns.php">DNS хостинг</a></li>
				<li><a href="/cp-adm.html">Администрирование</a></li>
				<li><a href="/cp-hosting-isp.html">Виртуальный xостинг</a></li>
				<li><a href="/cp-serv.html">Выделенные серверы</a></li>
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
