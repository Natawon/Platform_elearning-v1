var intevalLoadDiscussion;
var loadDiscussion = function(coursesID) {
    var deferredDiscussionList;
    var $eleDiscussionTableBody;
    var tmpl;
    var momentNow;
    var diffDays;
    var discussionBy;
    var discussionByType;
    var discussionDate;
    var unread;

    $eleDiscussionTableBody = $('#res-discussion').find('tbody');
    $eleDiscussionTableBody.css('opacity', 0.2);

    deferredDiscussionList = _instructors.getDiscussionListByCourse(coursesID);
    deferredDiscussionList.done(function(dataDiscussionList) {
        // console.log('loadDiscussion');
        // console.log(dataDiscussionList);

        if (dataDiscussionList.length > 0) {
            tmpl = "";
            momentNow = moment();
            $.each(dataDiscussionList, function(index, value) {
                diffDays = momentNow.diff(dataDiscussionList[index].create_datetime, 'hours');

                if (diffDays >= 22) {
                    discussionDate = fns.normalDateTimeTHClock(dataDiscussionList[index].create_datetime);
                } else {
                    discussionDate = moment(dataDiscussionList[index].create_datetime).fromNow();
                }

                switch(dataDiscussionList[index].type) {
                    case 1:
                        discussionByType = '(ผู้ดูแลระบบ)';
                        discussionBy = dataDiscussionList[index].modify_by;
                        break;
                    case 2:
                        discussionByType = '(วิทยากร)';
                        discussionBy = dataDiscussionList[index].instructors.title;
                        break;
                    default:
                        discussionByType = '';
                        discussionBy = dataDiscussionList[index].members.first_name + ' ' +dataDiscussionList[index].members.last_name;
                }

                dataDiscussionList[index].unread_class = "";

                if (!dataDiscussionList[index].is_instructors_read) {
                    dataDiscussionList[index].unread_class = "hl-default";
                }

                var replyUnread = _.find(dataDiscussionList[index].replies, ['is_instructors_read', false]);
                if (replyUnread !== undefined) {
                    dataDiscussionList[index].unread_class = "hl-default";
                }

                for (var j = 0; j < dataDiscussionList[index].replies.length; j++) {
                    var subReplyUnread = _.find(dataDiscussionList[index].replies[j].replies, ['is_instructors_read', false]);
                    if (subReplyUnread !== undefined) {
                        dataDiscussionList[index].unread_class = "hl-default";
                        break;
                    }
                }

                tmpl += '<tr class="'+dataDiscussionList[index].unread_class+'">'+
                            '<td>'+
                                '<a role="button" class="block btnShowDiscussion" data-id="'+dataDiscussionList[index].id+'">'+dataDiscussionList[index].topic+'</a>'+
                                '<small class="">โดยคุณ <span>'+discussionBy+'</span> '+discussionByType+'<span class="m-l-3 visible-xs"><i class="fa fa-clock-o f-12"></i> '+discussionDate+'</span></small>'+
                            '</td>'+
                            '<td class="hidden-xs middle"><i class="fa fa-calendar"></i> '+discussionDate+'</td>'+
                            '<td class="middle"><i class="fa fa-eye"></i> '+dataDiscussionList[index].view+'</td>'+
                            '<td class="middle"><i class="fa fa-reply"></i> '+dataDiscussionList[index].count_reply+'</td>'+
                        '</tr>';
            });

            $eleDiscussionTableBody.html(tmpl);
            $eleDiscussionTableBody.animate({
                opacity: 1
            }, 800);
        } else {
            tmpl = '<tr>'+
                        '<td colspan="4">'+
                            '<a href="#" class="col-md-12">ยังไม่มีการตั้งหัวเรื่องในหลักสูตรนี้</a>'+
                        '</td>'+
                    '</tr>';

            $eleDiscussionTableBody.html(tmpl);
            $eleDiscussionTableBody.animate({
                opacity: 1
            }, 800);
        }
    });
};

