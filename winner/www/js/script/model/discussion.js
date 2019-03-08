var _discussion = function() {

	var uploadPicture = function(formData) {
		return $.ajax({
			method: "POST",
			url: '/data-file/discussions_file_upload.php',
			contentType: false,
			cache: false,
			processData:false,
			data: formData,
			success: function(result){

			}
		});
	};

	var create = function(data) {
		return $.ajax({
			method: 'POST',
			url: URL_API+'/site/discussion/send',
			contentType: 'application/json',
			dataType: 'json',
			data: data,
			success: function(result){
				// $.getJSON("/api/site/discussion/groups/"+ result.groupsKey  +"/courses/"+ result.coursesID, function(data){
				// 	// $('#res-discussion').html("<tr><td><a href='#' class='col-md-12'>"+data.topic+"</a><small class='col-md-12'>โดยคุณ </small></td><td><i class='fa fa-calendar'></i> </td><td><i class='fa fa-eye'></i> </td><td><i class='fa fa-reply'></i> 0</td></tr>");
				// 	// console.log(data);
				// });
			}
		});
	};

	var reply = function(data) {
		return $.ajax({
			method: 'POST',
			url: URL_API+'/site/discussion/reply',
			contentType: 'application/json',
			dataType: 'json',
			data: data,
			success: function(result){
				// $.getJSON("/api/site/discussion/groups/"+ result.groupsKey  +"/courses/"+ result.coursesID, function(data){
				// 	// $('#res-discussion').html("<tr><td><a href='#' class='col-md-12'>"+data.topic+"</a><small class='col-md-12'>โดยคุณ </small></td><td><i class='fa fa-calendar'></i> </td><td><i class='fa fa-eye'></i> </td><td><i class='fa fa-reply'></i> 0</td></tr>");
				// 	// console.log(data);
				// });
			}
		});
	};

	var instructorsReply = function(data) {
		return $.ajax({
			method: 'POST',
			url: URL_API+'/site/discussion/instructors/reply',
			contentType: 'application/json',
			dataType: 'json',
			data: data,
			success: function(result){
			}
		});
	};

	var getListByCourse = function(cid) {
		return $.ajax({
			method: 'GET',
			url: URL_API+'/site/discussion/groups/'+fns.currentGroup()+'/courses/'+cid,
			contentType: 'application/json',
			dataType: 'json',
			// data: JSON.stringify(data),
			success: function(result){

			}
		});
	};

	var getListOfInstructorByCourse = function(cid) {
		return $.ajax({
			method: 'GET',
			url: URL_API+'/site/discussion/groups/'+fns.currentGroup()+'/courses/'+cid+'/instructors',
			contentType: 'application/json',
			dataType: 'json',
			// data: JSON.stringify(data),
			success: function(result){

			}
		});
	};

	var get = function(id) {
		return $.ajax({
			method: 'GET',
			url: URL_API+'/site/discussion/'+id,
			contentType: 'application/json',
			dataType: 'json',
			success: function(result){

			}
		});
	};

	var updateView = function(id) {
		return $.ajax({
			method: "PUT",
			url: URL_API+'/site/discussion/'+id+'/view',
			contentType: "application/json",
			dataType: 'json',
			success: function(result){

			}
		});
	};

	var updateLike = function(id) {
		return $.ajax({
			method: "PUT",
			url: URL_API+'/site/discussion/'+id+'/like',
			contentType: "application/json",
			dataType: 'json',
			success: function(result){

			}
		});
	};

	var updateDislike = function(id) {
		return $.ajax({
			method: "PUT",
			url: URL_API+'/site/discussion/'+id+'/dislike',
			contentType: "application/json",
			dataType: 'json',
			success: function(result){

			}
		});
	};

	return {

		uploadPicture: function(formData) {
			return uploadPicture(formData);
		},
		create: function(data) {
			return create(data);
		},
		reply: function(data) {
			return reply(data);
		},
		instructorsReply: function(data) {
			return instructorsReply(data);
		},
		getListByCourse: function(cid) {
			return getListByCourse(cid);
		},
		getListOfInstructorByCourse: function(cid) {
			return getListOfInstructorByCourse(cid);
		},
		get: function(id) {
			return get(id);
		},
		updateView: function(id) {
			return updateView(id);
		},
		updateLike: function(id) {
			return updateLike(id);
		},
		updateDislike: function(id) {
			return updateDislike(id);
		},

	};

}();