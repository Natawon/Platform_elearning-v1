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

$members = session_require();
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

$enroll = enroll($_GET['id']);
$members = session_require('learning', $enroll['courses_id']);

if ($members['avatar_id'] == '') {
    $head_avatar = '<i class="fa fa-user"></i>';
} else {
    $avatar = avatars($members['avatar_id']);
    $head_avatar = "<img width='22' src='".constant("_BASE_DIR_AVATARS").$avatar["avatar_img"]."'>";
}

$courses = courses($enroll['courses_id'], $groups['key']);
$topics = $enroll['topics'];

if($_GET['type'] == 'pre-test'){ $exam = $courses['pre_test']; $enroll_exam = $enroll['pre_test']; }
if($_GET['type'] == 'post-test'){ $exam = $courses['post_test']; $enroll_exam = $enroll['post_test']; }
if($_GET['type'] == 'exam'){ $exam = $courses['exam']; $enroll_exam = $enroll['exam']; }
if($_GET['type'] == 'quiz'){ $quiz = quiz($enroll['id'], $_GET['quiz_id']);  $exam = $quiz['data']; $enroll_exam = $quiz['quiz'];  $quiz2topic = $quiz['quiz2topic'];}
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
    <link href="/bower_components/noty/lib/noty.css" rel="stylesheet">
    <link href="/bower_components/jquery-confirm/dist/jquery-confirm.min.css" rel="stylesheet">

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
                                                        <?php if ($rs_parent['quiz']) { ?><span class="sub-line <?php if ($rs_parent['quiz']['id'] == $_GET['quiz_id']) { echo "in-active"; } ?>"><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/<?=$rs_parent['quiz']['id']?>/quiz"><?=$rs_parent['quiz']['title']?></a></span><?php } ?>
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
                                                        <?php if ($rs_parent['quiz']) { ?><span class="sub-line <?php if ($rs_parent['quiz']['id'] == $_GET['quiz_id']) { echo "in-active"; } ?>"><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/<?=$rs_parent['quiz']['id']?>/quiz"><?=$rs_parent['quiz']['title']?></a></span><?php } ?>
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
                                                <?php if ($rs_parent['quiz']) { ?><span class="sub-line <?php if ($rs_parent['quiz']['id'] == $_GET['quiz_id']) { echo "in-active"; } ?>"><?=$rs_parent['quiz']['title']?></span><?php } ?>
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
                                                    <?php if ($rs_parent['quiz']) { ?><span class="sub-line <?php if ($rs_parent['quiz']['id'] == $_GET['quiz_id']) { echo "in-active"; } ?>"><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/<?=$rs_parent['quiz']['id']?>/quiz"><?=$rs_parent['quiz']['title']?></a></span><?php } ?>
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
                                                    <?php if ($rs_parent['quiz']) { ?><span class="sub-line <?php if ($rs_parent['quiz']['id'] == $_GET['quiz_id']) { echo "in-active"; } ?>"><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/<?=$rs_parent['quiz']['id']?>/quiz"><?=$rs_parent['quiz']['title']?></a></span><?php } ?>
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
                <div class="col-md-9 col-sm-9 exam">

                    <div id="exam-information">
                        <h3 class="text-center"><?=$exam['title']?></h3>
                        <div class="col-md-12 col-sm-12 description">
                            <?=$exam['description']?>
                        </div>
                        <div class="col-md-12 col-sm-12 description-list">
                            <div class="row">
                                <p class="text-left"><strong class="col-md-3 col-sm-3">จำนวนคำถาม</strong> <span class="col-md-9 col-sm-9"><?=count($exam['questions'])?> ข้อ</span></p>
                            </div>
                        </div>
                        <?php if($exam['time']) { ?>
                            <div class="col-md-12 col-sm-12 description-list">
                                <div class="row">
                                    <p class="text-left"><strong class="col-md-3 col-sm-3">จำกัดเวลา</strong> <span class="col-md-9 col-sm-9"><?=$exam['time']?> นาที</span></p>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if($exam['passing_score']) { ?>
                            <div class="col-md-12 col-sm-12 description-list">
                                <div class="row">
                                    <p class="text-left"><strong class="col-md-3 col-sm-3">เกณฑ์การทำข้อสอบ</strong> <span class="col-md-9 col-sm-9 col-xs-12">ผู้เรียนต้องทำคะแนนให้ได้เกณฑ์มากกว่า <?=$exam['passing_score']?> %</span></p>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if($exam['take_new_exam'] > 1){ ?>
                            <div class="col-md-12 col-sm-12 description-list">
                                <div class="row">
                                    <p class="text-left"><strong class="col-md-3 col-sm-3">การสอบใหม่</strong> <span class="col-md-9 col-sm-9 col-xs-12">สอบใหม่ได้ <?=$exam['take_new_exam']?> ครั้ง (คุณใช้ไปแล้ว <?=count($enroll_exam)?> ครั้ง)</span></p>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-12 col-sm-12 text-center">

                                <?php if($exam['type'] == 1){?>
                                    <?php if($enroll['pre_chk']['learning']){ ?>
                                        <h4 class="alert-success text-center col-md-4 col-md-offset-4">ทำแบบทดสอบเรียบร้อย</h4>
                                    <?php }else{ ?>
                                        <button type="button" onclick="startQuiz(<?=$enroll['id']?>, <?=$exam['id']?>, <?=$exam['type']?>);" class="btn btn-style1" id="btnExam">เริ่มแบบทดสอบ</button>
                                    <?php } ?>
                                 <?php } ?>
                                <?php if($exam['type'] == 2){?>
                                    <?php if($quiz['quiz_chk']['learning']){ ?>
                                        <h4 class="alert-success text-center col-md-4 col-md-offset-4">ทำแบบทดสอบเรียบร้อย</h4>
                                    <?php }else{ ?>
                                        <button type="button" onclick="startQuiz(<?=$enroll['id']?>, <?=$exam['id']?>, <?=$exam['type']?>);" class="btn btn-style1" id="btnExam">เริ่มแบบทดสอบ <span id="spDot"></span><span id="secDot"></span></button>
                                    <?php } ?>
                                <?php } ?>
                                <?php if($exam['type'] == 4){?>
                                    <?php if($enroll['post_chk']['learning']){ ?>
                                        <h4 class="alert-success text-center col-md-4 col-md-offset-4">ทำแบบทดสอบเรียบร้อย</h4>
                                    <?php }else{ ?>
                                        <button type="button" onclick="startQuiz(<?=$enroll['id']?>, <?=$exam['id']?>, <?=$exam['type']?>);" class="btn btn-style1" id="btnExam">เริ่มแบบทดสอบ</button>
                                    <?php } ?>
                                <?php } ?>
                                <?php if($exam['type'] == 3){?>
                                    <?php if($enroll['exam_chk']['learning']){ ?>
                                        <?php if($exam['passing_score']){?>
                                            <h4 class="alert-success text-center col-md-4 col-md-offset-4">ทำแบบทดสอบผ่านเรียบร้อย</h4>
                                        <?php }else{ ?>
                                            <button type="button" onclick="startQuiz(<?=$enroll['id']?>, <?=$exam['id']?>, <?=$exam['type']?>);" class="btn btn-style1" id="btnExam">เริ่มแบบทดสอบ</button>
                                        <?php } ?>
                                    <?php }else{ ?>
                                        <button type="button" onclick="startQuiz(<?=$enroll['id']?>, <?=$exam['id']?>, <?=$exam['type']?>);" class="btn btn-style1" id="btnExam">เริ่มแบบทดสอบ</button>
                                    <?php } ?>
                                <?php } ?>


                            <label id="alertExam" class="alert-danger"></label>
                        </div>
                    </div>

                    <div id="exam-quiz">
                        <h3 class="text-left">
                            <?=$exam['title']?>
                            <?php if($exam['time']){ ?><span class="pull-right" id="timer"><i class="fa fa-clock-o"></i> 00:00:00</span><?php } ?>
                        </h3>
                        <div class="col-md-12 col-sm-12 panel">
                            <?php
                            $i = 0;
                            $count = count($exam['questions']);
                            ?>
                            <form id="exam-form" data-all-questions="<?=$count?>" class="exam-form" role="form" data-toggle="validator" enctype="multipart/form-data">
                            <?php foreach($exam['questions'] as $rs_questions){ $i++;?>
                            <fieldset id="fieldset<?=$rs_questions['id']?>">
                                <div class="col-md-12 col-sm-12">
                                    <h4><?=$i.". ".clean_tag_p($rs_questions['questions'])?></h4>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <ol>
                                                <?php if ($rs_questions['type'] == 1) {?>
                                                    <?php foreach($rs_questions['answer'] as $rs_answer){ ?>
                                                        <li id="answer<?=$rs_answer['id']?>" class="">
                                                            <div class="radio">
                                                                <label>
                                                                    <input type="radio" class="answer-choice" name="<?=$rs_questions['id']?>" id="<?=$rs_questions['id']?>" questionsNo="questions<?=$i?>" value="<?=$rs_answer['id']?>"> <?=$rs_answer['answer']?>
                                                                </label>
                                                            </div>
                                                        </li>
                                                    <?php } ?>
                                                <?php } else if ($rs_questions['type'] == 2) {?>
                                                    <?php foreach($rs_questions['answer'] as $rs_answer){ ?>
                                                        <li id="answer<?=$rs_answer['id']?>" class="">
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="answer-choice" name="<?=$rs_questions['id']?>[]" id="<?=$rs_questions['id']?>" questionsNo="questions<?=$i?>" value="<?=$rs_answer['id']?>"> <?=$rs_answer['answer']?>
                                                            </label>
                                                        </li>
                                                    <?php } ?>
                                                <?php } else if ($rs_questions['type'] == 3) {?>
                                                    <div class="form-group">
                                                        <textarea class="form-control answer-choice" name="<?=$rs_questions['id']?>" id="<?=$rs_questions['id']?>" questionsNo="questions<?=$i?>"></textarea>
                                                    </div>
                                                <?php } ?>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <div class="col-md-12 btn-questions text-center">
                                    <?php if($i != 1){?><button type="button" class="btn btn-style3 btn-previous btn-style-xxs"><i class="fa fa-arrow-left"></i> ข้อก่อนหน้า</button><?php } ?>
                                    <?php if($exam['answer_submit']){?>
                                        <?php if($i != $count){?><button type="button" class="btn btn-style1 btn-next-some-answer btn-style-xxs" id="next-some-answer<?=$rs_questions['id']?>">ข้อถัดไป <i class="fa fa-arrow-right"></i></button><?php } ?>
                                        <button type="button" class="btn btn-style1 btn-style-xxs" id="submit-some-answer<?=$rs_questions['id']?>" onclick="submitAnswer(<?=$rs_questions['id']?>)">ส่งคำตอบ <i class="fa fa-arrow-right"></i></button>
                                        <?php if($i == $count){?><button type="button" class="btn btn-style1 btn-next-answer-score btn-style-xxs" onclick="answerScore()" id="next-some-answer<?=$rs_questions['id']?>">ผลการสอบ <i class="fa fa-arrow-right"></i></button><?php } ?>
                                    <?php }else{ ?>
                                        <?php if($i != $count){?><button type="button" class="btn btn-style1 btn-next btn-style-xxs">ข้อถัดไป <i class="fa fa-arrow-right"></i></button><?php } ?>
                                        <?php if($i == $count){?><button type="button" class="btn btn-style1 btn-style-xxs" id="submit-an-answer">ส่งคำตอบ <i class="fa fa-arrow-right"></i></button><?php } ?>
                                    <?php } ?>
                                </div>
                            </fieldset>
                            <?php } ?>
                                <input type="hidden" name="enroll2quiz" id="enroll2quiz" value="">
                                <input type="hidden" name="questionsCount" id="questionsCount" value="<?=$exam['questions_count']?>">
                            </form>
                            <input type="hidden" name="quiz2topic" id="quiz2topic" value="<?=isset($quiz2topic['id']) ? $quiz2topic['id'] : ''?>">
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12 text-center">
                                <ul class="pagination">
                                    <?php $i=0; foreach($exam['questions'] as $rs_questions){ $i++;?>
                                    <li class="questions<?=$i?>"><a href="javascript:fieldset(<?=$rs_questions['id']?>);"><?=$i?></a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php
                    // echo "<pre>";
                    // print_r($exam);
                    // echo "</pre>";
                    ?>
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
<script src="/bower_components/noty/lib/noty.min.js"></script>
<script src="/bower_components/jquery-confirm/dist/jquery-confirm.min.js"></script>
<script src="/js/script/config.js"></script>
<script src="/js/script/functions.js"></script>
<script src="/js/script/model/members.js"></script>
<script type="text/javascript">
    <?php if(isset($quiz2topic['auto_quiz']) && !$quiz['quiz_chk']['learning']){?>
        function funcstartQuiz(){
            setTimeout(
                function(){
                    startQuiz(<?=$enroll['id']?>, <?=$exam['id']?>, <?=$exam['type']?>);
                }
            ,5000)
        }
        function funcWrite(){ setTimeout("fncWriteDot();",800) }
        var sec = 5;
        function fncWriteDot(){
            if(sec<1) {
                sec = 1;
            }
            spDot.innerHTML = sec;
            secDot.innerHTML = ' sec.';
            funcWrite();
            sec--;
        }

        $(document).ready(function () {
            funcWrite();
            funcstartQuiz();
        });
    <?php } ?>

    function fieldset(fieldsetID){
        $('fieldset').hide();
        $('#fieldset'+fieldsetID).fadeIn(400);
    }

    <?php if($exam['time']){ ?>

    function pad(number, length) {
        var str = '' + number;
        while (str.length < length) {
            str = '0' + str;
        }
        return str;
    }

    function cdtd(timeDiff) {

        var timeDiff_sec = timeDiff;
        timeDiff_sec= timeDiff_sec - 1;
        var days = Math.floor(timeDiff_sec / 86400);
        var hours = Math.floor((timeDiff_sec % 86400)/3600);
        var minutes = Math.floor(((timeDiff_sec % 86400)%3600)/60);
        var seconds = ((timeDiff_sec % 86400)%3600)%60;
        hours %= 24;
        minutes %= 60;
        seconds %= 60;
        $("#timer").html('<i class="fa fa-clock-o"></i> ' + pad(hours,2) + ':' + pad(minutes,2) + ':' +pad(seconds,2));

        var timer = setTimeout(function(){cdtd(timeDiff_sec)},1000);

        if (timeDiff <= 1) {
            notification("error","หมดเวลา");
            clearTimeout(timer);
            setTimeout(function () {
                <?php if(isset($quiz2topic['auto_quiz'])){?>
                    window.location.href = '<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/course';
                <?php }else{ ?>
                    location.reload();
                <?php } ?>
            },2500);
        }
    }
    <?php } ?>

    function startQuiz(eid, qid, type){
        $('#btnExam').hide();
        $('#alertExam').fadeIn(400, function (){
            $.post('/api/site/enroll2quiz', {eid: eid, qid: qid, type: type}, function(data) {
                if(data.is_error == false){
                    $('#exam-information').fadeOut(400, function () {
                        $('#exam-quiz').fadeIn(400, function () {
                            if(data.enroll2quiz){
                                $('#enroll2quiz').val(data.enroll2quiz);
                                <?php if($exam['time']){ ?>
                                    cdtd(<?=$exam['time']?> * 60);
                                <?php } ?>
                            }
                        });
                    });
                }
                if(data.is_error == true){
                    $('#alertExam').html(data.message);
                }
            });
        });
    }

    <?php if($exam['answer_submit']){?>
    function submitAnswer(qid){
        var enroll2quiz = $('#enroll2quiz').val();
        var quiz2topic = $('#quiz2topic').val();
        var questionsCount = $('#questionsCount').val();
        var answer = $('#exam-form #'+qid).serialize()+'&enroll2quiz='+enroll2quiz+'&qid='+qid+'&quiz2topic='+quiz2topic+'&questionsCount='+questionsCount;
        if (!$('#exam-form #'+qid).serialize()) {
            fns.handleAlert('แจ้งเตือน', 'ไม่สามารถส่งคำตอบได้ เนื่องจากท่านยังไม่เลือกคำตอบ');
        }else{
            $('#exam-form #'+qid).prop('disabled', true);
            $('#submit-some-answer'+qid).hide();
            $('#next-some-answer'+qid).show();
            $.post('/api/site/questions2answer_single', answer, function(data) {
                for (var i = 0; i < data.answer.length; i++) {
                    if(data.answer[i].correct == 1){
                        $('#answer'+data.answer[i].id).addClass("alert-success");
                    }
                }
            });
        }
    }

    function answerScore() {
        window.location.href = '<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/'+ $('#enroll2quiz').val() +'/score';
    };
    <?php } ?>

    $(document).ready(function () {


        $('.exam-form input').on('click', function () {
            var activeQuestionsNo = $(this).attr("questionsNo");
            $('.pagination .'+ activeQuestionsNo).addClass('active');
        });

        $('.exam-form textarea').bind('input propertychange', function () {
            var activeQuestionsNo = $(this).attr("questionsNo");
            $('.pagination .'+ activeQuestionsNo).addClass('active');
        });

        $('.exam-form fieldset:first-child').fadeIn();

        <?php if($exam['answer_submit']){?>
        // next step
        $('.exam-form .btn-next-some-answer').on('click', function () {
            var parent_fieldset = $(this).parents('fieldset');
            var next_step = true;
            if (next_step) {
                parent_fieldset.fadeOut(400, function () {
                    $(this).next().fadeIn();
                });
            }
        });
        <?php }else{ ?>
        // next step
        $('.exam-form .btn-next').on('click', function () {
            var parent_fieldset = $(this).parents('fieldset');
            var next_step = true;
            if (next_step) {
                parent_fieldset.fadeOut(400, function () {
                    $(this).next().fadeIn();
                });
            }
        });
        <?php } ?>

        // previous step
        $('.exam-form .btn-previous').on('click', function () {
            $(this).parents('fieldset').fadeOut(400, function () {
                $(this).prev().fadeIn();
            });
        });
        <?php if(!$exam['answer_submit']){?>
        // submit
        $('#submit-an-answer').on('click', function () {
            var $examForm = $('#exam-form');
            var countMissing = 0;
            var quiz2topic = $('#quiz2topic').val();
            var questionsCount = $('#questionsCount').val();

            if (Object.keys($examForm.serializeObject()).length - 2 != $examForm.data('all-questions')) {
                fns.handleAlert('แจ้งเตือน', 'ไม่สามารถส่งคำตอบได้ เนื่องจากท่านทำข้อสอบไม่ครบทุกข้อตามที่กำหนดไว้');
            } else {
                $.each($examForm.serializeObject(), function(key, value) {
                    if (key !== "enroll2quiz" && key !== "questionsCount") {
                        if (typeof value === "object") {
                            if (value.length === undefined || value.length === 0) {
                                countMissing++;
                                return false;
                            }
                        } else if (value === null || value == "") {
                            countMissing++;
                            return false;
                        }
                    }
                });

                if (countMissing > 0) {
                    fns.handleAlert('แจ้งเตือน', 'ไม่สามารถส่งคำตอบได้ เนื่องจากท่านทำข้อสอบไม่ครบทุกข้อตามที่กำหนดไว้');
                } else {
                    $.confirm({
                        theme: 'supervan',
                        title: '<?=$exam['title']?>',
                        content: '',
                        buttons: {
                            confirm: {
                                text: "ยืนยันการส่งคำตอบ",
                                keys: ['enter', 'shift'],
                                action: function(){
                                    $.post('/api/site/questions2answer', $('#exam-form').serialize()+'&quiz2topic='+quiz2topic+'&questionsCount='+questionsCount, function(data) {
                                        window.location.href = '<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/'+ $('#enroll2quiz').val() +'/score';
                                    });
                                }
                            },
                            cancel: {
                                text: "ยกเลิก"
                            }
                        }
                    });
                }
            }
        });
        <?php } ?>
    });
</script>
<script src="/js/main.js"></script>
</body>
</html>