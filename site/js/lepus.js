var widgetId1;
var widgetId2;
var snd = new Audio("//"+document.domain+"/media/new.ogg");
var onloadCallback = function() {
	widgetId1 = grecaptcha.render(document.getElementById('captcha_reg'), { 'sitekey' : '6LcI6RETAAAAAOGz1Pbig57ErQ70tIRlvbhECQIw' });
	widgetId2 = grecaptcha.render(document.getElementById('captcha_lost'), { 'sitekey' : '6LcI6RETAAAAAOGz1Pbig57ErQ70tIRlvbhECQIw' });
};

$(document).on('click', '#noclick', function(e){ $(this).blur(); })

$(document).on("click", "[data-dns-domain-add]", function(e) {
	$(this).blur();
	var table = $('#dnsDomainsList').DataTable();
	dnsDomain = $('input[id=dnsDomain]').val();
	dnsDomainType = $('select[id=dnsDomainType]').val();
	dnsDomainMaster = $('input[id=dnsDomainMaster]').val();
	if(!dnsDomainMaster) dnsDomainMaster = '-';
	$.post("//"+document.domain+"/public/add_dnsDomain.php", { name: dnsDomain, type: dnsDomainType, master: dnsDomainMaster}, function( data ){
		if($.isNumeric(data)){
			table.row.add({
				DT_RowId: data,
				0:     table.page.info().recordsTotal+1,
				1:     dnsDomain,
				2:     dnsDomainType.toUpperCase(),
				3:     dnsDomainMaster,
				4:     '<a href="/pages/edit-domain.php?id='+data+'"><i class="glyphicon glyphicon-pencil"></i></a> &nbsp; <a href="nourl" data-dns-delete-id="'+data+'"><i class="glyphicon glyphicon-remove"></i></a>',
			}).draw( false );
			alertify.success("Ok, we add this domain");
		}else{
			alertify.error(data);
		}
	});
});

$(document).on("click", "[data-register-open]", function(e) {
	$(this).blur();
	e.preventDefault();
	grecaptcha.reset(widgetId1);;
	$('#regModal').modal('show');
});

$(document).on("click", "[data-send-lost-passwd]", function(e) {
	$(this).blur();
	email = $('input[id=lost_passwd_email]').val();
	$('#regLost').modal('hide');
	$.post("//"+document.domain+"/public/lost_passwd.php", { email: email, 'g-recaptcha-response': grecaptcha.getResponse(widgetId2)}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			alertify.success(data.mes);
		}else{
			alertify.error(data.mes);
		}
	});
});

$(document).on("click", "[data-register-send]", function(e) {
	$(this).blur();
	regEmail = $('input[id=regEmail]').val();
	$('#regModal').modal('hide');
	$.post("//"+document.domain+"/public/register.php", { email: regEmail, 'g-recaptcha-response': grecaptcha.getResponse(widgetId1)}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			alertify.success(data.mes);
		}else{
			alertify.error(data.mes);
		}
		return;
	});
});

$(document).on("click", "[data-lost-passwd]", function(e) {
	$(this).blur();
	e.preventDefault();
	grecaptcha.reset(widgetId2);
	$('#regLost').modal('show');
});

var lepus_login = function() {
	login_email = $('input[id=login_email]').val();
	login_passwd = $('input[id=login_passwd]').val();
	$.post("//"+document.domain+"/public/login.php", {command: 'login', email: login_email, passwd: login_passwd}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			location.reload();
		}else{
			alertify.error(data.mes);
		}
		return;
	});
};

$(document).keypress(function(e) {
    if(e.which == 13 && document.getElementById("check_auth") !== null) lepus_login();
});

$(document).on("click", "[data-do-login]", function(e) {
	$(this).blur();
	e.preventDefault();
	lepus_login();
});

$(document).on("click", "[data-cp-change-passwd]", function(e) {
	$(this).blur();
	e.preventDefault();
	passwd = $('input[id=real_passwd]').val();
	$.post("//"+document.domain+"/public/change_passwd.php", {passwd: passwd}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			alertify.success(data.mes);
			$('#changePasswdModal').modal('show');
			setTimeout(function(){location.reload()},2500);
		}else{
			alertify.error(data.mes);
		}
		return;
	});
});

$(document).on("click", "[data-cp-change-phone]", function(e) {
	$(this).blur();
	e.preventDefault();
	phone = $('input[id=new_phone]').val();
	$.post("//"+document.domain+"/public/change_phone.php", {phone: phone}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			alertify.success(data.mes);
			$('#changePhonewdModal').modal('show');
			setTimeout(function(){location.reload()},2500);
		}else{
			alertify.error(data.mes);
		}
		return;
	});
});

