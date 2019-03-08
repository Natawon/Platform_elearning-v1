<?php
$base = "service/";
include($base."service.php");
if (isset($_GET['group_key'])) {
    $groupKey = cleanGroupKey($_GET['group_key']);
} else {
    $groupKey = "G-Education";
}

$members = session_require();

// Check Tab Active
$allowTab = array("courses", "filter-courses", "orders");
$tabActive = $oFunc->setSpecialString($_GET['tab']);
if ($tabActive != "") {
    if (!in_array($tabActive, $allowTab)) {
        header("Location: /".$groupKey."/my-profile");
        exit();
    }
}

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

if (!empty($groups['questionnaire_packs']) && !empty($groups['questionnaire_packs']['questionnaires'])) {
    $questionnairesReady = true;
} else {
    $questionnairesReady = false;
}


// $mycourse = mycourses();
// $mycourse = mycourses_test();
$filter_course = filter_course($groups['key']);
$courseIdCer = "";
$courses = courses($_GET['course_id'], $groups['key']);
$courseIdCer = $_GET['course_id'];

// if (is_numeric($_GET['course_id'])) {
//     $courseCer = __::find($mycourse, function($o) { return $o['courses']['id'] == $_GET['course_id']; }); // 2
//     if ($courseCer === false) {
//         header("Location: /".$groupKey."/my-profile/courses");
//         exit();
//     }

//     $courseIdCer = $courseCer['courses_id'];
// }

$myorders = myorders();

$avatars_list = avatars_list();

if ($members['avatar_id'] == '') {
    $avatar['avatar_img'] = 'avatar-default.jpg';
    $head_avatar = '<i class="fa fa-user"></i>';
} else {
    $avatar = avatars($members['avatar_id']);
    $head_avatar = "<img width='22' src='".constant("_BASE_DIR_AVATARS").$avatar["avatar_img"]."'>";
}

