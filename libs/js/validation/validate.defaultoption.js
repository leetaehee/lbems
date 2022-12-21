// design..
jQuery.validator.setDefaults({
	errorClass: 'help-block col-lg-6',
	errorElement: 'span',
	highlight: function(element, errorClass, validClass) {
		$(element).parents('.form-group').removeClass('has-success').addClass('has-error');
	},
	unhighlight: function(element, errorClass, validClass) {
		$(element).parents('.form-group').removeClass('has-error').addClass('has-success');
	}
});