$(document).on("click", "[data-dns-delete-id]", function(e) {
	e.preventDefault();
	if(!confirm("Вы подтверждаете удаление?")) return;
	var table = $('#dnsDomainsList').dataTable();
	idDom = $(this).data("dns-delete-id");
	$.post("//"+document.domain+"/public/delete_dnsDomain.php", {id: idDom}, function( data ){
		if(data == '1'){
			alertify.success("Success");
			table.fnDeleteRow(table.$("#"+idDom));
		}else{
			alertify.error(data);
		}
	});
});

$(document).on("click", "[data-dns-zone-id]", function(e) {
	e.preventDefault();
	if(!confirm("Вы подтверждаете удаление?")) return;
	var idZone = $(this).data("dns-zone-id");
	$.post("//"+document.domain+"/public/delete_dnsZone.php", {id: idZone}, function( data ){
		if(data == '1'){
			alertify.success("Success");
			$('table#dnsZone tr#'+idZone).remove();
		}else{
			alertify.error(data);
		}
	});
});

$(document).on("click", "[data-dns-zone-add]", function(e) {
	$(this).blur();
	e.preventDefault();
	dnsZone = $('input[id=dnsZone]').val();
	dnsZoneType = $('select[id=dnsZoneType]').val();
	dnsZoneData = $('input[id=dnsZoneData]').val();
	dnsZonePrio = $('input[id=dnsZonePrio]').val();
	dnsDomainZoneID = $('input[id=dnsDomainZoneID]').val();
	if(!dnsZonePrio) dnsZonePrio = 0; 
	var zoneNumber = $('table#dnsZone tr').length;
	$.post("//"+document.domain+"/public/add_dnsZone.php", {name: dnsZone, type: dnsZoneType, content: dnsZoneData, prio: dnsZonePrio, domain_id: dnsDomainZoneID}, function( data ){
		if($.isNumeric(data)){
			alertify.success("Success");
			$('table#dnsZone tr:last').after('<tr id="'+data+'"><td>'+zoneNumber+'</td><td class="edit" id="name_'+data+'">'+dnsZone+'</td><td class="edit_type" id="type_'+data+'">'+dnsZoneType+'</td><td class="edit" id="content_'+data+'">'+dnsZoneData+'</td><td class="edit" id="prio_'+data+'">'+dnsZonePrio+'</td><td><a href="nourl" data-dns-zone-id='+data+'><i class="glyphicon glyphicon-remove"></i></a></td></tr>');
		}else{
			alertify.error(data);
		}
	});
});

$(document).on("click", "[data-open-new-tiket]", function(e) {
	$(this).blur();
	e.preventDefault();
	$(this).parent().hide();
	var that = $(this);
	setTimeout(function(){that.parent().show()},5000);
	var table = $('#supportList').DataTable();	
	title = $('input[id=tiketTitle]').val();
	msg = $('textarea[id=tiketMsg]').val();
	user = $('select[id=tiketUser]').val();
	if(!user) user = 'no';
	$.post("//"+document.domain+"/public/support.php", {do: 'new', title: title, msg: msg, user: user}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			$('input[id=tiketTitle]').val('');
			$('textarea[id=tiketMsg]').val('');
			alertify.success("Тикет создан");
			table.row.add({
				0:     data.mes.a,
				1:     data.mes.b,
				2:     data.mes.c,
				3:     data.mes.d,
				4:     data.mes.e,
			}).draw( false );
		}else{
			alertify.error(data.mes);
		}
	});
});

var lepus_support_send = function(input_click) {
	tid = $('input[id=tiketID]').val();
	msg = $('textarea[id=tiketMsg]').val();
	count = parseInt($('input[id=countMSG]').val());
	if(input_click == 'endTiket'){
		msg = 'END';
		$("#tiketStatus1").hide();
		$("#tiketStatus2").show();
	}
	if(input_click == 'reopenTiket'){
		msg = 'OPEN';
		$("#tiketStatus1").show();
		$("#tiketStatus2").hide();
	}
	$.post("//"+document.domain+"/public/support.php", {do: 'send_msg', tid: tid, msg: msg}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			alertify.success("Сообщение отправлено");
			$('input[id=countMSG]').val(count+1);
			$("#messageList").prepend(data.mes.msg);
			$('textarea[id=tiketMsg]').val('');
		}else{
			alertify.error(data.mes);
		}
	});
}

$(document).keydown(function(e) {
    if (e.ctrlKey && e.keyCode == 13 && document.getElementById("sendMSG") !== null)
		lepus_support_send();
});

$(document).on("click", "[data-tiket-send-msg], [data-tiket-send-close], [data-tiket-send-reopen]", function(e) {
	$(this).blur();
	e.preventDefault();
	lepus_support_send(e.target.id);
});

