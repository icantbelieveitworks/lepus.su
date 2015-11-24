var onloadCallback = function() {
	grecaptcha.render('captcha_reg', {'sitekey' : '6LdqjBETAAAAAGZfJ8Gq6eTM7w7V8LVTLaQvpoHC'});
	grecaptcha.render('captcha_lost', {'sitekey' : '6LdqjBETAAAAAGZfJ8Gq6eTM7w7V8LVTLaQvpoHC'});
};

$(document).on("click", "[data-register-open]", function(e) {
	$(this).blur();
	e.preventDefault();
	grecaptcha.reset();
	$('#regModal').modal('show');
});

$(document).on("click", "[data-lost-passwd]", function(e) {
	$(this).blur();
	e.preventDefault();
	grecaptcha.reset();
	$('#regLost').modal('show');
});


$(document).on("click", "[data-do-login]", function(e) {
	$(this).blur();
	e.preventDefault();

	login_email = $('input[id=login_email]').val();
	login_passwd = $('input[id=login_passwd]').val();

	$.post("http://"+document.domain+"/public/login.php", {command: 'login', email: login_email, passwd: login_passwd}, function( data ){
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

