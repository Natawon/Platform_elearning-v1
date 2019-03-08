$(document).ready(function() {

// Download Cer.
$('.btn-download-certificate').on('click', function(event) {
	var enrollId = parseInt($(this).data('id'));
	var certData = {
		'lang': $(this).data('cert-lang')
	};
	$.when(_enroll.downloadCertificate(enrollId, certData)).always(function() {
		// console.log("Always");
    }).done(function(data) {
    	if (!data.survey) {
    		$('#surveyModal').modal('show');

    		// Submit Survey
			$('#submit-an-survey').on('click', function () {
			    $.confirm({
			        theme: 'supervan',
			        title: 'แบบสอบถาม',
			        content: 'ต้องการ ยกเลิก หรือ ยืนยันการส่งแบบสอบถาม',
			        buttons: {
			            confirm: {
			                text: "ยืนยันการส่งแบบสอบถาม",
			                keys: ['enter', 'shift'],
			                action: function(){
			                	$.when(_questions.submitSurvey($('#survey-form').serialize())).done(function(dataSurvey) {
			                		if (!dataSurvey.is_error) {
			                			$('#surveyModal').modal('hide');
				                        setTimeout(function() {
				                        	window.location.href = data.certificate_url;
				                        }, 800);
			                		} else {
			                			$.alert({
											"type": 'red',
											"title": 'เกิดข้อผิดพลาด!',
											"content": msg + '<br>กรุณาติดต่อ Contact Center โทร. 02-000-0000<br>อีเมล์ : info@domain.com'
										});
			                		}
			                	}).fail(function(dataSurvey) {
			                		var msg = '';

			                		if (dataSurvey.responseJSON !== undefined) {
				                		if (dataSurvey.responseJSON.error_msg !== undefined) {
				                			msg = dataSurvey.responseJSON.error_msg;
				                		} else if (dataSurvey.responseJSON.message !== undefined) {
				                			msg = dataSurvey.responseJSON.message;
				                		}
			                		}

			                		$.alert({
										"type": 'red',
										"title": 'เกิดข้อผิดพลาด!',
										"content": msg + '<br>กรุณาติดต่อ Contact Center โทร. 02-000-0000<br>อีเมล์ : info@domain.com'
									});
			                	});
			                }
			            },
                        cancel: {
                            text: "ยกเลิก"
                        }
			        }
			    });
			});
    	} else {
    		window.location.href = data.certificate_url;
    	}
    }).fail(function(data) {
    	if (data.status === 401) {
    		window.location.href = '/';
    	} else if (data.status === 422) {
    		$.confirm({
				type: 'red',
				title: 'ไม่สามารถขอรับวุฒิบัตรได้',
				content: (data.responseJSON.message ? data.responseJSON.message+'.<br>' : ''),
				buttons: {
			        cancel: function () {
			            // $.alert('Canceled!');
			        },
			        editProfile: {
			            text: 'แก้ไขข้อมูลส่วนตัว',
			            btnClass: 'btn-set',
			            keys: ['enter'],
			            action: function(){
			                window.location.href = $('header').data('group-site') + '/my-profile';
			            }
			        }
				}
			});
    	} else if (data.status === 404) {
    		$.alert({
				type: 'red',
				title: 'ไม่พบวุฒิบัตรดังกล่าว',
				content: (data.responseJSON.message ? data.responseJSON.message+'.<br>' : '') + 'กรุณาติดต่อ Contact Center โทร. 02-000-0000<br>อีเมล์ : info@domain.com'
			});
    	} else {
    		$.alert({
				type: 'red',
				title: 'เกิดข้อผิดพลาด!',
				content: (data.responseJSON.error_msg ? data.responseJSON.error_msg+'.<br>' : '') + 'กรุณาติดต่อ Contact Center โทร. 02-000-0000<br>อีเมล์ : info@domain.com'
			});
    	}
    });
});


});




