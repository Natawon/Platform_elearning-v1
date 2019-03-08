$(document).ready(function() {

// Popup Filer Courses
$('#btn-start-filter-courses').on('click', function(event) {
	if ($(this).data('login') == true) {
		$('#filterCoursesModal').modal('show');
	} else {
		$('.btn-trigger-login').trigger('click');
	}
});
// End Popup Filer Courses

});




