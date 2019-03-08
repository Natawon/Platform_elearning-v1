var members = function() {

	// var getAll = function() {
	// 	return $.ajax({
	// 		method: 'GET',
	// 		url: URL_API+'/api/evaluation/evaluations',
	// 		contentType: 'application/json',
	// 		dataType: 'json',
	// 		headers: {'Authorization': 'Bearer '+localStorage.getItem("token")},
	// 		success: function(result){

	// 		}
	// 	});
	// };

	// var getDetail = function(id) {
	// 	return $.ajax({
	// 		method: 'GET',
	// 		url: URL_API+'/api/evaluation/evaluation/'+id,
	// 		contentType: 'application/json',
	// 		dataType: 'json',
	// 		headers: {'Authorization': 'Bearer '+localStorage.getItem("token")},
	// 		success: function(result){

	// 		}
	// 	});
	// };

	// var create = function(formData) {
	// 	return $.ajax({
	// 		method: "POST",
	// 		url: URL_API+'/api/evaluation/evaluation',
	// 		contentType: false,
	// 		cache: false,
	// 		processData:false,
	// 		headers: {"authorization": "Bearer "+TOKEN},
	// 		data: formData,
	// 		success: function(result){

	// 		}
	// 	});
	// };

	// var reject = function(id, formData) {
	// 	return $.ajax({
	// 		method: "POST",
	// 		url: URL_API+'/api/evaluation/evaluation/'+id+'/reject',
	// 		contentType: "application/json",
	// 		dataType: 'json',
	// 		headers: {"authorization": "Bearer "+TOKEN},
	// 		data: formData,
	// 		success: function(result){

	// 		}
	// 	});
	// };

	// var importCsv = function(id, formData) {
	// 	return $.ajax({
	// 		method: "POST",
	// 		url: URL_API+'/api/evaluation/evaluation/'+id+'/csv',
	// 		contentType: false,
	// 		cache: false,
	// 		processData:false,
	// 		dataType: 'json',
	// 		headers: {"authorization": "Bearer "+TOKEN},
	// 		data: formData,
	// 		success: function(result){

	// 		}
	// 	});
	// };


	var login = function(redirectParams, currentGroup) {
		// return $.ajax({
		// 	method: 'GET',
		// 	url: URL_SET+'/unauthorizedAccess.do?redirectPage='+redirectPage,
		// 	success: function(result){

		// 	}
		// });

		if (URL_GROUP_SET[currentGroup] !== undefined) {
			var objGroup = URL_GROUP_SET[currentGroup];
			var fullLoginUrl = objGroup.login;

			if (objGroup.isRedirect && objGroup.redirectPage !== null) {
				fullLoginUrl += '?redirectPage=' + encodeURIComponent(objGroup.redirectPage + redirectParams);
			}

			window.location.href = fullLoginUrl;
		} else {
			// window.location.href = URL_SET+'/unauthorizedAccess.do?redirectPage='+redirectPage;
		}
	};

	var logout = function() {
		return $.ajax({
			method: "POST",
			url: URL_API+'/set/logout',
			contentType: "application/json",
			dataType: 'json',
			// data: formData,
			success: function(result){

			}
		});
		// fns.submitForm(URL_API+'/set/logout', {});
	};

	var updateAvatar = function(membersData) {
		return $.ajax({
			method: "PUT",
			url: URL_API+'/site/members/avatar',
			contentType: "application/json",
			dataType: 'json',
			data: membersData,
			success: function(result){

			}
		});
	};

	var forgetSession = function() {
		return $.ajax({
			method: "DELETE",
			url: URL_API+'/set/user/temp/session',
			contentType: "application/json",
			dataType: 'json',
			// data: formData,
			success: function(result){

			}
		});
		// fns.submitForm(URL_API+'/set/logout', {});
	};

	var manageActionLogin = function(data) {
		return $.ajax({
			method: "PUT",
			url: URL_API+'/site/user/action/login',
			contentType: "application/json",
			dataType: 'json',
			data: data,
			success: function(result){

			}
		});
	};

	var changePassword = function(data) {
		return $.ajax({
			method: "PUT",
			url: URL_API+'/site/user/change-password',
			contentType: "application/json",
			dataType: 'json',
			data: data,
			success: function(result){

			}
		});
	};

	var getTaxInvoice = function() {
		return $.ajax({
			method: 'GET',
			url: URL_API+'/site/user/tax-invoice',
			contentType: 'application/json',
			dataType: 'json',
			success: function(result){

			}
		});
	};

	return {

		// Main function to initiate template pages
		// init: function() {
		// 	someFunction();
		// }

		login: function(redirectPage, currentGroup) {
			return login(redirectPage, currentGroup);
		},
		logout: function() {
			return logout();
		},
		updateAvatar: function(membersData) {
			return updateAvatar(membersData);
		},
		forgetSession: function() {
			return forgetSession();
		},
		manageActionLogin: function(data) {
			return manageActionLogin(data);
		},
		changePassword: function(data) {
			return changePassword(data);
		},
		getTaxInvoice: function() {
			return getTaxInvoice();
		}

	};

}();