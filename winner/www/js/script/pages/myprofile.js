$(document).ready(function () {
    var params = fns.htmlspecialchars($('#params').val());
    if (params == 'courses') {
        $('.myprofile-nav a i').removeClass('menu_icon_active');
        $('.myprofile-nav a').removeClass('menu_active');

        $('a[href="#tab2"]').addClass('menu_active');
        $('a[href="#tab2"] i').addClass('menu_icon_active');

        $('.tab').addClass('hidden');
        $('#tab2').removeClass('hidden');
        $('a[href="#tab2"]').tab('show').on('shown.bs.tab', function (e) {
            // e.target // newly activated tab
            // e.relatedTarget // previous active tab
            // console.log('Click btn');
            // $('.btn-download-certificate[data-course="'+$('#params').data('course')+'"][data-cert-lang="'+$('#params').data('cert-lang')+'"').trigger('click');
        });
        //$('#tab2').tab('show');
    } else if (params == 'filter-courses') {
        $('.myprofile-nav a i').removeClass('menu_icon_active');
        $('.myprofile-nav a').removeClass('menu_active');

        $('a[href="#tab3"]').addClass('menu_active');
        $('a[href="#tab3"] i').addClass('menu_icon_active');

        $('.tab').addClass('hidden');
        $('#tab3').removeClass('hidden');
        $('a[href="#tab3"]').tab('show');
    } else if (params == 'orders') {
        $('.myprofile-nav a i').removeClass('menu_icon_active');
        $('.myprofile-nav a').removeClass('menu_active');

        $('a[href="#tab4"]').addClass('menu_active');
        $('a[href="#tab4"] i').addClass('menu_icon_active');

        $('.tab').addClass('hidden');
        $('#tab4').removeClass('hidden');
        $('a[href="#tab4"]').tab('show');
    } else {
        // $('#tab1').tab('show');
        //$('a[href="#tab1"]').tab('show');
    }

    $('.myprofile-nav a').click(function () {
        $('.myprofile-nav a i').removeClass('menu_icon_active');
        $('.myprofile-nav a').removeClass('menu_active');
        var id = $(this).attr('id');
        $(this).addClass('menu_active');
        $('#menu_icon_' + id).addClass('menu_icon_active');
        $('.tab').addClass('hidden');
        $('#tab' + id).removeClass('hidden');
    });

    $('.myprofile-avatar').mouseout(function() {
        var id = $(this).attr('id');
        $('#' + id + '-edit').addClass('hidden');
    })
    .mouseover(function() {
        var id = $(this).attr('id');
        $('#' + id + '-edit').removeClass('hidden');
    });

    $('.myprofile-avatar').on('click', function() {
        $("#modalAvatar").modal('show');
    });

    $('.thumbnail-avatar').on('click', function() {
        var $this = $(this);
        var id = $this.data('id');
        theData = {
            avatar_id: id
        }

        var membersData = JSON.stringify(theData);

        //$this.button('loading');

        $.when(members.updateAvatar(membersData)).always(function() {
            // $this.button('reset');
        }).done(function(data) {
            $('#modalAvatar').modal('hide');
            $('#myprofile-avatar-img').fadeOut().promise().done(function(){
                $('#myprofile-avatar-img').attr('src', PROJECT_ROOT+'/data-file/avatars/'+data.path);
                $('#myprofile-avatar-img').fadeIn();
            });
            $('.head-avatar').html("<img width='22' src='"+PROJECT_ROOT+"/data-file/avatars/" + data.path + "'>");
            //window.location.href = '/';
        }).fail(function(data) {
            //window.location.reload();
        });
    });

    // Dowload Cer
    $(document).on('click', '.btn-download-certificate', function(event) {
        var enrollId = parseInt($(this).data('id'));
        var courseId = parseInt($(this).data('course'));
        var certData = {
            'lang': $(this).data('cert-lang')
        };
        $.when(_enroll.downloadCertificate(enrollId, certData)).always(function() {
            // console.log("Always");
        }).done(function(data) {
            if (!data.survey) {
                createSurveyModal(enrollId, courseId);
                // $('#surveyModal-'+enrollId).modal('show');

                // Submit Survey
                $(document).on('click', '#submit-an-survey-'+enrollId, function () {
                    $.confirm({
                        theme: 'supervan',
                        title: 'แบบสอบถาม',
                        content: 'ต้องการ ยกเลิก หรือ ยืนยันการส่งแบบสอบถาม',
                        buttons: {
                            confirm: {
                                text: "ยืนยันการส่งแบบสอบถาม",
                                keys: ['enter', 'shift'],
                                action: function(){
                                    $.when(_questions.submitSurvey($('#survey-form-'+enrollId).serialize())).done(function(dataSurvey) {
                                        if (!dataSurvey.is_error) {
                                            $('#surveyModal-'+enrollId).modal('hide');
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