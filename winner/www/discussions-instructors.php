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

$instructor = instructor_session_require();

$configuration = configuration();
$groups = groups($groupKey);

$courses = courses($_GET['id'], $groups['key']);

$instructorInCourse = array_filter($courses['instructors'], function($course_instructor) use ($instructor) {
    return $course_instructor['code'] == $instructor['code'];
});

if (empty($courses) || empty($courses['id'])) {
  header("Location: /".$groupKey."/list");
  exit();
}

if($instructor){
    $groups2id = groups2id($instructor['groups_id']);
    if($groups2id['id'] != $groups['id']){ header('Location: '.constant("_BASE_SITE_URL").'/'.$groups2id['key']); }
}

if(!$groups){ header('Location: '.constant("_PAGE_404"));}
if ($groups['status'] != 1) {
    header('Location: '.constant("_PAGE_404"));
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
    #content-info-page {
        min-height: 570px;
    }
    </style>

</head>

<body>
<?php include 'include/inc.header-instructors.php'; ?>
<!-- End header -->

<section id="content-info-page">
<input type="hidden" id="param_courses_id" value="<?=$courses['id']?>">
<?php if ($instructor && !empty($instructorInCourse)) { ?>
<h2 class="text-center title-page <?=$courses['categories']['css_class']?>"><?=$courses['code']." : ".$courses['title']?></h2>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-style-1 m-t-20">
                <div class="panel-body">
                    <h3 class="m-t-0"><i class="fa fa-comments"></i> กระดานสนทนาสำหรับวิทยากร</h3>
                    <?php if ($courses['is_discussion'] == 1) { ?>
                        <!-- <div class="collapse" id="collapseDiscussionForm">
                            <button id="btnToggleDiscussionFormIn" type="button" data-toggle="collapse" data-target="#collapseDiscussionForm" aria-expanded="false" aria-controls="collapseDiscussionForm" class="btn btn-link btn-anchor-set f-18 p-l-5"><i class="fa fa-plus f-11"></i> ตั้งหัวเรื่องใหม่</button>
                            <div class="panel" style="padding-top:20px;">
                                <form id="discussion-form" method="post" role="form" data-toggle="validator" enctype="multipart/form-data">
                                    <input type="hidden" name="enroll" id="enroll" value="<?php // $enroll['id']?>">
                                    <div class="form-group clearfix">
                                        <div class="col-md-12"><label>ตั้งหัวเรื่อง</label> </div>
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
                        </div> -->

                        <!-- <div class="">
                            <div class="col-xs-6 p-l-5">
                                <div class="text-left">
                                    <button id="btnToggleDiscussionFormOut" type="button" data-toggle="collapse" data-target="#collapseDiscussionForm" aria-expanded="false" aria-controls="collapseDiscussionForm" class="btn btn-link btn-anchor-set f-18"><i class="fa fa-plus f-11"></i> ตั้งหัวเรื่องใหม่</button>
                                </div>
                            </div>
                            <div class="col-xs-6 p-r-5">
                                <div class="text-right">
                                    <button id="btnReloadDiscussion" type="button" class="btn btn-link btn-anchor f-18"><i class="fa fa-refresh f-11"></i> โหลดข้อมูลอีกครั้ง</button>
                                </div>
                            </div>
                        </div> -->

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
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } else { ?>
    <?php if ($instructor) { ?>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h3 class="m-t-50"><i class="fa fa-exclamation-circle"></i> ไม่พบกระดานสนทนา</h3>
                </div>
            </div>
        </div>
    <?php } ?>
<?php } ?>
</section>

<?php if (!empty($instructorInCourse) && $courses['is_discussion'] == 1) { ?>
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
                        <div class="form-group clearfix">
                            <div class="col-md-12"><label id="label-reply">แสดงความคิดเห็น</label> </div>
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

<?php if (!$instructor) { ?>
    <div class="modal fade" id="accessModal" tabindex="-1" role="dialog" aria-labelledby="accessModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="accessModalLabel">กรุณากรอกรหัสผ่าน</h4>
                </div>
                <form id="instructors-login-frm" role="form" data-toggle="validator">
                    <div class="modal-body">
                            <div class="form-group">
                                <!-- <label for="code" class="control-label">รหัสผ่าน</label> -->
                                <input id="code" type="password" class="form-control" name="code">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-style1" id="btnInstructorLogin">ยืนยัน</button>
                    </div>
                </form>
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
    <script src="/bower_components/jquery-confirm/dist/jquery-confirm.min.js"></script>
    <script src="/bower_components/moment/min/moment-with-locales.min.js"></script>
    <script src="/js/formvalidation.js"></script>
    <script src="/bower_components/lodash/dist/lodash.min.js"></script>
    <script src="/js/script/config.js"></script>
    <script src="/js/script/functions.js"></script>
    <script src="/js/script/model/instructors.js"></script>
    <script src="/js/script/model/discussion.js"></script>
    <script src="/js/script/pages/discussions-instructors.js"></script>

    <script src="/js/main.js"></script>
</body>
</html>