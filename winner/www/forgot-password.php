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
                    <form id="forgot-form" method="post" role="form" data-toggle="validator" enctype="multipart/form-data">
                        <input type="hidden" id="groups_id" name="groups_id" value="<?=$groups['id']?>">
                        <div class="form-group">
                            <input type="text" id="email" name="email" class="form-control required" placeholder="Email">
                            <span class="input-icon"><i class="fa fa-envelope"></i></span>
                        </div>
                        <button class="button_fullwidth">ยืนยันการขอรหัสผ่าน</button>
                        <a href="<?=groupKey($groupKey)?>/login" class="button_fullwidth-2">เข้าสู่ระบบ</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section> <!-- End login -->

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
    $(document).ready(function() {

        jQuery.ajaxPrefilter( function( options, originalOptions, jqXHR ) {
            options.crossDomain ={
                crossDomain: true
            };
            options.xhrFields = {
                withCredentials: true
            };
        });

        // IMPORTANT: You must call .steps() before calling .formValidation()
        $('#forgot-form')
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
                    }
                }
            })
            .on('success.form.fv', function(e) {
                notification("warning","กรุณารอสักครู่...");
                $.post('/api/site/user/forgot', $('#forgot-form').serialize(), function(data) {
                    if(data.is_error == false){
                        notification("success",data.message);
                        setTimeout(function () {
                            window.location.href = '<?=groupKey($groupKey)?>/login';
                        },1800);
                    }
                    if(data.is_error == true){
                        notification("error",data.message);
                    }
                });
                return true;
            });
    });
</script>
</body>
</html>