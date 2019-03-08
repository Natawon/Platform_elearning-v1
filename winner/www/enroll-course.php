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


$enroll = enroll($_GET['id']);
$members = session_require('learning', $enroll['courses_id']);

if ($members) {
    $groups2id = groups2id($members['groups_id']);
    if ($groups2id['id'] != $groups['id']) { header('Location: '.constant("_BASE_SITE_URL").'/'.$groups2id['key']); }
}

if (!$groups) { header('Location: '.constant("_PAGE_404"));}
if ($groups['status'] != 1) {
    header('Location: '.constant("_PAGE_404"));
}

if ($members['avatar_id'] == '') {
    $head_avatar = '<i class="fa fa-user"></i>';
} else {
    $avatar = avatars($members['avatar_id']);
    $head_avatar = "<img width='22' src='".constant("_BASE_DIR_AVATARS").$avatar["avatar_img"]."'>";
}

$courses = courses($enroll['courses_id'], $groups['key']);
$topics = $enroll['topics'];

if (count($courses['pre_test']) > 0 && $enroll['pre_chk']['learning'] !== true) {
    header('Location: '.constant("_BASE_SITE_URL").'/'.$groups2id['key'].'/enroll/'.$enroll['id'].'/exam/pre-test');
    exit();
}

if ($_GET['topics_id']) {

        $enroll2topic = enroll2topic_skip($enroll['id'], $_GET['topics_id']);
        // print_r($enroll2topic['log']);
        // exit;
        $enroll2topicFiltered = array_filter($enroll2topic['log'], function($log) {
            return $log['topics_id'] == $_GET['topics_id'];
        });

        if (count($enroll2topicFiltered) == 0) {
            header('Location: '.constant("_BASE_SITE_URL").'/'.$groups2id['key'].'/courses/'.$enroll['courses_id'].'/info');
            exit();
        }
} else {
    if ($courses['topics']['live_status'] == 1) {
        header('Location: '.constant("_BASE_SITE_URL").'/'.$groups2id['key'].'/enroll/'.$_GET['id'].'/topics/'.$courses['topics']['id']);
    } else {
        $enroll2topic = enroll2topic($enroll['id']);
    }
}

$enroll_topics = $enroll2topic['topics'];

if ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 0) {
    $diff = diff($enroll_topics['live_start_datetime']);
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
    <title><?=$enroll_topics['title']?> - <?=$configuration['title']?></title>
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
    <link href="/bower_components/OwlCarousel2/dist/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="/bower_components/OwlCarousel2/dist/assets/owl.theme.default.min.css" rel="stylesheet">
    <link href="/bower_components/noty/lib/noty.css" rel="stylesheet">
    <link href="/bower_components/jquery-confirm/dist/jquery-confirm.min.css" rel="stylesheet">

    <!-- CSS Style -->
    <link href="/css/style.css" rel="stylesheet">
    <link href="/css/custom.css" rel="stylesheet">

    <style type="text/css">
    .panel-shadow {
    box-shadow: rgba(0, 0, 0, 0.23) 7px 7px 7px;
    }
    .panel-light-white {
    border: 1px solid #a7a7a7;
    background: #fdfdfd;
    }
    .panel-light-white .panel-heading {
    color: #333;
    background-color: #fff;
    border-color: #ddd;
    }
    .panel-light-white .panel-footer {
    background-color: #fff;
    border-color: #ddd;
    }

    .panel-light-grey {
    /*border: 1px solid #adadad;*/
    border: 1px solid #ccc;
    background-color: #f7f7f7;
    }

    .post-box{ margin-top: 20px; }

    .modal-body .post { margin-bottom: 25px; }

    .post .post-heading {
    height: 84px;
    padding: 20px 15px 0 15px;
    }
    .post .post-heading .avatar {
    width: 60px;
    height: 60px;
    display: block;
    margin-right: 15px;
    }
    .post .post-heading .meta .title {
    margin-bottom: 0;
    font-size: 18px;
    }
    .post .post-heading .meta .title a {
    color: black;
    }
    .post .post-heading .meta .title a:hover {
    color: #aaaaaa;
    }
    .post .post-heading .meta .time {
    margin-top: 8px;
    color: #999;
    font-size: 16px;
    }
    .post .post-image .image {
    width: 100%;
    height: auto;
    }
    .post .post-description {
    padding: 15px;
    }
    .post .post-description p, .post-reply .post-description p {
    font-size: 18px;
    }
    .post .post-description .stats {
    margin-top: 20px;
    }
    .post .post-description .stats .stat-item {
    display: inline-block;
    margin-right: 15px;
    }
    .post .post-description .stats .stat-item .icon {
    margin-right: 8px;
    }
    .post .post-footer, .post-reply .post-footer {
    /*border-top: 1px solid #ddd;*/
    padding: 0px 15px 15px;
    }
    .post .post-footer .input-group-addon a {
    color: #454545;
    }
    .post .post-footer .comments-list {
    padding: 0;
    margin-top: 20px;
    list-style-type: none;
    }
    .post .post-footer .comments-list .comment {
    display: block;
    width: 100%;
    margin: 20px 0;
    }
    .post .post-footer .comments-list .comment .avatar {
    width: 35px;
    height: 35px;
    }
    .post .post-footer .comments-list .comment .comment-heading {
    display: block;
    width: 100%;
    }
    .post .post-footer .comments-list .comment .comment-heading .user {
    font-size: 14px;
    font-weight: bold;
    display: inline;
    margin-top: 0;
    margin-right: 10px;
    }
    .post .post-footer .comments-list .comment .comment-heading .time {
    font-size: 12px;
    color: #aaa;
    margin-top: 0;
    display: inline;
    }
    .post .post-footer .comments-list .comment .comment-body {
    margin-left: 50px;
    }
    .post .post-footer .comments-list .comment > .comments-list {
    margin-left: 50px;
    }

    #discussionModal .modal-footer{
        text-align: left;
    }

    .post-reply { padding-left: 15px; padding-right: 15px; }

    .post-reply:first-child { margin-top: 5px; }

    .post-reply .post-heading{ height: 64px; padding: 0 15px 0 15px; }

    .post-reply .post-description { padding: 15px; }

    #discussionModal .modal-body {

    }
    </style>

</head>

<body>
<?php include 'include/inc.header.php'; ?>
<!-- End header -->

