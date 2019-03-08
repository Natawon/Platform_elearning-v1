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

$configuration = configuration();
$categories = categories($groupKey);
$groups = groups($groupKey);

if($members){
    $groups2id = groups2id($members['groups_id']);
    if($groups2id['id'] != $groups['id']){ header('Location: '.constant("_BASE_SITE_URL").'/'.$groups2id['key']); }
}

if(!$groups){ header('Location: '.constant("_PAGE_404"));}
if ($groups['status'] != 1) {
    header('Location: '.constant("_PAGE_404"));
}

$enroll = enroll($_GET['id'], $groups['key']);
$members = session_require('learning', $enroll['courses_id']);

if ($members['avatar_id'] == '') {
    $head_avatar = '<i class="fa fa-user"></i>';
} else {
    $avatar = avatars($members['avatar_id']);
    $head_avatar = "<img width='22' src='".constant("_BASE_DIR_AVATARS").$avatar["avatar_img"]."'>";
}

$courses = courses($enroll['courses_id'], $groups['key']);
$topics = $enroll['topics'];
?>
<!DOCTYPE html>
<!--[if lt IE 8 ]><html lang="en" class="noJs ie ieLegacy outdated permanent" xmlns:og="http://ogp.me/ns#" xmlns:mixi="http://mixi-platform.com/ns#"><![endif]-->
<!--[if IE 8 ]><html lang="en" class="noJs ie ie8 outdated" xmlns:og="http://ogp.me/ns#" xmlns:mixi="http://mixi-platform.com/ns#"><![endif]-->
<!--[if IE 9 ]><html lang="en" class="noJs ie ie9" xmlns:og="http://ogp.me/ns#" xmlns:mixi="http://mixi-platform.com/ns#"><![endif]-->
<!--[if !(IE)]><!--><html lang="en" class="noJs" xmlns:og="http://ogp.me/ns#" xmlns:mixi="http://mixi-platform.com/ns#"><!--<![endif]-->
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# website: http://ogp.me/ns/fb/website#">
    <!-- Meta -->
    <meta charset="utf-8">
    <title><?=$courses['title']?> - <?=$configuration['title']?></title>
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

