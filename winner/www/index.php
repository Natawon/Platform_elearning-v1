<?php
$base = "service/";
include($base."service.php");
if (isset($_GET['group_key'])) {
  $groupKey = cleanGroupKey($_GET['group_key']);
} else {
  $groupKey = "G-Education";
}

// Active Menu
$activeMenu = "home";

$members = session();
$configuration = configuration();
$categories = categories($groupKey);
$highlights = highlights($groupKey);
$groups = groups($groupKey);

if($members){
    $groups2id = groups2id($members['groups_id']);
    if($groups2id['id'] != $groups['id']){ header('Location: '.constant("_BASE_SITE_URL").'/'.$groups2id['key']); }
}

if(!$groups){ header('Location: '.constant("_PAGE_404"));}
if ($groups['status'] != 1) {
    header('Location: '.constant("_PAGE_404"));
}

$filter_course = filter_course($groups['key'], ['per_page' => 4]);
$courses_list = courses_list($groups['key'], [
    'ignore_state' => 'live'
]);
$live_courses_list = live_courses_list($groups['key']);

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

    <!-- CSS Style -->
    <link href="/css/style.css" rel="stylesheet">
    <link href="/css/custom.css" rel="stylesheet">

  </head>

  <body>
  <?php include 'include/inc.header.php'; ?>
<!-- End header -->

<section id="landing">
  <div class="container">
    <?php if (count($highlights) > 0) { ?>
        <?php if (count($highlights) == 1) { ?>
            <?php foreach ($highlights as $rs_highlights) { ?>
                <?php if ($rs_highlights['url'] != "#") { ?>
                    <a href="<?=$rs_highlights['url']?>" target="_blank"><img src="<?=constant("_BASE_DIR_HIGHLIGHTS").$rs_highlights['picture']?>" class="img-responsive"></a>
                <?php } else { ?>
                    <img src="<?=constant("_BASE_DIR_HIGHLIGHTS").$rs_highlights['picture']?>" class="img-responsive">
                <?php } ?>
            <?php } ?>
        <?php } else { ?>
            <div id="slides">
                <?php foreach ($highlights as $rs_highlights) { ?>
                    <div class="slide-item">
                        <?php if ($rs_highlights['url'] != "#") { ?>
                            <a href="<?=$rs_highlights['url']?>" target="_blank"><img src="<?=constant("_BASE_DIR_HIGHLIGHTS").$rs_highlights['picture']?>" class="img-responsive"></a>
                        <?php } else { ?>
                            <img src="<?=constant("_BASE_DIR_HIGHLIGHTS").$rs_highlights['picture']?>" class="img-responsive">
                        <?php } ?>
                    </div>
                <?php } ?>
                <a href="#" class="slidesjs-previous slidesjs-navigation"><img src="/images/arrow-left@2x.png" class="img-responsive"></a>
                <a href="#" class="slidesjs-next slidesjs-navigation"><img src="/images/arrow-right@2x.png" class="img-responsive"></a>
            </div>
        <?php } ?>
    <?php } ?>
  </div>
</section><!-- End slider -->

<?php
if (!empty($groups['questionnaire_packs']) && !empty($groups['questionnaire_packs']['questionnaires'])) {
    $questionnairesReady = true;
} else {
    $questionnairesReady = false;
}
?>