$(document).on("click", "[data-make-payment]", function(e) {
	$(this).blur();
	e.preventDefault();
	amount = $('input[id=pay_sum]').val();
	pay_system = $('select[id=psystem]').val();
	if(pay_system=='bitcoin'){
		$('#bitcoinModal').modal('show');
		return;
	}
	$('#modalPay').modal('show');
	$.post("//"+document.domain+"/public/pay_preview.php", {system: pay_system, amount: amount}, function(json){
		data = JSON.parse(json);
		$("#modal_pay_text").html(data.mes);
	});
});

$(document).on("click", "[data-cron-add]", function(e) {
	$(this).blur();
	e.preventDefault();
	time = $('input[id=cronTime]').val();
	url = $('input[id=cronURL]').val();
	var table = $('#cronTable').DataTable();
	$.post("//"+document.domain+"/public/add_cron.php", {do: 'add', time: time, url: url}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			alertify.success('Задание добавлено');
			table.row.add({
				0:     data.mes.d,
				1:     data.mes.a,
				2:     data.mes.b,
				3:     data.mes.c,
			}).draw( false );
		}else{
			alertify.error(data.mes);
		}
	});
});

$(document).on("click", "[data-cron-task-id]", function(e) {
	$(this).blur();
	e.preventDefault();
	if(!confirm("Вы подтверждаете удаление?")) return;
	var table = $('#cronTable').DataTable();
	var this_cron = $(this).parents('tr');
	idTask = $(this).data("cron-task-id");
    $.post("//"+document.domain+"/public/add_cron.php", {do: 'remove', id: idTask}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			alertify.success(data.mes);
			table.row(this_cron).remove().draw();
		}else{
			alertify.error(data.mes);
		}
	});
});

$(document).on("click", "[data-admin-addip]", function(e) {
	$(this).blur();
	e.preventDefault();
	ip = $('input[id=ipAddress]').val();
	mac = $('input[id=ipMAC]').val();
	host = $('input[id=ipHost]').val();
	server = $('select[id=ipServer]').val();
	user = $('select[id=ipUser]').val();

	server_text = $('select[id=ipServer] option:selected').text();
	user_text = $('select[id=ipUser] option:selected').text();
	
	var table = $('#IPmanagerList').DataTable();
	$.post("//"+document.domain+"/public/admin/add_ip.php", {ip: ip, mac: mac, host: host, server: server, user: user}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			alertify.success('IP успешно добавлен');
			table.row.add({
				0:     data.mes.a,
				1:     ip,
				2:     server_text,
				3:     0,
				4:     user_text,
				5:     mac,
				6:     host,
				7:     data.mes.b,
			}).draw( false );
		}else{
			alertify.error(data.mes);
		}
	});
});

$(document).on("click", "[data-adminip-delete-id]", function(e) {
	$(this).blur();
	e.preventDefault();
	ip = $(this).data("adminip-delete-id");
	var this_ip = $(this).parents('tr');
	var table = $('#IPmanagerList').DataTable();
	if(!confirm("Вы подтверждаете удаление?")) return;
	$.post("//"+document.domain+"/public/admin/remove_ip.php", {id: ip}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			alertify.success('IP успешно удален');
			table.row(this_ip).remove().draw();
		}else{
			alertify.error(data.mes);
		}
	});
});

$(document).on("click", "[data-order-service]", function(e) {
	$(this).blur();
	e.preventDefault();
	$("#order_hide").show();
	var order_id = $("#idServiceOrder option:selected").val();
	var promo = $("#promo_code").val();
	$.post("//"+document.domain+"/public/order_preview.php", {id: order_id, promo: promo}, function(data){
		$("#modal_order_text" ).html(data);
		$('#confirmOrder').modal('show');
	});
});

$(document).on("click", "[data-order-finish]", function(e) {
	$(this).hide();
	var order_id = $("#idServiceOrder option:selected").val();
	var ostype = $("#ostype option:selected").val(); 
	var promo = $("#promo_code").val();
	if(!ostype) ostype = 'no';
	$.post("//"+document.domain+"/public/order.php", {id: order_id, promo: promo, ostype: ostype}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			alertify.success("Готово");
			$("#modal_order_text" ).html("<center>Ваш заказ будет скоро готов.<br/> Вы можете посмотреть статус заказа и связаться с поддержкой <a href='https://"+document.domain+"/pages/tiket.php?id="+data.mes+"'>через этот тикет</a>.</center>");
		}else{
			alertify.error(data.mes);
		}
	});
});

$(document).on("click", "[data-autoextend-id]", function(e) {
	var val = $(this);
	$(val).blur();
	e.preventDefault();
	var id = $(val).data("autoextend-id");
	$.post("//"+document.domain+"/public/autoextend.php", {id: id}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			if($(val).val() === 'on'){
				$(val).val('off');
				$(val).attr('class', 'btn btn-danger btn-xs');
			}else{
				$(val).val('on');
				$(val).attr('class', 'btn btn-success btn-xs');
			}
		}else{
			alertify.error(data.mes);
		}
	});
});