<section id="content-info-page">
    <h2 class="text-center col-md-12 col-sm-12 title-page <?=$courses['categories']['css_class']?>"><?=$courses['code']." : ".$courses['title']?></h2>
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-sm-12 content-info-tab">
                <div class="row">
                    <ul class="col-md-12 col-sm-12">
                        <li><a href="<?=groupKey($groupKey)?>/courses/<?=$courses['id']?>/info">รายละเอียดหลักสูตร</a></li>
                        <li class="active">เข้าเรียน</li>
                        <li><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/summary">ผลการเรียน</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-12 col-sm-12 content-learning">
                <div class="col-md-3 col-sm-3 time-line">
                    <ul>
                        <?php if (count($courses['documents'])) { ?>
                            <li class="active in-active">
                                <i class="fa fa-download"></i>
                                <p><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/download">ดาวน์โหลดเอกสาร</a></p>
                            </li>
                        <?php } ?>
                        <?php if (count($courses['pre_test'])) { $pre_test_chk = true;?>
                            <li class="<?php if ($enroll['pre_test']) { echo "active"; } ?>">
                                <i class="fa fa-question"></i>
                                <p><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/pre-test">แบบทดสอบก่อนเรียน (Pre-Test)</a></p>
                            </li>
                        <?php } ?>
                        <?php foreach($topics as $rs_topics) { ?>
                            <li class="<?php if ($rs_topics['enroll2parent']) { echo "active"; } ?>">
                                <p class="<?php if (!$enroll['pre_test'] and $pre_test_chk) { echo "opacity"; }?>"><?=$rs_topics['title']?></p>
                            </li>
                            <?php if ($rs_topics['parent']) { ?>
                                <?php $i=0; foreach($rs_topics['parent'] as $rs_parent) { ?>
                                    <li id="tp-<?=$rs_parent['id']?>" class="<?php if ($rs_parent['enroll2topic']['status'] == 1) { echo "active"; } if (isset($enroll_topics) && $rs_parent['id'] == $enroll_topics['id']) { echo " in-active"; } ?>">
                                        <?php if ($rs_parent['state'] == 'live') { ?>
                                        <i class="fa fa-rss <?php if ($rs_parent['streaming_status'] == 0) { echo 'not'; } else if ($rs_parent['streaming_status'] == 1) { echo 'done'; }?>"></i>
                                        <?php } else if ($rs_parent['state'] == 'vod') { ?>
                                        <i class="fa fa-play <?php if (!$rs_parent['enroll2topic']) { echo 'not'; } else if ($rs_parent['enroll2topic']['status'] == 1) { echo 'done'; } else { echo 'semicircle'; }?>"></i>
                                        <?php } ?>
                                        <?php if ($courses['not_skip'] == 1) { ?>
                                            <?php if ($rs_parent['enroll2topic']) {?>
                                                <?php if ($enroll['pre_test']) { ?>
                                                    <p class="sub-line">
                                                        <a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/topics/<?=$rs_parent['id']?>">
                                                            <?=$rs_parent['title']?>
                                                            <?php if ($rs_parent['state'] == 'live') { ?>
                                                                <?php if ($rs_parent['streaming_status'] == 0) { ?>
                                                                <br>
                                                                <span class="f-18 text-muted">(ถ่ายทอดสดวันที่ <?=$rs_parent['live_datetime']?>)</span>
                                                                <?php } else if ($rs_parent['streaming_status'] == 1) { ?>
                                                                <span class="f-16 topic-status-live">ถ่ายทอดสด</span>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        </a>
                                                    </p>
                                                    <?php if ($rs_parent['enroll2topic']['status'] == 1) { ?>
                                                        <?php if ($rs_parent['quiz']) { ?><span class="sub-line"><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/<?=$rs_parent['quiz']['id']?>/quiz"><?=$rs_parent['quiz']['title']?></a></span><?php } ?>
                                                    <?php } else { ?>
                                                        <?php if ($rs_parent['quiz']) { ?><span class="sub-line opacity"><?=$rs_parent['quiz']['title']?></span><?php } ?>
                                                    <?php } ?>
                                                <?php } else if ($pre_test_chk) {?>
                                                    <p class="sub-line opacity">
                                                        <?=$rs_parent['title']?>
                                                        <?php if ($rs_parent['state'] == 'live') { ?>
                                                            <?php if ($rs_parent['streaming_status'] == 0) { ?>
                                                            <br>
                                                            <span class="f-18 text-muted">(ถ่ายทอดสดวันที่ <?=$rs_parent['live_datetime']?>)</span>
                                                            <?php } else if ($rs_parent['streaming_status'] == 1) { ?>
                                                            <span class="f-16 topic-status-live">ถ่ายทอดสด</span>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </p>
                                                    <?php if ($rs_parent['quiz']) { ?><span class="sub-line opacity"><?=$rs_parent['quiz']['title']?></span><?php } ?>
                                                <?php } else { ?>
                                                    <p class="sub-line">
                                                        <a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/topics/<?=$rs_parent['id']?>">
                                                            <?=$rs_parent['title']?>
                                                            <?php if ($rs_parent['state'] == 'live') { ?>
                                                                <?php if ($rs_parent['streaming_status'] == 0) { ?>
                                                                <br>
                                                                <span class="f-18 text-muted">(ถ่ายทอดสดวันที่ <?=$rs_parent['live_datetime']?>)</span>
                                                                <?php } else if ($rs_parent['streaming_status'] == 1) { ?>
                                                                <span class="f-16 topic-status-live">ถ่ายทอดสด</span>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        </a>
                                                    </p>
                                                    <?php if ($rs_parent['enroll2topic']['status'] == 1) { ?>
                                                        <?php if ($rs_parent['quiz']) { ?><span class="sub-line"><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/<?=$rs_parent['quiz']['id']?>/quiz"><?=$rs_parent['quiz']['title']?></a></span><?php } ?>
                                                    <?php } else { ?>
                                                        <?php if ($rs_parent['quiz']) { ?><span class="sub-line opacity"><?=$rs_parent['quiz']['title']?></span><?php } ?>
                                                    <?php } ?>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <p class="sub-line">
                                                    <?php if ($rs_parent['state'] == 'live') { ?>
                                                        <?php if ($rs_parent['streaming_status'] == 0) { ?>
                                                        <?=$rs_parent['title']?>
                                                        <br>
                                                        <span class="f-18 text-muted">(ถ่ายทอดสดวันที่ <?=$rs_parent['live_datetime']?>)</span>
                                                        <?php } else if ($rs_parent['streaming_status'] == 1) { ?>
                                                        <a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/topics/<?=$rs_parent['id']?>">
                                                            <?=$rs_parent['title']?> <span class="f-16 topic-status-live">ถ่ายทอดสด</span>
                                                        </a>
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                        <?=$rs_parent['title']?>
                                                    <?php } ?>
                                                </p>
                                                <?php if ($rs_parent['quiz']) { ?><span class="sub-line"><?=$rs_parent['quiz']['title']?></span><?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if ($courses['not_skip'] == 0) { ?>
                                            <?php if ($enroll['pre_test']) { ?>
                                                <p class="sub-line">
                                                    <a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/topics/<?=$rs_parent['id']?>">
                                                        <?=$rs_parent['title']?>
                                                        <?php if ($rs_parent['state'] == 'live') { ?>
                                                            <?php if ($rs_parent['streaming_status'] == 0) { ?>
                                                            <br>
                                                            <span class="f-18 text-muted">(ถ่ายทอดสดวันที่ <?=$rs_parent['live_datetime']?>)</span>
                                                            <?php } else if ($rs_parent['streaming_status'] == 1) { ?>
                                                            <span class="f-16 topic-status-live">ถ่ายทอดสด</span>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </a>
                                                </p>
                                                <?php if ($rs_parent['enroll2topic']['status'] == 1) { ?>
                                                    <?php if ($rs_parent['quiz']) { ?><span class="sub-line"><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/<?=$rs_parent['quiz']['id']?>/quiz"><?=$rs_parent['quiz']['title']?></a></span><?php } ?>
                                                <?php } else { ?>
                                                    <?php if ($rs_parent['quiz']) { ?><span class="sub-line opacity"><?=$rs_parent['quiz']['title']?></span><?php } ?>
                                                <?php } ?>
                                            <?php } else if ($pre_test_chk) {?>
                                                <p class="sub-line opacity">
                                                    <?=$rs_parent['title']?>
                                                    <?php if ($rs_parent['state'] == 'live') { ?>
                                                        <?php if ($rs_parent['streaming_status'] == 0) { ?>
                                                        <br>
                                                        <span class="f-18 text-muted">(ถ่ายทอดสดวันที่ <?=$rs_parent['live_datetime']?>)</span>
                                                        <?php } else if ($rs_parent['streaming_status'] == 1) { ?>
                                                        <span class="f-16 topic-status-live">ถ่ายทอดสด</span>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </p>
                                                <?php if ($rs_parent['quiz']) { ?><span class="sub-line opacity"><?=$rs_parent['quiz']['title']?></span><?php } ?>
                                            <?php } else { ?>
                                                <p class="sub-line">
                                                    <a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/topics/<?=$rs_parent['id']?>">
                                                        <?=$rs_parent['title']?>
                                                        <?php if ($rs_parent['state'] == 'live') { ?>
                                                            <?php if ($rs_parent['streaming_status'] == 0) { ?>
                                                            <br>
                                                            <span class="f-18 text-muted">(ถ่ายทอดสดวันที่ <?=$rs_parent['live_datetime']?>)</span>
                                                            <?php } else if ($rs_parent['streaming_status'] == 1) { ?>
                                                            <span class="f-16 topic-status-live">ถ่ายทอดสด</span>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </a>
                                                </p>
                                                <?php if ($rs_parent['enroll2topic']['status'] == 1) { ?>
                                                    <?php if ($rs_parent['quiz']) { ?><span class="sub-line"><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/<?=$rs_parent['quiz']['id']?>/quiz"><?=$rs_parent['quiz']['title']?></a></span><?php } ?>
                                                <?php } else { ?>
                                                    <?php if ($rs_parent['quiz']) { ?><span class="sub-line opacity"><?=$rs_parent['quiz']['title']?></span><?php } ?>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                    </li>
                                    <?php $i++; } ?>
                            <?php } ?>
                        <?php } ?>
                        <?php if (count($courses['post_test'])) { $post_test_chk = true; ?>
                            <li class="<?php if ($enroll['post_test']) { echo "active"; } ?> <?php if ($_GET['type'] == 'post-test') { echo " in-active"; } ?>">
                                <i class="fa fa-question"></i>
                                <?php if ($enroll['courses']['learning']) {?>
                                    <p><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/post-test">แบบทดสอบหลังเรียน (Post-Test)</a></p>
                                <?php } else { ?>
                                    <p class="opacity">แบบทดสอบหลังเรียน (Post-Test)</p>
                                <?php } ?>
                            </li>
                        <?php } ?>
                        <?php if (count($courses['exam'])) { ?>
                            <li class="<?php if ($enroll['exam']) { echo "active"; } ?> <?php if ($_GET['type'] == 'exam') { echo " in-active"; } ?>">
                                <i class="fa fa-pencil"></i>
                                <?php if ($enroll['post_chk']['learning']) {?>
                                    <p><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/exam">แบบทดสอบเพื่อวัดความรู้ (Examination)</a></p>
                                <?php } else if ($post_test_chk) { ?>
                                    <p class="opacity">แบบทดสอบเพื่อวัดความรู้ (Examination)</p>
                                <?php } else { ?>
                                    <p><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/exam">แบบทดสอบเพื่อวัดความรู้ (Examination)</a></p>
                                <?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="col-md-9 col-sm-9 learning">
                    <h3 class="text-center">ยินดีต้อนรับ คุณ <span><?=$members['first_name']." ".$members['last_name']?></span> เข้าสู่การเรียน e-Learning หลักสูตร <span><?=$courses['title']?></span></h3>
                    <div class="col-md-12 col-sm-12">
                        <h4>ดาวน์โหลดเอกสารประกอบการเรียนทั้งหมดของหลักสูตรนี้</h4>
                        <?php foreach($courses['documents'] as $rs_documents){ ?>
                        <div class="col-md-12 col-sm-12 download panel">
                            <div class="col-md-2 col-xs-4 col-xs-offset-4 col-sm-2 col-sm-offset-0"><img src="/img/<?=$rs_documents['type']?>.svg" class="img-responsive"></div>
                            <div class="col-md-8 col-sm-6 col-xs-12">
                                <h5><?=$rs_documents['title']?></h5>
                                <?php if($rs_documents['size']){?><p><?=$rs_documents['size']?></p><?php } ?>
                            </div>
                            <div class="col-md-2 col-sm-4 col-sm-offset-0 col-xs-offset-3 btn">
                                <?php if($rs_documents['link']){ ?>
                                    <a href="<?=$rs_documents['link']?>" target="_blank" class="text-center">
                                        <div class="col-md-12 col-sm-12"><i class="fa fa-external-link"></i></div>
                                        <div class="col-md-12 col-sm-12">ลิ้งค์</div>
                                    </a>
                                <?php }else{ ?>
                                    <a href="<?=constant("_BASE_DIR_FILE").$rs_documents['file']?>" target="_blank" class="text-center">
                                        <div class="col-md-12 col-sm-12"><i class="fa fa-download"></i></div>
                                        <div class="col-md-12 col-sm-12">ดาวน์โหลด</div>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if(count($courses['pre_test'])){ ?>
                            <a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/pre-test" class="btn-style1 pull-right"><i class="fa fa-arrow-right"></i> ถัดไป</a>
                        <?php }else{ ?>
                            <a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/course" class="btn-style1 pull-right"><i class="fa fa-arrow-right"></i> ถัดไป</a>
                        <?php } ?>
                    </div>
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