var loadDiscussionDetail = function(discussionId, focusDiscussionId) {
    var deferredDiscussionDetail;
    var $modal = $('#discussionModal');
    var tmpl;
    var momentNow;
    var diffDays;
    var discussionByType, discussionBy, replyByType, replyBy, subReplyByType, subReplyBy;
    var discussionDate, replyDate, subReplyDate;

    deferredDiscussionDetail = _instructors.getDiscussion(discussionId);
    deferredDiscussionDetail.done(function(data) {
        // console.log(data);
        momentNow = moment();

        $modal.find('#parent_id').val(data.id);
        $modal.attr('data-id', data.id);
        $modal.find('#discussionModalLabel').text(data.topic);

        if (data.file) {
            $modal.find('#discussionModalPicture').html('<img src="'+URL_DATA_FILE+'/discussion/'+data.file+'" alt="'+data.topic+'" class="img-responsive m-b-10">');
        }

        $modal.find('#discussionModalDescription').html(fns.parseNewLineToHtml(data.description));

        switch(data.type) {
            case 1:
                discussionByType = '(ผู้ดูแลระบบ)';
                discussionBy = data.modify_by;
                break;
            case 2:
                discussionByType = '(วิทยากร)';
                discussionBy = data.instructors.title;
                break;
            default:
                discussionByType = '';
                discussionBy = data.members.first_name + ' ' +data.members.last_name;
        }

        $modal.find('#discussionModalBy').text(discussionBy);
        $modal.find('#discussionModalByType').text(discussionByType);

        diffDays = momentNow.diff(data.create_datetime, 'hours');

        if (diffDays >= 22) {
            discussionDate = fns.normalDateTimeTHClock(data.create_datetime);
        } else {
            discussionDate = moment(data.create_datetime).fromNow();
        }

        $modal.find('#discussionModalDateTime').text(discussionDate);

        tmpl = "";
        $.each(data.replies, function(index, value) {

            diffDays = momentNow.diff(data.replies[index].create_datetime, 'hours');

            if (diffDays >= 22) {
                replyDate = fns.normalDateTimeTHClock(data.replies[index].create_datetime);
            } else {
                replyDate = moment(data.replies[index].create_datetime).fromNow();
            }

            switch(data.replies[index].type) {
                case 1:
                    replyByType = '(ผู้ดูแลระบบ)';
                    replyBy = data.replies[index].modify_by;
                    break;
                case 2:
                    replyByType = '(วิทยากร)';
                    replyBy = data.replies[index].instructors.title;
                    break;
                default:
                    replyByType = '';
                    replyBy = data.replies[index].members.first_name + ' ' +data.replies[index].members.last_name;
            }

            data.replies[index].unread_class = "";
            if (!data.replies[index].is_instructors_read) {
                data.replies[index].unread_class = "border-hl-warning";
            }

            tmpl += '<div class="col-sm-12">'+
                        '<div id="discussionId_'+data.replies[index].id+'" class="panel panel-light-white post panel-shadow '+data.replies[index].unread_class+'">'+
                            '<div class="post-description">';

                                if (data.replies[index].file) {
                                    tmpl += '<img src="'+URL_DATA_FILE+'/discussion/'+data.replies[index].file+'" alt="'+data.replies[index].topic+'" class="img-responsive">';
                                }

                                tmpl += '<p class="m-b-0">'+fns.parseNewLineToHtml(data.replies[index].description)+'</p>'+
                            '</div> '+
                            '<div class="post-footer"> '+
                                '<div class="f-16">'+
                                    '— <strong>'+replyBy+'</strong>'+' '+replyByType+
                                    '<span class="time m-l-3"><i class="fa fa-clock-o f-12"></i> '+replyDate+'</span>'+
                                '</div>'+

                                '<div class="stats f-14 m-t-5">'+
                                    // '<a role="button" class="btn btn-default btn-xs f-14 m-r-5 stat-item btnLikeComment" data-id="'+data.replies[index].id+'">'+
                                    //     '<i class="top-1 fa fa-thumbs-up icon m-r-5"></i>'+data.replies[index].count_like+
                                    // '</a>'+
                                    // '<a role="button" class="btn btn-default btn-xs f-14 m-r-5 stat-item btnDislikeComment" data-id="'+data.replies[index].id+'">'+
                                    //     '<i class="top-1 fa fa-thumbs-down icon m-r-5"></i>'+data.replies[index].count_dislike+
                                    // '</a>'+
                                    '<a role="button" class="btn btn-primary btn-xs f-14 m-r-5 stat-item btnReplyComment" data-reply-type="1" data-mention-to="'+replyBy+'" data-id="'+data.replies[index].id+'" data-parent-id="'+data.replies[index].parent_id+'">'+
                                        '<i class="top-1 fa fa-reply icon m-r-5"></i>ตอบกลับความคิดเห็น'+
                                    '</a>'+
                                '</div>'+
                            '</div>';

            $.each(data.replies[index].replies, function(sub_index, sub_value) {

                diffDays = momentNow.diff(data.replies[index].replies[sub_index].create_datetime, 'hours');

                if (diffDays >= 22) {
                    subReplyDate = fns.normalDateTimeTHClock(data.replies[index].replies[sub_index].create_datetime);
                } else {
                    subReplyDate = moment(data.replies[index].replies[sub_index].create_datetime).fromNow();
                }

                switch(data.replies[index].replies[sub_index].type) {
                    case 1:
                        subReplyByType = '(ผู้ดูแลระบบ)';
                        subReplyBy = data.replies[index].replies[sub_index].modify_by;
                        break;
                    case 2:
                        subReplyByType = '(วิทยากร)';
                        subReplyBy = data.replies[index].replies[sub_index].instructors.title;
                        break;
                    default:
                        subReplyByType = '';
                        subReplyBy = data.replies[index].replies[sub_index].members.first_name + ' ' +data.replies[index].replies[sub_index].members.last_name;
                }

                data.replies[index].replies[sub_index].unread_class = "";
                if (!data.replies[index].replies[sub_index].is_instructors_read) {
                    data.replies[index].replies[sub_index].unread_class = "border-hl-light-warning";
                }

                tmpl += '<div id="discussionId_'+data.replies[index].replies[sub_index].id+'" class="post-reply">'+
                            '<div class="panel panel-light-grey panel-shadow-disabled m-b-15 '+data.replies[index].replies[sub_index].unread_class+'">'+
                                '<div class="post-description">';

                                    if (data.replies[index].replies[sub_index].file) {
                                        tmpl += '<img src="'+URL_DATA_FILE+'/discussion/'+data.replies[index].replies[sub_index].file+'" alt="'+data.replies[index].replies[sub_index].topic+'" class="img-responsive">';
                                    }

                                    tmpl += '<p class="m-b-0">'+fns.parseNewLineToHtml(data.replies[index].replies[sub_index].description)+'</p>'+
                                '</div> '+
                                '<div class="post-footer"> '+
                                    '<div class="f-16">'+
                                        '— <strong>'+subReplyBy+'</strong>'+' '+subReplyByType+
                                        '<span class="time m-l-3"><i class="fa fa-clock-o f-12"></i> '+subReplyDate+'</span>'+
                                    '</div>'+

                                    '<div class="stats f-14 m-t-5">'+
                                        // '<a role="button" class="btn btn-default btn-xs f-14 m-r-5 stat-item btnLikeComment" data-id="'+data.replies[index].replies[sub_index].id+'">'+
                                        //     '<i class="top-1 fa fa-thumbs-up icon m-r-5"></i>'+data.replies[index].replies[sub_index].count_like+
                                        // '</a>'+
                                        // '<a role="button" class="btn btn-default btn-xs f-14 m-r-5 stat-item btnDislikeComment" data-id="'+data.replies[index].replies[sub_index].id+'">'+
                                        //     '<i class="top-1 fa fa-thumbs-down icon m-r-5"></i>'+data.replies[index].replies[sub_index].count_dislike+
                                        // '</a>'+
                                        '<a role="button" class="btn btn-primary btn-xs f-14 m-r-5 stat-item btnReplyComment" data-reply-type="2" data-mention-to="'+subReplyBy+'" data-id="'+data.replies[index].replies[sub_index].id+'" data-parent-id="'+data.replies[index].replies[sub_index].parent_id+'">'+
                                            '<i class="top-1 fa fa-reply icon m-r-5"></i>ตอบกลับความคิดเห็น'+
                                        '</a>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>';
            });

            tmpl += '</div>'+
                '</div>';
        });

        $modal.find('#reply-box').html(tmpl);

        if (focusDiscussionId !== undefined) {
            // reset the scroll to top
            $(window).scrollTop(0);
            $('#discussionModal').scrollTop(0);
            setTimeout(function() {
                //scroll the container
                $('#discussionModal').animate({
                    // scrollTop: $('#discussionId_'+focusDiscussionId).offset().top - (($(window).height() / 2) - 100)
                    scrollTop: $('#discussionId_'+focusDiscussionId).offset().top - 10
                }, "slow");

                setTimeout(function() {
                    // console.log('#discussionId_'+focusDiscussionId+' > .post-heading');
                    $('#discussionId_'+focusDiscussionId).find('.post-footer .time').fadeOut('fast').delay(100).fadeIn(700)
                }, 800);
            }, 400);
        } else {
            var topEleReplyFocus;
            var topEleSubReplyFocus;
            $(window).scrollTop(0);
            $('#discussionModal').scrollTop(0);
            setTimeout(function() {
                if ($('.border-hl-warning').length > 0) {
                    topEleReplyFocus = $('.border-hl-warning').offset().top;
                }

                if ($('.border-hl-light-warning').length > 0) {
                    topEleSubReplyFocus = $('.border-hl-light-warning').offset().top;
                }

                console.log(!isNaN(topEleReplyFocus));
                console.log(!isNaN(topEleSubReplyFocus));

                if (!isNaN(topEleReplyFocus) && !isNaN(topEleSubReplyFocus)) {
                    if (topEleReplyFocus < topEleSubReplyFocus) {
                        console.log('outer');
                        $('#discussionModal').animate({
                            scrollTop: topEleReplyFocus - 10
                        }, "slow");
                    } else {
                        console.log('inner');
                        $('#discussionModal').animate({
                            scrollTop: topEleSubReplyFocus - 10
                        }, "slow");
                    }
                } else if (!isNaN(topEleReplyFocus)) {
                    $('#discussionModal').animate({
                        scrollTop: topEleReplyFocus - 10
                    }, "slow");
                } else if (!isNaN(topEleSubReplyFocus)) {
                    $('#discussionModal').animate({
                        scrollTop: topEleSubReplyFocus - 10
                    }, "slow");
                }
            }, 400);
        }

        _instructors.readDiscussion(discussionId);

    }).fail(function(data) {
        console.log('failed');
    });
};

