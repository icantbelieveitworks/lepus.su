<!--<div id="myModal" class="modal fade" data-backdrop="static">  -->
<!-- https://github.com/poiuty/gamecp/blob/master/web/private/template/include/modal.php -->
<div id="regModal" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Регистрация</h4>
			</div>
			<div id="modal_info" class="modal-body">
				<center><p><input class="form-control input-sm" id="email" style="display:inline; position:relative;width:300px;" type="text" placeholder="E-mail"> </p>
				<p><input class="form-control input-sm" id="password" style="display:inline; position:relative;width:300px;margin-top: 3px;" type="password" placeholder="Password"> </p>
				<p><input class="form-control input-sm" id="re_password" style="display:inline; position:relative;width:300px;margin-top: 3px;" type="password" placeholder="Re-Password"> </p>
				<div id="captcha_reg" style="width:300px;margin-top: 3px;"></div></center>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" data-save-settings>Регистрация</button>
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
				<center><p><input class="form-control input-sm" id="email" style="display:inline; position:relative;width:300px;" type="text" placeholder="E-mail"> </p>
				<div id="captcha_lost" style="width:300px;margin-top: 3px;"></div></center>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" data-save-settings>Восстановить пароль</button>
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