$(document).on("click", "[data-change-tariff]", function(e) {
	$(this).blur();
	e.preventDefault();
	id = $('input[id=service_id]').val();
	order_id = $("#idServiceOrder option:selected").val();
	$("#tarif_change_hide").show();
	$('#confirmChangeTariff').modal('show');
	$.post("//"+document.domain+"/public/tariff_preview.php", {id: id, sid: order_id}, function(json){
		data = JSON.parse(json);
		if(data.mes.show == 0 || data.err != 'OK'){
			$("#tarif_change_hide").hide();
		}
		if(data.err == 'OK'){
			$("#modal_tariff_text").html(data.mes.text);
		}else{
			$("#modal_tariff_text").html(data.mes);
		}
	});
});

$(document).on("click", "[data-change-tariff-finish]", function(e) {
	$(this).hide();
	id = $('input[id=service_id]').val();
	order_id = $("#idServiceOrder option:selected").val();
	$.post("//"+document.domain+"/public/change_tariff.php", {id: id, sid: order_id}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			alertify.success("Готово");
			$('#confirmChangeTariff').modal('hide');
			setTimeout(function(){location.reload();}, 800);
		}else{
			$("#modal_tariff_text").html(data.mes);
		}
	});
});

$(document).on("click", "[data-archive-show]", function(e) {
	$(this).blur();
	e.preventDefault();
	id = $(this).data("archive-show");
	$('#modalArchive').modal('show');
	$.post("//"+document.domain+"/public/archive.php", {id: id}, function(json){
		data = JSON.parse(json);
		$("#modal_archive_text").html(data.mes);
	});
});

$(document).on("click", "[data-vm-restart], [data-vm-restart-hard], [data-vm-stopandstart]", function(e) {
	$(this).blur();
	e.preventDefault();
	id = $(this).data("vm-restart");
	command = 'restart';
	if(!id){
		id = $(this).data("vm-restart-hard");
		command = 'hardrestart';
	}
	if(!id){
		id = $(this).data("vm-stopandstart");
		command = 'stopandstart';
	}
	boot = $('select[id=idboot]').val();
	$.post("//"+document.domain+"/public/add_task.php", {id: id, boot: boot, command: command}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			alertify.success(data.mes);
		}else{
			alertify.error(data.mes);
		}
	});
});

$(document).on("click", "[data-try-extend-services]", function(e) {
	$(this).blur();
	e.preventDefault();
	$.post("//"+document.domain+"/public/extend.php", function(data){
		alertify.success(data);
		setTimeout(function(){location.reload();}, 500);
	});
});

$(document).on("click", "[data-show-api]", function(e) {
	$(this).blur();
	e.preventDefault();
	$(this).html($(this).data("show-api"));
});

$(document).on("click", "[data-change-api-key]", function(e) {
	$(this).blur();
	e.preventDefault();
	if(!confirm("Вы подтверждаете действие?")) return;
	$.post("//"+document.domain+"/public/change_api.php", function(data){
		$("#api_key").html(data);
	});
});

$(document).on("click", "[data-move-archive-id]", function(e) {
	$(this).blur();
	e.preventDefault();
	if(!confirm("Вы подтверждаете действие?")) return;
	id = $(this).data("move-archive-id");
	var table = $('#incomeTable').DataTable();
	var this_income = $(this).parents('tr');
	$.post("//"+document.domain+"/public/admin/move_archive.php", {id: id}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			table.row(this_income).remove().draw();
			alertify.success(data.mes);
		}else{
			alertify.error(data.mes);
		}
	});
});

$(document).on("click", "[data-vm-vnc]", function(e) {
	$(this).blur();
	e.preventDefault();
	var vps_id = $(this).data("vm-vnc");
	$('#modalVNC').find('[data-vnc-passwd]').data('vm-vnc', vps_id);
	$('#modalVNC').modal('show');
	$.post("//"+document.domain+"/public/vnc.php", {id: vps_id, do: 'info'}, function(data){
		$("#modal_vnc_text").html(data);
	});
});

$(document).on("click", "[data-vnc-passwd]", function(e) {
	$(this).blur();
	var vps_id = $(this).data("vm-vnc");
	e.preventDefault();
	$.post("//"+document.domain+"/public/vnc.php", {id: vps_id, do: 'passwd'}, function(data){
		$("#modal_vnc_text").html(data);
	});
});

$(document).on("click", "[data-admin-send-emails]", function(e) {
	$(this).blur();
	e.preventDefault();
	text = $('textarea[id=email_text]').val();
	title = $('input[id=email_title]').val();
	$.post("//"+document.domain+"/public/admin/send.php", {title: title, text: text}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
			alertify.success(data.mes);
		}else{
			alertify.error(data.mes);
		}
	});
});
