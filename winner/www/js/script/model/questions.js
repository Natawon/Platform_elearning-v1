var _questions = function() {

	var submitSurvey = function(data) {
		return $.ajax({
			method: 'POST',
			url: URL_API+'/site/questions2survey',
			// contentType: 'application/json',
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

		submitSurvey: function(data) {
			return submitSurvey(data);
		},

	};

}();