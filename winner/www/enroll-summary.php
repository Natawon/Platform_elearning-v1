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

$enroll2summary = enroll2summary($enroll['id']);
$topics = $enroll2summary['topics'];

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
    <link href="/bower_components/jquery-confirm/dist/jquery-confirm.min.css" rel="stylesheet">

    <!-- CSS Style -->
    <link href="/css/style.css" rel="stylesheet">
    <link href="/css/custom.css" rel="stylesheet">

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
                        <?php if($courses['course2access']){?>
                            <li><a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/course">เข้าเรียน</a></li>
                        <?php }else{ ?>
                            <li class="disabled"><a href="#">เข้าเรียน</a></li>
                        <?php } ?>
                        <li class="active">ผลการเรียน</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-12 col-sm-12 col-xs-12 panel">
                <h3>สถานะเรียนคุณ <span class='text-active'><?=$members['first_name']." ".$members['last_name']?></span></h3>
                <div class="row panel summary-progress">
                    <div class="<?php if(!$enroll['exam'] and !$enroll['pre_test']){ echo "col-md-4 col-md-offset-4"; }else{ echo "col-md-4 col-md-offset-0";}?> col-sm-5 col-sm-offset-1 col-xs-12 summary-progress_">
                        <h4 class="text-center">บทเรียน <span class="text-active hidden-xs"><br>&nbsp;</span></h4>
                        <!-- <div class="col-md-12 col-sm-12 col-xs-12 graph-line"><img src="/images/graph-line.png" class="img-responsive"></div> -->
                        <div class="row">
                            <div id="courses" class="col-md-12 col-sm-12 col-xs-12"></div>
                        </div>
                    </div>
                    <?php if($courses['exam']){ ?>
                    <div class="col-md-4 col-sm-5 col-xs-12 summary-progress_">
                        <h4 class="text-center">แบบทดสอบเพื่อวัดความรู้ (Examination) <span class='text-active'><br><?php if($enroll['exam']){ echo number_format($enroll2summary['exam']['score'])."/".number_format($enroll2summary['exam']['count'])." คะแนน"; }else{ echo "ยังไม่ได้ทำ"; } ?></span></h4>
                        <!-- <div class="col-md-12 col-sm-12 col-xs-12 graph-line"><img src="/images/graph-line.png" class="img-responsive"></div> -->
                        <div class="row">
                            <div id="exam" class="col-md-12 col-sm-12 col-xs-12"></div>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if($courses['pre_test']){ ?>
                    <div class="col-md-4 col-sm-12 col-xs-12 summary-progress_">
                        <h4 class="text-center">แบบทดสอบก่อนเรียนและหลังเรียน <br>(Pre-Test & Post-Test)</h4>
                        <div class="vspace-40 hidden-xs hidden-sm space-pre-post"></div>
                        <div class="row">
                            <!-- <div class="col-md-12 col-sm-12 col-xs-12 graph-horizontal"><img src="/images/graph-horizontal.png" class="img-responsive"></div> -->
                            <div class="col-sm-8 col-sm-offset-2 col-md-12 col-md-offset-0 test-progress">
                                <div class="row">
                                    <div class="col-md-3 col-sm-3 col-xs-3 text-right">Pre-Test</div>
                                    <div id="pre" class="col-md-6 col-sm-6 col-xs-6 custom-offset"></div>
                                    <div class="col-md-3 col-sm-3 col-xs-3"><span class='text-active'><?php if($enroll['pre_test']){ echo number_format($enroll2summary['pre_test']['score'])."/".number_format($enroll2summary['pre_test']['count'])." คะแนน"; }else{ echo "ยังไม่ได้ทำ"; } ?></span></div>
                                </div>
                            </div>
                            <div class="col-sm-8 col-sm-offset-2 col-md-12 col-md-offset-0">
                                <hr class="divider-dashed">
                            </div>
                            <div class="col-sm-8 col-sm-offset-2 col-md-12 col-md-offset-0 test-progress2">
                                <div class="row">
                                    <div class="col-md-3 col-sm-3 col-xs-3 text-right">Post-Test</div>
                                    <div id="post" class="col-md-6 col-sm-6 col-xs-6 custom-offset"></div>
                                    <div class="col-md-3 col-sm-3 col-xs-3"><span class='text-active'><?php if($enroll['post_test']){ echo number_format($enroll2summary['post_test']['score'])."/".number_format($enroll2summary['post_test']['count'])." คะแนน"; }else{ echo "ยังไม่ได้ทำ"; } ?></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                     <?php if ($enroll2summary['certificate']) { ?>
                        <div class="col-md-12 col-sm-12 col-xs-12 diploma">
                            <i class="fa fa-certificate"></i> ผ่านการเรียนและทดสอบตามที่กำหนด
                            <?php if ($enroll2summary['courses']['download_certificate'] == 1) { ?>
                                &nbsp;คลิกที่นี่เพื่อขอรับวุฒิบัตร &nbsp;
                                <?php if ($groups['multi_lang_certificate'] == 1) { ?>
                                    <div class="btn-group">
                                        <?php if ($members['is_foreign'] == 1) { ?>
                                            <button type="button" class="btn btn-style3 btn-download-certificate" data-id="<?php echo $enroll['id']; ?>" data-cert-lang="en">Request for certification</button>
                                        <?php } else { ?>
                                            <button type="button" class="btn btn-style3 btn-download-certificate" data-id="<?php echo $enroll['id']; ?>" data-cert-lang="th">ขอรับวุฒิบัตร</button>
                                        <?php } ?>
                                        <button type="button" class="btn btn-style3 dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="caret"></span>
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <ul class="dropdown-menu f-18">
                                            <li>
                                                <a class="btn-download-certificate" data-id="<?php echo $enroll['id']; ?>" data-cert-lang="th" role="button">ขอรับวุฒิบัตร (ภาษาไทย)</a>
                                            </li>
                                            <li>
                                                <a class="btn-download-certificate" data-id="<?php echo $enroll['id']; ?>" data-cert-lang="en" role="button">Request for certification (Foreign)</a>
                                            </li>
                                        </ul>
                                    </div>
                                <?php } else { ?>
                                    <button type="button" class="btn btn-style3 btn-download-certificate" data-id="<?php echo $enroll['id']; ?>" data-cert-lang="th">ขอรับวุฒิบัตร</button>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="col-md-12 col-sm-12 col-xs-12 diploma">
                            <i class="fa fa-info-circle"></i>
                            <?php
                            if ($enroll2summary['courses']['download_certificate'] == 1) {
                                echo " ไม่สามารถขอรับวุฒิบัตรได้ เนื่องจากการเรียนหรือทดสอบไม่ผ่านตามที่กำหนด";
                            } else {
                                echo "การเรียนหรือทดสอบไม่ผ่านตามที่กำหนด";
                            }
                            ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="row panel activity">
                    <div class="col-md-12 col-xs-12">
                        <table class="table table-topics-lists">
                            <tbody>
                            <?php if(count($courses['pre_test'])){ ?>
                            <tr>
                                <th scope="row" class="col-md-4 col-xs-4">แบบทดสอบก่อนเรียน (Pre-Test)</th>
                                <td class="col-md-4 col-xs-4 exam-col"><i class="fa fa-question-circle"></i> แบบทดสอบก่อนเรียน (Pre-Test)</td>
                                <?php
                                if ($enroll['pre_test']) {
                                    $classStatus = "text-active";
                                    $textStatus = "ทำแล้ว";
                                } else {
                                    $classStatus = "text-muted";
                                    $textStatus = "ยังไม่ได้ทำ";
                                }
                                ?>
                                <th class="col-md-4 col-xs-4 exam-col <?php echo $classStatus; ?>"><?php echo $textStatus; ?></th>
                            </tr>
                            <?php } ?>

                            <?php
                            foreach($topics as $rs_topics){
                                $rowSpan = count($rs_topics['parent']);
                                foreach($rs_topics['parent'] as $inedx => $rs_parent){ ?>
                                    <?php if ($inedx == 0) { ?>
                                    <tr>
                                        <th rowspan="<?=$rowSpan?>" scope="row" class="col-md-4 col-xs-4"><?=$rs_topics['title']?></th>
                                    <?php } else { ?>
                                    <tr class="multi-row-topics">
                                    <?php } ?>
                                        <td class="col-md-4 col-xs-6 topics-col">

                                                <?php if($courses['not_skip'] == 1){ ?>
                                                    <?php if($rs_parent['enroll2topic']){?>
                                                        <div><i class="fa fa-play-circle"></i> <a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/topics/<?=$rs_parent['id']?>"><?=$rs_parent['title']?></a></div>
                                                        <?php if($rs_parent['quiz']){?><div><i class="fa fa-question-circle"></i> <?=$rs_parent['quiz']['title']?></div><?php } ?>
                                                    <?php }else{ ?>
                                                        <div><i class="fa fa-play-circle"></i> <?=$rs_parent['title']?></div>
                                                        <?php if($rs_parent['quiz']){?><div><i class="fa fa-question-circle"></i> <?=$rs_parent['quiz']['title']?></div><?php } ?>
                                                    <?php } ?>
                                                <?php } ?>
                                                <?php if($courses['not_skip'] == 0){ ?>
                                                    <div class="fa-i"><i class="fa fa-circle"></i> <a href="<?=groupKey($groupKey)?>/enroll/<?=$enroll['id']?>/topics/<?=$rs_parent['id']?>"><?=$rs_parent['title']?></a></div>
                                                    <?php if($rs_parent['quiz']){?><div><i class="fa fa-question-circle"></i> <?=$rs_parent['quiz']['title']?></div><?php } ?>
                                                <?php } ?>

                                        </td>
                                        <?php
                                        if ($rs_parent['percentage'] == 100) {
                                            $classPercentStatus = "text-active";
                                        } else {
                                            $classPercentStatus = "text-muted";
                                        }
                                        ?>
                                        <th class="col-md-4 col-xs-2 topics-percent-col <?php echo $classPercentStatus; ?>">
                                            <div id="topic<?=$rs_parent['id']?>" class="progress-i visible-md visible-lg"></div>
                                            <?php if($rs_parent['enroll2quiz']){?><div class="text-active"><?=$rs_parent['enroll2quiz']['score']?>/<?=$rs_parent['enroll2quiz']['count']?> คะแนน</div><?php }else if($rs_parent['quiz']){ ?><div class="text-muted">ยังไม่ได้ทำ</div><?php } ?>
                                            <span class="visible-xs visible-sm"><?=$rs_parent['percentage']."%"?></span>
                                        </th>
                                    </tr>
                                <?php
                                }
                            }
                            ?>

                            <?php if(count($courses['post_test'])){ ?>
                                <tr>
                                    <th scope="row" class="col-md-4 col-xs-4">แบบทดสอบหลังเรียน (Post-Test)</th>
                                    <td class="col-md-4 col-xs-4 exam-col"><i class="fa fa-question-circle"></i> แบบทดสอบหลังเรียน (Post-Test)</td>
                                    <?php
                                    if ($enroll['post_test']) {
                                        $classStatus = "text-active";
                                        $textStatus = "ทำแล้ว";
                                    } else {
                                        $classStatus = "text-muted";
                                        $textStatus = "ยังไม่ได้ทำ";
                                    }
                                    ?>
                                    <th class="col-md-4 col-xs-4 exam-col <?php echo $classStatus; ?>"><?php echo $textStatus; ?></th>
                                </tr>
                            <?php } ?>
                            <?php if(count($courses['exam'])){ ?>
                                <tr>
                                    <th scope="row" class="col-md-4 col-xs-4">แบบทดสอบเพื่อวัดความรู้ (Examination)</th>
                                    <td class="col-md-4 col-xs-4 exam-col"><i class="fa fa-pencil-square"></i> แบบทดสอบเพื่อวัดความรู้ (Examination)</td>
                                    <?php
                                    if ($enroll['exam']) {
                                        $classStatus = "text-active";
                                        $textStatus = "ทำแล้ว";
                                    } else {
                                        $classStatus = "text-muted";
                                        $textStatus = "ยังไม่ได้ทำ";
                                    }
                                    ?>
                                    <th class="col-md-4 col-xs-4 exam-col <?php echo $classStatus; ?>"><?php echo $textStatus; ?></th>
                                </tr>
                            <?php } ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="surveyModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 class="modal-title" id="myModalLabel"><i class="fa fa-edit"></i> แบบสอบถาม</h3>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 survey">
                        <form id="survey-form" class="survey-form" role="form" data-toggle="validator" enctype="multipart/form-data">
                            <input type="hidden" name="eid" id="eid" value="<?=$enroll['id']?>">
                            <input type="hidden" name="qid" id="qid" value="<?=$courses['survey']['id']?>">
                            <input type="hidden" name="type" id="type" value="<?=$courses['survey']['type']?>">
                                <?php $i=0; foreach($courses['survey']['questions'] as $rs_survey){ $i++;?>
                                        <div class="col-md-12 col-sm-12">
                                            <h4><?=$i.". ".clean_tag_p($rs_survey['questions'])?></h4>
                                            <div class="col-md-12 col-xs-12 col-sm-12">
                                                <div class="row">
                                                    <ol>
                                                        <?php if($rs_survey['type'] == 1) {?>
                                                            <?php foreach($rs_survey['answer'] as $rs_answer){ ?>
                                                                <li>
                                                                    <div class="radio">
                                                                        <label>
                                                                            <input type="radio" name="<?=$rs_survey['id']?>" id="<?=$rs_survey['id']?>" questionsNo="questions<?=$i?>" value="<?=$rs_answer['id']?>"> <?=$rs_answer['answer']?>
                                                                        </label>
                                                                    </div>
                                                                </li>
                                                            <?php } ?>
                                                        <?php } ?>
                                                        <?php if($rs_survey['type'] == 2) {?>
                                                            <?php foreach($rs_survey['answer'] as $rs_answer){ ?>
                                                                <li>
                                                                    <label class="checkbox-inline">
                                                                        <input type="checkbox" name="<?=$rs_survey['id']?>[]" id="<?=$rs_survey['id']?>" questionsNo="questions<?=$i?>" value="<?=$rs_answer['id']?>"> <?=$rs_answer['answer']?>
                                                                    </label>
                                                                </li>
                                                            <?php } ?>
                                                        <?php } ?>
                                                        <?php if($rs_survey['type'] == 3) {?>
                                                            <div class="form-group">
                                                                <textarea class="form-control" name="<?=$rs_survey['id']?>" id="<?=$rs_survey['id']?>" questionsNo="questions<?=$i?>"></textarea>
                                                            </div>
                                                        <?php } ?>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                <?php } ?>
                                <div class="col-md-12 col-sm-12">
                                    <div class="col-md-12 col-xs-12 col-sm-12">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-style1 pull-right" id="submit-an-survey">ส่งแบบสอบถาม <i class="fa fa-arrow-right"></i></button>
                                        </div>
                                    </div>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'include/inc.footer.php'; ?>

<!-- Javascript Library -->
<script src="/bower_components/jquery/dist/jquery.min.js"></script>
<script src="/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="/bower_components/html5shiv/dist/html5shiv.min.js"></script>
<script src="/bower_components/respond/dest/respond.min.js"></script>
<script src="/bower_components/superfish/dist/js/superfish.min.js"></script>
<script src="/bower_components/jquery-confirm/dist/jquery-confirm.min.js"></script>
<script src="/bower_components/progressbar/dist/progressbar.min.js"></script>
<script src="/js/script/config.js"></script>
<script src="/js/script/functions.js"></script>
<script src="/js/script/model/members.js"></script>
<script src="/js/script/model/enroll.js"></script>
<script src="/js/script/model/questions.js"></script>
<script src="/js/script/pages/enroll-summary.js"></script>
<script type="text/javascript">
    var bar = new ProgressBar.SemiCircle('#courses', {
        strokeWidth: 15,
        color: '#FFFFFF',
        trailColor: '#C6C6C6',
        trailWidth: 15,
        easing: 'easeInOut',
        duration: 1400,
        step: function(state, bar) {
            var value = Math.round(bar.value() * 100);
            bar.setText(value + '%');
        }
    });
    bar.path.setAttribute('stroke', '#2a8039');
    bar.text.style.color = '#2a8039';
    bar.text.style.fontSize = '48px';
    var value = Math.round(<?=$enroll2summary['duration2percentage']?>)/100;
    bar.animate(value);
    <?php if($courses['exam']){ ?>
    var bar_exam = new ProgressBar.SemiCircle('#exam', {
        strokeWidth: 15,
        color: '#FFFFFF',
        trailColor: '#C6C6C6',
        trailWidth: 15,
        easing: 'easeInOut',
        duration: 1400,
        step: function(state, bar_exam) {
            var value = Math.round(bar_exam.value() * 100);
            bar_exam.setText(value + '%');
        }
    });
    bar_exam.path.setAttribute('stroke', '#2a8039');
    bar_exam.text.style.color = '#2a8039';
    bar_exam.text.style.fontSize = '48px';
    var value = Math.round(<?=$enroll2summary['exam']['percentage']?>)/100;
    bar_exam.animate(value);
    <?php } ?>

    <?php if($courses['pre_test']){ ?>
    var bar_pre = new ProgressBar.Line('#pre', {
        strokeWidth: 15,
        color: '#FFFFFF',
        trailColor: '#C6C6C6',
        trailWidth: 15,
        easing: 'easeInOut',
        duration: 1400,
        step: function(state, bar_pre) {
            var value = Math.round(bar_pre.value() * 100);
            bar_pre.setText(value + '%');
            // console.log(value);
        }
    });
    bar_pre.path.setAttribute('stroke', '#2a8039');
    bar_pre.text.style.fontSize = '20px';
    var value = Math.round(<?=$enroll2summary['pre_test']['percentage']?>)/100;
    bar_pre.animate(value);
    <?php } ?>

    <?php if($courses['pre_test']){ ?>
    var bar_post = new ProgressBar.Line('#post', {
        strokeWidth: 15,
        color: '#FFFFFF',
        trailColor: '#C6C6C6',
        trailWidth: 15,
        easing: 'easeInOut',
        duration: 1400,
        step: function(state, bar_post) {
            var value = Math.round(bar_post.value() * 100);
            bar_post.setText(value + '%');
        }
    });
    bar_post.path.setAttribute('stroke', '#2a8039');
    bar_post.text.style.fontSize = '20px';
    var value = Math.round(<?=$enroll2summary['post_test']['percentage']?>)/100;
    bar_post.animate(value);
    <?php } ?>

    <?php foreach($topics as $rs_topics){ ?>
        <?php foreach($rs_topics['parent'] as $rs_parent){ ?>

            var bar_<?=$rs_parent['id']?> = new ProgressBar.Line('#topic<?=$rs_parent['id']?>', {
                strokeWidth: 5,
                color: '#FFFFFF',
                trailColor: '#C6C6C6',
                trailWidth: 5,
                easing: 'easeInOut',
                duration: 1400,
                step: function(state, bar_<?=$rs_parent['id']?>) {
                    var value = Math.round(bar_<?=$rs_parent['id']?>.value() * 100);
                    bar_<?=$rs_parent['id']?>.setText(value + '%');
                }
            });
            bar_<?=$rs_parent['id']?>.path.setAttribute('stroke', '#2a8039');
            var value = Math.round(<?=$rs_parent['percentage']?>)/100;
            bar_<?=$rs_parent['id']?>.animate(value);

        <?php } ?>
    <?php } ?>
</script>
<script src="/js/main.js"></script>
</body>
</html>