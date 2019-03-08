<?php
$base = "service/";
include($base."service.php");
if (isset($_GET['group_key'])) {
  $groupKey = cleanGroupKey($_GET['group_key']);
} else {
  $groupKey = "G-Education";
}

// Active Menu
$activeMenu = "courses";

$members = session();
$configuration = configuration();
$categories = categories($groupKey);
$groups = groups($groupKey);
$courses_list = courses_list($groups['key'], [
    'page' => $_GET['page']
]);

function desc_sort_json($array1, $array2){
    $on = 'streaming_status';

    if ($array1['topics'][$on] == $array2['topics'][$on]) {
        return 0;
    }
    return ($array1['topics'][$on] < $array2['topics'][$on]) ? 1 : -1;
}

usort($courses_list['data'], "desc_sort_json");

if($members){
    $groups2id = groups2id($members['groups_id']);
    if($groups2id['id'] != $groups['id']){ header('Location: '.constant("_BASE_SITE_URL").'/'.$groups2id['key']); }
}

if(!$groups){ header('Location: '.constant("_PAGE_404"));}
if ($groups['status'] != 1) {
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
    <title>หลักสูตร - <?=$configuration['title']?></title>
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

<section id="content-list-page">
    <div class="row">
        <h2 class="text-center col-md-12 col-xs-12">หลักสูตรทั้งหมด</h2>
    </div>
<div class="container">
    <div class="row">

        <?php foreach($courses_list['data'] as $rs_courses_list){ ?>
            <div class="col-lg-3 col-md-3 col-sm-4">
                <div class="col-item <?=(!$rs_courses_list['topics']) ? 'col-item-vod' : ''?> <?php echo $rs_courses_list['categories']['css_class'] ? $rs_courses_list['categories']['css_class'] : "t0"; ?>">
                    <div class="photo">
                        <?php if ($rs_courses_list['topics'] == null) { ?>
                        <?php if($rs_courses_list['latest']){ ?><div class="ribbon"><img src="/images/ribbin-new@2x.png" class="img-responsive"></div><?php } ?>
                        <?php } ?>
                        <a href="<?=groupKey($groupKey)?>/courses/<?=$rs_courses_list['id']?>/info"><img src="<?=getImage(constant("_BASE_DIR_COURSES_THUMBNAIL"),$rs_courses_list['thumbnail'])?>"></a>
                        
                        <?php if ($rs_courses_list['topics']) { ?>
                        <div class="state-status state-status-<?=($rs_courses_list['topics']['state'] == 'live' && $rs_courses_list['topics']['streaming_status'] == 1) ? 'danger' : 'info'?>">
                            <?=($rs_courses_list['topics']['streaming_status'] == 1) ? 'ถ่ายทอดสด' : 'กำลังจะมาถึง'?>
                        </div>
                        <?php } ?>

                    </div>
                    <div class="info">
                        <div class="row">
                            <div class="course_info col-md-12 col-xs-12">
                                <h4><a href="<?=groupKey($groupKey)?>/courses/<?=$rs_courses_list['id']?>/info"><?=$rs_courses_list['code']?></a></h4>
                                <h4><a href="<?=groupKey($groupKey)?>/courses/<?=$rs_courses_list['id']?>/info"><?=$rs_courses_list['title']?></a></h4>
                                <!--Public-->
                                <span><?=$rs_courses_list['subject']?></span>

                                <?php if ($rs_courses_list['topics']) { ?>
                                <div class="clearfix"></div>
                                <hr class="m-t-10 m-b-10">
                                <div class="descriotion-live <?=($rs_courses_list['topics']['streaming_status'] == 1) ? 'text-live' : ''?>">
                                    <?=($rs_courses_list['topics']['streaming_status'] == 1) ? 'กำลังถ่ายทอดสดหัวข้อ - '.$rs_courses_list['topics']['title'] : 'ถ่ายทอดสดวันที่ '.$rs_courses_list['topics']['live_datetime']?>
                                </div>
                                <?php } ?>

                                <?php if($rs_courses_list['free']) {?><p class="free">ฟรี</p><?php }else{ ?><p><?=number_format($rs_courses_list['price'])." บาท"?></p><?php } ?>
                            </div>
                        </div>
                        <div class="separator clearfix">
                            <p class="btn-add"><?=$rs_courses_list['categories']['title']?></p>
                            <p class="btn-details"> <a href="<?=groupKey($groupKey)?>/courses/<?=$rs_courses_list['id']?>/info" class="btn btn-style1"> เข้าเรียน</a></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

    </div>

    <div class="row">
        <div class="col-md-12 text-center">
            <?php
            if (!empty($courses_list['data'])) {
                echo $oFunc->createPagination($oFunc->getCurrentURL(), $courses_list['total'], $courses_list['per_page'], 'pagination', '', '<span aria-hidden="true">&laquo;</span> ย้อนกลับ', 'ถัดไป <span aria-hidden="true">&raquo;</span>');
            }
            ?>
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