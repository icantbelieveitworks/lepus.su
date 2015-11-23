$(document).on("click", "[data-register-open]", function(e) {
	$(this).blur();
	e.preventDefault();
	$('#myModal').modal('show');
});
