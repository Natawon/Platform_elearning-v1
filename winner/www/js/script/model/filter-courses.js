var filterCourses = function() {
	var suit = function(data) {
		return $.ajax({
			method: "POST",
			url: URL_API+'/site/filter_courses/',
			contentType: "application/json",
			dataType: 'json',
			data: data,
			success: function(result){

			}
		});
	};

	return {

		// Main function to initiate template pages
		// init: function() {
		// 	someFunction();
		// }

		suit: function(data) {
			return suit(data);
		},

	};

}();