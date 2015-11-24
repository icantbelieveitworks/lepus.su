<?php
 if(empty($user)){ ?>
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
<?php }else{ ?>

<div class="blocks">
	<div class="block-login">
		<div class="block-body">
			<ul>
				<center>
					<a href="./logout.html" title="Выход"><b>poiuty@lepus.su</b></a>
				</center>
				<hr/>
				<li><a href="./cp.html">Настройки</a></li>
				<li><a href="./cp.html">Пополнить счет</a></li>
				<li><a href="./cp-cron.html">Планировщик задач</a></li>
				<hr>
				<li><a href="http://dom.lepus.su" target="_blank">Домены</a></li>
				<li><a href="./cp-vps.html">VPS хостинг</a></li>
				<li><a href="./cp-domains.html">DNS хостинг</a></li>
				<li><a href="./cp-adm.html">Администрирование</a></li>
				<li><a href="./cp-hosting-isp.html">Виртуальный xостинг</a></li>
				<li><a href="./cp-serv.html">Выделенные серверы</a></li>
				<hr/>
				<li><a href="./cp-support.html">Тех. поддержка</a></li>
				<li><a href="./cp-iplogs.html">Логи авторизаций</a></li>
				<li><a href="./cp-buylogs.html">Логи оплаты услуг</a></li>
				<li><a href="./cp-finlogs.html">Логи пополнения счета</a></li>
				<hr/>
				<li><b>Баланс</b>: 251 рублей</li>
				<hr/>
			</ul>
		</div>
	</div>
</div>
<?php } ?>

