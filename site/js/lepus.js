var widgetId1;
var widgetId2;
var snd = new Audio("//"+document.domain+"/media/new.ogg");
var onloadCallback = function() {
	widgetId1 = grecaptcha.render(document.getElementById('captcha_reg'), { 'sitekey' : '6LcI6RETAAAAAOGz1Pbig57ErQ70tIRlvbhECQIw' });
	widgetId2 = grecaptcha.render(document.getElementById('captcha_lost'), { 'sitekey' : '6LcI6RETAAAAAOGz1Pbig57ErQ70tIRlvbhECQIw' });
};

$(document).on("click", "[data-dns-domain-add]", function(e) {
	$(this).blur();
	var table = $('#dnsDomainsList').DataTable();
	var dnsNumber = $('#dnsDomainsList tr').length;
	dnsDomain = $('input[id=dnsDomain]').val();
	dnsDomainType = $('select[id=dnsDomainType]').val();
	dnsDomainMaster = $('input[id=dnsDomainMaster]').val();
	if(!dnsDomainMaster) dnsDomainMaster = '-';
	$.post("//"+document.domain+"/public/add_dnsDomain.php", { name: dnsDomain, type: dnsDomainType, master: dnsDomainMaster}, function( data ){
		if($.isNumeric(data)){
			table.row.add({
				DT_RowId: data,
				0:     dnsNumber,
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
	$.post("//"+document.domain+"/public/lost_passwd.php", { email: email, 'g-recaptcha-response': grecaptcha.getResponse(widgetId2)}, function( data ){
		if(data == '1'){
			alertify.success("Check your email");
		}else{
			alertify.error(data);
		}
	});
});

$(document).on("click", "[data-register-send]", function(e) {
	$(this).blur();
	regEmail = $('input[id=regEmail]').val();
	$('#regModal').modal('hide');
	$.post("//"+document.domain+"/public/register.php", { email: regEmail, 'g-recaptcha-response': grecaptcha.getResponse(widgetId1)}, function( data ){
		if(data == '1'){
			alertify.success("Check your email");
		}else{
			alertify.error(data);
		}
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
	$.post("//"+document.domain+"/public/change_passwd.php", {passwd: passwd}, function( data ){
		if(data == '1'){
			alertify.success("Success");
			$('#changePasswdModal').modal('show');
			setTimeout(function(){location.reload()},2500);
		}else{
			alertify.error(data);
		}
		return;
	});
});

$(document).on("click", "[data-cp-change-phone]", function(e) {
	$(this).blur();
	e.preventDefault();
	phone = $('input[id=new_phone]').val();
	$.post("//"+document.domain+"/public/change_phone.php", {phone: phone}, function( data ){
		if(data == '1'){
			alertify.success("Success");
			$('#changePhonewdModal').modal('show');
			setTimeout(function(){location.reload()},2500);
		}else{
			alertify.error(data);
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
	var table = $('#supportList').DataTable();	
	title = $('input[id=tiketTitle]').val();
	msg = $('textarea[id=tiketMsg]').val();
	$.post("//"+document.domain+"/public/support.php", {do: 'new', title: title, msg: msg}, function(json){
		data = JSON.parse(json);
		if(data.err == 'OK'){
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
