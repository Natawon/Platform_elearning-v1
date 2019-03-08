<?php
$base = "service/";
include($base."service.php");
if (isset($_GET['group_key'])) {
  $groupKey = cleanGroupKey($_GET['group_key']);
} else {
  $groupKey = "G-Education";
}

$keyword = $_GET['keyword'];

// Active Menu
// $activeMenu = "courses";
temp_session_require();
$members = session();
$configuration = configuration();
$categories = categories($groupKey);
// $highlights = highlights($groupKey);
$groups = groups($groupKey);

if($members){
    $groups2id = groups2id($members['groups_id']);
    if($groups2id['id'] != $groups['id']){ header('Location: '.constant("_BASE_SITE_URL").'/'.$groups2id['key']); }
}

if(!$groups){ header('Location: '.constant("_PAGE_404"));}
if ($groups['status'] != 1) {
    header('Location: '.constant("_PAGE_404"));
}

$courses_list = search_courses($groups['key'], $keyword);

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

    <!-- CSS Style -->
    <link href="/css/style.css" rel="stylesheet">
    <link href="/css/custom.css" rel="stylesheet">

  </head>

  <body>
  <?php include 'include/inc.header.php'; ?>
<!-- End header -->

<section id="content-list" style="min-height: 470px;">
    <div class="container">
        <div class="row">

            <div class="col-sm-12">
                <div class="vspace-20"></div>
            </div>

            <div class="col-sm-12">
            	<h3><i class="fa fa-info-circle" aria-hidden="true"></i>&nbsp; บัญชีผู้ใช้งานดังกล่าวมีการใช้งานอยู่ในขณะนี้</h3>
            </div>

            <div class="col-sm-12">
                <ul>
                    <li class="m-b-5"><a class="btn-manage-user cursor-pointer" data-action="destroy" data-url="<?=$_GET['redirectPage']?>"><i class="fa fa-sign-in f-16" aria-hidden="true"></i>&nbsp; ตัดการใช้งานปัจจุบันและเข้าสู่ระบบ</a></li>
                    <li class="m-b-5"><a class="btn-manage-user cursor-pointer" data-action="back"><i class="fa fa-home f-16" aria-hidden="true"></i>&nbsp; กลับสู่หน้าหลัก</a></li>
                </ul>
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
  <script src="/bower_components/slidesjs/source/jquery.slides.js"></script>
  <script src="/js/script/config.js"></script>
  <script src="/js/script/functions.js"></script>
  <script src="/js/script/model/members.js"></script>
  <script src="/js/script/pages/session-exists.js"></script>
  <script type="text/javascript">
    $(function() {
        $('#slides').slidesjs({
           width: 1200,
           height: 360,
           navigation: false
        });
    });
  </script>
  <script src="/js/main.js"></script>
  </body>
</html>