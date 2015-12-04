var widgetId1;
var widgetId2;
var onloadCallback = function() {
	widgetId1 = grecaptcha.render(document.getElementById('captcha_reg'), { 'sitekey' : '6LcI6RETAAAAAOGz1Pbig57ErQ70tIRlvbhECQIw' });
	widgetId2 = grecaptcha.render(document.getElementById('captcha_lost'), { 'sitekey' : '6LcI6RETAAAAAOGz1Pbig57ErQ70tIRlvbhECQIw' });
};

$(document).on("click", "[data-register-open]", function(e) {
	$(this).blur();
	e.preventDefault();
	grecaptcha.reset(widgetId1);;
	$('#regModal').modal('show');
});

$(document).on("click", "[data-send-lost-passwd]", function(e) {
	email = $('input[id=lost_passwd_email]').val();
	$.post("//"+document.domain+"/public/lost_passwd.php", { 'g-recaptcha-response': grecaptcha.getResponse(widgetId2)}, function( data ){
			alertify.error(data);
	});
});

$(document).on("click", "[data-register-send]", function(e) {
	regEmail = $('input[id=regEmail]').val();
	regEmail = $('input[id=regEmail]').val();
	regEmail = $('input[id=regEmail]').val();
	$.post("//"+document.domain+"/public/register.php", { 'g-recaptcha-response': grecaptcha.getResponse(widgetId1)}, function( data ){
		alertify.error(data);
	});
});

$(document).on("click", "[data-lost-passwd]", function(e) {
	$(this).blur();
	e.preventDefault();
	grecaptcha.reset(widgetId2);
	$('#regLost').modal('show');
});


$(document).on("click", "[data-do-login]", function(e) {
	$(this).blur();
	e.preventDefault();

	login_email = $('input[id=login_email]').val();
	login_passwd = $('input[id=login_passwd]').val();

	$.post("//"+document.domain+"/public/login.php", {command: 'login', email: login_email, passwd: login_passwd}, function( data ){
		$('#myModal').modal('hide');
		if(data == '1'){
			alertify.success("Success");
			location.reload();
		}else{
			alertify.error(data);
		}
		return;
	});
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
