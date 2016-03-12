<div id="regModal" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Регистрация</h4>
			</div>
			<div id="modal_info" class="modal-body">
				<center><p><input class="form-control input-sm" id="regEmail" style="display:inline; position:relative;width:300px;" type="text" placeholder="E-mail"> </p>
				<div id="captcha_reg" style="width:300px;margin-top: 3px;"></div></center>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" data-register-send>Регистрация</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<div id="regLost" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Восстановление пароля</h4>
			</div>
			<div id="modal_info" class="modal-body">
				<center><p><input class="form-control input-sm" id="lost_passwd_email" style="display:inline; position:relative;width:300px;" type="text" placeholder="E-mail"> </p>
				<div id="captcha_lost" style="width:300px;margin-top: 3px;"></div></center>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" data-send-lost-passwd>Восстановить пароль</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>


<div id="changePasswdModal" class="modal fade" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div id="modal_info" class="modal-body">
				<center>По-вашему запросу, мы поменяли пароль и отправили новый - на вашу почту.</center>
			</div>
		</div>
	</div>
</div>

<div id="changePhonewdModal" class="modal fade" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div id="modal_info" class="modal-body">
				<center>По-вашему запросу, мы поменяли номер телефона.</center>
			</div>
		</div>
	</div>
</div>

<div id="bitcoinModal" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Пополнение счета через bitcoin</h4>
			</div>
			<div id="modal_info" class="modal-body">
				Вы можете оплатить хостинг с помощью bitcoin. Внутренний курс 1 BTC = 30000 рублей.<br/>
				Переведите произвольную сумму на адрес: <u><?php echo @$user['bitcoin']; ?></u><br/>
				После 6 подтверждений транзакции - мы автоматически увеличим ваш баланс на сайте.
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<div id="confirmOrder" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Подтверждение заказа</h4>
			</div>
			<div id="modal_info" class="modal-body">
				<div id="modal_order_text"></div>
			</div>
			<div class="modal-footer">
				<button id="order_hide" type="button" class="btn btn-success" data-order-finish>Отправить</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<div id="confirmChangeTariff" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Подтвердить изменения тарифа</h4>
			</div>
			<div id="modal_info" class="modal-body">
				<div id="modal_tariff_text"></div>
			</div>
			<div class="modal-footer">
				<button id="tarif_change_hide" type="button" class="btn btn-success" data-change-tariff-finish>Отправить</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<div id="modalArchive" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Дополнительная информация по услуге</h4>
			</div>
			<div id="modal_info" class="modal-body">
				<div id="modal_archive_text"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<div id="modalPay" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Подтверждение платежа</h4>
			</div>
			<div id="modal_info" class="modal-body">
				<div id="modal_pay_text"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>