function startLoadDiscussion() {
    loadDiscussion($('#param_courses_id').val());
    intevalLoadDiscussion = setInterval(function() {
        loadDiscussion($('#param_courses_id').val());
    }, 10000);
}

$(document).ready(function() {
    $('#accessModal').modal('show');

    $('#accessModal').on('shown.bs.modal', function (e) {
        $(this).find('#code').focus();
    });

    $('#accessModal').on('hide.bs.modal', function (e) {
        window.location.href = "/" + fns.currentGroup();
    });

    $('#instructors-login-frm')
        .formValidation({
            framework: 'bootstrap',
            excluded: ':disabled',
            fields: {
                code: {
                    validators: {
                        notEmpty: {
                            message: 'The code is required'
                        }
                    }
                },
            }
        })
        .on('success.form.fv', function(e) {
            var $this = $(this);
            var modalPopup = "#accessModal";
            var param = JSON.stringify($('#instructors-login-frm').serializeObject());

            $this.find('#btnInstructorLogin').prop('disabled', true);

            _instructors.login($('#param_courses_id').val(), param).done(function(resp) {
                notification("success", resp.message);
                window.location.reload();
            }).fail(function(resp) {
                setTimeout(function() {
                    $this.find('#btnInstructorLogin').prop('disabled', false).removeClass('disabled');
                }, 500);
                fns.handleError(resp, $this, modalPopup);
            });
            return true;
        });

    if ($('#accessModal').length < 1) {
        startLoadDiscussion();
    }

    $('#discussion-reply-form').formValidation({
        framework: 'bootstrap',
        excluded: ':disabled',
        fields: {
            description: {
                validators: {
                    notEmpty: {
                        message: 'กรุณาระบุ ข้อความ'
                    }
                }
            }
        }
    }).on('success.form.fv', function(e) {
        var $this = $(this);
        var defaultMsg = 'แสดงความคิดเห็น';
        var waitingMsg = 'ระบบกำลังอัพโหลดและตรวจสอบข้อมูล...';
        var successMsg = 'แสดงความคิดเห็น';

        var dataDiscussionReply, dataFile;
        var deferredReply, deferredPictureUpload;

        $this.find('#discussion-reply-btn').html(waitingMsg).prop('disabled', true);
        dataDiscussionReply = $this.serializeObject();
        // console.log(dataDiscussionReply);
        // return false;
        if($this.find('#file').val()){
            dataFile = new FormData($this.closest('form')[0]);
            deferredPictureUpload = _discussion.uploadPicture(dataFile);
            deferredPictureUpload.done(function(responseDataPicture) {
                responseDataPicture = $.parseJSON(responseDataPicture);
                dataDiscussionReply.file = responseDataPicture.file_name;
                    deferredReply = _instructors.discussionReply(JSON.stringify(dataDiscussionReply))
                    deferredReply.done(function(data) {
                        if (data.is_error == false) {
                            $this.find('#discussion-reply-btn').html(successMsg).prop('disabled', false);

                            $('.btnCancelReply').trigger('click');
                            $this[0].reset();
                            loadDiscussionDetail($this.closest('#discussionModal').attr('data-id'), data.discussion_id);
                        } else {
                            $this.find('#discussion-reply-btn').html(defaultMsg).prop('disabled', false);
                            notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง" );
                        }
                    }).fail(function(data) {
                        $this.find('#discussion-reply-btn').html(defaultMsg).prop('disabled', false);
                        notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง" );
                    });
                }).fail(function(data) {
                    $this.find('#discussion-reply-btn').html(defaultMsg).prop('disabled', false);
                    notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง" );
                });
        }else{
                deferredReply = _instructors.discussionReply(JSON.stringify(dataDiscussionReply))
                deferredReply.done(function(data) {
                    if (data.is_error == false) {
                        $this.find('#discussion-reply-btn').html(successMsg).prop('disabled', false);

                        $('.btnCancelReply').trigger('click');
                        $this[0].reset();
                        loadDiscussionDetail($this.closest('#discussionModal').attr('data-id'), data.discussion_id);
                    } else {
                        $this.find('#discussion-reply-btn').html(defaultMsg).prop('disabled', false);
                        notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง" );
                    }
                }).fail(function(data) {
                    $this.find('#discussion-reply-btn').html(defaultMsg).prop('disabled', false);
                    notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง" );
                });
        }
        return false;
    });

    $('input[name=file]').on('change', function(event) {
        if (this.files[0].size > 2000000) {
            $(this).val('');
            notification("error", "ขนาดรูปภาพเกิน 2 MB");
        }
    });

    $('#btnReloadDiscussion').on('click', function(event) {
        event.preventDefault();
        loadDiscussion($('#param_courses_id').val());
    });

    $('body').on('click', '.btnShowDiscussion', function(event) {
        event.preventDefault();
        var $this = $(this);

        _instructors.updateDiscussionView($this.data('id'));

        loadDiscussionDetail($this.data('id'));
        $('#discussionModal').modal('show');
    });

    // $('body').on('click', '.btnLikeComment', function(event) {
    //     event.preventDefault();
    //     var $this = $(this);

    //     _discussion.updateLike($this.data('id'));

    //     loadDiscussionDetail($this.closest('#discussionModal').attr('data-id'));

    // });

    // $('body').on('click', '.btnDislikeComment', function(event) {
    //     event.preventDefault();
    //     var $this = $(this);

    //     _discussion.updateDislike($this.data('id'));

    //     loadDiscussionDetail($this.closest('#discussionModal').attr('data-id'));

    // });

    $('body').on('click', '.btnReplyComment', function(event) {
        event.preventDefault();
        var $this = $(this);
        var $eleReplySelected = $('#discussionId_'+$this.data('id')).clone();

        $('#wrapper-reply-selected').closest('.form-group').removeClass('hide');
        $eleReplySelected.find('.post-description > img').addClass('m-w-25');

        if ($this.data('reply-type') == 1) {
            $eleReplySelected.css('border', '2px solid #ffa400').addClass('m-b-10');
            $eleReplySelected.find('.post-reply').remove();

            $this.closest('#discussionModal').find('#discussion-reply-form #parent_id').val($this.data('id'));
            $this.closest('#discussionModal').find('#discussion-reply-form #mention_id').val(null);
            $this.closest('#discussionModal').find('#discussion-reply-form #description').val('');
        } else {
            $eleReplySelected.find('.panel').css('border', '2px solid #ffa400').addClass('m-b-10');

            $this.closest('#discussionModal').find('#discussion-reply-form #parent_id').val($this.data('parent-id'));
            $this.closest('#discussionModal').find('#discussion-reply-form #mention_id').val($this.data('id'));

            $this.closest('#discussionModal').find('#discussion-reply-form #description').val('@'+$this.data('mention-to')+' ');
        }

        $eleReplySelected.find('.stats').remove();
        $('#wrapper-reply-selected').html($eleReplySelected);

        $this.closest('#discussionModal').find('#discussion-reply-form #discussion-reply-btn').html('ตอบกลับความคิดเห็นที่เลือก');
        $this.closest('#discussionModal').find('#discussion-reply-form #label-reply').html('ตอบกลับความคิดเห็นที่เลือก <a role="button" class="btnCancelReply text-danger f-16"><i class="fa fa-times f-12"></i> ยกเลิกการตอบกลับ</a>');
        $this.closest('#discussionModal').find('#discussion-reply-form #description').focus();
    });

    $('body').on('click', '.btnCancelReply', function(event) {
        event.preventDefault();
        var $this = $(this);

        $('#wrapper-reply-selected').closest('.form-group').addClass('hide');
        $('#wrapper-reply-selected').empty();
        $this.closest('#discussionModal').find('#discussion-reply-form #parent_id').val($this.closest('#discussionModal').attr('data-id'));
        $this.closest('#discussionModal').find('#discussion-reply-form #mention_id').val(null);
        $this.closest('#discussionModal').find('#discussion-reply-form #description').val('');
        $this.closest('#discussionModal').find('#discussion-reply-form #discussion-reply-btn').html('แสดงความคิดเห็น');
        $this.closest('#discussionModal').find('#discussion-reply-form #label-reply').text('แสดงความคิดเห็น');
    });

    $('#discussionModal').on('hide.bs.modal', function (e) {
        var $modal = $('#discussionModal');

        startLoadDiscussion();
        $modal.find('#discussionModalPicture').html('');
        $('#wrapper-reply-selected').closest('.form-group').addClass('hide');
        $('#wrapper-reply-selected').empty();

        $modal.find('#discussion-reply-form #parent_id').val($modal.attr('data-id'));
        $modal.find('#discussion-reply-form #mention_id').val(null);
        $modal.find('#discussion-reply-form #description').val('');
        $modal.find('#discussion-reply-form #discussion-reply-btn').html('แสดงความคิดเห็น');
        $modal.find('#discussion-reply-form #label-reply').text('แสดงความคิดเห็น');
    });

    $('#discussionModal').on('show.bs.modal', function (e) {
        clearInterval(intevalLoadDiscussion);
    });

    // Toggle Discussion Form
    $('#collapseDiscussionForm').on('show.bs.collapse', function () {
        $('#btnToggleDiscussionFormOut').hide();
        $('#btnToggleDiscussionFormIn').show();
    });

    $('#collapseDiscussionForm').on('hidden.bs.collapse', function () {
        $('#btnToggleDiscussionFormIn').hide();
        $('#btnToggleDiscussionFormOut').show();
    });

    $('#btn-instructors-logout').on('click', function(event) {
        event.preventDefault();
        $.when(_instructors.logout()).done(function(data) {
            window.location.href = "/" + fns.currentGroup();
        }).fail(function(data) {
            console.log('failed to logout.');
        });
    });
});




