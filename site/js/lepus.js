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
	alertify.success("Success notification");
	//alertify.error("Success notification");
});

