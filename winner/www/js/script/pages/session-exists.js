$(document).ready(function() {

$('.btn-manage-user').on('click', function(event) {
	if ($(this).data('action') === "back") {
		members.forgetSession().always(function() {
			window.location.href = "/" + fns.currentGroup();
			return false;
		});
	} else {
		// console.log($(this).data('url'));
		fns.submitForm($(this).data('url'), {'forceLogin': true});
	}
});

});




