<?php
$base = "service/";
include($base."service.php");
if (isset($_GET['group_key'])) {
    $groupKey = cleanGroupKey($_GET['group_key']);
} else {
    $groupKey = "G-Education";
}
$members = session();
$configuration = configuration();
$categories = categories($groupKey);
$groups = groups($groupKey);

if($members){
    $groups2id = groups2id($members['groups_id']);
    if($groups2id['id'] != $groups['id']){ header('Location: '.constant("_BASE_SITE_URL").'/'.$groups2id['key']); }
}

if(!$groups){ header('Location: '.constant("_PAGE_404"));}
if ($groups['status'] != 1 || $groups['internal'] != 1) {
    header('Location: '.constant("_PAGE_404"));
}

if ($members['avatar_id'] == '') {
    $head_avatar = '<i class="fa fa-user"></i>';
} else {
    $avatar = avatars($members['avatar_id']);
    $head_avatar = "<img width='22' src='".constant("_BASE_DIR_AVATARS").$avatar["avatar_img"]."'>";
}
?>
<!DOCTYPE html>
<!--[if lt IE 8 ]><html lang="en" class="noJs ie ieLegacy outdated permanent" xmlns:og="http://ogp.me/ns#" xmlns:mixi="http://mixi-platform.com/ns#"><![endif]-->
<!--[if IE 8 ]><html lang="en" class="noJs ie ie8 outdated" xmlns:og="http://ogp.me/ns#" xmlns:mixi="http://mixi-platform.com/ns#"><![endif]-->
<!--[if IE 9 ]><html lang="en" class="noJs ie ie9" xmlns:og="http://ogp.me/ns#" xmlns:mixi="http://mixi-platform.com/ns#"><![endif]-->
<!--[if !(IE)]><!--><html lang="en" class="noJs" xmlns:og="http://ogp.me/ns#" xmlns:mixi="http://mixi-platform.com/ns#"><!--<![endif]-->
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# website: http://ogp.me/ns/fb/website#">
    <!-- Meta -->
    <meta charset="utf-8">
    <title><?=$configuration['title']?></title>
    <?php include 'include/inc.meta.php'; ?>
    <meta name="description" content="<?=$configuration['meta_description']?>" />
    <meta name="keywords" content="<?=$configuration['meta_keywords']?>" />
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"> -->

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">
    <link rel="manifest" href="/favicons/manifest.json">
    <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="theme-color" content="#ffffff">

    <!-- CSS Library -->
    <link href="/bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/bower_components/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="/bower_components/superfish/dist/css/superfish.css" rel="stylesheet" media="screen">
    <link href="/bower_components/animate/animate.css" rel="stylesheet">
    <link href="/bower_components/noty/lib/noty.css" rel="stylesheet">
    <link href="/bower_components/jquery-confirm/dist/jquery-confirm.min.css" rel="stylesheet">

    <!-- CSS Style -->
    <link href="/css/style.css" rel="stylesheet">
    <link href="/css/custom.css" rel="stylesheet">

</head>

<body>
<?php include 'include/inc.header.php'; ?>
<!-- End header -->

<section id="login_bg">
    <div  class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
                <div id="login">
                    <p class="text-center">
                        <img src="<?=constant("_BASE_DIR_LOGO").$configuration['logo']?>">
                    </p>
                    <hr>
                    <form id="login-form" method="post" role="form" data-toggle="validator" enctype="multipart/form-data">
                        <input type="hidden" id="groups_id" name="groups_id" value="<?=$groups['id']?>">
                        <div class="form-group">
                            <input type="text" id="email" name="email" class="form-control" placeholder="อีเมลล์">
                            <span class="input-icon"><i class="fa fa-user"></i></span>
                        </div>
                        <div class="form-group" style="margin-bottom:5px;">
                            <input type="password" id="password" name="password" class="form-control" placeholder="รหัสผ่าน" style="margin-bottom:5px;">
                            <span class="input-icon"><i class="fa fa-lock"></i></span>
                        </div>
                        <p class="small">
                            <a href="<?=groupKey($groupKey)?>/forgot-password">ลืมรหัสผ่าน ?</a>
                        </p>
                        <button id="btn-internal-login" class="button_fullwidth">เข้าสู่ระบบ</button>
                        <a href="<?=groupKey($groupKey)?>/register" class="button_fullwidth-2">สมัครสมาชิก</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section> <!-- End login -->

<!-- Modal ChangePassword -->
<div class="modal fade" id="ChangePasswordModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-lock"></i> เปลี่ยน Password</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form id="change-password-form" method="post" role="form" data-toggle="validator" class="form-horizontal" enctype="multipart/form-data">
                            <input type="hidden" id="option" name="option" class="form-control">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="password" id="change_password" name="change_password" class="form-control required"  placeholder="ระบุ Password ใหม่">
                                        <span class="input-group-addon hint-addon" id="hint-addon"><i class="fa fa-question-circle icon-popover hint-password" rel="popover"></i></span>
                                    </div>
                                    <div class="progress password-meter" id="passwordMeter">
                                        <div class="progress-bar"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-style1">ยืนยันการเปลี่ยน Password</button>
                                    หรือ
                                    <a onclick="useOldPassword()" class="btn btn-style3">ต้องการใช้ Password เดิม</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- //Modal ChangePassword -->


