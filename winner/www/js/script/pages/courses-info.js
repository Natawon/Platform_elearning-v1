function enroll_summary(cid, gkey){
    $.post('/api/site/enroll2learning', {cid: cid}, function(data) {
        $.get('/api/site/groups/'+gkey, function(groups) {
            window.location.href = '/'+ groups.key +'/enroll/'+ data.eid +'/summary';
        });
    });
}

function enroll(cid, gkey, tid, type){
    $.get('/api/site/groups/'+gkey+'/courses/'+cid, function(data) {
            if(data.enroll_chk){
                $.post('/api/site/enroll2learning', {cid: cid}, function(data) {
                    //console.log(data);
                    $.get('/api/site/groups/'+gkey, function(groups) {
                        window.location.href = '/'+ groups.key +'/enroll/'+ data.eid +'/'+data.navigator;
                    });
                });
            }else{
                $.confirm({
                    theme: 'supervan',
                    title:  data.title,
                    content: data.enroll_msg,
                    buttons: {
                        confirm: {
                            text: "ตกลง",
                            action: function(){
                                $.post('/api/site/enroll2learning', {cid: cid, tid: tid, type: type}, function(data) {
                                    $.get('/api/site/groups/'+gkey, function(groups) {
                                        window.location.href = '/'+ groups.key +'/enroll/'+ data.eid +'/'+data.navigator;
                                    });
                                }).fail(function(data) {
                                    if (data.status === 401) {
                                        $.get('/api/site/groups/'+gkey, function(groups) {
                                            if(groups.internal){
                                                window.location.href = '/'+ groups.key +'/login';
                                            }else{
                                                members.login("?courseid="+cid+"&action=info", fns.currentGroup());
                                            }
                                        });
                                    } else {
                                        if (data.responseJSON !== undefined) {
                                            $.alert({
                                                type: 'red',
                                                title: 'เกิดข้อผิดพลาด!',
                                                content: (data.responseJSON.error_msg ? data.responseJSON.error_msg+'.<br>' : '') + 'กรุณาติดต่อ Contact Center โทร. 02-000-0000<br>อีเมล์ : info@domain.com'
                                            });
                                        }
                                    }
                                });

                            }
                        },
                        cancel: {
                            text: "ยกเลิก"
                        }
                    }
                });
            }
    });
}

function createOrders(courses_id, is_clicked) {
    var $btn = $('#btnCreateOrder');
    var defaultMsg = 'ดำเนินการชำระเงิน';
    var waitingMsg = 'กำลังดำเนินการ...';
    var successMsg = '';

    if (is_clicked === true) {
        $btn.html(waitingMsg).prop('disabled', true);
    }

    var ordersData = JSON.stringify({'courses_id': courses_id, 'type_tax_invoice': $('#type_tax_invoice').val()});
    $.when(_orders.create(ordersData)).always(function() {
        // console.log("Always");
    }).done(function(data) {
        // console.log(data);
        // $('#taxInvoiceModal').modal('hide');
        fns.submitForm(data.payment_url, data.payment_request);
    }).fail(function(data) {
        if (data.status === 401) {
            $.get('/api/site/groups/'+fns.currentGroup(), function(groups) {
                if(groups.internal){
                    window.location.href = '/'+ groups.key +'/login';
                }else{
                    members.login("?courseid="+courses_id+"&action=info", fns.currentGroup());
                }
            });
        } else {
            $btn.html(defaultMsg).prop('disabled', false);

            var msg = '';

            if (data.responseJSON !== undefined) {
                if (data.responseJSON.error_msg !== undefined) {
                    msg = data.responseJSON.error_msg;
                } else if (data.responseJSON.message !== undefined) {
                    msg = data.responseJSON.message;
                }
            }

            fns.handleAlert('เกิดข้อผิดพลาด', msg, 'red', true);
        }
    });
}

// function payment(formSelector) {
//     console.log($(formSelector).data('orders-id'));
//     return false;
//     var paymentsData = $(formSelector).serializeObject();
//     paymentsData.orders_id = $(formSelector).data('orders-id');
//     paymentsData = JSON.stringify(paymentsData);

//     notification("warning",'กรุณารอสักครู่ ระบบกำลังตรวจสอบข้อมูล...');

//     setTimeout(function(){
//         $.when(_payments.create(paymentsData)).always(function() {
//             // console.log("Always");
//         }).done(function(data) {
//             notification("success", data.message);

//             setTimeout(function(){
//                 window.location.reload();
//             }, 3000);
//         }).fail(function(data) {
//             var msg = '';