<section id="content-info-page">
    <h2 class="text-center col-md-12 title-page <?=$courses['categories']['css_class']?>"><?=$courses['code']." : ".$courses['title']?></h2>
    <div class="container">
        <div class="row">
            <div class="col-md-12 content-info-tab">
                <div class="row">
                    <ul class="col-md-12">
                        <li><a href="<?=groupKey($groupKey)?>/courses/<?=$courses['id']?>/info">รายละเอียดหลักสูตร</a></li>
                        <li class="active">เข้าเรียน</li>
                        <li><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/summary">ผลการเรียน</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-12 content-learning">
                <div class="col-md-3 time-line">
                    <ul>
                        <?php if (count($courses['documents'])) { ?>
                            <li class="active">
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
                                                <?php }else if ($pre_test_chk) {?>
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
                                                <p class="sub-line ">
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
                                                <?php if ($rs_parent['quiz']) { ?><span class="sub-line opacity"><?=$rs_parent['quiz']['title']?></span><?php } ?>
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
                                            <?php }else if ($pre_test_chk) {?>
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
                                <?php }else if ($post_test_chk) { ?>
                                    <p class="opacity">แบบทดสอบเพื่อวัดความรู้ (Examination)</p>
                                <?php } else { ?>
                                    <p><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/exam">แบบทดสอบเพื่อวัดความรู้ (Examination)</a></p>
                                <?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="col-md-9 learning">
                    <div class="col-md-12 player">
                        <div class="row">

                            <div id="col-video" class="<?php if ($enroll_topics['slides'] && (($enroll_topics['state'] == 'vod' && $enroll_topics['streaming_status'] == 1) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 1 && $enroll_topics['streaming_pause'] == 0))) { echo "col-md-5"; } else { echo "col-md-12"; } ?> <?=(($enroll_topics['state'] == 'vod' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 1 && $enroll_topics['streaming_pause'] == 1)) ? 'bg-white' : ''?>">
                                <input type="hidden" id="duration">
                                <div id="wrapper-soon" class="row m-0 <?=(($enroll_topics['state'] == 'vod' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 1 && $enroll_topics['streaming_pause'] == 1)) ? 'align-items-center' : ''?>">
                                    <div class="col-xs-12 col-md-7 <?=(($enroll_topics['state'] == 'vod' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 1 && $enroll_topics['streaming_pause'] == 1)) ? '' : 'hidden'?>">
                                        <img class="img-responsive mx-auto visible-xs visible-sm" src="<?=getImage(constant("_BASE_DIR_COURSES_THUMBNAIL"),$courses['thumbnail'])?>" alt="">
                                        <img class="img-responsive w-100 hidden-xs hidden-sm" src="<?=getImage(constant("_BASE_DIR_COURSES_THUMBNAIL"),$courses['thumbnail'])?>" alt="">
                                    </div>
                                    <div class="col-xs-6 col-xs-offset-3 col-md-5 col-md-offset-0 px-15 <?=(($enroll_topics['state'] == 'vod' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 1 && $enroll_topics['streaming_pause'] == 1)) ? '' : 'hidden'?>">
                                        <div class="wrapper-countdown text-center">
                                            <?php if ($enroll_topics['state'] == 'live' && $diff > 0) { ?>
                                            <h2 class="transparent m-t-20 m-b-0 p-0">เริ่มถ่ายทอดสดในอีก</h2>
                                            <div class="countdown-time pull-left w-100">
                                                <div class="col-xs-3">
                                                    <h1 class="cd-d">
                                                        0
                                                    </h1>
                                                    <h3 class="b-none m-t-0">
                                                        วัน
                                                    </h3>
                                                </div>
                                                <div class="col-xs-3">
                                                    <h1 class="cd-h">
                                                        0
                                                    </h1>
                                                    <h3 class="b-none m-t-0">
                                                        ชั่วโมง
                                                    </h3>
                                                </div>
                                                <div class="col-xs-3">
                                                    <h1 class="cd-m">
                                                        0
                                                    </h1>
                                                    <h3 class="b-none m-t-0">
                                                        นาที
                                                    </h3>
                                                    <br>
                                                </div>
                                                <div class="col-xs-3">
                                                    <h1 class="cd-s">
                                                        0
                                                    </h1>
                                                    <h3 class="b-none m-t-0">
                                                        วินาที
                                                    </h3>
                                                    <br>
                                                </div>
                                            </div>
                                            <?php } else { ?>
                                            <h1 class="m-t-20 m-b-0 p-0 px-15">
                                                <?php if ($enroll_topics['state'] == 'live') { ?>
                                                กรุณารอสักครู่...<br>การถ่ายทอดสดจะเริ่มเร็วๆ นี้
                                                <?php } else if ($enroll_topics['state'] == 'vod' && $enroll_topics['streaming_status'] == 0 && $enroll_topics['vod_format'] != 'end_live') { ?>
                                                ขอบคุณที่รับชม<br>ติดตามชมย้อนหลังได้<br>เร็วๆ นี้
                                                <?php } else if ($enroll_topics['state'] == 'vod' && $enroll_topics['streaming_status'] == 0 && $enroll_topics['vod_format'] == 'end_live') { ?>
                                                สิ้นสุดการถ่ายทอดสด<br>ขอบคุณที่รับชม
                                                <?php } ?>
                                            </h1>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <div id="wrapper-player" class="embed-responsive embed-responsive-16by9 responsive-player <?=(($enroll_topics['state'] == 'vod' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 1 && $enroll_topics['streaming_pause'] == 1)) ? 'hidden' : ''?>">
                                    <div id="player"></div>
                                    <?php if ($enroll_topics['state'] == 'live') { ?>
                                    <div id="state-status" class="state-status state-status-danger <?=($enroll_topics['streaming_status'] == 0) ? 'hidden' : ''?> <?=($enroll_topics['slides']) ? 'f-14 px-7' : ''?>">
                                        ถ่ายทอดสด
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <?php if ($enroll_topics['slides']) { ?>
                            <div id="col-slides" class="<?=(($enroll_topics['state'] == 'vod' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 1 && $enroll_topics['streaming_pause'] == 1)) ? 'hidden' : ''?>">
                                <div class="col-md-7">
                                    <div id="sync1" class="owl-carousel owl-theme">
                                        <?php foreach($enroll_topics['slides'] as $rs_slides) { ?>
                                            <div class="item x"><img src="<?=constant("_BASE_DIR_SLIDES").$rs_slides['picture']?>" class="img-responsive"></div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="col-md-12 slide-thumbnail">
                                    <div id="sync2" class="owl-carousel owl-theme">
                                        <?php foreach($enroll_topics['slides'] as $rs_slides) { ?>
                                        <div class="item" <?php if (!$courses['not_seek']) { ?>onclick="seekTime(<?=$rs_slides['time_convert']?>);"<?php } ?>><img src="<?=constant("_BASE_DIR_SLIDES").$rs_slides['picture']?>" class="img-responsive"></div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                            <div id="slides-action" class="pull-right">
                                <a href="javascript:void(0);" id="slidesActiveOff" class="btn btn-style3 m-r-5 <?=(($enroll_topics['state'] == 'vod') || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 1 && $enroll_topics['streaming_pause'] == 1)) ? 'hidden' : ''?>"><i class="fa fa-stop f-16 icon-middle"></i> หยุดสไลด์อัตโนมัติ</a>
                                <a href="javascript:void(0);" id="slidesActiveOn" class="btn btn-style3 m-r-5 <?=(($enroll_topics['state'] == 'vod') || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 1 && $enroll_topics['streaming_pause'] == 1)) ? 'hidden' : ''?>"><i class="fa fa-play f-16 icon-middle"></i> เริ่มสไลด์อัตโนมัติ</a>

                                <a href="javascript:void(0);" id="slidesOut" class="btn btn-style3 <?=(($enroll_topics['state'] == 'vod' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 1 && $enroll_topics['streaming_pause'] == 1)) ? 'hidden' : ''?>"><i class="fa fa-th f-16 icon-middle"></i> ปิด Slide</a>
                                <a href="javascript:void(0);" id="slidesIn" class="btn btn-style3 <?=(($enroll_topics['state'] == 'vod' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 0) || ($enroll_topics['state'] == 'live' && $enroll_topics['streaming_status'] == 1 && $enroll_topics['streaming_pause'] == 1)) ? 'hidden' : ''?>"><i class="fa fa-th f-16 icon-middle"></i> แสดง Slide</a>
                            </div>
                            <?php } ?>

                        </div>
                    </div>

                    <div class="col-md-12 tab-course">
                        <div class="row">
                            <ul class="nav nav-tabs" role="tablist" id="eventsTab">
                                <?php if (isset($enroll_topics['detail']) && $enroll_topics['detail'] != "") { ?>
                                    <li id="li-tabs1" role="presentation" class="active"><a href="#tabs1" aria-controls="tabs1" role="tab" data-toggle="tab">รายละเอียด</a></li>
                                <?php } ?>
                                <?php if ($courses['is_discussion'] == 1) { ?>
                                    <li id="li-tabs2" role="presentation" class="<?=!isset($enroll_topics['detail']) || $enroll_topics['detail'] == "" ? 'active' : ''?>"><a href="#tabs2" aria-controls="tabs2" role="tab" data-toggle="tab" aria-expanded="true">กระดานสนทนา</a></li>
                                <?php } ?>
                            </ul>
                            <div class="tab-content">
                                <?php if (isset($enroll_topics['detail']) && $enroll_topics['detail'] != "") { ?>
                                    <div role="tabpanel" class="col-md-12 tab-pane active" id="tabs1">
                                        <?=$enroll_topics['detail']?>
                                    </div>
                                <?php } ?>
                                <?php if ($courses['is_discussion'] == 1) { ?>
                                    <div role="tabpanel" class="col-md-12 tab-pane <?=!isset($enroll_topics['detail']) || $enroll_topics['detail'] == "" ? 'active' : ''?>" id="tabs2">
                                        <div class="row">
                                            <div class="collapse" id="collapseDiscussionForm">
                                                <button id="btnToggleDiscussionFormIn" type="button" data-toggle="collapse" data-target="#collapseDiscussionForm" aria-expanded="false" aria-controls="collapseDiscussionForm" class="btn btn-link btn-anchor-set f-18 p-l-5"><i class="fa fa-plus f-11"></i> ตั้งหัวเรื่องใหม่</button>
                                                <div class="panel" style="padding-top:20px;">
                                                    <form id="discussion-form" method="post" role="form" data-toggle="validator" enctype="multipart/form-data">
                                                        <input type="hidden" name="enroll" id="enroll" value="<?=$enroll['id']?>">
                                                        <input type="hidden" name="topics_id" id="topics_id" value="<?=$enroll_topics['id']?>">
                                                        <div class="form-group clearfix">
                                                            <div class="col-md-12"><label>ตั้งหัวเรื่อง</label> <!-- โดยคุณ <?=$members['first_name']." ".$members['last_name']?> --></div>
                                                        </div>
                                                        <div class="form-group clearfix">
                                                            <div class="col-md-12">
                                                            <label for="topic">หัวเรื่อง<remark>*</remark></label>
                                                            <input type="text" name="topic" id="topic" class="form-control required">
                                                            </div>
                                                        </div>
                                                        <div class="form-group clearfix">
                                                            <div class="col-md-12">
                                                            <label for="description">ข้อความ<remark>*</remark></label>
                                                            <textarea type="textarea" name="description" id="description" class="form-control required" rows="5"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="form-group clearfix">
                                                            <div class="col-md-12">
                                                                <label for="file">อัพโหลดรูป <remark>(ขนาดไม่เกิน 2MB)</remark></label>
                                                                <input type="file" name="file" id="file" class="form-control" accept="image/jpeg,image/png">
                                                            </div>
                                                        </div>
                                                        <div class="form-group clearfix text-center">
                                                            <div class="col-md-12">
                                                                <button id="discussion-btn" class="btn btn-style1 pull-left">ตั้งหัวเรื่องใหม่</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 p-l-5">
                                                <div class="text-left">
                                                    <button id="btnToggleDiscussionFormOut" type="button" data-toggle="collapse" data-target="#collapseDiscussionForm" aria-expanded="false" aria-controls="collapseDiscussionForm" class="btn btn-link btn-anchor-set f-18"><i class="fa fa-plus f-11"></i> ตั้งหัวเรื่องใหม่</button>
                                                </div>
                                            </div>
                                            <div class="col-xs-6">
                                                <div class="text-right">
                                                    <button id="btnReloadDiscussion" type="button" class="btn btn-link btn-anchor f-18"><i class="fa fa-refresh f-11"></i> โหลดข้อมูลอีกครั้ง</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <table class="table table-striped table-condensed" id="res-discussion">
                                                <thead>
                                                    <tr>
                                                        <th width="">หัวเรื่อง</th>
                                                        <th class="hidden-xs">วันที่ตั้งหัวเรื่อง</th>
                                                        <th>จำนวนคนอ่าน</th>
                                                        <th>จำนวนคำตอบ</th>
                                                    </tr>
                                                </thead>

                                                <tbody></tbody>
                                            </table>

                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($courses['is_discussion'] == 1) { ?>
<div class="modal fade" id="discussionModal" tabindex="-1" role="dialog" aria-labelledby="discussionModalLabel" data-id="">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="discussionModalLabel"></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="" id="discussionModalPicture"></div>
                        <div class="wrapper" id="discussionModalDescription"></div>
                        <div class="f-16 p-t-15">— <strong class="" id="discussionModalBy"></strong> <span id="discussionModalByType"></span><span class="m-l-3"><i class="fa fa-clock-o f-12"></i></span> <span id="discussionModalDateTime"></span></div>
                    </div>
                </div>
                <div class="row post-box" id="reply-box"></div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <form id="discussion-reply-form" method="post" role="form" data-toggle="validator" enctype="multipart/form-data">
                        <input type="hidden" name="parent_id" id="parent_id" value="">
                        <input type="hidden" name="mention_id" id="mention_id" value="">
                        <input type="hidden" name="enroll" id="enroll" value="<?=$enroll['id']?>">
                        <input type="hidden" name="topics_id" id="topics_id" value="<?=$enroll_topics['id']?>">
                        <div class="form-group clearfix">
                            <div class="col-md-12"><label id="label-reply">แสดงความคิดเห็น</label> <!-- โดยคุณ <?=$members['first_name']." ".$members['last_name']?> --></div>
                        </div>

                        <div class="form-group clearfix hide">
                            <div class="col-md-12">
                                <div id="wrapper-reply-selected"></div>
                            </div>
                        </div>

                        <div class="form-group clearfix">
                            <div class="col-md-12">
                            <label for="description">ข้อความ<remark>*</remark></label>
                            <textarea type="textarea" name="description" id="description" class="form-control required" rows="5"></textarea>
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <div class="col-md-12">
                                <label for="file">อัพโหลดรูป <remark>(ขนาดไม่เกิน 2MB)</remark></label>
                                <input type="file" name="file" id="file" class="form-control" accept="image/jpeg,image/png">
                            </div>
                        </div>
                        <div class="form-group clearfix text-center">
                            <div class="col-md-12">
                                <button id="discussion-reply-btn" class="btn btn-style1 pull-left">แสดงความคิดเห็น</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php include 'include/inc.footer.php'; ?>

<!-- Javascript Library -->
<script src="/bower_components/jquery/dist/jquery.min.js"></script>
<script src="/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="/bower_components/html5shiv/dist/html5shiv.min.js"></script>
<script src="/bower_components/respond/dest/respond.min.js"></script>
<script src="/bower_components/superfish/dist/js/superfish.min.js"></script>
<script src="/bower_components/noty/lib/noty.min.js"></script>
<script src="/bower_components/OwlCarousel2/dist/owl.carousel.min.js"></script>
<script src="/bower_components/jquery-confirm/dist/jquery-confirm.min.js"></script>
<script src="/bower_components/moment/min/moment-with-locales.min.js"></script>
<script src="/bower_components/lodash/dist/lodash.min.js"></script>
<script src="/js/script/config.js"></script>
<script src="/js/script/functions.js"></script>
<script src="/js/script/model/members.js"></script>

<script type="text/javascript">
    // Live - Variable Default
    var getSlideActive;
    var courses_id = '<?=$enroll['courses_id']?>';
    var topics_id = '<?=$enroll_topics['id']?>';
    var topics_state = '<?=$enroll_topics['state']?>';
    var topics_streaming_status = '<?=$enroll_topics['streaming_status']?>';
    var isSlidesActive = false;
    var isCheckIn = false;

    var defaultDiscussionModal;

    jQuery(document).ready(function($) {
        moment.locale('th');
        // $("#discussionModal").modal("show")
    });

    var sync1 = $("#sync1");
    var sync2 = $("#sync2");
    var slidesPerPage = 5;
    var syncedSecondary = false;
    var index_sync2 = 0;
    var sync_status = 1;

    sync1.owlCarousel({
        items : 1,
        slideSpeed : 2000,
        nav: false,
        autoplay: false,
        dots: false,
        loop: true,
        touchDrag: false,
        mouseDrag: false,
        responsiveRefreshRate : 200
    }).on('changed.owl.carousel', syncPosition);

    sync2.on('initialized.owl.carousel', function () { sync2.find(".owl-item").eq(0).addClass("current"); })
        .owlCarousel({
            items : slidesPerPage,
            dots: false,
            nav: true,
            smartSpeed: 200,
            slideSpeed : 500,
            slideBy: slidesPerPage, //alternatively you can slide by 1, this way the active slide will stick to the first item in the second carousel
            responsiveRefreshRate : 100,
            navText: ['<svg width="100%" height="100%" viewBox="0 0 11 20"><path style="fill:none;stroke-width: 1px;stroke: #000;" d="M9.554,1.001l-8.607,8.607l8.607,8.606"/></svg>','<svg width="100%" height="100%" viewBox="0 0 11 20" version="1.1"><path style="fill:none;stroke-width: 1px;stroke: #000;" d="M1.054,18.214l8.606,-8.606l-8.606,-8.607"/></svg>']
        }).on('changed.owl.carousel' , syncPosition2);

    function syncPosition(el) {
        if (topics_state == 'vod') {
            var count = el.item.count-1;
            var current = Math.round(el.item.index - (el.item.count/2) - .5);

            if (current < 0) { current = count; }
            if (current > count) { current = 0; }

            sync2.find(".owl-item").removeClass("current").eq(current).addClass("current");

            var onscreen = sync2.find('.owl-item.active').length - 1;
            var start = sync2.find('.owl-item.active').first().index();
            var end = sync2.find('.owl-item.active').last().index();
            if (current > end) {
                sync2.data('owl.carousel').to(current, 100, true);
            }
            if (current < start) {
                sync2.data('owl.carousel').to(current - onscreen, 100, true);
            }
        } else {
            var count = el.item.count-1;

            var current = index_sync2;

            if (current < 0) { current = count; }
            if (el.item.count > 2) {
                if (current > count) { current = 0; }
            } else {
                current--;
            }

            sync2.find(".owl-item").removeClass("current").eq(current).addClass("current");

            var onscreen = sync2.find('.owl-item.active').length - 1;
            var start = sync2.find('.owl-item.active').first().index();
            var end = sync2.find('.owl-item').last().index();

            if (current > end || current == end) {
                sync2.data('owl.carousel').to(current, 100, true);
            }

            if (current < start) {
                sync2.data('owl.carousel').to(current - onscreen, 100, true);
            }

            if (current < end) {
                sync2.data('owl.carousel').to(current - onscreen, 100, true);
            }
        }

    }

    function syncPosition2(el) {
        if (syncedSecondary) {
            var number = el.item.index;
            sync1.data('owl.carousel').to(number, 100, true);
        }
    }

    sync2.on("click", ".owl-item", function(e) {
        e.preventDefault();
        var current_index = $(this).index();

        // If click other slides then slideActive will stop function.
        if (index_sync2 != current_index && sync_status == 1) {
            $("#slidesActiveOff").click();
        }

        index_sync2 = current_index;
        sync1.data('owl.carousel').to(index_sync2, 300, true);
    });

    var jsonVar = [
        <?php $i=0; foreach($enroll_topics['slides'] as $rs_slides){ $i++; ?>
        { image: '<?=constant("_BASE_DIR_SLIDES").$rs_slides['picture']?>', time: <?=$rs_slides['time_convert']?>, id:<?=$rs_slides['id']?> },
        <?php } ?>
    ];

    function slidesActiveOn() {
        isSlidesActive = true;
        $.get('/api/site/courses/' + courses_id + '/getSlideActive', function(data) {
            var index_slide;
            if (Object.keys(data).length == 0) {
                index_slide = index_sync2;
            } else {
                // Use find one item
                index_slide = _.findIndex(jsonVar, function(o) { return o.id == data.id; });
                index_sync2 = index_slide;
            }

            sync1.data('owl.carousel').to(index_slide, 300, true);
        });

        getSlideActive = setTimeout(function(){
            slidesActiveOn();
        }, 20000);
    }

    function slidesActiveOff() {
        isSlidesActive = false;
        clearTimeout(getSlideActive);
    }
</script>
<?php
$rsYouTube = $oFunc->isYouTube($enroll_topics['streaming_url']);
if ($rsYouTube['has_match'] == 1) {
    $is_compatible_jwplayer = true;
    ?>
    <script src="/js/jwplayer-7.11.3/jwplayer.js"></script>
    <script>jwplayer.key="ysQTVfHC5iQ8flS72k460WTgxEPDzPg90dTu2NzjVT0=";</script>
    <?php
} else {
    $is_compatible_jwplayer = false;
    ?>
    <script src="/js/jwplayer-8.7.4/jwplayer.js"></script>
    <script>jwplayer.key="UcxlWibHG+/Cf7dpIh1GG/ajKLgzwE3er8b69ri340I=";</script>
    <?php
}
?>
<script>
    var viewsId = null;
    var updateDurationInterval;
    function updateDuration() {
        $.post('/api/site/enroll/views', {
            id: viewsId,
            enroll_id: <?=$enroll['id']?>,
            topics_id: '<?=$enroll2topic['topics_id']?>',
            state: '<?=$enroll_topics['state']?>'
        }, function(resp) {
            viewsId = resp.id;
        });
    }

    // Variable for enroll2topic "Live"
    if (topics_state == 'live') {
        // Topic is Live
        var enroll2topic_id = '<?=$enroll2topic['id']?>';
        var enroll_topics_id = '<?=$enroll_topics['id']?>';
        var diff_start_live_datetime = '<?=$diff?>';
        var secondsCountUp = 0;
        var timeCountUpOn;
        var ivUpdateLiveDuration;
        var ivGetStream;
        var timePlayer;
        var isSlides = false;

        var elementSoon = $('#wrapper-soon');
        var elementColVideo = $('#col-video');
        var elementWrapperPlayer = $('#wrapper-player');
        var elementPlayer = $('#player');
        var elementSlides = $('#col-slides');
        var elementStateStatus = $('#state-status');

        <?php if ($enroll_topics['slides']) { ?>
            isSlides = true;
        <?php } ?>

        function countUpOn() {
            ++secondsCountUp;
            timeCountUpOn = setTimeout(function(){
                countUpOn();
            }, 1000);
        }

        function countUpOff() {
            clearTimeout(timeCountUpOn);
        }

        function pad(number, length) {
            var str = '' + number;
            while (str.length < length) {
                str = '0' + str;
            }
            return str;
        }

        // Countdown
        function cdtd(timeDiff) {
            if (timeDiff < 1) {
                // console.log('wrapper countdown');
                // clearTimeout(ivGetStream);
                // getStream();
            } else {
                var timeDiff_sec = timeDiff;
                timeDiff_sec = timeDiff_sec - 1;
                var days = Math.floor(timeDiff_sec / 86400);
                var hours = Math.floor((timeDiff_sec % 86400) / 3600);
                var minutes = Math.floor(((timeDiff_sec % 86400) % 3600) / 60);
                var seconds = ((timeDiff_sec % 86400) % 3600) % 60;

                hours %= 24;
                minutes %= 60;
                seconds %= 60;

                $('.cd-d').html(days);
                $('.cd-h').html(hours);
                $('.cd-m').html(minutes);
                $('.cd-s').html(seconds);

                timePlayer = setTimeout(function () {
                    cdtd(timeDiff_sec)
                }, 1000);
            }
        }

        function ivUpdateLiveDurationOn() {
            ivUpdateLiveDuration = setInterval(function() {
                $.post('/api/site/enroll2topic_live_duration', {
                    'id': enroll2topic_id,
                    'duration': secondsCountUp
                });
            }, 5000);
        }

        function ivUpdateLiveDurationOff() {
            clearInterval(ivUpdateLiveDuration);
        }

        function setupPlayer(url) {
            var playerInstance = jwplayer("player");
            playerInstance.setup({
                "file": url,
                "aspectratio": "16:9",
                "width": "100%",
                "autostart": "true"
            });

            playerInstance.on('play', function() {
                updateDuration();
                updateDurationInterval = setInterval(updateDuration, 5000);
            });
            playerInstance.on('pause', function() {
                clearInterval(updateDurationInterval);
            });
        }

        function toggleLayoutLearning(status, data) {
            if (status == 'on') {
                elementSoon.addClass('hidden');
                elementPlayer.closest(elementWrapperPlayer).removeClass('hidden');
                elementStateStatus.removeClass('hidden');
                elementColVideo.addClass('bg-black');
                elementColVideo.removeClass('bg-white');

                setupPlayer(data.streamData.streaming_url);
            } else if (status == 'off') {
                var playerInstance = jwplayer("player");
                playerInstance.remove();

                elementColVideo.removeClass('col-md-5');
                elementColVideo.addClass('col-md-12');
                elementColVideo.addClass('bg-white');
                elementColVideo.removeClass('bg-black');

                elementSoon.addClass('align-items-center');
                elementSoon.find('div').removeClass('hidden');
                elementSoon.removeClass('hidden');
                elementWrapperPlayer.addClass('hidden');
                elementSlides.addClass('hidden');
                elementSlides.next('#slides-action').find('a').addClass('hidden');

                $('.wrapper-countdown').html('<h1 class="m-t-20 m-b-0 p-0 px-15">' + data.message + '</h1>');
            }
        }

        function recordStats() {
            if (isCheckIn == false) {
                $.post('/api/site/member2live',
                    {
                        'courses_id': courses_id,
                        'topics_id': topics_id
                    },
                    function(data) {
                        // console.log(data);
                        isCheckIn = true;
                    });
            }
        }

        function getStream() {
            $.getJSON("/api/site/topics/" + enroll_topics_id + "/stream", function (data) {
                if (data.streamData.status == 'coming' || data.streamData.status == 'pause' || data.streamData.status == 'countdown') {
                    clearTimeout(ivGetStream);

                    if (data.streamData.status != 'countdown') {
                        $('.wrapper-countdown').html('<h1 class="m-t-20 m-b-0 p-0 px-15">' + data.message + '</h1>');
                    }

                    var timePlayerRespond = setInterval(function () {
                        $.getJSON("/api/site/topics/" + enroll_topics_id + "/stream", function (data) {
                            if (data.streamData.status != 'countdown') {
                                $('.wrapper-countdown').html('<h1 class="m-t-20 m-b-0 p-0 px-15">' + data.message + '</h1>');
                            }

                            if (data.streamData.status == 'stream') {
                                if (typeof timePlayerRespond !== "undefined") {
                                    clearTimeout(timePlayerRespond);
                                    window.location.reload();
                                    // getStream();
                                }
                            } else if (data.streamData.status == 'stop') {
                                if (typeof timePlayerRespond !== "undefined") {
                                    clearTimeout(timePlayerRespond);
                                    getStream();
                                }
                            } else if (data.streamData.status == 'vod') {
                                if (typeof timePlayerRespond !== "undefined") {
                                    clearTimeout(timePlayerRespond);
                                    location.reload();
                                }
                            }
                        });
                    // }, 10000);
                    }, 5000);
                } else if (data.streamData.status == 'stream') {
                    clearTimeout(timePlayer);
                    clearTimeout(ivGetStream);

                    toggleLayoutLearning('on', data);

                    $('#tp-' + enroll_topics_id).find('i').removeClass('not').addClass('done');
                    $('#tp-' + enroll_topics_id).find('a').find('span').attr('class', 'f-16 topic-status-live').html('ถ่ายทอดสด');

                    if (isSlides == true) {
                        elementColVideo.removeClass('col-md-12');
                        elementColVideo.addClass('col-md-5');
                        elementSlides.removeClass('hidden');
                        elementSlides.next('#slides-action').find('a').removeClass('hidden');
                        slidesActiveOff();
                        slidesActiveOn();
                    }

                    countUpOn();
                    ivUpdateLiveDurationOn();
                    recordStats();
                    cdtdRespond();

                } else if (data.streamData.status == 'stop') {
                    toggleLayoutLearning('off', data);

                    clearTimeout(timePlayer);
                    clearTimeout(ivGetStream);
                    slidesActiveOff();
                    ivUpdateLiveDurationOff();
                    countUpOff();

                    $('#tp-' + enroll_topics_id).find('i').removeClass('done').removeClass('fa-rss').addClass('fa-play').addClass('semicircle');
                    $('#tp-' + enroll_topics_id).find('a').find('span').remove();
                }
            });

            ivGetStream = setTimeout(function () {
                getStream()
            }, 10000);
        }

        function cdtdRespond() {
            var timeRespond = setInterval(function() {
                $.getJSON("/api/site/topics/" + enroll_topics_id + "/stream", function(data) {

                    if ('<?=$enroll_topics['streaming_url']?>' !== data.streamData.streaming_url && data.streamData.status == 'stream') {
                        window.location.reload();
                    }

                    if (data.streamData.status == 'coming' || data.streamData.status == 'pause') {
                        if (typeof timeRespond !== "undefined") {
                            clearInterval(timeRespond);

                            toggleLayoutLearning('off', data);

                            getStream();

                            return true;
                        }
                    } else if (data.streamData.status == 'stop') {
                        if (typeof timeRespond !== "undefined") {
                            clearInterval(timeRespond);

                            toggleLayoutLearning('off', data);
                            $('#tp-' + enroll_topics_id).find('i').removeClass('done').removeClass('fa-rss').addClass('fa-play').addClass('semicircle');
                            $('#tp-' + enroll_topics_id).find('a').find('span').remove();

                            slidesActiveOff();
                            ivUpdateLiveDurationOff();
                            countUpOff();
                        }
                    } else if (data.streamData.status == 'vod') {
                        clearInterval(timeRespond);
                        location.reload();
                    } else if (data.streamData.status == 'countdown') {
                        clearInterval(timeRespond);
                        location.reload();
                    }
                });
            }, 10000);
        }

        if (topics_streaming_status == 0 && diff_start_live_datetime > 0) {
            cdtd('<?=$diff?>');
        }


        // if (topics_state == 'live') {
        clearTimeout(ivGetStream);
        getStream();
        // }
    }

    // Topic is VOD
    if (topics_state == 'vod' && topics_streaming_status == 1) {
        var playerInstance = jwplayer("player");
        playerInstance.setup({
            "file": "<?=$enroll_topics['streaming_url']?>",
            image: "<?=getImage(constant("_BASE_DIR_COURSES_THUMBNAIL"),$courses['thumbnail'])?>",
            aspectratio: "16:9",
            width: "100%",
            autostart: "true",
            <?php if($courses['not_seek']){ ?>
            "skin" : {
                "url":"/css/skin-player.css",
                "name" : "setskin"
            },
            <?php } ?>
            <?php if($enroll_topics['is_show_subtitles']){ ?>
            tracks: [{
                file: '<?=$enroll_topics['subtitles_url']?>',
                kind: 'captions',
                "default": true,
            }],
            <?php } ?>
        });

        playerInstance.setCaptions({
            'fontSize': 11,
            'edgeStyle': '<?=$enroll_topics['video']['subtitle_edge_style']?>',
            'color': '<?=$enroll_topics['video']['subtitle_font_color']?>',
            'fontOpacity': '<?=$enroll_topics['video']['subtitle_font_opacity']?>',
            'backgroundColor': '<?=$enroll_topics['video']['subtitle_background_color']?>',
            'backgroundOpacity': '<?=$enroll_topics['video']['subtitle_background_opacity']?>',
            'windowColor': '<?=$enroll_topics['video']['subtitle_window_color']?>',
            'windowOpacity': '<?=$enroll_topics['video']['subtitle_window_opacity']?>',
        });

        playerInstance.on('time', function(event) {
            var position = Math.floor(event.position);
            $('#duration').val(position);
            for(var slide in jsonVar) {
                if(position === jsonVar[slide].time){
                    sync1.data('owl.carousel').to( slide , 300, true);
                }
            }
        });

        playerInstance.on('seek', function(event) {
            var offset = Math.floor(event.offset);
            for(var slide in jsonVar) {
                if(offset >= jsonVar[slide].time && offset <= jsonVar[+slide + 1].time){
                    sync1.data('owl.carousel').to( slide , 300, true);
                }
            }
        });

        playerInstance.on('play', function() {
            updateDuration();
            updateDurationInterval = setInterval(updateDuration, 5000);
        });
        playerInstance.on('pause', function() {
            clearInterval(updateDurationInterval);
        });
    }

    <?php if(!$enroll2topic['status'] && $enroll_topics['state'] == 'vod'){?>
    function seekDuration(time) {
        time = time === undefined ? 0 : time;
        playerInstance.seek(time)
        setInterval(function(){
            var duration = $('#duration').val();
            $.post('/api/site/enroll2topic_duration', {'id' : <?=$enroll2topic['id']?>, 'duration': duration});
        }, 5000);
    }

    playerInstance.on('play', function() {
        var duration = $('#duration').val();
        if (!duration) {
            seekDuration(<?=$enroll2topic['duration']?>);
        }
    });

    playerInstance.on('complete', function() {
        $.post('/api/site/enroll2topic_status', {'id' : '<?=$enroll2topic['id']?>', 'status': '1'}, function(data) {
            if (data.message.auto_quiz) {
                window.location.href = '<?=groupKey($groupKey)?>/enroll/'+ <?=$enroll['id']?> +'/exam/'+ data.message.qid +'/quiz';
            } else {
                $.confirm({
                    theme: 'supervan',
                    title: data.message.header,
                    content: '',
                    buttons: {
                        confirm: {
                            text: data.message.btn1,
                            keys: ['enter', 'shift'],
                            action: function() {
                                if (data.chk == 'quiz') {
                                    window.location.href = '<?=groupKey($groupKey)?>/enroll/'+ <?=$enroll['id']?> +'/exam/'+ data.message.qid +'/quiz';
                                }else if (data.chk == 'post-test') {
                                    window.location.href = '<?=groupKey($groupKey)?>/enroll/'+ <?=$enroll['id']?> +'/exam/post-test';
                                }else if (data.chk == 'examination') {
                                    window.location.href = '<?=groupKey($groupKey)?>/enroll/'+ <?=$enroll['id']?> +'/exam/exam';
                                }else if (data.chk == 'success') {
                                    window.location.href = '<?=groupKey($groupKey)?>/enroll/'+ <?=$enroll['id']?> +'/summary';
                                }else if (data.chk == 'next') {
                                    $.post('/api/site/enroll2topic_stage', {'enroll' : <?=$enroll['id']?>, 'topics': data.topics.next.id}, function() {
                                        window.location.href = '<?=groupKey($groupKey)?>/enroll/'+ <?=$enroll['id']?> +'/course'
                                    });
                                }else if (data.chk == 'return') {
                                    window.location.href = '<?=groupKey($groupKey)?>/enroll/'+ <?=$enroll['id']?> +'/course'
                                }
                            }
                        },
                        cancel: {
                            text: data.message.btn2,
                            action: function() {
                                playerInstance.play();
                            }
                        }
                    }
                });
            }
        });
    });
    <?php } ?>

    function seekTime(time) {
        playerInstance.seek(time);
    }

    jQuery(document).ready(function($) {
        $("#slidesOut").click(function() {
            $('#col-slides').fadeOut(100, function() {
                $('#col-video').removeClass('col-md-5');
                $('#col-video').addClass('col-md-12');
                $('#slidesOut').hide();
                $('#slidesIn').css('display', 'inline-block');

                if (topics_state == 'live') {
                    elementStateStatus.removeClass('f-14 px-7');
                    elementStateStatus.css('letter-spacing', '0px');
                    if (isSlidesActive == true) {
                        $('#slidesActiveOff').click();
                        $('#slidesActiveOn').hide();
                    } else {
                        $('#slidesActiveOn').hide();
                    }
                }
            });
        });

        $("#slidesIn").click(function() {
            $('#col-slides').fadeIn(100, function() {
                $('#col-video').removeClass('col-md-12');
                $('#col-video').addClass('col-md-5');
                $('#slidesOut').css('display', 'inline-block');
                $('#slidesIn').hide();

                if (topics_state == 'live') {
                    elementStateStatus.addClass('f-14 px-7');
                    elementStateStatus.css('letter-spacing', '0.8px');
                    if (isSlidesActive == false) {
                        $('#slidesActiveOn').click();
                        $('#slidesActiveOn').hide();
                        $('#slidesActiveOff').css('display', 'inline-block');
                    }
                }
            });
        });

        $("#slidesActiveOff").click(function() {
            $('#slidesActiveOff').hide();
            $('#slidesActiveOn').css('display', 'inline-block');
            sync_status = 0;
            slidesActiveOff();
        });

        $("#slidesActiveOn").click(function() {
            $('#slidesActiveOff').css('display', 'inline-block');
            $('#slidesActiveOn').hide();
            sync_status = 1;
            slidesActiveOn();
        });
    });

</script>

<script src="/js/formvalidation.js"></script>
<script src="/js/script/model/discussion.js"></script>
<script type="text/javascript">
    var loadDiscussion = function(coursesID) {
        var deferredDiscussionList;
        var $eleDiscussionTableBody;
        var tmpl;
        var momentNow;
        var diffDays;
        var discussionBy;
        var discussionByType;
        var discussionDate;

        $eleDiscussionTableBody = $('#res-discussion').find('tbody');
        $eleDiscussionTableBody.css('opacity', 0.2);

        deferredDiscussionList = _discussion.getListByCourse(coursesID);
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

                    tmpl += '<tr>'+
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

        deferredDiscussionDetail = _discussion.get(discussionId);
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

                tmpl += '<div class="col-sm-12">'+
                            '<div id="discussionId_'+data.replies[index].id+'" class="panel panel-light-white post panel-shadow">'+
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

                    tmpl += '<div id="discussionId_'+data.replies[index].replies[sub_index].id+'" class="post-reply">'+
                                '<div class="panel panel-light-grey panel-shadow-disabled m-b-15">'+
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
                        console.log('#discussionId_'+focusDiscussionId+' > .post-heading');
                        $('#discussionId_'+focusDiscussionId).find('.post-footer .time').fadeOut('fast').delay(100).fadeIn(700)
                    }, 800);
                }, 400);
            }

        }).fail(function(data) {
            console.log('failed');
        });
    };

    loadDiscussion(<?=$courses['id']?>);

    $('#discussion-form').formValidation({
        framework: 'bootstrap',
        excluded: ':disabled',
        fields: {
            topic: {
                validators: {
                    notEmpty: {
                        message: 'กรุณาระบุ หัวเรื่อง'
                    }
                }
            },
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
        var defaultMsg = 'ตั้งหัวเรื่อง';
        var waitingMsg = 'ระบบกำลังอัพโหลดและตรวจสอบข้อมูล...';
        var successMsg = 'ตั้งหัวเรื่องเรียบร้อย';

        var dataDiscussion, dataFile;
        var deferredCreate, deferredPictureUpload;

        $this.find('#discussion-btn').html(waitingMsg).prop('disabled', true);
        dataDiscussion = $this.serializeObject();
        // console.log(dataDiscussion);
        // return false;
        if ($this.find('#file').val()) {
            dataFile = new FormData($this.closest('form')[0]);
            deferredPictureUpload = _discussion.uploadPicture(dataFile);
            deferredPictureUpload.done(function(responseDataPicture) {
                responseDataPicture = $.parseJSON(responseDataPicture);
                dataDiscussion.file = responseDataPicture.file_name;
                    deferredCreate = _discussion.create(JSON.stringify(dataDiscussion))
                    deferredCreate.done(function(data) {
                        if (data.is_error == false) {
                            $this.find('#discussion-btn').html(successMsg).prop('disabled', false);
                            $('#collapseDiscussionForm').collapse('toggle');
                            var dialog = $.alert({
                                title: 'ตั้งหัวเรื่องสำเร็จ',
                                content: '<p style="font-size: 20px;">คุณได้ตั้งหัวเรื่องในหลักสูตร <strong style="letter-spacing: 2px;">"<?=$courses['title']?>"</strong></p>',
                                type: 'green',
                                typeAnimated: true,
                                closeIcon: true
                            });

                            $this[0].reset();
                            loadDiscussion(data.coursesID);
                        } else {
                            $this.find('#discussion-btn').html(defaultMsg).prop('disabled', false);
                            notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง (1)" );
                        }
                    }).fail(function(data) {
                        $this.find('#discussion-btn').html(defaultMsg).prop('disabled', false);
                        notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง (2)" );
                    });
                }).fail(function(data) {
                    $this.find('#discussion-btn').html(defaultMsg).prop('disabled', false);
                    notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง (3)" );
                });
        } else {
                deferredCreate = _discussion.create(JSON.stringify(dataDiscussion))
                deferredCreate.done(function(data) {
                    if (data.is_error == false) {
                        $this.find('#discussion-btn').html(successMsg).prop('disabled', false);
                        $('#collapseDiscussionForm').collapse('toggle');
                        var dialog = $.alert({
                            title: 'ตั้งหัวเรื่องสำเร็จ',
                            content: '<p style="font-size: 20px;">คุณได้ตั้งหัวเรื่องในหลักสูตร <strong style="letter-spacing: 2px;">"<?=$courses['title']?>"</strong></p>',
                            type: 'green',
                            typeAnimated: true,
                            closeIcon: true
                        });

                        $this[0].reset();
                        loadDiscussion(data.coursesID);
                    } else {
                        $this.find('#discussion-btn').html(defaultMsg).prop('disabled', false);
                        notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง (4)" );
                    }
                }).fail(function(data) {
                    $this.find('#discussion-btn').html(defaultMsg).prop('disabled', false);
                    notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง (5)" );
                });
        }
        return false;
    });

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
        if ($this.find('#file').val()) {
            dataFile = new FormData($this.closest('form')[0]);
            deferredPictureUpload = _discussion.uploadPicture(dataFile);
            deferredPictureUpload.done(function(responseDataPicture) {
                responseDataPicture = $.parseJSON(responseDataPicture);
                dataDiscussionReply.file = responseDataPicture.file_name;
                    deferredReply = _discussion.reply(JSON.stringify(dataDiscussionReply))
                    deferredReply.done(function(data) {
                        if (data.is_error == false) {
                            $this.find('#discussion-reply-btn').html(successMsg).prop('disabled', false);

                            $('.btnCancelReply').trigger('click');
                            $this[0].reset();
                            loadDiscussionDetail($this.closest('#discussionModal').attr('data-id'), data.discussion_id);
                        } else {
                            $this.find('#discussion-reply-btn').html(defaultMsg).prop('disabled', false);
                            notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง (6)" );
                        }
                    }).fail(function(data) {
                        $this.find('#discussion-reply-btn').html(defaultMsg).prop('disabled', false);
                        notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง (7)" );
                    });
                }).fail(function(data) {
                    $this.find('#discussion-reply-btn').html(defaultMsg).prop('disabled', false);
                    notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง (8)" );
                });
        } else {
                deferredReply = _discussion.reply(JSON.stringify(dataDiscussionReply))
                deferredReply.done(function(data) {
                    if (data.is_error == false) {
                        $this.find('#discussion-reply-btn').html(successMsg).prop('disabled', false);

                        $('.btnCancelReply').trigger('click');
                        $this[0].reset();
                        loadDiscussionDetail($this.closest('#discussionModal').attr('data-id'), data.discussion_id);
                    } else {
                        $this.find('#discussion-reply-btn').html(defaultMsg).prop('disabled', false);
                        notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง (9)" );
                    }
                }).fail(function(data) {
                    $this.find('#discussion-reply-btn').html(defaultMsg).prop('disabled', false);
                    notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง (10)" );
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
        loadDiscussion(<?=$courses['id']?>);
    });

    $('body').on('click', '.btnShowDiscussion', function(event) {
        event.preventDefault();
        var $this = $(this);

        _discussion.updateView($this.data('id'));

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

        loadDiscussion(<?=$courses['id']?>);
        $modal.find('#discussionModalPicture').html('');
        $('#wrapper-reply-selected').closest('.form-group').addClass('hide');
        $('#wrapper-reply-selected').empty();

        $modal.find('#discussion-reply-form #parent_id').val($modal.attr('data-id'));
        $modal.find('#discussion-reply-form #mention_id').val(null);
        $modal.find('#discussion-reply-form #description').val('');
        $modal.find('#discussion-reply-form #discussion-reply-btn').html('แสดงความคิดเห็น');
        $modal.find('#discussion-reply-form #label-reply').text('แสดงความคิดเห็น');
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
</script>

<script src="/js/main.js"></script>
<script type="text/javascript">
    setInterval(function () {
        $('.state-status-danger').css('opacity', '0.6');
        $('.state-status-danger').animate({
            opacity: 1
        }, 1000);
    }, 3000);
</script>
</body>
</html>