<section id="content-info">
<?php if (($configuration['description_status'] == 1 && !empty($configuration['description'])) || $questionnairesReady) { ?>
    <div class="container">
        <div class="row">
            <div class="<?=$questionnairesReady ? 'col-md-8' : 'col-md-12'?>">
                <?php if ($configuration['description_status'] == 1) {
                    echo $configuration['description'];
                } else {
                    ?>
                    <div class="wrapper-info">
                        <div class="row">
                            <div class="col-md-12">
                                <h2>บทเรียนออนไลน์ e-Learning อิสระแห่งการเรียนรู้ ทุกที่ ทุกเวลา</h2>
                                <hr>
                                <p>ยังไม่มีข่าวประชาสัมพันธ์...</p>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php if ($questionnairesReady) { ?>
                <div class="col-md-4">
                    <div class="wrapper-filter-courses text-center text-xs-center text-sm-center text-md-right">
                        <a id="btn-start-filter-courses" class="cursor-pointer" <?php echo $members ? 'data-login="true"' : ''; ?>>
                            <img src="/images/img-search-course.jpg" alt="" class="img-thumbnail">
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>
</section>

<section id="content-list">
    <div class="container">
        <?php if ($filter_course['courses']['data']) { ?>
            <div class="row">
                <div class="col-md-12 text-center">
                    <h1 class="text-center">หลักสูตรที่เหมาะสม</h1>
                </div>

                <?php foreach($filter_course['courses']['data'] as $filter_courses_list){ ?>
                    <div class="col-lg-3 col-md-3 col-sm-4">
                        <div class="col-item <?php echo $filter_courses_list['categories']['css_class'] ? $filter_courses_list['categories']['css_class'] : "t0"; ?>">
                            <div class="photo">
                                <?php if($filter_courses_list['latest']){ ?><div class="ribbon"><img src="/images/ribbin-new@2x.png" class="img-responsive"></div><?php } ?>
                                <a href="<?=groupKey($groupKey)?>/courses/<?=$filter_courses_list['id']?>/info"><img src="<?=getImage(constant("_BASE_DIR_COURSES_THUMBNAIL"),$filter_courses_list['thumbnail'])?>"></a>
                            </div>
                            <div class="info">
                                <div class="row">
                                    <div class="course_info col-md-12 col-xs-12">
                                        <h4><a href="<?=groupKey($groupKey)?>/courses/<?=$filter_courses_list['id']?>/info"><?=$filter_courses_list['code']?></a></h4>
                                        <h4><a href="<?=groupKey($groupKey)?>/courses/<?=$filter_courses_list['id']?>/info"><?=$filter_courses_list['title']?></a></h4>
                                        <!--Public-->
                                        <span><?=$filter_courses_list['subject']?></span>
                                        <?php if($filter_courses_list['free']) {?><p class="free">ฟรี</p><?php }else{ ?><p><?=number_format($filter_courses_list['price'])." บาท"?></p><?php } ?>
                                    </div>
                                </div>
                                <div class="separator clearfix">
                                    <span class="col-md-10 col-xs-10"><?=$filter_courses_list['categories']['title']?></span>
                                    <p class="btn-add"><?=$rs_live_courses_list['categories']['title']?></p>
                                <p class="btn-details"> <a href="<?=groupKey($groupKey)?>/courses/<?=$rs_live_courses_list['id']?>/info" class="btn btn-style1"> เข้าเรียน</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

            </div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <a href="<?=groupKey($groupKey)?>/my-profile/filter-courses" class="btn btn-style1">ดูหลักสูตรที่เหมาะสมทั้งหมด</a>
                    <div class="divider-dashed m-t-30"></div>
                </div>
            </div>
        <?php } ?>

        <div class="row">
        <?php if (count($live_courses_list['data_live']) > 0 || count($live_courses_list['data']) > 0) { ?>
            <div class="col-md-12 text-center">
                <h1 class="text-center">หลักสูตรถ่ายทอดสด</h1>
            </div>
        <?php } ?>

        <?php if (count($live_courses_list['data_live']) > 0) { ?>
            <?php foreach($live_courses_list['data_live'] as $rs_live_courses_list){ ?>
                <div class="col-lg-3 col-md-3 col-sm-4">
                    <div class="col-item <?php echo $rs_live_courses_list['categories']['css_class'] ? $rs_live_courses_list['categories']['css_class'] : "t0"; ?>">
                        <div class="photo">
                            <a href="<?=groupKey($groupKey)?>/courses/<?=$rs_live_courses_list['id']?>/info">
                                <img src="<?=getImage(constant("_BASE_DIR_COURSES_THUMBNAIL"),$rs_live_courses_list['thumbnail'])?>">
                            </a>
                            <div class="state-status state-status-<?=($rs_live_courses_list['topics']['state'] == 'live' && $rs_live_courses_list['topics']['streaming_status'] == 1) ? 'danger' : 'info'?>">
                                <?=($rs_live_courses_list['topics']['state'] == 'live' && $rs_live_courses_list['topics']['streaming_status'] == 1) ? 'ถ่ายทอดสด' : 'กำลังจะมาถึง'?>
                            </div>
                        </div>
                        <div class="info">
                            <div class="row">
                                <div class="course_info col-md-12 col-xs-12">
                                    <h4><a href="<?=groupKey($groupKey)?>/courses/<?=$rs_live_courses_list['id']?>/info"><?=$rs_live_courses_list['code']?></a></h4>
                                    <h4><a href="<?=groupKey($groupKey)?>/courses/<?=$rs_live_courses_list['id']?>/info"><?=$rs_live_courses_list['title']?></a></h4>
                                    <!--Public-->
                                    <span><?=$rs_live_courses_list['subject']?></span>
                                    <div class="clearfix"></div>
                                    <hr class="m-t-10 m-b-10">
                                    <div class="descriotion-live <?=($rs_live_courses_list['topics']['streaming_status'] == 1) ? 'text-live' : ''?>">
                                        <?='กำลังถ่ายทอดสดหัวข้อ - '.$rs_live_courses_list['topics']['title']?>
                                    </div>
                                    <?php if($rs_live_courses_list['free']) {?><p class="free">ฟรี</p><?php }else{ ?><p><?=number_format($rs_live_courses_list['price'])." บาท"?></p><?php } ?>
                                </div>
                            </div>
                            <div class="separator clearfix">
                                <p class="btn-add"><?=$rs_live_courses_list['categories']['title']?></p>
                                <p class="btn-details"> <a href="<?=groupKey($groupKey)?>/courses/<?=$rs_live_courses_list['id']?>/info" class="btn btn-style1"> เข้าเรียน</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>

        <?php if (count($live_courses_list['data']) > 0) { ?>
            <?php foreach($live_courses_list['data'] as $rs_live_courses_list){ ?>
                <?php if ($rs_live_courses_list['topics'] != null) { ?>
                <div class="col-lg-3 col-md-3 col-sm-4">
                    <div class="col-item <?php echo $rs_live_courses_list['categories']['css_class'] ? $rs_live_courses_list['categories']['css_class'] : "t0"; ?>">
                        <div class="photo">
                            <a href="<?=groupKey($groupKey)?>/courses/<?=$rs_live_courses_list['id']?>/info">
                                <img src="<?=getImage(constant("_BASE_DIR_COURSES_THUMBNAIL"),$rs_live_courses_list['thumbnail'])?>">
                            </a>
                            <div class="state-status state-status-<?=($rs_live_courses_list['topics']['state'] == 'live' && $rs_live_courses_list['topics']['streaming_status'] == 1) ? 'danger' : 'info'?>">
                                <?=($rs_live_courses_list['topics']['state'] == 'live' && $rs_live_courses_list['topics']['streaming_status'] == 1) ? 'ถ่ายทอดสด' : 'กำลังจะมาถึง'?>
                            </div>
                        </div>
                        <div class="info">
                            <div class="row">
                                <div class="course_info col-md-12 col-xs-12">
                                    <h4><a href="<?=groupKey($groupKey)?>/courses/<?=$rs_live_courses_list['id']?>/info"><?=$rs_live_courses_list['code']?></a></h4>
                                    <h4><a href="<?=groupKey($groupKey)?>/courses/<?=$rs_live_courses_list['id']?>/info"><?=$rs_live_courses_list['title']?></a></h4>
                                    <!--Public-->
                                    <span><?=$rs_live_courses_list['subject']?></span>
                                    <div class="clearfix"></div>
                                    <hr class="m-t-10 m-b-10">
                                    <div class="descriotion-live <?=($rs_live_courses_list['topics']['streaming_status'] == 1) ? 'text-live' : ''?>">
                                        <?='ถ่ายทอดสดวันที่ '.$rs_live_courses_list['topics']['live_datetime']?>
                                    </div>
                                    <?php if($rs_live_courses_list['free']) {?><p class="free">ฟรี</p><?php }else{ ?><p><?=number_format($rs_live_courses_list['price'])." บาท"?></p><?php } ?>
                                </div>
                            </div>
                            <div class="separator clearfix">
                                <p class="btn-add"><?=$rs_live_courses_list['categories']['title']?></p>
                                <p class="btn-details"> <a href="<?=groupKey($groupKey)?>/courses/<?=$rs_live_courses_list['id']?>/info" class="btn btn-style1"> เข้าเรียน</a></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            <?php } ?>
        <?php } ?>
        </div>
        
        <?php
        $count_all = count($live_courses_list['data_live']) + count($live_courses_list['data']);
        if ($count_all == 4) {
        ?>
        <div class="row">
            <div class="col-md-12 text-center">
                <a href="<?=groupKey($groupKey)?>/list" class="btn btn-style1">ดูหลักสูตรถ่ายทอดสดทั้งหมด</a>
            </div>
        </div>
        <?php } ?>

        <div class="row">
            <div class="col-md-12 text-center">
                <h1 class="text-center">หลักสูตรทั้งหมด</h1>
            </div>

            <?php foreach($courses_list['data'] as $rs_courses_list){ ?>
                <div class="col-lg-3 col-md-3 col-sm-4">
                    <div class="col-item col-item-vod <?php echo $rs_courses_list['categories']['css_class'] ? $rs_courses_list['categories']['css_class'] : "t0"; ?>">
                        <div class="photo">
                            <?php if($rs_courses_list['latest']){ ?><div class="ribbon"><img src="/images/ribbin-new@2x.png" class="img-responsive"></div><?php } ?>
                            <a href="<?=groupKey($groupKey)?>/courses/<?=$rs_courses_list['id']?>/info"><img src="<?=getImage(constant("_BASE_DIR_COURSES_THUMBNAIL"),$rs_courses_list['thumbnail'])?>"></a>
                        </div>
                        <div class="info">
                            <div class="row">
                                <div class="course_info col-md-12 col-xs-12">
                                    <h4><a href="<?=groupKey($groupKey)?>/courses/<?=$rs_courses_list['id']?>/info"><?=$rs_courses_list['code']?></a></h4>
                                    <h4><a href="<?=groupKey($groupKey)?>/courses/<?=$rs_courses_list['id']?>/info"><?=$rs_courses_list['title']?></a></h4>
                                    <!--Public-->
                                    <span><?=$rs_courses_list['subject']?></span>
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
                <a href="<?=groupKey($groupKey)?>/list" class="btn btn-style1">ดูหลักสูตรทั้งหมด</a>
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
  <script src="/bower_components/noty/lib/noty.min.js"></script>
  <script src="/js/script/config.js"></script>
  <script src="/js/script/functions.js"></script>
  <script src="/js/script/model/members.js"></script>
  <script src="/js/script/model/filter-courses.js"></script>
  <script src="/js/script/pages/index.js"></script>
  <script type="text/javascript">
    $(function() {
        $('#slides').slidesjs({
            width: 1200,
            height: 360,
            navigation: {
                active: false,
                effect: "slide"
            },
            play: {
                active: false,
                effect: "slide",
                interval: 5000,
                auto: true,
                swap: true,
                pauseOnHover: true,
                restartDelay: 2500
            },
        });
    });
  </script>
  <script src="/js/main.js"></script>
  </body>
</html>