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
$licenseTypes = array();

if($members){
    $groups2id = groups2id($members['groups_id']);
    if($groups2id['id'] != $groups['id']){ header('Location: '.constant("_BASE_SITE_URL").'/'.$groups2id['key']); }
}

if(!$groups){ header('Location: '.constant("_PAGE_404"));}
if ($groups['status'] != 1 || $groups['internal'] != 1) {
    header('Location: '.constant("_PAGE_404"));
}

if ($groups['id'] == 3) {
    $licenseTypes = licenseTypes();
}

if ($members['avatar_id'] == '') {
    $head_avatar = '<i class="fa fa-user"></i>';
} else {
    $avatar = avatars($members['avatar_id']);
    $head_avatar = "<img width='22' src='".constant("_BASE_DIR_AVATARS").$avatar["avatar_img"]."'>";
}

/* Captcha */
$_SESSION = array();
include("bower_components/simple-php-captcha-master/simple-php-captcha.php");
$_SESSION['captcha'] = simple_php_captcha();
/* Captcha */

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
    <link href="/bower_components/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="/bower_components/jquery-ui/jquery-ui.css" rel="stylesheet">
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
	<div class="col-md-8 col-md-offset-2 col-sm-6 col-sm-offset-3">
		<div id="login">
            <p class="text-center">
                <img src="<?=constant("_BASE_DIR_LOGO").$configuration['logo']?>">
            </p>
			<hr>
            <div class="row">
                <div class="col-md-12">
                    <div class="text-center">
                        <p class="text-center">
                            <strong class="f-24 label-thai">สมัครสมาชิก</strong>
                            <strong class="f-24 label-foreign">Register</strong>
                        </p>
                    </div>
                    <hr class="hr-divider-dashed hr-space-md">
                </div>
            </div>
            <form id="account-form" method="post" role="form" data-toggle="validator" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" id="groups_id" name="groups_id" value="<?=$groups['id']?>">
                    <input type="hidden" id="is_foreign" name="is_foreign" value="0">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="">อีเมล์ <span class="text-danger">*</span></label>
                            <input type="text" id="email" name="email" class="form-control required" placeholder="อีเมล์">
                            <span class="input-icon"><i class="fa fa-envelope"></i></span>
                        </div>
                        <div class="form-group">
                            <label class="">รหัสผ่าน <span class="text-danger">*</span> <i class="fa fa-question-circle icon-popover hint-password" rel="popover"></i></label>
                            <input type="password" id="password" name="password" class="form-control required" placeholder="รหัสผ่าน">
                            <span class="input-icon"><i class="fa fa-lock"></i></span>
                            <div class="progress password-meter" id="passwordMeter">
                                <div class="progress-bar"></div>
                            </div>
                            <label>รหัสผ่านต้องเป็น Strong เท่านั้นถึงจะผ่าน</label>
                            <label>ตัวอย่างรหัสผ่านที่ถูกต้อง :  passw0rd</label>
                            <div>
                                <span id="password_condition1" class="glyphicon glyphicon-minus" aria-hidden="true"></span>
                                รหัสผ่านมีตัวอักษร 8 ตัวขึ้นไป
                            </div>
                            <div>
                                <span id="password_condition2" class="glyphicon glyphicon-minus" aria-hidden="true"></span>
                                รหัสผ่านใช้ตัวเลข
                            </div>
                            <div>
                                <span id="password_condition3" class="glyphicon glyphicon-minus" aria-hidden="true"></span>
                                รหัสผ่านใช้ตัวพิมพ์เล็ก
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="">ยืนยันรหัสผ่าน <span class="text-danger">*</span></label>
                            <input type="password" name="confirmPassword" class="form-control required" placeholder="ยืนยันรหัสผ่าน">
                            <span class="input-icon"><i class="fa fa-lock"></i></span>
                        </div>

                        <div class="form-group">
                            <label class="">คำนำหน้าชื่อ <span class="text-danger">*</span></label><br>
                            <label class="radio-inline">
                                <input class="removeButton" data-remove="disabled" type="radio" name="name_title" id="name_title_1" value="นาย" checked> <span class="">นาย</span> &nbsp;
                            </label>
                            <label class="radio-inline">
                                <input class="removeButton" data-remove="disabled" type="radio" name="name_title" id="name_title_2" value="นาง"> <span class="">นาง</span> &nbsp;
                            </label>
                            <label class="radio-inline">
                                <input class="removeButton" data-remove="disabled" type="radio" name="name_title" id="name_title_3" value="นางสาว"> <span class="">นางสาว</span> &nbsp;
                            </label>
                            <label class="radio-inline">
                                <input class="addButton" data-add="enabled" type="radio" name="name_title" id="name_title_4" value="other"> <span class="">อื่น ๆ</span>
                            </label>
                        </div>

                        <div class="form-group hide" id="name_title_otherTemplate">
                            <input type="text" id="name_title_other" name="name_title_other" class="form-control m-t-10" placeholder="คำนำหน้าชื่อ" disabled>
                        </div>

                        <div class="form-group">
                            <label class="">คำนำหน้าชื่อ (EN) <span class="text-danger">*</span></label><br>
                            <label class="radio-inline">
                                <input class="removeButton" data-remove="disabled" type="radio" name="name_title_en" id="name_title_1_en" value="Mr." checked> <span class="">Mr.</span> &nbsp;
                            </label>
                            <label class="radio-inline">
                                <input class="removeButton" data-remove="disabled" type="radio" name="name_title_en" id="name_title_2_en" value="Mrs."> <span class="">Mrs.</span> &nbsp;
                            </label>
                            <label class="radio-inline">
                                <input class="removeButton" data-remove="disabled" type="radio" name="name_title_en" id="name_title_3_en" value="Ms."> <span class="">Ms.</span> &nbsp;
                            </label>
                            <label class="radio-inline">
                                <input class="addButton" data-add="enabled" type="radio" name="name_title_en" id="name_title_4_en" value="other"> <span class="">Other</span>
                            </label>
                        </div>

                        <div class="form-group hide" id="name_title_other_enTemplate">
                            <input type="text" id="name_title_other_en" name="name_title_other_en" class="form-control m-t-10" placeholder="คำนำหน้าชื่อ (EN)" disabled>
                        </div>

                        <div class="vspace-5 hide-for-name-title-other"></div>

                        <div class="form-group">
                            <label>ชื่อ <span class="text-danger">*</span></label>
                            <input type="text" id="first_name" name="first_name" class="form-control required"  placeholder="ชื่อ">
                        </div>
                        <div class="form-group">
                            <label>นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" id="last_name" name="last_name" class="form-control required"  placeholder="นามสกุล">
                        </div>
                        <div class="form-group">
                            <label>ชื่อ (EN) <span class="text-danger">*</span></label>
                            <input type="text" id="first_name_en" name="first_name_en" class="form-control required"  placeholder="ชื่อ (EN)">
                        </div>
                        <div class="form-group">
                            <label>นามสกุล (EN) <span class="text-danger">*</span></label>
                            <input type="text" id="last_name_en" name="last_name_en" class="form-control required"  placeholder="นามสกุล (EN)">
                        </div>
                        <div class="form-group">
                            <label class="">เพศ <span class="text-danger">*</span></label><br>
                            <label class="radio-inline">
                                <input type="radio" name="gender" id="gender_1" value="M" checked> <span class="">ชาย</span> &nbsp;
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="gender" id="gender_2" value="F"> <span class="">หญิง</span> &nbsp;
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        
                        <div class="form-group">
                            <label>เลขบัตรประชาชน <span class="text-muted">(เฉพาะผู้ที่ต้องการรับใบประกาศ)</span></label>
                            <input type="text" id="id_card" name="id_card" maxlength="20" class="form-control required"  placeholder="เลขบัตรประชาชน">
                        </div>
                        <div class="form-group">
                            <label>วันเกิด <span class="text-muted">(เฉพาะผู้ที่ต้องการรับใบประกาศ)</span></label>
                            <input type="text" id="" name="birth_date" class="form-control birth_date required"  placeholder="วันเกิด (YYYY-MM-dd)">
                       </div>

                        <div class="form-group">
                            <label>หมายเลขโทรศัพท์มือถือ <span class="text-danger">*</span></label>
                            <input type="text" id="mobile_number" name="mobile_number" maxlength="10" class="form-control required"  placeholder="หมายเลขโทรศัพท์มือถือ">
                        </div>
                        <div class="form-group">
                            <label><?=$groups['meaning_of_sub_groups_id']?> <span class="text-danger">*</span></label>
                            <div class="styled-select" style="margin-bottom: 0px;">
                                <select class="form-control sub_groups_id" name="sub_groups_id" id="">
                                    <option value="" selected>เลือก<?=$groups['meaning_of_sub_groups_id']?></option>
                                    <?php foreach($groups['sub_groups'] as $sub_groups) {?>
                                    <option value="<?=$sub_groups['id']?>"><?=$sub_groups['title']?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?=$groups['meaning_of_level_groups_id']?> <span class="text-danger">*</span></label>
                            <div class="styled-select" style="margin-bottom: 0px;">
                                <select class="form-control level_groups_id" name="level_groups_id" id="">
                                    <option value="" selected>เลือก<?=$groups['meaning_of_level_groups_id']?></option>
                                </select>
                            </div>
                        </div>

                        <div class="vspace-20 hidden-xxs hidden-xs hidden-sm"></div>

                        <div class="form-group">
                            <label>ระดับการศึกษา <span class="text-danger">*</span></label>
                            <div class="styled-select">
                                <select name="education_level_id" id="education_level_id" class="form-control">
                                    <option value="1">ต่ำกว่าปริญญาตรี</option>
                                    <option value="2">ปริญญาตรี</option>
                                    <option value="3">ปริญญาโท</option>
                                    <option value="4">ปริญญาเอก</option>
                                </select>
                            </div>
                       </div>

                       <div class="vspace-27 hidden-xxs hidden-xs hidden-sm hide-for-name-title"></div>

                        <div class="form-group">
                            <div class="text-center box-captcha">
                                <img class="captchaImg" src="<?=$_SESSION['captcha']['image_src']?>" alt="CAPTCHA code"><br>
                            </div>
                            <label>รหัสยืนยัน <span class="text-danger">*</span></label>
                            <button id="" type="button" class="btn btn-link pull-right reloadCaptcha" style="padding-right: 0;"><i class="fa fa-refresh" aria-hidden="true"></i> <span>โหลดรูปภาพอีกครั้ง</span></button>
                            <input type="text" id="captcha" name="captcha" class="form-control required"  placeholder="" autocomplete="false">
                        </div>

                    </div>
                    <div id="pass-info" class="clearfix"></div>
                    <button class="button_fullwidth" id="submit-btn">ยืนยันการสมัครสมาชิก</button>
			</form>
		</div>
	</div>
</div>
</div>
</section><!-- End register -->

<?php include 'include/inc.footer.php'; ?>

<!-- Javascript Library -->
<script src="/bower_components/jquery/dist/jquery.min.js"></script>
<script src="/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="/bower_components/html5shiv/dist/html5shiv.min.js"></script>
<script src="/bower_components/respond/dest/respond.min.js"></script>
<script src="/bower_components/superfish/dist/js/superfish.min.js"></script>
<script src="/bower_components/noty/lib/noty.min.js"></script>
<script src="/bower_components/jquery-confirm/dist/jquery-confirm.min.js"></script>
<script src="/bower_components/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="/bower_components/bootstrap-datepicker/locales/bootstrap-datepicker.th.min.js"></script>
<script src="/bower_components/jquery-ui/jquery-ui.min.js"></script>


<script src="/bower_components/moment/min/moment.min.js"></script>
<script src="/js/formvalidation.js"></script>
<script src="/js/script/config.js"></script>
<script src="/js/script/functions.js"></script>
<script src="/js/script/model/members.js"></script>
<script src="/js/main.js"></script>
<script type="text/javascript">
$(document).ready(function() {

    jQuery.ajaxPrefilter( function( options, originalOptions, jqXHR ) {
        options.crossDomain ={
            crossDomain: true
        };
        options.xhrFields = {
            withCredentials: true
        };
    });

    $('.birth_date').datepicker({
        // language: "th-TH",
        // endDate: "0d",
        // startView: 2,
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        defaultDate: new Date(),
        yearRange: "-100:+0",
        
        isBuddhist: true,

    }).on('changeDate', function(e) {
        $(this).closest('form').formValidation('revalidateField', 'birth_date');
    });

    $('input[type=radio][name=name_title], input[type=radio][name=name_title_en]').on('change', function(event) {
        var $formWarpper = $(this).closest('form');

        if ($(this).attr('name') === "name_title") {
            switch ($(this).val()) {
                case 'นาย': $formWarpper.find('input[type=radio][name=name_title_en][value="Mr."]').prop('checked', true).trigger('click'); break;
                case 'นาง': $formWarpper.find('input[type=radio][name=name_title_en][value="Mrs."]').prop('checked', true).trigger('click'); break;
                case 'นางสาว': $formWarpper.find('input[type=radio][name=name_title_en][value="Ms."]').prop('checked', true).trigger('click'); break;
                case 'other': $formWarpper.find('input[type=radio][name=name_title_en][value="other"]').prop('checked', true).trigger('click'); break;
            }
        } else {
            switch ($(this).val()) {
                case 'Mr.': $formWarpper.find('input[type=radio][name=name_title][value="นาย"]').prop('checked', true).trigger('click'); break;
                case 'Mrs.': $formWarpper.find('input[type=radio][name=name_title][value="นาง"]').prop('checked', true).trigger('click'); break;
                case 'Ms.': $formWarpper.find('input[type=radio][name=name_title][value="นางสาว"]').prop('checked', true).trigger('click'); break;
                case 'other': $formWarpper.find('input[type=radio][name=name_title][value="other"]').prop('checked', true).trigger('click'); break;
            }
        }

        switch ($(this).val()) {
            case 'นาย':
            case 'Mr.':
                $formWarpper.find('input[type=radio][name=gender][value="M"]').prop('checked', true); break;
            break;

            case 'นาง':
            case 'นางสาว':
            case 'Mrs.':
            case 'Ms.':
                $formWarpper.find('input[type=radio][name=gender][value="F"]').prop('checked', true); break;
            break;
        }
    });

    $('.btnFormThai').on('click', function(event) {
        $(this).addClass('active');
        $('.btnFormForeign').removeClass('active');
        // $('#is_foreign').val(0);
        $('#account-form-foreign, .label-foreign').fadeOut('fast', function() {
            $('#account-form, .label-thai').fadeIn();
        });
    });

    $('.btnFormForeign').on('click', function(event) {
        $(this).addClass('active');
        $('.btnFormThai').removeClass('active');
        // $('#is_foreign').val(1);
        $('#account-form, .label-thai').fadeOut('fast', function() {
            $('#account-form-foreign, .label-foreign').fadeIn();
        });
    });

    function conditionSetting(id,result){
        if(result){
            $('#'+id).removeClass('glyphicon-minus');
            $('#'+id).addClass('glyphicon-ok');
            $('#'+id).css('color','green');
        }
        else {
            $('#'+id).removeClass('glyphicon-ok');
            $('#'+id).addClass('glyphicon-minus');
            $('#'+id).css('color','');
        }
    }


    // IMPORTANT: You must call .steps() before calling .formValidation()
    $('#account-form, #account-form-foreign')
        .formValidation({
            framework: 'bootstrap',
            // excluded: ':disabled',
            fields: {
                email: {
                    validators: {
                        notEmpty: {
                            message: 'The email address is required.'
                        },
                        emailAddress: {
                            message: 'The input is not a valid email address.'
                        }
                    }
                },
                password: {
                    validators: {
                        notEmpty: {
                            message: 'The password is required.'
                        },
                        different: {
                            field: 'username',
                            message: 'The password cannot be the same as username.'
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
                                
                                if(value.length >= 8){
                                    score += 1;
                                    conditionSetting('password_condition1',true);
                                }else{
                                    conditionSetting('password_condition1',false);
                                }

                                // The password contains number
                                if (/[0-9]/.test(value)) {
                                    score += 1;
                                    conditionSetting('password_condition2',true);
                                }else{
                                    conditionSetting('password_condition2',false);
                                }

                                // The password contains lowercase character
                                if (/[a-z]/.test(value)) {
                                    score += 1;
                                    conditionSetting('password_condition3',true);
                                }else{
                                    conditionSetting('password_condition3',false);
                                }

                                var $bar  = validator.$form.find('#passwordMeter .progress-bar');

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
                },
                confirmPassword: {
                    validators: {
                        notEmpty: {
                            message: 'The reconfirmation password is required.'
                        },
                        identical: {
                            field: 'password',
                            message: 'The reconfirmation password must be the same as original one.'
                        }
                    }
                },
                name_title_other: {
                    validators: {
                        notEmpty: {
                            message: 'The name title is required.'
                        },
                        regexp: {
                            regexp: /^[.ๅภถุึคตจขชๆไำพะัีรนยบลฃฟหกดเ้่าสวงผปแอิืทมใฝู฿ฎฑธํ๊ณฯญฐฅฤฆฏโฌ็๋ษศซฉฮฺ์ฒฬฦ ]+$/i,
                            message: 'The name title can consist of Thai characters and spaces only.'
                        }
                    }
                },
                name_title_other_en: {
                    validators: {
                        notEmpty: {
                            message: 'The name title is required.'
                        },
                        regexp: {
                            regexp: /^[.a-z\s]+$/i,
                            message: 'The name title can consist of alphabetical English characters and spaces only.'
                        }
                    }
                },
                first_name: {
                    validators: {
                        notEmpty: {
                            message: 'The first name is required.'
                        },
                        regexp: {
                            regexp: /^[.ๅภถุึคตจขชๆไำพะัีรนยบลฃฟหกดเ้่าสวงผปแอิืทมใฝู฿ฎฑธํ๊ณฯญฐฅฤฆฏโฌ็๋ษศซฉฮฺ์ฒฬฦ ]+$/i,
                            message: 'The first name can consist of Thai characters and spaces only.'
                        }
                    }
                },
                first_name_en: {
                    validators: {
                        notEmpty: {
                            message: 'The first name is required.'
                        },
                        regexp: {
                            regexp: /^[.a-z\s]+$/i,
                            message: 'The first name can consist of alphabetical English characters and spaces only.'
                        }
                    }
                },
                last_name: {
                    validators: {
                        notEmpty: {
                            message: 'The last name is required.'
                        },
                        regexp: {
                            regexp: /^[.ๅภถุึคตจขชๆไำพะัีรนยบลฃฟหกดเ้่าสวงผปแอิืทมใฝู฿ฎฑธํ๊ณฯญฐฅฤฆฏโฌ็๋ษศซฉฮฺ์ฒฬฦ ]+$/i,
                            message: 'The last name can consist of Thai characters and spaces only.'
                        }
                    }
                },
                last_name_en: {
                    validators: {
                        notEmpty: {
                            message: 'The last name is required.'
                        },
                        regexp: {
                            regexp: /^[.a-z\s]+$/i,
                            message: 'The last name can consist of alphabetical English characters and spaces only.'
                        }
                    }
                },
                mobile_number: {
                    validators: {
                        notEmpty: {
                            message: 'The mobile number is required.'
                        },
                        digits: {
                            message: 'The value is not contains only digits.'
                        },
                        stringLength: {
                            message: 'The value must be between 9 and 10 characters.',
                            min: 9,
                            max: 10
                        }
                    }
                },
                sub_groups_id: {
                    validators: {
                        notEmpty: {
                            message: 'The sub group is required.'
                        },
                        identical: {
                            message: 'The input is not a valid sub group.'
                        }
                    }
                },
                level_groups_id: {
                    validators: {
                        notEmpty: {
                            message: 'The unit is required.'
                        },
                        identical: {
                            message: 'The input is not a valid unit.'
                        }
                    }
                },
                education_level_id: {
                    validators: {
                        notEmpty: {
                            message: 'The education degree is required.'
                        },
                        numeric: {
                            message: 'The education degree must be a number.'
                        }
                    }
                },
                <?php if ($groups['id'] == 3) { ?>
                    position_id: {
                        validators: {
                            notEmpty: {
                                message: 'The position is required.'
                            }
                        }
                    },
                    <?php
                }
                ?>
                captcha: {
                    validators: {
                        notEmpty: {
                            message: 'The captcha is required.'
                        },
                        identical: {
                            message: 'The input is not a valid captcha.'
                        },
                        numeric: {
                            message: 'The captcha must be a number.'
                        }
                    }
                }
            }
        })
        .on('click', '.addButton', function() {
            if ($(this).attr('name') === "name_title") {
                if ($(this).attr('data-add') != "disabled") {
                    var $template = $(this).closest('form').find('#name_title_otherTemplate'),
                        $clone    = $template.clone().removeClass('hide').removeAttr('id').insertBefore($template),
                        $option   = $clone.find('[name="name_title_other"]').removeAttr('disabled');

                    // Add new field
                    $(this).closest('form').formValidation('addField', $option);

                    $(this).closest('.form-group').addClass('m-b-0');
                    $(this).closest('.form-group').find('.removeButton[name="name_title"]').attr('data-remove', 'enabled');
                }
            } else if ($(this).attr('name') === "name_title_en") {
                if ($(this).attr('data-add') != "disabled") {
                    var $template = $(this).closest('form').find('#name_title_other_enTemplate'),
                        $clone    = $template.clone().removeClass('hide').removeAttr('id').insertBefore($template),
                        $option   = $clone.find('[name="name_title_other_en"]').removeAttr('disabled');

                    // Add new field
                    $(this).closest('form').formValidation('addField', $option);

                    $(this).closest('.form-group').addClass('m-b-0');
                    $(this).closest('.form-group').find('.removeButton[name="name_title_en"]').attr('data-remove', 'enabled');
                }
            }
        })
        .on('click', '.removeButton', function() {
            if ($(this).attr('name') === "name_title") {
                if ($(this).attr('data-remove') != "disabled") {
                    var $row    = $(this).parents('.form-group').next('.form-group'),
                        $option = $row.find('[name="name_title_other"]');

                    // Remove element containing the option
                    $row.remove();

                    // Remove field
                    $(this).closest('form').formValidation('removeField', $option);

                    $(this).closest('.form-group').removeClass('m-b-0');
                    $(this).closest('.form-group').find('.removeButton[name="name_title"]').attr('data-remove', 'disabled');
                }
            } else if ($(this).attr('name') === "name_title_en") {
                if ($(this).attr('data-remove') != "disabled") {
                    var $row    = $(this).parents('.form-group').next('.form-group'),
                        $option = $row.find('[name="name_title_other_en"]');

                    // Remove element containing the option
                    $row.remove();

                    // Remove field
                    $(this).closest('form').formValidation('removeField', $option);

                    $(this).closest('.form-group').removeClass('m-b-0');
                    $(this).closest('.form-group').find('.removeButton[name="name_title_en"]').attr('data-remove', 'disabled');
                }
            }
        })
        .on('added.field.fv', function(e, data) {
            // data.field   --> The field name
            // data.element --> The new field element
            // data.options --> The new field options

            if (data.field === 'name_title_other') {
                if ($(this).closest('form').find(':visible[name="name_title_other"]').length >= 1) {
                    $(this).closest('form').find('.addButton[name="name_title"]').attr('data-add', 'disabled');
                }
            } else if (data.field === 'name_title_other_en') {
                if ($(this).closest('form').find(':visible[name="name_title_other_en"]').length >= 1) {
                    $(this).closest('form').find('.addButton[name="name_title_en"]').attr('data-add', 'disabled');
                }
            }
        })
        .on('removed.field.fv', function(e, data) {
           if (data.field === 'name_title_other') {
                if ($(this).closest('form').find(':visible[name="name_title_other"]').length < 1) {
                    $(this).closest('form').find('.addButton[name="name_title"]').attr('data-add', 'enabled');
                }
            } else if (data.field === 'name_title_other_en') {
                if ($(this).closest('form').find(':visible[name="name_title_other_en"]').length < 1) {
                    $(this).closest('form').find('.addButton[name="name_title_en"]').attr('data-add', 'enabled');
                }
            }
        })
        .on('err.form.fv', function(e) {
            var $this = $(this);
            if ($this.find(".has-error:first").length) {
                $('html, body').animate({
                    scrollTop: ($(this).find(".has-error:first").offset().top - 100)
                }, 500);
            }
        })
        .on('success.form.fv', function(e) {
            var $this = $(this);
            var defaultMsg = 'ยืนยันการสมัครสมาชิก';
            var waitingMsg = 'กรุณารอสักครู่ ระบบกำลังตรวจสอบข้อมูล...';
            var successMsg = 'ตรวจสอบข้อมูลเรียบร้อย';
            // console.log($(this).serializeObject());
            // return false;
            if ($this.attr('id') == "account-form-foreign") {
                defaultMsg = 'Confirm Registration';
                waitingMsg = 'Please wait...';
                successMsg = 'Successfully checked data';
            }

            $this.find('#submit-btn').html(waitingMsg).prop('disabled', true);
            $promiseData = $.post('/api/site/user/register', $this.serializeObject());
            $promiseData.done(function(data) {
                $this.find('#submit-btn').html(successMsg);
                if(data.is_error == false){
                    $.confirm({
                        theme: 'supervan',
                        title: 'แจ้งเตือน',
                        content: data.message,
                        buttons: {
                            cancel: {
                                text: 'ตกลง',
                                action: function(){
                                    window.location.href = '<?=groupKey($groupKey)?>';
                                }
                            }
                        }
                    });
                }
                if(data.is_error == true){
                    $this.find('#submit-btn').html(defaultMsg).prop('disabled', false);
                    notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง" );
                }
            }).fail(function(resp) {
                var data = resp.responseJSON;
                $this.find('#submit-btn').html(defaultMsg).prop('disabled', false);
                notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง" );
            });
            return true;
        });

        $(".sub_groups_id").change(function(){
            var $this = $(this)
            var subGroupsID = $(this).val();
            $.ajax({
                url: "/api/site/sub_groups/"+subGroupsID,
                global: false,
                type: "GET",
                dataType: "JSON",
                async:false,
                success: function(jd) {
                    var opt="<option value=\"\" selected=\"selected\">เลือก<?=$groups['meaning_of_level_groups_id']?></option>";
                    $.each(jd, function(key, val){
                        opt +="<option value='"+ val["id"] +"'>"+val["title"]+"</option>"
                    });
                    $this.closest('form').find(".level_groups_id").html( opt );
                }
            });
        });

        $(".reloadCaptcha").on('click', function(event) {
            event.preventDefault();
            $this = $(this);
            $.get(PROJECT_ROOT+'/api-captcha.php?' + (new Date).getTime(), function(data) {
                $(".captchaImg").attr('src', data);
            });
        });
});
</script>
  </body>
</html>