//             if (data.responseJSON !== undefined) {
//                 if (data.responseJSON.error_msg !== undefined) {
//                     msg = data.responseJSON.error_msg;
//                 } else if (data.responseJSON.message !== undefined) {
//                     msg = data.responseJSON.message;
//                 }
//             }

//             fns.handleAlert('เกิดข้อผิดพลาด', msg);
//         });
//     }, 1000);
// }

function generateFormTaxInvoice() {
    if ($('#type_tax_invoice').val() === "personal") {
        $('.box-personal').show();
        $('.box-corporate').hide();

        if ($('.box-personal').find('.box-form').is(':visible') && !$('.box-personal').find('.box-form').hasClass('box-form-edit')) {
            $('#btnToggleFormTaxInvoice').hide();
            $('#btnUpdateTaxInvoice').show();
            $('#btnCreateOrder').hide();
        } else if ($('.box-personal').find('.box-form').is(':visible')) {
            $('#btnToggleFormTaxInvoice').show();
            $('#btnUpdateTaxInvoice').hide();
            $('#btnCreateOrder').show();
        } else {
            $('#btnToggleFormTaxInvoice').html('<i class="fa fa-pencil-square-o f-14 top-1" aria-hidden="true"></i> แก้ไขข้อมูล').blur();
            $('#btnToggleFormTaxInvoice').show();
            $('#btnUpdateTaxInvoice').hide();
            $('#btnCreateOrder').show();
        }
    } else {
        $('.box-corporate').show();
        $('.box-personal').hide();

        if ($('.box-corporate').find('.box-form').is(':visible') && !$('.box-corporate').find('.box-form').hasClass('box-form-edit')) {
            $('#btnToggleFormTaxInvoice').hide();
            $('#btnUpdateTaxInvoice').show();
            $('#btnCreateOrder').hide();
        } else if ($('.box-corporate').find('.box-form').is(':visible')) {
            $('#btnToggleFormTaxInvoice').show();
            $('#btnUpdateTaxInvoice').show();
            $('#btnCreateOrder').hide();
        } else {
            $('#btnToggleFormTaxInvoice').html('<i class="fa fa-pencil-square-o f-14 top-1" aria-hidden="true"></i> แก้ไขข้อมูล').blur();
            $('#btnToggleFormTaxInvoice').show();
            $('#btnUpdateTaxInvoice').hide();
            $('#btnCreateOrder').show();
        }
    }
}

function loadTaxInvoice() {
    var dfd = $.Deferred();

    members.getTaxInvoice().done(function(data) {
        var tmplPersonal = '<p class="m-b-10"><strong>ชื่อ - นามสกุล : </strong> ' + data.inv_personal_first_name + ' ' + data.inv_personal_last_name + '</p>'+
                    '<p class="m-b-10"><strong>เลขประจำตัวผู้เสียภาษี (เลขที่บัตรประชาชน) : </strong> ' + data.inv_personal_tax_id + '</p>'+
                    '<p class="m-b-10"><strong>ที่อยู่สำหรับออกใบกำกับภาษี : </strong> ' + data.inv_personal_address + '</p>'+
                    '<p class="m-b-10"><strong>รหัสไปรษณีย์ : </strong> ' + data.inv_personal_zip_code + '</p>'+
                    '<p class="m-b-10"><strong>เบอร์โทรศัพท์ : </strong> ' + data.inv_personal_tel + '</p>'+
                    '<p class="m-b-10"><strong>อีเมล์สำหรับจัดส่งใบกำกับภาษี : </strong> ' + data.inv_personal_email + '</p>';

        $('.box-personal').find('.box-info .box-info-content').empty().html(tmplPersonal);

        var txtBranch = '<span class="m-l-10">(สำนักงานใหญ่)</span>';
        if (data.inv_corporate_branch == 1) {
            txtBranch = '<strong class="m-l-15">สาขาที่ : </strong> ' + data.inv_corporate_branch_no;
        }

        var tmplCorporate = '<p class="m-b-10"><strong>ชื่อ-นามสกุล / ชื่อบริษัท : </strong> ' + data.inv_corporate_name + ' ' + txtBranch + '</p>'+
                    '<p class="m-b-10"><strong>เลขประจำตัวผู้เสียภาษี : </strong> ' + data.inv_corporate_tax_id + '</p>'+
                    '<p class="m-b-10"><strong>ที่อยู่สำหรับออกใบกำกับภาษี : </strong> ' + data.inv_corporate_address + '</p>'+
                    '<p class="m-b-10"><strong>รหัสไปรษณีย์ : </strong> ' + data.inv_corporate_zip_code + '</p>'+
                    '<p class="m-b-10"><strong>เบอร์โทรศัพท์ : </strong> ' + data.inv_corporate_tel + '</p>'+
                    '<p class="m-b-10"><strong>อีเมล์สำหรับจัดส่งใบกำกับภาษี : </strong> ' + data.inv_corporate_email + '</p>';

        $('.box-corporate').find('.box-info .box-info-content').empty().html(tmplCorporate);

        dfd.resolve(true);
    }).fail(function(data) {
        if (data.status === 401) {
            $.get('/api/site/groups/'+fns.currentGroup(), function(groups) {
                if(groups.internal){
                    window.location.href = '/'+ groups.key +'/login';
                }else{
                    members.login("?courseid="+fns.currentCourse()+"&action=info", fns.currentGroup());
                }
            });
        } else {
            if (data.responseJSON !== undefined) {
                // notification("error", data.responseJSON.message !== undefined ? data.responseJSON.message : "เกิดข้อผิดพลาดไม่สามารถโหลดข้อมูลได้" );
                notification("error", "เกิดข้อผิดพลาดไม่สามารถโหลดข้อมูลได้");
            }
        }
        dfd.reject(false);
    });

    return dfd.promise();
}

