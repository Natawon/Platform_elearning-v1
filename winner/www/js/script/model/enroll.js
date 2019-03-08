var _enroll = function() {

	var downloadCertificate = function(id, data) {
		return $.ajax({
			method: 'POST',
			url: URL_API+'/site/enroll/'+id+'/certificate',
			contentType: 'application/json',
			dataType: 'json',
			data: JSON.stringify(data),
			success: function(result){

			}
		});

		// window.location.href = URL_API+'/site/enroll/'+id+'/certificate';
	};

	var getByCourse = function(cid) {
		return $.ajax({
			method: 'GET',
			url: URL_API+'/site/enroll/courses/'+cid,
			contentType: 'application/json',
			dataType: 'json',
			// data: JSON.stringify(data),
			success: function(result){

			}
		});
	};

	return {

		// Main function to initiate template pages
		// init: function() {
		// 	someFunction();
		// }

		downloadCertificate: function(id, data) {
			return downloadCertificate(id, data);
		},
		getByCourse: function(cid) {
			return getByCourse(cid);
		},

	};

}();