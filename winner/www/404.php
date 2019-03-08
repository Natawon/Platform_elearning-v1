<?php
$base = "service/";
include($base."service.php");

$configuration = configuration();
$groups404 = groups404();
?>
<!DOCTYPE html>
<!--[if lt IE 8 ]><html lang="en" class="noJs ie ieLegacy outdated permanent" xmlns:og="http://ogp.me/ns#" xmlns:mixi="http://mixi-platform.com/ns#"><![endif]-->
<!--[if IE 8 ]><html lang="en" class="noJs ie ie8 outdated" xmlns:og="http://ogp.me/ns#" xmlns:mixi="http://mixi-platform.com/ns#"><![endif]-->
<!--[if IE 9 ]><html lang="en" class="noJs ie ie9" xmlns:og="http://ogp.me/ns#" xmlns:mixi="http://mixi-platform.com/ns#"><![endif]-->
<!--[if !(IE)]><!--><html lang="en" class="noJs" xmlns:og="http://ogp.me/ns#" xmlns:mixi="http://mixi-platform.com/ns#"><!--<![endif]-->
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# website: http://ogp.me/ns/fb/website#">
    <!-- Meta -->
    <meta charset="utf-8">
    <title>เกิดข้อผิดพลาด "ไม่พบหน้าที่ต้องการ" - <?=$configuration['title']?></title>
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

    <!-- CSS Style -->
    <link href="/css/style.css" rel="stylesheet">
    <link href="/css/custom.css" rel="stylesheet">

</head>

<body>
<header class="">
    <div class="container">
        <div class="row">
            <div class="col-xxs-6 col-xxs-offset-3 col-xs-4 col-xs-offset-4 col-sm-3 col-sm-offset-0 col-md-2 col-md-offset-0 ">
                <div class="text-center visible-xs visible-sm">
                    <a href="/">
                        <img src="<?=constant("_BASE_DIR_LOGO").$configuration['logo']?>" id="logo" class="img-responsive hidden-webview">
                    </a>
                </div>
                <div class="visible-md visible-lg">
                    <a href="/">
                        <img src="<?=constant("_BASE_DIR_LOGO").$configuration['logo']?>" id="logo" class="img-responsive hidden-webview">
                    </a>
                </div>
            </div>
        </div>
    </div>
</header><!-- End header -->

<section id="session404">
    <div class="container">
        <div class='row'>
            <div class='col-md-12'>
               <h1 class="h1404">เกิดข้อผิดพลาด "ไม่พบหน้าที่ต้องการ"</h1>
               <h3>กรุณาตรวจสอบ <strong>URL</strong> หรือ <strong>คลิกที่รูป</strong> เพื่อเข้าสู่กลุ่มที่ท่านต้องการ</h3>
                <div class="row">
                    <?php foreach($groups404 as $rs_groups404){ ?>
                    <div class="col-lg-3 col-md-3 col-sm-4">
                        <div class="col-item">
                            <div class="photo">
                                <a href="<?=groupKey($rs_groups404['key'])?>"><img src="<?=constant("_BASE_DIR_GROUPS_THUMBNAIL").$rs_groups404['thumbnail']?>"></a>
                            </div>
                            <div class="info" style="padding-bottom: 10px;">
                                <div class="row">
                                    <div class="course_info col-md-12 col-xs-12">
                                        <h4><a href="<?=groupKey($rs_groups404['key'])?>"><?=$rs_groups404['title']?></a></h4>
                                        <h4><a href="<?=groupKey($rs_groups404['key'])?>"><?=$rs_groups404['subject']?></a></h4>
                                        <small><a href="<?=constant("_BASE_SITE_URL").groupKey($rs_groups404['key'])?>" class="url404"><?=constant("_BASE_SITE_URL").groupKey($rs_groups404['key'])?></a></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>


<?php include 'include/inc.footer.php'; ?>

<!-- Javascript Library -->
<script src="/bower_components/jquery/dist/jquery.min.js"></script>
<script src="/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="/bower_components/html5shiv/dist/html5shiv.min.js"></script>
<script src="/bower_components/respond/dest/respond.min.js"></script>
<script src="/bower_components/superfish/dist/js/superfish.min.js"></script>
<script src="/js/script/config.js"></script>
<script src="/js/script/functions.js"></script>
<script src="/js/script/model/members.js"></script>
<script src="/js/main.js"></script>

</body>
</html>