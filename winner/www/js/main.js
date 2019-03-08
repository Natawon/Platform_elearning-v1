// Handle Frobidden
$(document).ajaxError(function(event, jqxhr, settings, thrownError) {
    if (jqxhr.responseText === "No input file specified.") {
        window.location.reload();
        // window.location.href = PROJECT_ROOT;
    }
});


// MENU  UPDATED
if ( $(window).width() > 767) {
    jQuery('ul.sf-menu').superfish({
        animation: {opacity:'show'},
        animationOut: {opacity:'hide'}
    });
}
else {
    jQuery('ul.sf-menu').superfish({
        animation: {opacity:'visible'},
        animationOut: {opacity:'visible'}
    });
}

// HOVER IMAGE MAGNIFY
//Set opacity on each span to 0%
$(".photo_icon").css({'opacity':'0'});

$('.picture a').hover(
    function() {
        $(this).find('.photo_icon').stop().fadeTo(800, 1);
    },
    function() {
        $(this).find('.photo_icon').stop().fadeTo(800, 0);
    }
)

// STICKY NAV
$(window).scroll(function() {
    if ($(this).scrollTop() > ($('header.header-member').height() + 85)) {
        $('nav').addClass("sticky");
    }
    else{
        $('nav').removeClass("sticky");
    }
});

// MENU MOBILE
$('#mobnav-btn').click(
function () {
    $('.sf-menu').slideToggle(400).toggleClass("xactive");
});

$('.mobnav-subarrow').click(
function () {
    $(this).parent().toggleClass("xpopdrop");
});

// SCROLL TO TOP
$(function() {
    $(window).scroll(function() {
        if($(this).scrollTop() != 0) {
            $('#toTop').fadeIn();
        } else {
            $('#toTop').fadeOut();
        }
    });

    $('#toTop').click(function() {
        $('body,html').animate({scrollTop:0},500);
    });

});

if( window.innerWidth < 770 ) {
    $("button.forward, button.backword").click(function() {
        $("html, body").animate({ scrollTop: 115 }, "slow");
        return false;
    });
}

if( window.innerWidth < 500 ) {
    $("button.forward, button.backword").click(function() {
        $("html, body").animate({ scrollTop: 245 }, "slow");
        return false;
    });
}
if( window.innerWidth < 340 ) {
    $("button.forward, button.backword").click(function() {
        $("html, body").animate({ scrollTop: 280 }, "slow");
        return false;
    });
}

// NOTY
var notification = function (status,alertText,time,isContact) {
    if (typeof Noty !== 'undefined') {
        if (isContact === true) {
            alertText = alertText + '<br><span class="text-small">กรุณาติดต่อผู้ดูแลหลักสูตร หรือ Contact Center<br>โทร. 02-000-0000<br>อีเมล์ : info@domain.com</span>';
        }

        new Noty({
            type: status,
            layout: 'topRight',
            theme: 'bootstrap-v4',
            text: '<h4>'+alertText+'</h4>',
            timeout: (time != undefined && time != null) ? time : 2500,
            progressBar: true
        }).show();
    } else {
        alert(alertText);
    }
}


$(document).ready(function() {
    // Login
    $(".btn-login, .btn-register").on('click', function(event) {
        var currentGroup = $(this).data('group');
        var redirectParams = "?action=home";

        members.login(redirectParams, currentGroup);
    });

    // Logout
    $("header, nav").on('click', '#btn-logout', function(event) {
        var $this = $(this);
        $this.button('loading');

        // members.logout();

        $.when(members.logout()).always(function() {
            // $this.button('reset');
        }).done(function(data) {
            if (data.params) {
                fns.submitForm(data.signoutUrl, data.params);
            } else {
                window.location.href = data.signoutUrl;
            }
        }).fail(function(data) {
            window.location.reload();
        });
    });

    // Popover
    $('[data-toggle="popover"]').popover();

    // Hint
    $('.hint-password').popover({
        "container": "body",
        "placement": "bottom",
        "title": "Hint Info.",
        'html': true,
        'content': '<ul class="hint-list"> <li>The password is at least 8 characters long.</li> <li>The password is alphanumeric and contain both letters and numbers.</li> <li>The password is a mix of uppercase and lowercase letters.</li> <li>The password should not contain contextual information such as login credentials, website name etc.</li> </ul>'
        // 'content': '<ul class="hint-list"> <li>The password is at least 8 characters long.</li> <li>The password is alphanumeric and contain both letters and numbers.</li> <li>The password is a mix of uppercase and lowercase letters.</li> <li>The password contains special characters such as #,$ etc.</li> <li>The password should not contain contextual information such as login credentials, website name etc.</li> </ul>'
    });

    // Datepicker
    if ($.fn.datepicker !== undefined) {
        $.fn.datepicker.defaults.autoclose = true;
        $.fn.datepicker.defaults.format = "yyyy-mm-dd";
        $.fn.datepicker.defaults.todayHighlight = true;
    }

    // Search Courses
    $('#frm-search-courses').submit(function(event) {
        var search_keyword = $(this).find('#search_keyword').val();

        if (search_keyword != "" && search_keyword.length >= 3) {
            window.location.href = "/" + fns.currentGroup() + "/search/" + search_keyword;
        }

        return false;
    });

    // Filter Courses Modal
    if ($('#filterCoursesModal').data('popup') == true) {
        $('#filterCoursesModal').modal('show');
    }

    $('.btn-open-filter-courses').on('click', function(event) {
        $('#filterCoursesModal').modal('show');
    });

    $('#btnFilterCourses').button({loadingText: 'กำลังประมวลผล...'});
    $('#btnFilterCourses').on('click', function(event) {
        var $this = $(this);
        var formValue = $('#filter-courses-form').serializeObject();
        var params = {
            "questions": []
        };

        $this.button('loading');
        params.qpid = formValue.qpid;
        delete formValue.qpid;

        for (var key in formValue) {
            if (formValue.hasOwnProperty(key)) {
                params.questions.push({
                    "id": key,
                    "answer": formValue[key]
                });
            }
        }

        setTimeout(function() {
            filterCourses.suit(JSON.stringify(params)).always(function() {
                $this.button('reset');
            }).done(function(data) {
                notification("success", data.message);
                $('#filterCoursesModal').modal('hide');
                setTimeout(function() {
                    window.location.href = "/" + fns.currentGroup() + "/my-profile/filter-courses";
                }, 1000);
            }).fail(function(data) {
                var errObj = data.responseJSON;
                notification("error", errObj.message, 3000, true);
            });
        }, 500);

    });

    $('#filterCoursesModal').on('click', function(event) {
        $uiClicked = $(event.target);

        if ($uiClicked.attr('id') == "filterCoursesModal" || $uiClicked.parent('.close').length) {
            var param = JSON.stringify({ "filter_courses_status": 0 });
            members.manageActionLogin(param);
            $('#filterCoursesModal').modal('hide');
        }
    });

    $('#btnCloseWithAgain').on('click', function(event) {
        var param = JSON.stringify({ "filter_courses_status": 0 });
        members.manageActionLogin(param);
        $('#filterCoursesModal').modal('hide');
    });

    $('#btnCloseWithoutAgain').on('click', function(event) {
        var param = JSON.stringify({ "filter_courses_status": 1 });
        members.manageActionLogin(param);
        $('#filterCoursesModal').modal('hide');
    });
    // End Filter Courses Modal

});

// Check Menu Responsive
var _detectIOS = fns.detectIOS();
if (_detectIOS) {
    $('.sf-menu').css({
        'letter-spacing': '-0.5px',
        'font-family': 'inherit'
    });
}