if ($members['is_foreign'] != 1) {
    include("include/inc.language-th.php");
} else {
    include("include/inc.language-en.php");
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
    <title>My Profile - <?=$configuration['title']?></title>
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
    <link href="/bower_components/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="/bower_components/jquery-ui/jquery-ui.css" rel="stylesheet">
    <!-- CSS Style -->
    <link href="/css/style.css" rel="stylesheet">
    <link href="/css/custom.css" rel="stylesheet">

</head>

<body>
<?php include 'include/inc.header.php'; ?>
<!-- End header -->

<section id="content-list">
    <div class="container">
        <div class='row'>
            <div class="wrapper-myprofile">
                <div class='col-md-12 wrapper-myprofile-content'>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="row">
                                <?php if ($members['groups']['id'] == 3) {
                                    $classMyProfileNav = " high";
                                } else {
                                    $classMyProfileNav = "";
                                } ?>
                                <div class="myprofile-nav<?=$classMyProfileNav?>">
                                    <a href="#tab1" id="1" class="col-xs-12 menu_active" aria-controls="myprofile" role="tab" data-toggle="tab"><i id="menu_icon_1" class="fa fa-user-o menu_icon menu_icon_active" aria-hidden="true"></i> ข้อมูลส่วนตัว</a>
                                    <a href="#tab2" id="2" class="col-xs-12" aria-controls="course" role="tab" data-toggle="tab"><i id="menu_icon_2" class="icon-course-registered2x menu_icon"></i> หลักสูตรที่ลงทะเบียน</a>
                                    <a href="#tab3" id="3" class="col-xs-12" aria-controls="filter-courses" role="tab" data-toggle="tab"><i id="menu_icon_3" class="icon-course-filtered2x menu_icon"></i> หลักสูตรที่เหมาะสม</a>
                                    <a href="#tab4" id="4" class="col-xs-12" aria-controls="orders" role="tab" data-toggle="tab"><i id="menu_icon_4" class="icon-orders2x menu_icon"></i> รายการสั่งซื้อ</a>
                                </div>
                                <input id="params" type="hidden" data-course="<?=$courseIdCer?>" data-cert-lang="<?=$_GET['certLang']?>" value="<?=$tabActive?>">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div role="tabpanel" class="tab fade in active" id="tab1">
                                    <div class="col-md-12 box-inset">
                                        <div class="row">
                                            <div class="header dropdown">
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div id="myprofile-avatar" class="col-xs-6 col-sm-6 col-md-5 b-all-2 b-ra-4 p-t-b-8 text-center cursor-pointer myprofile-avatar">
                                                        <img width="108" id="myprofile-avatar-img" src="<?=constant("_BASE_DIR_AVATARS").$avatar['avatar_img']?>">
                                                        <div id="myprofile-avatar-edit" class="avatar-edit hidden"><i class="fa fa-pencil" aria-hidden="true"></i></div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="col-xs-12 header-name"><?=$fullName?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="content b-t-d-1<?=$classMyProfileNav?> box-inset">
                                                <div class="col-md-11 col-md-offset-1">
                                                    <div class="row b-t-d-1-in">
                                                        <div class="col-xs-4 col-md-4 text-warning text-right"><strong><?=constant('_GROUP')?> : </strong></div>
                                                        <div class="col-xs-8 col-md-8"><?=$members['groups']['title']?></div>
                                                    </div>
                                                    <div class="row b-t-d-1-in">
                                                        <div class="col-xs-4 col-md-4 text-warning text-right"><strong><?=constant('_GROUP_NAME')?> : </strong></div>
                                                        <div class="col-xs-8 col-md-8"><?=$members['groups']['subject']?></div>
                                                    </div>
                                                    <?php if($members['sub_groups']){ ?>
                                                    <div class="row b-t-d-1-in">
                                                        <div class="col-xs-4 col-md-4 text-warning text-right"><strong><?=$members['is_foreign'] != 1 ? $members['groups']['meaning_of_sub_groups_id'] : $members['groups']['meaning_of_sub_groups_id_en']?> : </strong></div>
                                                        <div class="col-xs-8 col-md-8"><?=$members['sub_groups']['title']?></div>
                                                    </div>
                                                    <?php } ?>
                                                    <?php if($members['level_groups']){ ?>
                                                        <div class="row b-t-d-1-in">
                                                            <div class="col-xs-4 col-md-4 text-warning text-right"><strong><?=$members['is_foreign'] != 1 ? $members['groups']['meaning_of_level_groups_id'] : $members['groups']['meaning_of_level_groups_id_en']?> : </strong></div>
                                                            <div class="col-xs-8 col-md-8">
                                                                <?php foreach($members['level_groups'] as $rs_level_groups) {
                                                                    echo $rs_level_groups['title']."<br>";
                                                                } ?>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                    <?php if($members['occupation_id']){ ?>
                                                        <div class="row b-t-d-1-in">
                                                            <div class="col-xs-4 col-md-4 text-warning text-right"><strong><?=$members['is_foreign'] != 1 ? $members['groups']['meaning_of_occupation_id'] : $members['groups']['meaning_of_occupation_id_en']?> : </strong></div>
                                                            <div class="col-xs-8 col-md-8"><?=$members['occupation_id']?></div>
                                                        </div>
                                                    <?php } ?>
                                                    <?php if($groups['id'] == 3){ ?>
                                                        <div class="row b-t-d-1-in">
                                                            <div class="col-xs-4 col-md-4 text-warning text-right"><strong><?=constant('_LICENSE_ID')?> : </strong></div>
                                                            <div class="col-xs-8 col-md-8"> <?=$oFunc->isNull($members['license_id'])?> </div>
                                                        </div>
                                                    <?php } ?>
                                                    <?php if ($members['is_foreign'] != 1 || $groups['multi_lang_certificate'] == 1) { ?>
                                                        <div class="row b-t-d-1-in">
                                                            <div class="col-xs-4 col-md-4 text-warning text-right"><strong>คำนำหน้าชื่อ : </strong></div>
                                                            <div class="col-xs-8 col-md-8"> <?=$oFunc->isNull($members['name_title'])?> </div>
                                                        </div>
                                                        <div class="row b-t-d-1-in">
                                                            <div class="col-xs-4 col-md-4 text-warning text-right"><strong>ชื่อ : </strong></div>
                                                            <div class="col-xs-8 col-md-8"> <?=$oFunc->isNull($members['first_name'])?> </div>
                                                        </div>
                                                        <div class="row b-t-d-1-in">
                                                            <div class="col-xs-4 col-md-4 text-warning text-right"><strong>นามสกุล : </strong></div>
                                                            <div class="col-xs-8 col-md-8"> <?=$oFunc->isNull($members['last_name'])?> </div>
                                                        </div>
                                                    <?php } ?>
                                                    <?php if ($members['is_foreign'] == 1 || $groups['multi_lang_certificate'] == 1) { ?>
                                                        <div class="row b-t-d-1-in">
                                                        <div class="col-xs-4 col-md-4 text-warning text-right"><strong>Name Title : </strong></div>
                                                        <div class="col-xs-8 col-md-8"><?=$oFunc->isNull($members['name_title_en'])?></div>
                                                    </div>
                                                    <div class="row b-t-d-1-in">
                                                        <div class="col-xs-4 col-md-4 text-warning text-right"><strong>First Name : </strong></div>
                                                        <div class="col-xs-8 col-md-8"><?=$oFunc->isNull($members['first_name_en'])?></div>
                                                    </div>
                                                    <div class="row b-t-d-1-in">
                                                        <div class="col-xs-4 col-md-4 text-warning text-right"><strong>Last Name : </strong></div>
                                                        <div class="col-xs-8 col-md-8"><?=$oFunc->isNull($members['last_name_en'])?></div>
                                                    </div>
                                                    <?php } ?>
                                                    <?php if(in_array($members['gender'], ['M','F'])){ ?>
                                                    <div class="row b-t-d-1-in">
                                                        <div class="col-xs-4 col-md-4 text-warning text-right"><strong><?=constant('_GENDER')?> : </strong></div>
                                                        <div class="col-xs-8 col-md-8">
                                                            <?php echo $members['gender'] == "M" ? constant('_MALE') : constant('_FEMALE'); ?>
                                                        </div>
                                                    </div>
                                                    <?php } ?>

                                                    <div class="row b-t-d-1-in">
                                                        <div class="col-xs-4 col-md-4 text-warning text-right"><strong><?=constant('_EMAIL')?>  : </strong></div>
                                                        <div class="col-xs-8 col-md-8"><?=$members['email']?></div>
                                                    </div>
                                                    <?php if($members['id_card']){ ?>
                                                        <div class="row b-t-d-1-in">
                                                            <div class="col-xs-4 col-md-4 text-warning text-right"><strong><?=constant('_ID_CARD')?>  : </strong></div>
                                                            <div class="col-xs-8 col-md-8"><?=$members['id_card']?></div>
                                                        </div>
                                                    <?php } ?>
                                                    <?php if($members['mobile_number']){ ?>
                                                        <div class="row b-t-d-1-in">
                                                            <div class="col-xs-4 col-md-4 text-warning text-right"><strong><?=constant('_TEL')?>  : </strong></div>
                                                            <div class="col-xs-8 col-md-8"><?=$members['mobile_number']?></div>
                                                        </div>
                                                    <?php } ?>
                                                    <?php if($members['birth_date']){ ?>
                                                        <div class="row b-t-d-1-in">
                                                            <div class="col-xs-4 col-md-4 text-warning text-right"><strong><?=constant('_BIRTH_DATE_WITH_FORMAT')?>  : </strong></div>
                                                            <div class="col-xs-8 col-md-8"><?=$members['birth_date']?></div>
                                                        </div>
                                                    <?php } ?>
                                                    <div class="row b-t-d-1-in">
                                                        <div class="col-xs-4 col-md-4 text-warning text-right"><strong><?=constant('_LAST_LOGIN')?> : </strong></div>
                                                        <div class="col-xs-8 col-md-8"><?=$members['last_login']?></div>
                                                    </div>
                                                    <?php if ($groups['internal'] == 1) { ?>
                                                        <div class="row b-t-d-1-in">
                                                            <div class="col-xs-6 col-md-3 col-md-offset-3 p-0">
                                                                <a data-toggle="modal" href="#MyProfileModal" class="btn btn-sm btn-style1 btn-edit w-100"><i class="fa fa-edit top-2"></i> <?=constant('_EDIT_PROFILE')?></a>
                                                            </div>
                                                            <div class="col-xs-6 col-md-3 p-r-0">
                                                                <a data-toggle="modal" href="#ChangePasswordModal" class="btn btn-sm btn-style1 btn-edit w-100"><i class="fa fa-key top-2"></i> <?=constant('_CHANGE_PASSWORD')?></a>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab content2 hidden fade box-inset" id="tab2">
                                    <div class="wrapper-search-mycourses">
                                        <div class="col-xs-10 col-md-4">
                                            <form id="frm-search-mycourses" class="form-horizontal">
                                                <div class="form-group m-b-0">
                                                    <div class="prepend-icon prepend-icon-sm">
                                                        <input id="search_mycourses" type="text" class="form-control form-white" name="search_mycourses" placeholder="ค้นหาหลักสูตรที่ลงทะเบียน" value="<?=(isset($courses) && $courses['title'] ? $courses['title'] : '')?>">
                                                        <i class="fa fa-search c-main" aria-hidden="true"></i>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-xs-2">
                                            <a id="btn-refresh" class="refresh" title="Clear search">
                                                <i class="fa fa-refresh" aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="table-responsive">
                                        <table id="table-mycourses" class="table table-mycourses f-18">
                                            <thead class="f-20">
                                                <tr>
                                                    <th>หลักสูตร</th>
                                                    <th></th>
                                                    <th class="text-center">วันลงทะเบียน</th>
                                                    <th class="text-center">ใช้งานล่าสุด</th>
                                                    <th class="text-center">สถานะ</th>
                                                    <th class="text-center" style="min-width: 160px;">วุฒิบัตร</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="wrapper-pagination-courses">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                ทั้งหมด <strong id="total-mycourses">0</strong> รายการ
                                            </div>
                                            <div class="col-sm-6">
                                                <ul id="pagination-courses" class="pagination"></ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab content2 hidden fade box-inset" id="tab3">
                                    <?php if ($questionnairesReady) { ?>
                                        <div class="text-left">
                                            <button class="btn btn-style1 btn-open-filter-courses" type="button"><i class="fa fa-search" aria-hidden="true"></i> ค้นหาหลักสูตรที่เหมาะสม</button>
                                        </div>
                                    <?php } ?>
                                    <div class="table-responsive">
                                        <table class="table table-mycourses f-18">
                                            <thead class="f-20">
                                                <tr>
                                                    <th>หลักสูตร</th>
                                                    <th style="min-width: 200px;"></th>
                                                    <th>รายละเอียด</th>
                                                    <th class="text-center" style="min-width: 160px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php

                                                if ($filter_course['courses']['data']) {
                                                    foreach($filter_course['courses']['data'] as $filter_courses_list){
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                <a href="<?=groupKey($groupKey)?>/courses/<?=$filter_courses_list['id']?>/info"><img width="120" class="b-all-1" src="<?=getImage(constant("_BASE_DIR_COURSES_THUMBNAIL"), $filter_courses_list['thumbnail'])?>"></a>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <div><strong><?=$filter_courses_list['code']?></strong></div>
                                                                    <div><strong><?=$filter_courses_list['title']?></strong></div>
                                                                </div>
                                                            </td>
                                                            <td class="middle text-left"><?=$oFunc->cutStr($filter_courses_list['subject'], 120)?></td>
                                                            <td class="middle text-center">
                                                                <a href="<?=groupKey($groupKey)?>/courses/<?=$filter_courses_list['id']?>/info" class="btn btn-style1 f-16" role="button">ดูรายละเอียดเพิ่มเติม</a>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                } else {
                                                ?>
                                                    <tr>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">ไม่มีหลักสูตรที่เหมาะสม</td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab content2 hidden fade box-inset" id="tab4">
                                    <div class="table-responsive">
                                        <table class="table table-mycourses f-18">
                                            <thead class="f-20">
                                                <tr>
                                                    <th width="100">เลขที่การสั่งซื้อ</th>
                                                    <th width="120">หลักสูตร</th>
                                                    <th></th>
                                                    <th class="text-center">ราคา</th>
                                                    <th class="text-center">สถานะ</th>
                                                    <th>การชำระเงิน</th>
                                                    <!-- <th class="text-center" style="min-width: 160px;">การชำระเงิน</th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($myorders) {
                                                    // echo "<pre>";
                                                    // print_r($myorders);
                                                    // echo "</pre>";
                                                    foreach($myorders as $rs_myorders){

                                                        if ($rs_myorders['payments']) {
                                                            if ($rs_myorders['payments']['approve_datetime']) {
                                                                $payment_approve_datetime = $oFunc->thai_date_and_time_short(strtotime($rs_myorders['payments']['approve_datetime']));
                                                            }

                                                            if ($rs_myorders['payments']['is_canceled'] == 1) {
                                                                $statusClass = "text-danger";
                                                                $statusText = "ถูกยกเลิก";
                                                            } else {
                                                                switch ($rs_myorders['payments']['payment_status']) {
                                                                    case 'successful': $statusClass  = "text-success"; $statusText = "ชำระเงินเรียบร้อย"; break;
                                                                    case 'pending': $statusClass  = "text-default"; $statusText = "รอชำระเงิน"; break;
                                                                    case 'rejected': $statusClass  = "text-danger"; $statusText  = "ถูกปฏิเสธ"; break;
                                                                    case 'canceled_by_user': $statusClass  = "text-danger"; $statusText  = "ยกเลิกโดยผู้ใช้"; break;
                                                                    case 'failed': $statusClass  = "text-danger"; $statusText  = "เกิดข้อผิดพลาด"; break;
                                                                    default: $statusClass = "text-default"; $statusText = $rs_myorders['payments']['payment_status']; break;
                                                                }
                                                            }
                                                        } else {
                                                            $statusClass = "text-default";
                                                            $statusText = "ไม่มีการชำระเงิน";
                                                        }

                                                    ?>
                                                        <tr>
                                                            <td><span class="m-l-15"><?=$rs_myorders['id']?></span></td>
                                                            <td>
                                                                <a href="<?=groupKey($groupKey)?>/courses/<?=$rs_myorders['courses']['id']?>/info"><img width="120" class="b-all-1" src="<?=getImage(constant("_BASE_DIR_COURSES_THUMBNAIL"), $rs_myorders['courses']['thumbnail'])?>"></a>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <div><strong><?=$rs_myorders['courses_code']?></strong></div>
                                                                    <div><strong><?=$rs_myorders['courses_title']?></strong></div>
                                                                </div>
                                                            </td>
                                                            <td class="middle text-center"><?=number_format($rs_myorders['courses_price'])?> <?=strtoupper($rs_myorders['currency']) == 'THB' ? 'บาท' : $rs_myorders['currency']?></td>
                                                            <td class="middle text-center">
                                                                <strong class="<?=$statusClass?>"><?=$statusText?></strong>
                                                            </td>
                                                            <td>
                                                                <?php if ($rs_myorders['payments'] && $rs_myorders['payments']['payment_status'] == "successful") { ?>
                                                                    <b>ยอดชำระ : </b> <?=number_format($rs_myorders['payments']['amount'])?> <?=strtoupper($rs_myorders['payments']['currency']) == 'THB' ? 'บาท' : $rs_myorders['payments']['currency']?><br>
                                                                    <b>เลขที่อ้างอิง (TXN) : </b> <?=$rs_myorders['payments']['txn']?><br>
                                                                    <b>วิธีชำระเงิน : </b> <?=$rs_myorders['payments']['methods_type']?><br>
                                                                    <b>อนุมัติเมื่อ : </b> <?=$payment_approve_datetime?><br>
                                                                <?php } else { ?>
                                                                    -
                                                                <?php } ?>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    }
                                                } else {
                                                ?>
                                                    <tr>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">ไม่มีรายการสั่งซื้อ</td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-danger">
                                        <strong>หมายเหตุ​ : ใบเสร็จรับเงิน/ใบกำกับภาษีจะส่งทางอีเมล์ที่ระบุภายใน 5 วันทำการนับแต่วันที่มีการชำระเงินสำเร็จ</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="wrapper-survey">

</div>
<?php
foreach($mycourse as $rs_mycourse) {
    $courses = courses($rs_mycourse['courses_id'], $groups['key']);
    ?>
    <div class="modal fade" id="surveyModal-<?=$rs_mycourse['id']?>" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3 class="modal-title" id="myModalLabel"><i class="fa fa-edit"></i> แบบสอบถาม - <?=$rs_mycourse['courses']['title']?></h3>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 survey">
                            <form id="survey-form-<?=$rs_mycourse['id']?>" class="survey-form" role="form" data-toggle="validator" enctype="multipart/form-data">
                                <input type="hidden" name="eid" id="eid" value="<?=$rs_mycourse['id']?>">
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
                                                <button type="button" class="btn btn-style1 pull-right" id="submit-an-survey-<?=$rs_mycourse['id']?>">ส่งแบบสอบถาม <i class="fa fa-arrow-right"></i></button>
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
    <?php
}
?>

<?php include 'include/inc.footer.php'; ?>

<div class="modal fade" id="modalAvatar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header b-none bg-orange">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h3 class="modal-title" id="myModalLabel">Choose your Avatar</h3>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12 choose-avatar">
                        <?php foreach($avatars_list as $rs_avatar){ ?>
                        <div data-id="<?=$rs_avatar['id']?>" class="col-xs-6 col-sm-6 col-md-3 thumbnail-avatar cursor-pointer"><img width="65" src="<?=constant("_BASE_DIR_AVATARS").$rs_avatar['avatar_img']?>"></div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer b-none">
                <div class="row">
                    <div class="col-xs-12 text-right">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($groups['internal'] == 1) { ?>
    <div class="modal fade" id="MyProfileModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-user"></i> <?=constant('_EDIT_PROFILE')?></h4>
                </div>
                <div class="modal-body">

                    <?php if (!empty($groups['contact_profile_editing'])) { ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="f-85-p f-bold text-muted m-b-10"> <?=$groups['contact_profile_editing']?> </div>
                            </div>
                        </div>
                    <?php } ?>

                    <form id="edit-profile-form" method="post" role="form" data-toggle="validator" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label>หมายเลขโทรศัพท์มือถือ <span class="text-danger">*</span></label>
                                    <input type="text" id="mobile_number" name="mobile_number" value="<?=$members['mobile_number']?>" class="form-control required"  placeholder="หมายเลขโทรศัพท์มือถือ">
                                </div>
                                <div class="form-group">
                                    <label>วันเกิด <span class="text-danger">*</span></label>
                                    <input type="text" id="birth_date" name="birth_date" value="<?=$members['birth_date']?>" class="form-control birth_date required"  placeholder="วันเกิด">
                                </div>
                                <div class="form-group">
                                    <label>ระดับการศึกษา <span class="text-danger">*</span></label>
                                    <div class="styled-select">
                                        <select name="education_level_id" id="education_level_id" class="form-control">
                                            <option value="1" <?=$members['education_level_id'] == 1 ? 'selected' : ''?>>ต่ำกว่าปริญญาตรี</option>
                                            <option value="2" <?=$members['education_level_id'] == 2 ? 'selected' : ''?>>ปริญญาตรี</option>
                                            <option value="3" <?=$members['education_level_id'] == 3 ? 'selected' : ''?>>ปริญญาโท</option>
                                            <option value="4" <?=$members['education_level_id'] == 4 ? 'selected' : ''?>>ปริญญาเอก</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button class="button_fullwidth submit-btn">ยืนยันการแก้ไข</button>
                        <button type="button" data-dismiss="modal" class="button_fullwidth-2">ยกเลิก</button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal ChangePassword -->
    <div class="modal fade" id="ChangePasswordModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-lock"></i> <?=constant('_CHANGE_PASSWORD')?></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <form id="change-password-form" method="post" role="form" data-toggle="validator" class="form-horizontal" enctype="multipart/form-data">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="old_password"><?=constant('_OLD_PASSWORD')?></label>
                                        <input type="password" id="old_password" name="old_password" class="form-control required" placeholder="<?=constant('_OLD_PASSWORD')?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="new_password"><?=constant('_NEW_PASSWORD')?></label>
                                        <div class="input-group">
                                            <input type="password" id="new_password" name="new_password" class="form-control required" placeholder="<?=constant('_NEW_PASSWORD')?>">
                                            <span class="input-group-addon hint-addon" id="hint-addon"><i class="fa fa-question-circle icon-popover hint-password" rel="popover"></i></span>
                                        </div>
                                        <div class="progress password-meter" id="passwordMeter">
                                            <div class="progress-bar"></div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="password_confirmation"><?=constant('_CONFIRM_PASSWORD')?></label>
                                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control required" placeholder="<?=constant('_CONFIRM_PASSWORD')?>">
                                    </div>
                                </div>
                                <div class="text-right">
                                    <button id="btn-change-password" class="btn btn-style1"><?=constant('_CHANGE_PASSWORD')?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<!-- Javascript Library -->
<script src="/bower_components/jquery/dist/jquery.min.js"></script>
<script src="/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="/bower_components/html5shiv/dist/html5shiv.min.js"></script>
<script src="/bower_components/respond/dest/respond.min.js"></script>
<script src="/bower_components/superfish/dist/js/superfish.min.js"></script>
<script src="/bower_components/noty/lib/noty.min.js"></script>
<script src="/bower_components/jquery-confirm/dist/jquery-confirm.min.js"></script>
<script src="/bower_components/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="/bower_components/bootstrap-datepicker/locales/bootstrap-datepicker.th.min.js"></script>
<script src="/bower_components/jquery-ui/jquery-ui.min.js"></script>


<script src="/bower_components/josecebe-twbs-pagination/jquery.twbsPagination.min.js"></script>
<script src="/js/script/config.js"></script>
<script src="/js/script/functions.js"></script>
<script src="/js/script/model/members.js"></script>
<script src="/js/script/model/enroll.js"></script>
<script src="/js/script/model/questions.js"></script>
<script src="/js/script/model/filter-courses.js"></script>
<script src="/js/script/model/myprofile.js"></script>
<script src="/js/script/pages/myprofile.js"></script>
<script src="/js/formvalidation.js"></script>
<script src="/js/main.js"></script>
<script>
    var mycourses_data = {
        page_data: {
            current_page: 1,
            pagination_status: 0,
            total: 0,
            is_change: false,
            enableSetupPage: true
        },
        tmp_data: {
            list: '',
            loading: '',
            buttonCertificate: '',
            survey: '',
            survey_questions: '',
            survey_answer: ''
        },
        search: ''
    };


    var languageData = {
        'initial': 'th',
        'btn_txt': 'ดาวน์โหลด',
        'language_txt': '(ภาษาไทย)'
    };

    var groupKey = '<?=$groupKey?>';
    var eleMyCourseTableBody;
    var _BASE_DIR_COURSES_THUMBNAIL = '<?=constant("_BASE_DIR_COURSES_THUMBNAIL")?>';
    var multi_lang_certificate = '<?=$groups['multi_lang_certificate']?>';
    var is_foreign = '<?=$members['is_foreign']?>';
    var course_id = '<?=$courses['id']?>';
    var course_title = '<?=$courses['title']?>';
    if (course_title) {
        mycourses_data.search = course_title;
    }

    if (is_foreign == 1) {
        languageData.initial = 'en';
        languageData.btn_txt = 'Download';
        languageData.language_txt = '(Foreign)';
    }

    eleMyCourseTableBody = $('#table-mycourses').find('tbody');

    function loadMyCourse(page, search) {
        // Loading
        // $('#total-mycourses').html('<i class="fa fa-refresh fa-spin fa-fw"></i>');
        $('html,body').animate({
            scrollTop: $('#tab2').offset().top - 100
        }, 'slow');

        mycourses_data.tmp_data.loading = '';
        mycourses_data.tmp_data.loading  += '<tr>'+
                            '<td colspan="6">'+
                            '<h3 class="text-center text-muted"><i class="fa fa-refresh fa-spin fa-fw"></i> กำลังโหลดข้อมูล</h3>'+
                            '</td>'+
                        '</tr>';
        eleMyCourseTableBody.html(mycourses_data.tmp_data.loading);

        var deferredMyCourseList;
        deferredMyCourseList = _myprofile.getCoursesList(page, search);
        deferredMyCourseList.done(function(response) {
            mycourses_data.page_data.current_page = response.current_page;

            var dataMyCourseList;
            dataMyCourseList = response.data.filter(function (el) { return el != null; });
            dataMyCourseListNull = response.data.filter(function (el) { return el == null; });
            if (Number.isInteger(dataMyCourseListNull.length)) {
                response.total -= dataMyCourseListNull.length;
            }

            if (dataMyCourseList.length > 0) {
                mycourses_data.page_data.pagination_status = 1;
                mycourses_data.tmp_data.list = '';

                var currentGroup = fns.currentGroup();

                $.each(dataMyCourseList, function(index, value) {
                    mycourses_data.tmp_data.buttonCertificate = '';
                    if (dataMyCourseList[index].certificate && dataMyCourseList[index].courses.download_certificate == 1) {
                        if (multi_lang_certificate == 1) {
                            mycourses_data.tmp_data.buttonCertificate += '<div class="btn-group">'+
                                                        '<button type="button" class="btn btn-style1 btn-download-certificate f-16" data-course="' + dataMyCourseList[index].courses.id + '" data-id="' + dataMyCourseList[index].id + '" data-cert-lang="' + languageData.initial + '">' + languageData.btn_txt + '</button>'+
                                                        '<button type="button" class="btn btn-style1 dropdown-toggle f-16" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'+
                                                            '<span class="caret"></span>'+
                                                            '<span class="sr-only">Toggle Dropdown</span>'+
                                                        '</button>'+
                                                        '<ul class="dropdown-menu f-16">'+
                                                            '<li>'+
                                                                '<a class="btn-download-certificate btn-dropdown" data-course="' + dataMyCourseList[index].courses.id + '" data-id="' + dataMyCourseList[index].id + '" data-cert-lang="th" role="button">ดาวน์โหลด (ภาษาไทย)</a>'+
                                                            '</li>'+
                                                            '<li>'+
                                                                '<a class="btn-download-certificate btn-dropdown" data-course="' + dataMyCourseList[index].courses.id + '" data-id="' + dataMyCourseList[index].id + '" data-cert-lang="en" role="button">Download (Foreign)</a>'+
                                                            '</li>'+
                                                        '</ul>'+
                                                    '</div>';
                        } else {
                            mycourses_data.tmp_data.buttonCertificate += '<button type="button" class="btn btn-style1 btn-download-certificate f-16" data-course="' + dataMyCourseList[index].courses.id + '" data-id="' + dataMyCourseList[index].id + '" data-cert-lang="th">ดาวน์โหลด</button>';
                        }
                    } else {
                        mycourses_data.tmp_data.buttonCertificate = '-';
                    }

                    mycourses_data.tmp_data.list += '<tr>'+
                                    '<td>'+
                                        '<a href="/'+ currentGroup + '/courses/' + dataMyCourseList[index].courses.id +'/info"><img width="120" class="b-all-1" src="' + fns.getImage(_BASE_DIR_COURSES_THUMBNAIL, dataMyCourseList[index].courses.thumbnail) + '"></a>'+
                                    '</td>'+
                                    '<td>'+
                                        '<div>'+
                                            '<div><strong>' + dataMyCourseList[index].courses.code + '</strong></div>'+
                                            '<div><strong>' + dataMyCourseList[index].courses.title + '</strong></div>'+
                                        '</div>'+
                                    '</td>'+
                                    '<td class="middle text-center">' + dataMyCourseList[index].statusData.enroll_datetime + '</td>'+
                                    '<td class="middle text-center">' + dataMyCourseList[index].statusData.last_datetime + '</td>'+
                                    '<td class="middle text-center ' + dataMyCourseList[index].statusData.status_color + '">'+
                                        '<div class="row"><strong>' + dataMyCourseList[index].statusData.status + '</strong></div>'+
                                        '<div><strong>' + dataMyCourseList[index].statusData.status_datetime + '</strong></div>'+
                                    '</td>'+
                                    '<td class="middle text-center">'+
                                        mycourses_data.tmp_data.buttonCertificate
                                    '</td">'+
                                '</tr>';
                });
            } else if (dataMyCourseList.length === 0) {
                // Alert
                mycourses_data.page_data.pagination_status = 1;
                response.last_page = 1;
                mycourses_data.tmp_data.list = '<tr>'+
                                '<td colspan="6" class="text-center text-muted">ไม่มีหลักสูตรที่ลงทะเบียน</td>'+
                            '</tr>';
                mycourses_data.page_data.enableSetupPage = true;
            }

            eleMyCourseTableBody.html(mycourses_data.tmp_data.list);
            console.log('Setup Content Succuss.');
            // $('#total-mycourses').html(response.total);

            // console.log(mycourses_data.page_data.enableSetupPage);
            if (mycourses_data.page_data.enableSetupPage == true) {
                mycourses_data.page_data.enableSetupPage = false;
                $('#pagination-courses').twbsPagination('destroy');
                loadPagination(mycourses_data.page_data.pagination_status, response.last_page, response.total);
            }

            if (course_id) {
                console.log('Click');
                if (multi_lang_certificate == 1) {
                    $('.btn-dropdown.btn-download-certificate[data-course="'+$('#params').data('course')+'"][data-cert-lang="'+$('#params').data('cert-lang')+'"').trigger('click');
                } else {
                    $('.btn-download-certificate[data-course="'+$('#params').data('course')+'"][data-cert-lang="'+$('#params').data('cert-lang')+'"').trigger('click');
                }
            }
        });
    }

    function loadPagination(pagination_status, last_page, total) {
        if (pagination_status == 1) {
            var visiblePages = 5;
            var screen_width = screen.width;
            if (screen_width < 768) {
                visiblePages = 3;
            }
            $('#pagination-courses').twbsPagination({
                totalPages: last_page,
                visiblePages: visiblePages,
                prev: '<span aria-hidden="true">«</span> ย้อนกลับ',
                next: 'ถัดไป <span aria-hidden="true">»</span>',
                firstClass: 'hidden',
                lastClass: 'hidden',
                onPageClick: function (event, page) {
                    // console.log('mycourses_data.page_data.current_page: ' + mycourses_data.page_data.current_page);
                    // console.log('page: ' + page);
                    if (mycourses_data.page_data.current_page != page) {
                        var search_mycourses = $('#search_mycourses').val();
                        mycourses_data.page_data.current_page = page;
                        loadMyCourse(page, search_mycourses);
                    }
                }
            });
        } else {
            $('#pagination-courses').empty();
        }

        $('#total-mycourses').html(total);
    }

    function createSurveyModal(enrollId, courseId) {
        var deferredMyCourse;
        deferredMyCourse = _myprofile.getCourse(groupKey, courseId);
        deferredMyCourse.done(function(response) {
            // console.log(response);
            var course_data = response;
            mycourses_data.tmp_data.survey = '';

            var surveyQuestions = course_data.survey.questions
            mycourses_data.tmp_data.survey_questions = '';
            $.each(surveyQuestions, function(index, value) {

                surveyQuestions[index].questions = fns.cleanTagP(surveyQuestions[index].questions);

                if (surveyQuestions[index].type == 1 || surveyQuestions[index].type == 2) {
                    var surveyQuestionsAnswer = surveyQuestions[index].answer;

                    mycourses_data.tmp_data.survey_answer = '';
                    $.each(surveyQuestionsAnswer, function(index_answer, value) {
                        if (surveyQuestions[index].type == 1) {
                            mycourses_data.tmp_data.survey_answer += '<li>'+
                                                                        '<div class="radio">'+
                                                                            '<label>'+
                                                                                '<input type="radio" name="' + surveyQuestions[index].id + '" id="' + surveyQuestions[index].id + '" questionsNo="questions' + index + '" value="' + surveyQuestionsAnswer[index_answer].id + '"> ' + surveyQuestionsAnswer[index_answer].answer +
                                                                            '</label>'+
                                                                        '</div>'+
                                                                    '</li>';
                        } else {
                            mycourses_data.tmp_data.survey_answer += '<li>'+
                                                                        '<label class="checkbox-inline">'+
                                                                            '<input type="checkbox" name="' + surveyQuestions[index].id + '[]" id="' + surveyQuestions[index].id + '" questionsNo="questions' + index + '" value="' + surveyQuestionsAnswer[index_answer].id + '"> ' + surveyQuestionsAnswer[index_answer].answer +
                                                                        '</label>'+
                                                                    '</li>';
                        }
                    });
                } else if (surveyQuestions[index].type == 3) {
                    mycourses_data.tmp_data.survey_answer += '<div class="form-group">'+
                                                                '<textarea class="form-control" name="' + surveyQuestions[index].id + '" id="' + surveyQuestions[index].id + '" questionsNo="questions<?=$i?>"></textarea>'+
                                                            '</div>';
                }

                var questions_no = index + 1;
                mycourses_data.tmp_data.survey_questions += '<div class="col-md-12 col-sm-12">'+
                                                                '<h4>' + questions_no + '. ' + surveyQuestions[index].questions + '</h4>'+
                                                                '<div class="col-md-12 col-xs-12 col-sm-12">'+
                                                                    '<div class="row">'+
                                                                        '<ol>'+
                                                                            mycourses_data.tmp_data.survey_answer +
                                                                        '</ol>'+
                                                                    '</div>'+
                                                                '</div>'+
                                                            '</div>';
            });

            mycourses_data.tmp_data.survey += '<div class="modal fade" id="surveyModal-' + enrollId + '" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">'+
                                                    '<div class="modal-dialog">'+
                                                        '<div class="modal-content">'+
                                                            '<div class="modal-header">'+
                                                                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>'+
                                                                '<h3 class="modal-title" id="myModalLabel"><i class="fa fa-edit"></i> แบบสอบถาม - ' + course_data.title + '</h3>'+
                                                            '</div>'+
                                                            '<div class="modal-body">'+
                                                                '<div class="row">'+
                                                                    '<div class="col-md-12 survey">'+
                                                                        '<form id="survey-form-' + enrollId + '" class="survey-form" role="form" data-toggle="validator" enctype="multipart/form-data">'+
                                                                            '<input type="hidden" name="eid" id="eid" value="' + enrollId + '">'+
                                                                            '<input type="hidden" name="qid" id="qid" value="' + course_data.survey.id + '">'+
                                                                            '<input type="hidden" name="type" id="type" value="' + course_data.survey.type + '">'+
                                                                            mycourses_data.tmp_data.survey_questions +
                                                                            '<div class="col-md-12 col-sm-12">'+
                                                                                '<div class="col-md-12 col-xs-12 col-sm-12">'+
                                                                                    '<div class="form-group">'+
                                                                                        '<button type="button" class="btn btn-style1 pull-right" id="submit-an-survey-' + enrollId + '">ส่งแบบสอบถาม <i class="fa fa-arrow-right"></i></button>'+
                                                                                    '</div>'+
                                                                                '</div>'+
                                                                            '</div>'+
                                                                        '</form>'+
                                                                    '</div>'+
                                                                '</div>'+
                                                            '</div>'+
                                                        '</div>'+
                                                    '</div>'+
                                                '</div>';

            $('.wrapper-survey').html(mycourses_data.tmp_data.survey);
            $('#surveyModal-' + enrollId).modal('show');
        });
    }

    $(document).ready(function() {
        loadPagination(1, 1, 0);
        loadMyCourse(mycourses_data.page_data.current_page, mycourses_data.search);

        $('#frm-search-mycourses').submit(function(event) {
            var search_mycourses = $(this).find('#search_mycourses').val();

            loadMyCourse(mycourses_data.page_data.current_page, search_mycourses);

            return false;
        });

        $('#btn-refresh').on('click', function(event) {
            event.preventDefault();
            $('#search_mycourses').val('');
            loadMyCourse(mycourses_data.page_data.current_page, '');
            /* Act on the event */
        });


        jQuery.ajaxPrefilter( function( options, originalOptions, jqXHR ) {
            options.crossDomain ={
                crossDomain: true
            };
            options.xhrFields = {
                withCredentials: true
            };
        });

        $('.birth_date').datepicker({
        // language: "th-TH",
        // endDate: "0d",
        // startView: 2,
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        defaultDate: new Date(),
        yearRange: "-100:+0",
        
        isBuddhist: true,

        }).on('changeDate', function(e) {
            $(this).closest('form').formValidation('revalidateField', 'birth_date');
        });

        // $('.birth_date').datepicker({
        //     // language: "th-TH",
        //     endDate: "-5y",
        //     startView: 2
        // }).on('changeDate', function(e) {
        //     $(this).closest('form').formValidation('revalidateField', 'birth_date');
        // });

        // IMPORTANT: You must call .steps() before calling .formValidation()
        $('#edit-profile-form, #edit-profile-form-foreign')
            .formValidation({
                framework: 'bootstrap',
                excluded: ':disabled',
                fields: {
                    birth_date: {
                        validators: {
                            notEmpty: {
                                message: 'The birth date is required'
                            },
                            date: {
                                format: 'YYYY-MM-DD',
                                message: 'The birth date is not a valid date'
                            }
                        }
                    },
                    mobile_number: {
                        validators: {
                            notEmpty: {
                                message: 'The mobile number is required'
                            },
                            digits: {
                                message: 'The value is not contains only digits'
                            },
                            stringLength: {
                                message: 'The value must be between 9 and 10 characters',
                                min: 9,
                                max: 10
                            }
                        }
                    },
                    education_level_id: {
                        validators: {
                            notEmpty: {
                                message: 'The education degree is required'
                            },
                            numeric: {
                                message: 'The education degree must be a number'
                            }
                        }
                    },
                }
            })
            .on('err.form.fv', function(e) {
                var $this = $(this);
                if ($this.find(".has-error:first").length) {
                    $('#MyProfileModal').animate({
                        scrollTop: ($(this).find(".has-error:first").offset().top - 100)
                    }, 500);
                }
            })
            .on('success.form.fv', function(e) {
                var $this = $(this);
                var modalPopup = "#MyProfileModal";
                var defaultMsg = 'ยืนยันการแก้ไข';
                var waitingMsg = 'กรุณารอสักครู่ ระบบกำลังตรวจสอบข้อมูล...';
                var successMsg = 'แก้ไขข้อมูลเรียบร้อย';

                if ($this.attr('id') == "edit-profile-form-foreign") {
                    defaultMsg = 'Confirm Edit Profile';
                    waitingMsg = 'Please wait...';
                    successMsg = 'Successfully edited profile';
                }

                $this.find('.submit-btn').html(waitingMsg).prop('disabled', true);
                $.post('/api/site/user/edit_profile', $this.serializeObject(), function(data) {
                    if(data.is_error == false){
                        $this.find('.submit-btn').html(successMsg);
                        notification("success", data.message);
                        window.location.reload();
                    }
                    if(data.is_error == true){
                        setTimeout(function() {
                            $this.find('.submit-btn').html(defaultMsg).prop('disabled', false);
                        }, 800);
                        notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง" );
                    }
                })
                .fail(function(resp) {
                    var data = resp.responseJSON;
                    setTimeout(function() {
                        $this.find('.submit-btn').html(defaultMsg).prop('disabled', false);
                    }, 800);
                    fns.handleError(resp, $this, modalPopup)
                    // notification("error", data.message !== undefined ? data.message : "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง" );
                });
                return true;
            });

        $('input[type=radio][name=name_title], input[type=radio][name=name_title_en]').on('change', function(event) {
            if ($(this).val() == "other") {
                if ($(this).attr('name') == "name_title") {
                    $('#name_title_other').prop('disabled', false).removeClass('hide');
                } else {
                    $('#name_title_other_en').prop('disabled', false).removeClass('hide');
                }
                $('.hide-for-name-title').show();
                $('.hide-for-name-title-other').hide();
                $('.custom-for-name-title').css('margin-top', 'inherit');
                $('.box-captcha').css('min-height', '91px');
            } else {
                if ($(this).attr('name') == "name_title") {
                    $('#name_title_other').prop('disabled', true).addClass('hide');
                } else {
                    $('#name_title_other_en').prop('disabled', true).addClass('hide');
                }
                $('.hide-for-name-title').hide();
                $('.hide-for-name-title-other').show();
                $('.custom-for-name-title').css('margin-top', '-6px');
                $('.box-captcha').css('min-height', 'inherit');
            }
        });

        $('.btnFormThai').on('click', function(event) {
            $(this).addClass('active');
            $('.btnFormForeign').removeClass('active');
            // $('#is_foreign').val(0);
            $('#edit-profile-form-foreign, .label-foreign').fadeOut('fast', function() {
                $('#edit-profile-form, .label-thai').fadeIn();
            });
        });

        $('.btnFormForeign').on('click', function(event) {
            $(this).addClass('active');
            $('.btnFormThai').removeClass('active');
            // $('#is_foreign').val(1);
            $('#edit-profile-form, .label-thai').fadeOut('fast', function() {
                $('#edit-profile-form-foreign, .label-foreign').fadeIn();
            });
        });

        <?php if ($members['is_foreign'] == 1) { ?>
            if ($('.btnFormForeign').length) {
                $('.btnFormForeign').trigger('click');
            } else {
                $('#edit-profile-form, .label-thai').fadeOut('fast', function() {
                    $('#edit-profile-form-foreign, .label-foreign').fadeIn();
                });
            }
        <?php } ?>

        // Change Password
        $('#change-password-form')
            .formValidation({
                framework: 'bootstrap',
                excluded: ':disabled',
                fields: {
                    old_password: {
                        validators: {
                            notEmpty: {
                                message: 'The old password is required'
                            }
                        }
                    },
                    new_password: {
                        validators: {
                            notEmpty: {
                                message: 'The new password is required'
                            },
                            callback: {
                                callback: function(value, validator, $field) {
                                    var score = 0;

                                    if (value === '') {
                                        return {
                                            valid: true,
                                            score: null
                                        };
                                    }

                                    // Check the password strength
                                    score += ((value.length >= 8) ? 1 : -1);

                                    if(value.length >= 8){
                                        score += 1;
                                    }

                                    // The password contains lowercase character
                                    if (/[a-z]/.test(value)) {
                                        score += 1;
                                    }

                                    // The password contains number
                                    if (/[0-9]/.test(value)) {
                                        score += 1;
                                    }

                                    // The password contains special characters
                                    // if (/[!#$%&^~*_@]/.test(value)) {
                                    //     score += 1;
                                    // }

                                    var $bar  = $('#passwordMeter').find('.progress-bar');

                                    switch (true) {
                                        case (score === null):
                                            $bar.html('').css('width', '0%').removeClass().addClass('progress-bar');
                                            break;

                                        case (score <= 0):
                                            $bar.html('Very weak').css('width', '25%').removeClass().addClass('progress-bar progress-bar-danger');
                                            break;

                                        case (score > 0 && score <= 2):
                                            $bar.html('Weak').css('width', '50%').removeClass().addClass('progress-bar progress-bar-warning');
                                            break;

                                        case (score > 2 && score <= 3):
                                            $bar.html('Medium').css('width', '75%').removeClass().addClass('progress-bar progress-bar-warning-2');
                                            break;

                                        case (score > 3):
                                            $bar.html('Strong').css('width', '100%').removeClass().addClass('progress-bar progress-bar-success');
                                            break;

                                        default:
                                            break;
                                    }

                                    if (score < 4) {
                                        return {
                                            valid: false,
                                            // message: 'The password is weak.'
                                        }
                                    }

                                    return {
                                        valid: true,
                                        score: score    // We will get the score later
                                    };
                                }
                            }
                        }
                    },
                    password_confirmation: {
                        validators: {
                            identical: {
                                field: 'new_password',
                                message: 'The new password and its confirm are not the same'
                            },
                            notEmpty: {
                                message: 'The confirm password is required'
                            }
                        }
                    }
                }
            })
            .on('success.form.fv', function(e) {
                var $this = $(this);
                var modalPopup = "#ChangePasswordModal";
                var param = JSON.stringify($('#change-password-form').serializeObject());

                $this.find('#btn-change-password').prop('disabled', true);

                members.changePassword(param).done(function(resp) {
                    notification("success", resp.message);
                    window.location.reload();
                }).fail(function(resp) {
                    setTimeout(function() {
                        $this.find('#btn-change-password').prop('disabled', false);
                    }, 800);
                    fns.handleError(resp, $this, modalPopup);
                });
                return true;
            });

            $('#ChangePasswordModal').on('show.bs.modal', function (e) {
                $('#change-password-form')[0].reset();
                $('#change-password-form').data('formValidation').resetForm();
                $('#passwordMeter').html('<div class="progress-bar"></div>');
            });

    });
</script>
</body>
</html>