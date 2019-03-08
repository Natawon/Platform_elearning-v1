var _instructors = function() {

	var login = function(cid, data) {
		return $.ajax({
			method: 'POST',
			url: URL_API+'/site/instructors/groups/'+fns.currentGroup()+'/courses/'+cid+'/login',
			contentType: 'application/json',
			dataType: 'json',
			data: data,
			success: function(result){

			}
		});
	};

	var logout = function() {
		return $.ajax({
			method: 'POST',
			url: URL_API+'/site/instructors/logout',
			contentType: 'application/json',
			dataType: 'json',
			// data: data,
			success: function(result){

			}
		});
	};

	var discussionReply = function(data) {
		return $.ajax({
			method: 'POST',
			url: URL_API+'/site/instructors/discussion/reply',
			contentType: 'application/json',
			dataType: 'json',
			data: data,
			success: function(result){
			}
		});
	};

	var getDiscussionListByCourse = function(cid) {
		return $.ajax({
			method: 'GET',
			url: URL_API+'/site/instructors/discussion/groups/'+fns.currentGroup()+'/courses/'+cid,
			contentType: 'application/json',
			dataType: 'json',
			// data: JSON.stringify(data),
			success: function(result){

			}
		});
	};

	var getDiscussion = function(id) {
		return $.ajax({
			method: 'GET',
			url: URL_API+'/site/instructors/discussion/'+id,
			contentType: 'application/json',
			dataType: 'json',
			success: function(result){

			}
		});
	};

	var updateDiscussionView = function(id) {
		return $.ajax({
			method: "PUT",
			url: URL_API+'/site/instructors/discussion/'+id+'/view',
			contentType: "application/json",
			dataType: 'json',
			success: function(result){

			}
		});
	};

	var readDiscussion = function(id) {
		return $.ajax({
			method: "PUT",
			url: URL_API+'/site/instructors/discussion/'+id+'/read',
			contentType: "application/json",
			dataType: 'json',
			success: function(result){

			}
		});
	};

	return {

		login: function(cid, data) {
			return login(cid, data);
		},
		logout: function() {
			return logout();
		},
		discussionReply: function(data) {
			return discussionReply(data);
		},
		getDiscussionListByCourse: function(cid) {
			return getDiscussionListByCourse(cid);
		},
		getDiscussion: function(id) {
			return getDiscussion(id);
		},
		updateDiscussionView: function(id) {
			return updateDiscussionView(id);
		},
		readDiscussion: function(id) {
			return readDiscussion(id);
		},

	};

}();