var _orders = function() {
	var create = function(data) {
		return $.ajax({
			method: "POST",
			url: URL_API+'/site/orders/',
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

		create: function(data) {
			return create(data);
		},

	};

}();