<!-- Modal ChangeLevelGroups -->
<div class="modal fade" id="ChangeLevelGroupsModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-user"></i> แจ้งเตือนการเปลี่ยนกลุ่ม</h4>
            </div>
            <div class="modal-body" id="change-level-groups">
            </div>
        </div>
    </div>
</div>
<!-- //Modal ChangeLevelGroups -->

<!-- Modal ChangePassword -->
<div class="modal fade" id="SessionExistsModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-set">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-lock"></i> บัญชีผู้ใช้งานของท่านมีการใช้งานอยู่ในขณะนี้</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <ul>
                            <li class="m-b-5"><a class="btn-manage-user cursor-pointer" data-action="destroy"><i class="fa fa-sign-in f-16" aria-hidden="true"></i>&nbsp; ตัดการใช้งานปัจจุบันและเข้าสู่ระบบ</a></li>
                            <li class="m-b-5"><a class="btn-manage-user cursor-pointer" data-action="back"><i class="fa fa-home f-16" aria-hidden="true"></i>&nbsp; กลับสู่หน้าหลัก</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- //Modal ChangePassword -->


<?php include 'include/inc.footer.php'; ?>

<!-- Javascript Library -->
<script src="/bower_components/jquery/dist/jquery.min.js"></script>
<script src="/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="/bower_components/html5shiv/dist/html5shiv.min.js"></script>
<script src="/bower_components/respond/dest/respond.min.js"></script>
<script src="/bower_components/superfish/dist/js/superfish.min.js"></script>
<script src="/bower_components/noty/lib/noty.min.js"></script>
<script src="/js/script/config.js"></script>
<script src="/js/script/functions.js"></script>
<script src="/js/script/model/members.js"></script>
<script src="/js/main.js"></script>
<script src="/js/formvalidation.js"></script>
<script type="text/javascript">

    function useOldPassword(){
        $('#ChangePasswordModal').modal('hide');
        notification("warning",'กรุณารอสักครู่ ระบบกำลังตรวจสอบข้อมูล...');
        $.post('/api/site/user/use_old_password', function(data) {
            if(data.is_error == false){
                notification("success",data.message);

                if (data.hasGroupChanging) {
                    generateGroupChangingForm(data);
                } else {
                    setTimeout(function () {
                        window.location.href = '<?=groupKey($groupKey)?>';
                    },1800);
                }
            }
            if(data.is_error == true){
                notification("error",data.message);
            }
        });
    }

    function useSubGroup(sgID){
        $('#ChangeLevelGroupsModal').modal('hide');
        notification("warning",'กรุณารอสักครู่ ระบบกำลังตรวจสอบข้อมูล...');
        $.post('/api/site/user/use_sub_group', {sgID:sgID}, function(data) {
            if(data.is_error == false){
                notification("success",data.message);
                setTimeout(function () {
                    window.location.href = '<?=groupKey($groupKey)?>';
                },1800);
            }
            if(data.is_error == true){
                notification("error",data.message);
            }
        });
    }

    function oldSubGroup(){
        $('#ChangeLevelGroupsModal').modal('hide');
        setTimeout(function () {
            window.location.href = '<?=groupKey($groupKey)?>';
        },1800);
    }

    function generateGroupChangingForm(data) {
        $('#ChangeLevelGroupsModal').modal('show');
        var html = "";
        html +=
        '<h4>เลือกกลุ่มปัจุบันของท่าน</h4>' +
        '<div class="row">' +
            '<div class="col-xs-6 col-sm-6 col-md-6">' +
                '<a onclick="oldSubGroup();" class="btn btn-lg btn-style1 btn-block">'+ data.active.title +'</a>' +
            '</div>' +
        '</div>' +
        '<div class="login-or">' +
            '<hr class="hr-or">' +
            '<span class="span-or">หรือ</span>' +
        '</div>' +
        '<h4 class="m-b-0">เลือกกลุ่มที่สามารถเข้าร่วมได้</h4>' +
        '<div class="f-18 m-b-10">หมายเหตุ : หากท่านยืนยันการย้ายกลุ่ม หลังจากนั้นการเข้าสู่ระบบจะใช้อีเมล์ของกลุ่มใหม่ในการเข้าสู่ระบบเพื่อใช้งาน</div>' +
        '<div class="row">';

            for(var i=0; i<data.conflict.length; i++) {
                html +=
                '<div class="col-xs-6 col-sm-6 col-md-6">' +
                    '<a onclick="useSubGroup('+data.conflict[i].id+');" class="btn btn-lg btn-style3 btn-block">' +
                        data.conflict[i].title + '<br>' +
                        '( ' + data.conflict[i].pivot.email + ' )' +
                    '</a>' +
                '</div>';
            }

        html += '</div>';
        $('#change-level-groups').html(html);
    }

    function login(isForce) {
        var credentials = $('#login-form').serializeObject();

        if (isForce !== undefined && isForce === true) {
            credentials.forceLogin = true;
        }

        $("#btn-internal-login").button('loading');
        $.post('/api/site/user/login', credentials, function(data) {
            $('#SessionExistsModal').modal('hide');
            $("#btn-internal-login").button('reset');
            if(data.is_error == false){
                if(data.option == 'change-password'){
                    $('#option').val(data.option);
                    $('#ChangePasswordModal').modal('show');
                }else if(data.option == 'has-group-changing'){
                    generateGroupChangingForm(data);
                }else if(data.option == 're-active'){
                    $('#option').val(data.option);
                    $('#ChangePasswordModal').modal('show');
                }else{
                    notification("success",data.message);
                    setTimeout(function () {
                        window.location.href = '<?=groupKey($groupKey)?>';
                    },1800);
                }
            } else {
                if (data.redirectPage !== undefined) {
                    $('#SessionExistsModal').modal('show');
                } else {
                    if (data.isContact == true) {
                        notification("error",data.message,9000,true);
                    } else {
                        notification("error",data.message,9000);
                    }
                }
            }
        });
        return true;
    }

    $(document).ready(function() {

        jQuery.ajaxPrefilter( function( options, originalOptions, jqXHR ) {
            options.crossDomain ={
                crossDomain: true
            };
            options.xhrFields = {
                withCredentials: true
            };
        });

        // Login
        $('#login-form')
            .formValidation({
                framework: 'bootstrap',
                excluded: ':disabled',
                fields: {
                    email: {
                        validators: {
                            notEmpty: {
                                message: 'The email address is required'
                            },
                            emailAddress: {
                                message: 'The input is not a valid email address'
                            }
                        }
                    },
                    password: {
                        validators: {
                            notEmpty: {
                                message: 'The password is required'
                            },
                            different: {
                                field: 'username',
                                message: 'The password cannot be the same as username'
                            }
                        }
                    }
                }
            })
            .on('success.form.fv', function(e) {
                login();
            });

        // Change Password
        $('#change-password-form')
            .formValidation({
                framework: 'bootstrap',
                excluded: ':disabled',
                fields: {
                    change_password: {
                        validators: {
                            notEmpty: {
                                message: 'The password is required'
                            },
                            callback: {
                                callback: function(value, validator, $field) {
                                    var score = 0;

                                    if (value === '') {
                                        return {
                                            valid: true,
                                            score: null
                                        };
                                    }

                                    // Check the password strength
                                    score += ((value.length >= 8) ? 1 : -1);

                                    // The password contains uppercase character
                                    if (/[A-Z]/.test(value)) {
                                        score += 1;
                                    }

                                    // The password contains lowercase character
                                    if (/[a-z]/.test(value)) {
                                        score += 1;
                                    }

                                    // The password contains number
                                    if (/[0-9]/.test(value)) {
                                        score += 1;
                                    }

                                    // The password contains special characters
                                    // if (/[!#$%&^~*_@]/.test(value)) {
                                    //     score += 1;
                                    // }

                                    var $bar  = $('#passwordMeter').find('.progress-bar');

                                    switch (true) {
                                        case (score === null):
                                            $bar.html('').css('width', '0%').removeClass().addClass('progress-bar');
                                            break;

                                        case (score <= 0):
                                            $bar.html('Very weak').css('width', '25%').removeClass().addClass('progress-bar progress-bar-danger');
                                            break;

                                        case (score > 0 && score <= 2):
                                            $bar.html('Weak').css('width', '50%').removeClass().addClass('progress-bar progress-bar-warning');
                                            break;

                                        case (score > 2 && score <= 3):
                                            $bar.html('Medium').css('width', '75%').removeClass().addClass('progress-bar progress-bar-warning-2');
                                            break;

                                        case (score > 3):
                                            $bar.html('Strong').css('width', '100%').removeClass().addClass('progress-bar progress-bar-success');
                                            break;

                                        default:
                                            break;
                                    }

                                    if (score < 4) {
                                        return {
                                            valid: false,
                                            // message: 'The password is weak.'
                                        }
                                    }

                                    return {
                                        valid: true,
                                        score: score    // We will get the score later
                                    };
                                }
                            }
                        }
                    }
                }
            })
            .on('success.form.fv', function(e) {
                notification("warning",'กรุณารอสักครู่ ระบบกำลังตรวจสอบข้อมูล...');
                $.post('/api/site/user/change_password', $('#change-password-form').serialize(), function(data) {
                    if(data.is_error == false){
                        $('#ChangePasswordModal').modal('hide');
                        notification("success",data.message);

                        if (data.hasGroupChanging) {
                            generateGroupChangingForm(data);
                        } else {
                            setTimeout(function () {
                                window.location.href = '<?=groupKey($groupKey)?>';
                            },1800);
                        }
                    }
                    if(data.is_error == true){
                        notification("error",data.message);
                    }
                });
                return true;

            });

        // Manage Session
        $('.btn-manage-user').on('click', function(event) {
            if ($(this).data('action') === "back") {
                members.forgetSession().always(function() {
                    window.location.href = "/" + fns.currentGroup();
                    return false;
                });
            } else {
                login(true);
            }
        });

    });
</script>
</body>
</html>