$(document).ready(function() {
    $('#btnViewSummary').on('click', function(event) {
        $.when(_enroll.getByCourse($(this).data('course'))).done(function(resp) {
            if (resp.id !== null && resp.id !== undefined) {
                window.location.href = "/" + fns.currentGroup() + '/enroll/' + resp.id + '/summary';
            }
        });
    });

    // Begin Tax Invoice
    // setTimeout(function() {
    //     $('#taxInvoiceModal').modal('show');
    // }, 500);

    $('#btnTaxInvoiceModal').on('click', function(event) {
        event.preventDefault();
        $.when(loadTaxInvoice()).done(function(isLoaded) {
            $('#taxInvoiceModal').modal('show');
        });
    });

    // generateFormTaxInvoice();

    $('#taxInvoiceModal').on('shown.bs.modal', function (e) {
        generateFormTaxInvoice();
        // setTimeout(function() {
        //     loadTaxInvoice();
        // }, 500);
    });

    $('#type_tax_invoice').on('change', function(event) {
        event.preventDefault();
        generateFormTaxInvoice();
        loadTaxInvoice();
    });

    $('#btnUpdateTaxInvoice').on('click', function(event) {
        event.preventDefault();
        if ($('#type_tax_invoice').val() === "personal") {
            $('#personal-form').submit();
        } else {
            $('#corporate-form').submit();
        }
    });

    $('#inv_corporate_branch').on('change', function(event) {
        event.preventDefault();
        if ($('#inv_corporate_branch').val() == 1) {
            $('#inv_corporate_branch_no').val('');
            $('#inv_corporate_branch_no').prop('disabled', false);
        } else {
            $('#inv_corporate_branch_no').val('00000');
            $('#inv_corporate_branch_no').prop('disabled', true);
            $('#inv_corporate_branch_no').closest('.form-group').removeClass('has-success').removeClass('has-error');
            $('#inv_corporate_branch_no').closest('.form-group').find('.help-block').hide();
        }
    });

    $('#inv_corporate_branch_no').on('blur', function(event) {
        event.preventDefault();
        if (!isNaN($('#inv_corporate_branch_no').val())) {
            $('#inv_corporate_branch_no').val(fns.str_pad($('#inv_corporate_branch_no').val(), 5, "0", "STR_PAD_LEFT"));
        }
    });

    $('#btnToggleFormTaxInvoice').on('click', function(event) {
        event.preventDefault();
        if ($('#type_tax_invoice').val() === "personal") {
            if ($('.box-personal').find('.box-form').is(':visible')) {
                $('.box-personal').find('.box-form').addClass('hide');
                $('.box-personal').find('.box-info').removeClass('hide');
                $(this).html('<i class="fa fa-pencil-square-o f-14 top-1" aria-hidden="true"></i> แก้ไขข้อมูล').blur();
                $('#btnUpdateTaxInvoice').hide();
                $('#btnCreateOrder').show();
            } else {
                $('.box-personal').find('.box-form').removeClass('hide');
                $('.box-personal').find('.box-info').addClass('hide');
                $(this).html('<i class="fa fa-arrow-left f-14" aria-hidden="true"></i> ย้อนกลับ').blur();
                $('#btnUpdateTaxInvoice').show();
                $('#btnCreateOrder').hide();
            }
        } else {
            if ($('.box-corporate').find('.box-form').is(':visible')) {
                $('.box-corporate').find('.box-form').addClass('hide');
                $('.box-corporate').find('.box-info').removeClass('hide');
                $(this).html('<i class="fa fa-pencil-square-o f-14 top-1" aria-hidden="true"></i> แก้ไขข้อมูล').blur();
                $('#btnUpdateTaxInvoice').hide();
                $('#btnCreateOrder').show();
            } else {
                $('.box-corporate').find('.box-form').removeClass('hide');
                $('.box-corporate').find('.box-info').addClass('hide');
                $(this).html('<i class="fa fa-arrow-left f-14" aria-hidden="true"></i> ย้อนกลับ').blur();
                $('#btnUpdateTaxInvoice').show();
                $('#btnCreateOrder').hide();
            }
        }
    });

    $('#personal-form')
        .formValidation({
            framework: 'bootstrap',
            excluded: ':disabled',
            fields: {
                inv_personal_first_name: {
                    validators: {
                        notEmpty: {
                            message: 'The first name is required.'
                        }
                    }
                },
                inv_personal_last_name: {
                    validators: {
                        notEmpty: {
                            message: 'The last name is required.'
                        }
                    }
                },
                inv_personal_tax_id: {
                    validators: {
                        notEmpty: {
                            message: 'The tax ID is required.'
                        },
                        numeric: {
                            message: 'The tax ID must be a number.'
                        },
                        stringLength: {
                            min: 13,
                            max: 13,
                            message: 'The tax ID must be equal 13 characters.'
                        }
                    }
                },
                inv_personal_email: {
                    validators: {
                        notEmpty: {
                            message: 'The email address is required.'
                        },
                        emailAddress: {
                            message: 'The input is not a valid email address.'
                        }
                    }
                },
                inv_personal_tel: {
                    validators: {
                        notEmpty: {
                            message: 'The telephone number is required.'
                        },
                        digits: {
                            message: 'The value is contains only digits.'
                        },
                        stringLength: {
                            message: 'The value must be between 9 and 10 characters',
                            min: 9,
                            max: 10
                        }
                    }
                },
                inv_personal_address: {
                    validators: {
                        notEmpty: {
                            message: 'The address is required.'
                        },
                        stringLength: {
                            message: 'The value must be not greater than 220 characters.',
                            max: 220
                        }
                    }
                },
                inv_personal_zip_code: {
                    validators: {
                        notEmpty: {
                            message: 'The zip code is required.'
                        },
                        numeric: {
                            message: 'The zip code must be a number.'
                        },
                        stringLength: {
                            message: 'The value must be not greater than 5 characters.',
                            max: 5
                        }
                    }
                },
            }
        })
        .on('err.form.fv', function(e) {
            var $this = $(this);
            if ($this.find(".has-error:first").length) {
                $(window).scrollTop(0);
                $('#taxInvoiceModal').scrollTop(0);
                $('#taxInvoiceModal').animate({
                    scrollTop: ($(this).find(".has-error:first").offset().top - 10)
                }, 500);
            }
        })
        .on('success.form.fv', function(e) {
            var $this = $(this);
            var modalPopup = "#taxInvoiceModal";
            var defaultMsg = 'บันทึกข้อมูล';
            var waitingMsg = 'กำลังตรวจสอบข้อมูล...';
            var successMsg = 'บันทึึกข้อมูลเรียบร้อย';

            var params = $this.serializeObject();
            params.latest_type_tax = 'personal';

            $('#btnUpdateTaxInvoice').html(waitingMsg).prop('disabled', true);
            $.post('/api/site/user/tax-invoice', params, function(data) {
                if (data.is_error == false) {
                    notification("success", data.message);
                    $('#btnUpdateTaxInvoice').html(successMsg);
                    $('#btnUpdateTaxInvoice').html(defaultMsg).prop('disabled', false);
                    $this.closest('.box-form').addClass('box-form-edit');
                    generateFormTaxInvoice();
                    $.when(loadTaxInvoice()).done(function(isLoaded) {
                        $('#btnToggleFormTaxInvoice').trigger('click');
                    });
                    // createOrders($('#btnUpdateTaxInvoice').data('course'));
                } else {
                    setTimeout(function() {
                        $('#btnUpdateTaxInvoice').html(defaultMsg).prop('disabled', false);
                    }, 800);
                    notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง" );
                }
            })
            .fail(function(resp) {
                var data = resp.responseJSON;
                setTimeout(function() {
                    $('#btnUpdateTaxInvoice').html(defaultMsg).prop('disabled', false);
                }, 800);
                fns.handleError(resp, $this, modalPopup)
            });

            return true;
        });

    $('#corporate-form')
        .formValidation({
            framework: 'bootstrap',
            excluded: ':disabled',
            fields: {
                inv_corporate_name: {
                    validators: {
                        notEmpty: {
                            message: 'The first name is required.'
                        },
                        stringLength: {
                            message: 'The value must be not greater than 70 characters.',
                            max: 70
                        }
                    }
                },
                inv_corporate_branch: {
                    validators: {
                        notEmpty: {
                            message: 'The branch is required.'
                        }
                    }
                },
                inv_corporate_branch_no: {
                    validators: {
                        notEmpty: {
                            message: 'The branch no. is required.'
                        },
                        numeric: {
                            message: 'The branch no. must be a number.'
                        },
                        stringLength: {
                            max: 5,
                            message: 'The branch no. must be equal 5 characters.'
                        }
                    }
                },
                inv_corporate_tax_id: {
                    validators: {
                        notEmpty: {
                            message: 'The tax ID is required.'
                        },
                        numeric: {
                            message: 'The tax ID must be a number.'
                        },
                        stringLength: {
                            min: 13,
                            max: 13,
                            message: 'The tax ID must be equal 13 characters.'
                        }
                    }
                },
                inv_corporate_email: {
                    validators: {
                        notEmpty: {
                            message: 'The email address is required.'
                        },
                        emailAddress: {
                            message: 'The input is not a valid email address.'
                        }
                    }
                },
                inv_corporate_tel: {
                    validators: {
                        notEmpty: {
                            message: 'The telephone number is required'
                        },
                        digits: {
                            message: 'The value is contains only digits.'
                        },
                        stringLength: {
                            message: 'The value must be between 9 and 10 characters.',
                            min: 9,
                            max: 10
                        }
                    }
                },
                inv_corporate_address: {
                    validators: {
                        notEmpty: {
                            message: 'The address is required.'
                        },
                        stringLength: {
                            message: 'The value must be not greater than 220 characters.',
                            max: 220
                        }
                    }
                },
                inv_corporate_zip_code: {
                    validators: {
                        notEmpty: {
                            message: 'The zip code is required.'
                        },
                        numeric: {
                            message: 'The zip code must be a number.'
                        },stringLength: {
                            message: 'The value must be not greater than 5 characters.',
                            max: 5
                        }
                    }
                },
            }
        })
        .on('err.form.fv', function(e) {
            var $this = $(this);
            if ($this.find(".has-error:first").length) {
                $(window).scrollTop(0);
                $('#taxInvoiceModal').scrollTop(0);
                $('#taxInvoiceModal').animate({
                    scrollTop: ($(this).find(".has-error:first").offset().top - 10)
                }, 500);
            }
        })
        .on('success.form.fv', function(e) {
            var $this = $(this);
            var modalPopup = "#taxInvoiceModal";
            var defaultMsg = 'บันทึกข้อมูล';
            var waitingMsg = 'กำลังตรวจสอบข้อมูล...';
            var successMsg = 'บันทึึกข้อมูลเรียบร้อย';

            var params = $this.serializeObject();
            params.latest_type_tax = 'corporate';

            $('#btnUpdateTaxInvoice').html(waitingMsg).prop('disabled', true);
            $.post('/api/site/user/tax-invoice', params, function(data) {
                if (data.is_error == false) {
                    notification("success", data.message);
                    $('#btnUpdateTaxInvoice').html(successMsg);
                    $('#btnUpdateTaxInvoice').html(defaultMsg).prop('disabled', false);
                    $this.closest('.box-form').addClass('box-form-edit');
                    generateFormTaxInvoice();
                    $.when(loadTaxInvoice()).done(function(isLoaded) {
                        $('#btnToggleFormTaxInvoice').trigger('click');
                    });
                    // createOrders($('#btnUpdateTaxInvoice').data('course'));
                } else {
                    setTimeout(function() {
                        $('#btnUpdateTaxInvoice').html(defaultMsg).prop('disabled', false);
                    }, 800);
                    notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง" );
                }
            })
            .fail(function(resp) {
                var data = resp.responseJSON;
                setTimeout(function() {
                    $('#btnUpdateTaxInvoice').html(defaultMsg).prop('disabled', false);
                }, 800);
                fns.handleError(resp, $this, modalPopup)
            });

            return true;
        });
    // End Tax Invoice
});










