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

$courses = courses($enroll['courses_id'], $groups['key']);
$topics = $enroll['topics'];

$exam2score = exam2score($_GET['enroll2quiz_id']);
$exam = $exam2score['exam'];

if($exam['type'] == '1'){ $exam['type'] = 'pre-test'; }
if($exam['type'] == '4'){ $exam['type'] = 'post-test'; }
if($exam['type'] == '3'){ $exam['type'] = 'exam'; }
if($exam['type'] == '2'){ $exam['type'] = 'quiz'; }
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
                <div class="col-md-3 col-sm-12 time-line">
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
                                                    <?=$rs_parent['title']?>
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
                <div class="col-md-9 col-sm-9 exam">

                        <h3 class="text-center"><?=$exam['title']?></h3>
                        <div class="col-md-12 col-sm-12 panel">
                            <form id="exam-form" class="exam-form" role="form" data-toggle="validator" enctype="multipart/form-data">
                            <?php $i=0; $count=count($exam['questions2answer']); foreach($exam['questions2answer'] as $rs_questions2answer){ $i++;?>
                            <fieldset id="fieldset<?=$rs_questions2answer['id']?>">
                                <div class="col-md-12 col-sm-12">
                                    <h4><?=$i.". ".clean_tag_p($rs_questions2answer['questions']['questions'])?></h4>
                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                        <div class="row">
                                        <ol>
                                            <?php if($rs_questions2answer['questions']['type'] == 1) {?>
                                                <?php foreach($rs_questions2answer['questions']['answer'] as $rs_answer){ ?>
                                                    <li class="<?php if($rs_answer['correct'] == 1){?>alert-success<?php }else if(($rs_answer['correct'] != 1) and ($rs_answer['questions2answer'])){ ?>alert-danger<?php } ?>" >
                                                        <div class="radio">
                                                            <label><input type="radio" <?php if($rs_answer['questions2answer']){ echo "checked"; }?> disabled> <?=$rs_answer['answer']?></label>
                                                        </div>
                                                    </li>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if($rs_questions2answer['questions']['type'] == 2) {?>
                                                <?php foreach($rs_questions2answer['questions']['answer'] as $rs_answer){ ?>
                                                    <li class="<?php if($rs_answer['correct'] == 1){?>alert-success<?php }else if($rs_answer['correct'] != 1 and $rs_answer['questions2answer']){ ?>alert-danger<?php } ?>" >
                                                        <label class="checkbox-inline"><input type="checkbox" <?php if($rs_answer['questions2answer']){ echo "checked"; }?> disabled> <?=$rs_answer['answer']?></label>
                                                    </li>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if($rs_questions2answer['questions']['type'] == 3) {?>
                                                <div class="form-group">
                                                    <textarea class="form-control" disabled><?=$rs_questions2answer['answer_text']?></textarea>
                                                </div>
                                            <?php } ?>
                                        </ol>
                                        </div>
                                    </div>
                                </div>
                                <?php if($rs_questions2answer['questions']['answer_info']) {?>
                                <div class="col-md-12 col-sm-12">
                                    <h3>คำอธิบายเฉลย</h3>
                                    <?=$rs_questions2answer['questions']['answer_info']?>
                                </div>
                                <?php } ?>
                                <div class="col-md-6 col-md-offset-3 col-sm-6 col-sm-offset-3 btn-questions">
                                    <?php if($i != 1){?><button type="button" class="btn btn-style3 pull-left btn-previous"><i class="fa fa-arrow-left"></i> ข้อก่อนหน้า</button><?php } ?>
                                    <?php if($i != $count){?><button type="button" class="btn btn-style1 pull-right btn-next">ข้อถัดไป <i class="fa fa-arrow-right"></i></button><?php } ?>
                                    <?php if($i == $count){?><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/exam/<?=$exam2score['id']?>/score" type="button" class="btn btn-style1 pull-right" id="submit-an-answer">กลับสู่ผลคะแนน <i class="fa fa-arrow-right"></i></a><?php } ?>
                                </div>
                            </fieldset>
                            <?php } ?>
                            </form>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12 text-center">
                                <ul class="pagination">
                                    <?php $i=0; foreach($exam['questions2answer'] as $rs_questions2answer){ $i++;?>
                                    <li><a href="javascript:fieldset(<?=$rs_questions2answer['id']?>);"><?=$i?></a></li>
                                    <?php } ?>
                                </ul>
                            </div>
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
<script src="/bower_components/noty/lib/noty.min.js"></script>
<script src="/js/script/config.js"></script>
<script src="/js/script/functions.js"></script>
<script src="/js/script/model/members.js"></script>
<script type="text/javascript">
    function fieldset(fieldsetID){
        $('fieldset').hide();
        $('#fieldset'+fieldsetID).fadeIn(400);
    }
    $(document).ready(function () {
        $('.exam-form fieldset:first-child').fadeIn();
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
        // previous step
        $('.exam-form .btn-previous').on('click', function () {
            $(this).parents('fieldset').fadeOut(400, function () {
                $(this).prev().fadeIn();
            });
        });
    });
</script>
<script src="/js/main.js"></script>
</body>
</html>