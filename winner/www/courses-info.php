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

$courses = courses($_GET['id'], $groups['key']);

if (empty($courses) || empty($courses['id'])) {
  header("Location: /".$groupKey."/list");
  exit();
}

$related = $courses['related'];

if($members){
    $groups2id = groups2id($members['groups_id']);
    if($groups2id['id'] != $groups['id']){ header('Location: '.constant("_BASE_SITE_URL").'/'.$groups2id['key']); }
}

if(!$groups){ header('Location: '.constant("_PAGE_404"));}
if ($groups['status'] != 1) {
    header('Location: '.constant("_PAGE_404"));
}

$enrollByCourse = enrollByCourse($courses['id']);

if ($members['avatar_id'] == '') {
    $head_avatar = '<i class="fa fa-user"></i>';
} else {
    $avatar = avatars($members['avatar_id']);
    $head_avatar = "<img width='22' src='".constant("_BASE_DIR_AVATARS").$avatar["avatar_img"]."'>";
}

$alreadyPersonal = false;
$alreadyCorporate = false;

if (!empty($members['inv_personal_first_name']) && !empty($members['inv_personal_last_name']) && !empty($members['inv_personal_tax_id']) && !empty($members['inv_personal_email']) && !empty($members['inv_personal_tel']) && !empty($members['inv_personal_address'])) {
    $alreadyPersonal = true;
}

if (!empty($members['inv_corporate_name']) && !empty($members['inv_corporate_tax_id']) && !empty($members['inv_corporate_email']) && !empty($members['inv_corporate_tel']) && !empty($members['inv_corporate_address'])) {
    $alreadyCorporate = true;
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
                    <li class="active">รายละเอียดหลักสูตร</li>

                    <?php if (!empty($enrollByCourse)) { ?>
                        <?php if($courses['classrooms_targets']) { ?>
                        <li class="">
                            <a href="#" class="" onclick="enroll('<?=$courses['id']?>', '<?=$groupKey?>', '<?=$courses['classrooms_targets']['id']?>', '1')">เข้าเรียน</a>
                        </li>
                        <?php } else if($courses['classrooms_level_groups']) { ?>
                        <li class="">
                            <a href="#" class="" onclick="enroll('<?=$courses['id']?>', '<?=$groupKey?>', '<?=$courses['classrooms_level_groups']['id']?>', '2')">เข้าเรียน</a>
                        </li>
                        <?php } else if($courses['targets']) { ?>
                        <li class="">
                            <a href="#" class="" onclick="enroll('<?=$courses['id']?>', '<?=$groupKey?>', '', '3')">เข้าเรียน</a>
                        </li>
                        <?php } else if($courses['level_groups']) { ?>
                        <li class="">
                            <a href="#" class="" onclick="enroll('<?=$courses['id']?>', '<?=$groupKey?>', '<?=$courses['level_groups']['id']?>', '4')">เข้าเรียน</a>
                        </li>
                        <?php } else if($courses['level_public']) { ?>
                        <li class="">
                            <a href="#" class="" onclick="enroll('<?=$courses['id']?>', '<?=$groupKey?>', '', '')" class>เข้าเรียน</a>
                        </li>
                        <?php } else { ?>
                        <li class="disabled">
                            <a href="#" class="">เข้าเรียน</a>
                        </li>
                        <?php } ?>

                        <li class=""><a href="#" id="btnViewSummary" data-course="<?=$courses['id']?>" class="">ผลการเรียน</a></li>

                    <?php } else { ?>
                        <li class="disabled"><a href="#" class="">เข้าเรียน</a></li>
                        <li class="disabled"><a href="#" class="">ผลการเรียน</a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="col-md-12 col-sm-12 content-info-header">
            <div class="row">
                <div class="col-md-4 col-sm-4">
                    <div class="embed-responsive embed-responsive-16by9 responsive-player">
                        <?php if($courses['review_streaming_url']){?>
                            <div id="player"></div>
                        <?php } else { ?>
                            <img src="<?=getImage(constant("_BASE_DIR_COURSES_THUMBNAIL"),$courses['thumbnail'])?>" class="img-responsive">
                        <?php } ?>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4 content-info-header_">
                    <div class="row">
                        <div class="col-xs-2 col-sm-3 col-md-2"><img src="/images/icon1.png" class="img-responsive"></div>
                        <div class="col-xs-9 col-sm-9 col-md-9">
                            <h4>วิทยากร</h4>
                            <?php foreach ($courses['instructors'] as $rs_instructors) { ?>
                            <p>
                                <?php if (!empty($rs_instructors['pdf'])) { ?>
                                <a href="<?=constant("_BASE_DIR_INSTRUCTORS_PDF").$rs_instructors['pdf']?>" target="_blank">
                                    <?=$rs_instructors['title']?> <?=!empty($rs_instructors['short_remark']) ? "(".$rs_instructors['short_remark'].")" : ""?>
                                </a>
                                <?php } else { ?>
                                    <?=$rs_instructors['title']?> <?=!empty($rs_instructors['short_remark']) ? "(".$rs_instructors['short_remark'].")" : ""?>
                                <?php } ?>
                            </p>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-2 col-sm-3 col-md-2"><img style="margin-left: 3px;" src="/images/icon2.png" class="img-responsive"></div>
                        <div class="col-xs-9 col-sm-9 col-md-9">
                            <h4>ระยะเวลารวม</h4>
                            <p><?=$courses['duration']?></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-2 col-sm-3 col-md-2"><img style="margin-left: 3px;" src="/images/icon3.png" class="img-responsive"></div>
                        <div class="col-xs-9 col-sm-9 col-md-9">
                            <h4>ค่าธรรมเนียม</h4>
                            <?php if ($courses['free']) { ?><p class="free">ฟรี</p><?php } else { ?><p><?=number_format($courses['price'])." บาท"?></p><?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4">
                <?php if(!$courses['enroll_chk'] && !$courses['free'] && !$courses['confirm_order']) { ?>
                    <?php if ($courses['pending_order']) { ?>
                        <button disabled="" class="btn btn-style1 col-md-8 col-md-offset-2 col-xs-12">ชำระค่าธรรมเนียม</button>
                        <span class="label label-default col-md-8 col-md-offset-2 col-xs-12 label-order-staus m-t-5">รอชำระเงิน</span>
                    <?php } else { ?>
                        <button class="btn btn-style1 col-md-8 col-md-offset-2 col-xs-12" id="btnTaxInvoiceModal">ชำระค่าธรรมเนียม</button>
                    <?php } ?>
                    <?php if($courses['classrooms_targets']) { ?>
                    <div class="col-md-8 col-lg-offset-2 calendar">
                        <div class="row">
                            <div class="col-md-12 calendar_">
                                <div class="row">
                                    <div class="col-md-2 text-center start"><i class="fa fa-calendar"></i></div>
                                    <div class="col-md-10 text-left">
                                        <div class="row">
                                            <span class="col-md-12">เริ่มเรียน</span>
                                            <span class="col-md-12 start"><?=DateTime_TH($courses['classrooms_targets']['start_datetime'])?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 calendar_">
                                <div class="row">
                                    <div class="col-md-2 text-center end"><i class="fa fa-calendar"></i></div>
                                    <div class="col-md-10 text-left">
                                        <div class="row">
                                            <span class="col-md-12">วันสิ้นสุด</span>
                                            <span class="col-md-12 end"><?=DateTime_TH($courses['classrooms_targets']['end_datetime'])?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php }else if($courses['classrooms_level_groups']) { ?>
                    <div class="col-md-8 col-lg-offset-2 calendar">
                        <div class="row">
                            <div class="col-md-12 calendar_">
                                <div class="row">
                                    <div class="col-md-2 text-center start"><i class="fa fa-calendar"></i></div>
                                    <div class="col-md-10 text-left">
                                        <div class="row">
                                            <span class="col-md-12">เริ่มเรียน</span>
                                            <span class="col-md-12 start"><?=DateTime_TH($courses['classrooms_level_groups']['start_datetime'])?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 calendar_">
                                <div class="row">
                                    <div class="col-md-2 text-center end"><i class="fa fa-calendar"></i></div>
                                    <div class="col-md-10 text-left">
                                        <div class="row">
                                            <span class="col-md-12">วันสิ้นสุด</span>
                                            <span class="col-md-12 end"><?=DateTime_TH($courses['classrooms_level_groups']['end_datetime'])?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                <?php } else { ?>
                    <?php if($courses['classrooms_targets']) { ?>
                    <button onclick="enroll('<?=$courses['id']?>', '<?=$groupKey?>', '<?=$courses['classrooms_targets']['id']?>', '1')" class="btn-trigger-enroll btn btn-style1 col-md-8 col-md-offset-2 col-xs-12"><?=$courses['enroll_btn']?></button>
                    <?php if (!$courses['free'] && $courses['confirm_order']) { ?>
                    <span class="label label-success col-md-8 col-md-offset-2 col-xs-12 label-order-staus m-t-5">ชำระเงินเรียบร้อย</span>
                    <?php } ?>
                    <div class="col-md-8 col-lg-offset-2 calendar">
                        <div class="row">
                            <div class="col-md-12 calendar_">
                                <div class="row">
                                    <div class="col-md-2 text-center start"><i class="fa fa-calendar"></i></div>
                                    <div class="col-md-10 text-left">
                                        <div class="row">
                                            <span class="col-md-12">เริ่มเรียน</span>
                                            <span class="col-md-12 start"><?=DateTime_TH($courses['classrooms_targets']['start_datetime'])?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 calendar_">
                                <div class="row">
                                    <div class="col-md-2 text-center end"><i class="fa fa-calendar"></i></div>
                                    <div class="col-md-10 text-left">
                                        <div class="row">
                                            <span class="col-md-12">วันสิ้นสุด</span>
                                            <span class="col-md-12 end"><?=DateTime_TH($courses['classrooms_targets']['end_datetime'])?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php }else if($courses['classrooms_level_groups']) { ?>
                    <button onclick="enroll('<?=$courses['id']?>', '<?=$groupKey?>', '<?=$courses['classrooms_level_groups']['id']?>', '2')" class="btn-trigger-enroll btn btn-style1 col-md-8 col-md-offset-2 col-xs-12"><?=$courses['enroll_btn']?></button>
                    <?php if (!$courses['free'] && $courses['confirm_order']) { ?>
                    <span class="label label-success col-md-8 col-md-offset-2 col-xs-12 label-order-staus m-t-5">ชำระเงินเรียบร้อย</span>
                    <?php } ?>
                    <div class="col-md-8 col-lg-offset-2 calendar">
                        <div class="row">
                            <div class="col-md-12 calendar_">
                                <div class="row">
                                    <div class="col-md-2 text-center start"><i class="fa fa-calendar"></i></div>
                                    <div class="col-md-10 text-left">
                                        <div class="row">
                                            <span class="col-md-12">เริ่มเรียน</span>
                                            <span class="col-md-12 start"><?=DateTime_TH($courses['classrooms_level_groups']['start_datetime'])?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 calendar_">
                                <div class="row">
                                    <div class="col-md-2 text-center end"><i class="fa fa-calendar"></i></div>
                                    <div class="col-md-10 text-left">
                                        <div class="row">
                                            <span class="col-md-12">วันสิ้นสุด</span>
                                            <span class="col-md-12 end"><?=DateTime_TH($courses['classrooms_level_groups']['end_datetime'])?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php }else if ($courses['targets']) { ?>
                        <button onclick="enroll('<?=$courses['id']?>', '<?=$groupKey?>', '', '3')" class="btn-trigger-enroll btn btn-style1 col-md-8 col-md-offset-2 col-xs-12"><?=$courses['enroll_btn']?></button>
                        <?php if (!$courses['free'] && $courses['confirm_order']) { ?>
                        <span class="label label-success col-md-8 col-md-offset-2 col-xs-12 label-order-staus m-t-5">ชำระเงินเรียบร้อย</span>
                        <?php } ?>
                    <?php }else if($courses['level_groups']){ ?>
                        <button onclick="enroll('<?=$courses['id']?>', '<?=$groupKey?>', '<?=$courses['level_groups']['id']?>', '4')" class="btn-trigger-enroll btn btn-style1 col-md-8 col-md-offset-2 col-xs-12"><?=$courses['enroll_btn']?></button>
                        <?php if (!$courses['free'] && $courses['confirm_order']) { ?>
                        <span class="label label-success col-md-8 col-md-offset-2 col-xs-12 label-order-staus m-t-5">ชำระเงินเรียบร้อย</span>
                        <?php } ?>
                    <?php } else if($courses['level_public']){ ?>
                        <button onclick="enroll('<?=$courses['id']?>', '<?=$groupKey?>', '', '')" class="btn-trigger-enroll btn btn-style1 col-md-8 col-md-offset-2 col-xs-12"><?=$courses['enroll_btn']?></button>
                        <?php if (!$courses['free'] && $courses['confirm_order']) { ?>
                        <span class="label label-success col-md-8 col-md-offset-2 col-xs-12 label-order-staus m-t-5">ชำระเงินเรียบร้อย</span>
                        <?php } ?>
                    <?php }else{ ?>
                        <?php if ($courses['enroll_chk']) { ?>
                            <button onclick="enroll_summary('<?=$courses['id']?>', '<?=$groupKey?>')" class="btn-trigger-enroll btn btn-style1 col-md-8 col-md-offset-2 col-xs-12">ตรวจสอบผลการเรียน</button>
                            <?php if (!$courses['free'] && $courses['confirm_order']) { ?>
                            <span class="label label-success col-md-8 col-md-offset-2 col-xs-12 label-order-staus m-t-5">ชำระเงินเรียบร้อย</span>
                            <?php } ?>
                        <?php } else { ?>
                            <a class="btn btn-style3 col-md-8 col-md-offset-2 col-xs-12 disabled">คุณไม่มีสิทธิ์การเรียนคอร์สนี้</a>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>

                    <div class="clearfix"></div>

                    <?php if ($courses['agenda_live']) { ?>
                    <div class="col-md-10 col-md-offset-1 agenda-live">
                        <div class="information-line">
                            <h3 class="m-t-30 m-b-5">กำหนดการถ่ายทอดสด</h3>
                        </div>
                        <div class="">
                            <table class="table borderless table-agenda">
                                <?php foreach ($courses['agenda_live'] as $key => $rs_agenda_live) { ?>
                                <?php if ($rs_agenda_live['end_datetime'] > date('Y-m-d H:i:s') || $rs_agenda_live['streaming_status'] == 1) { ?>
                                <tr>
                                    <td>
                                        <?php if ($rs_agenda_live['state'] == 'live' && $rs_agenda_live['streaming_status'] == 1) { ?>
                                        <i class="fa fa-caret-right f-14"></i>
                                        <?php } else { ?>
                                        <span>-</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?=$rs_agenda_live['start_date'].' เวลา '.$rs_agenda_live['start_time'].' น. ถึง '.($rs_agenda_live['start_date'] != $rs_agenda_live['end_date'] ? $rs_agenda_live['end_date'].' เวลา ' : '').$rs_agenda_live['end_time'].' น.'?>
                                        <?php if ($rs_agenda_live['state'] == 'live' && $rs_agenda_live['streaming_status'] == 1) { ?>
                                    <p class="text-live f-18 m-t-5 m-b-5">
                                        <strong>กำลังถ่ายทอดสดหัวข้อ - <?=$rs_agenda_live['title']?></strong>
                                    </p>
                                    <?php } ?>
                                    </td>
                                </tr>
                                <?php } ?>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-sm-12 content-info-center">
            <ul class="nav nav-tabs" id="mytabs">
                <li class="active"><a href="#information" data-toggle="tab">รายละเอียดหลักสูตร</a></li>
                <li><a href="#structure" data-toggle="tab">โครงสร้างหลักสูตร</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade in active" id="information">

                    <?php if($courses['information']){?>
                    <div class="col-md-12 information-line">
                        <h3><i class="fa fa-file-text-o"></i> คำอธิบายหลักสูตร</h3>
                        <p><?=$courses['information']?></p>
                        <div class="col-md-12 dotted"></div>
                    </div>
                    <?php } ?>

                    <?php if($courses['objective']){?>
                    <div class="col-md-12 information-line">
                        <h3><i class="fa fa-font-awesome"></i> วัตถุประสงค์</h3>
                        <?=$courses['objective']?>
                        <div class="col-md-12 dotted"></div>
                    </div>
                    <?php } ?>

                    <?php if($courses['suitable']){?>
                    <div class="col-md-12 information-line">
                        <h3><i class="fa fa-user-o"></i> หลักสูตรนี้เหมาะสำหรับ</h3>
                        <?=$courses['suitable']?>
                        <div class="col-md-12 dotted"></div>
                    </div>
                    <?php } ?>

                    <?php if($courses['level']){?>
                    <div class="col-md-12 information-line">
                        <h3><i class="fa fa-check-circle-o"></i> ระดับเนื้อหา</h3>
                        <p>
                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<i class="fa fa-<?php if($courses['level'] == 'Beginner'){?>check-<?php } ?>circle-o"></i> ระดับต้น (Beginner)<br />
                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<i class="fa fa-<?php if($courses['level'] == 'Intermediate'){?>check-<?php } ?>circle-o"></i> ระดับกลาง (Intermediate)<br />
                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<i class="fa fa-<?php if($courses['level'] == 'Advance'){?>check-<?php } ?>circle-o"></i> ระดับสูง (Advance)
                        </p>
                        <div class="col-md-12 dotted"></div>
                    </div>
                    <?php } ?>

                    <?php if($courses['introductory']){?>
                    <div class="col-md-12 information-line">
                        <h3><i class="fa fa-files-o"></i> หลักสูตรแนะนำก่อนเข้าเรียน</h3>
                        <?=$courses['introductory']?>
                        <div class="col-md-12 dotted"></div>
                    </div>
                    <?php } ?>

                    <?php if($courses['getting_certificate'] && $courses['download_certificate'] == 1){ ?>
                    <div class="col-md-12 information-line">
                        <h3><i class="fa fa-certificate"></i> การรับวุฒิบัตร</h3>
                        <?=$courses['getting_certificate']?>
                        <?php if($courses['getting_certificate_url']){?><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<a href="<?=$courses['getting_certificate_url']?>" target="_blank" class="btn btn-style3">รายละเอียดเพิ่มเติม</a><?php } ?>
                        <div class="col-md-12 dotted"></div>
                    </div>
                    <?php } ?>

                    <?php if($courses['more_details']){ ?>
                    <div class="col-md-12 information-line">
                        <h3><i class="fa fa-bars"></i> รายละเอียดเพิ่มเติม</h3>
                        <?=$courses['more_details']?>
                        <div class="col-md-12 dotted"></div>
                    </div>
                    <?php } ?>

                </div><!-- End tab-pane -->

                <div class="tab-pane fade in" id="structure">
                    <?=$courses['structure']?>
                </div><!-- End tab-pane -->

               </div>

        </div>
    </div>
</div>
</section>

<?php if(count($related)){?>
  <section id="content-list">
  <div class="container">
  <div class="row">
      <div class="col-md-12 text-center">
          <h1 class="text-center">หลักสูตรที่เกี่ยวข้อง</h1>
      </div>

      <?php foreach($related as $rs_related){ ?>
          <div class="col-lg-3 col-md-3 col-sm-4">
              <div class="col-item <?=$rs_related['categories']['css_class']?>">
                  <div class="photo">
                      <?php if($rs_related['latest']){ ?><div class="ribbon"><img src="/images/ribbin-new@2x.png" class="img-responsive"></div><?php } ?>
                      <a href="<?=groupKey($groupKey)?>/courses/<?=$rs_related['id']?>/info"><img src="<?=getImage(constant("_BASE_DIR_COURSES_THUMBNAIL"),$rs_related['thumbnail'])?>"></a>
                  </div>
                  <div class="info">
                      <div class="row">
                          <div class="course_info col-md-12 col-xs-12">
                              <h4><a href="<?=groupKey($groupKey)?>/courses/<?=$rs_related['id']?>/info"><?=$rs_related['code']?></a></h4>
                              <h4><a href="<?=groupKey($groupKey)?>/courses/<?=$rs_related['id']?>/info"><?=$rs_related['title']?></a></h4>
                              <span><?=$rs_related['subject']?></span>
                              <?php if($rs_related['free']) {?><p class="free">ฟรี</p><?php }else{ ?><p><?=number_format($rs_related['price'])?> บาท</p><?php } ?>
                          </div>
                      </div>
                      <div class="separator clearfix">
                          <span class="col-md-10 col-xs-10"><?=$rs_related['categories']['title']?></span>
                      </div>
                  </div>
              </div>
          </div>
      <?php } ?>
  </div>
  </div>
  </section>
<?php } ?>

<!-- Modal -->
<div class="modal fade" id="taxInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="taxInvoiceModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="taxInvoiceModalLabel">รายละเอียดใบเสร็จรับเงิน/ใบกำกับภาษี</h4>
                <div class="f-16">กรุณากรอกข้อมูลและตรวจสอบข้อมูลเพื่อออกใบกำกับภาษี</div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="m-b-10">
                            <strong>หลักสูตร : </strong> <?=$courses['code']." - ".$courses['title']?>
                        </div>
                    </div>
                    <div class="col-xs-12">
                        <div class="m-b-10">
                            <strong>ราคา : </strong> <?=number_format($courses['price'])." บาท"?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label for="type_tax_invoice" class="control-label">ประเภทใบกำกับภาษี  <span class="text-danger">*</span></label>
                            <div class="styled-select" style="margin-bottom: 0px;">
                                <select name="type_tax_invoice" id="type_tax_invoice" class="form-control">
                                    <option <?=$members['latest_type_tax'] === "personal" ? "selected" : ""?> value="personal">บุคคลธรรมดา</option>
                                    <option <?=$members['latest_type_tax'] === "corporate" ? "selected" : ""?> value="corporate">นิติบุคคล</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="hr-divider-dashed m-t-5 m-b-20">

                <div class="box-personal">
                    <div class="box-form <?=$alreadyPersonal ? 'box-form-edit hide' : ''?>">
                        <form id="personal-form" class="">
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="inv_personal_first_name" class="control-label">ชื่อ <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="inv_personal_first_name" name="inv_personal_first_name" placeholder="" value="<?=$members['inv_personal_first_name']?>">
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="inv_personal_last_name" class="control-label">นามสกุล <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="inv_personal_last_name" name="inv_personal_last_name" placeholder="" value="<?=$members['inv_personal_last_name']?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="inv_personal_tax_id" class="control-label">เลขประจำตัวผู้เสียภาษี (เลขที่บัตรประชาชน) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="inv_personal_tax_id" name="inv_personal_tax_id" placeholder="" value="<?=$members['inv_personal_tax_id']?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="inv_personal_email" class="control-label">อีเมล์สำหรับจัดส่งใบกำกับภาษี <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="inv_personal_email" name="inv_personal_email" placeholder="" value="<?=$members['inv_personal_email']?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="inv_personal_tel" class="control-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="inv_personal_tel" name="inv_personal_tel" placeholder="" value="<?=$members['inv_personal_tel']?>">
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="inv_personal_address" class="control-label">ที่อยู่สำหรับออกใบกำกับภาษี <span class="text-danger">*</span></label>
                                        <textarea class="form-control rs-vertical inv_personal_address" id="inv_personal_address" name="inv_personal_address"><?=$members['inv_personal_address']?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="inv_personal_zip_code" class="control-label">รหัสไปรษณีย์ <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="inv_personal_zip_code" name="inv_personal_zip_code" placeholder="" value="<?=$members['inv_personal_zip_code']?>">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="box-info <?=$alreadyPersonal ? '' : 'hide'?>">
                        <div class="box-info-content"></div>
                        <div class="box-info remark">
                            <div class="text-center m-t-20">(ใบเสร็จรับเงิน/ใบกำกับภาษีจะส่งทางอีเมล์ที่ระบุภายใน 5 วันทำการนับแต่วันที่มีการชำระเงินสำเร็จ)</div>
                            <div class="text-danger text-center f-22 f-bold m-t-10">
                                โปรดตรวจสอบความถูกต้องของข้อมูลให้เรียบร้อยก่อน ยืนยันและชำระเงิน<br>
                                ทั้งนี้ขอสงวนสิทธิ์ในการแก้ไขทุกกรณี
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box-corporate">
                    <div class="box-form <?=$alreadyCorporate ? 'box-form-edit hide' : ''?>">
                        <form id="corporate-form" class="">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="form-group">
                                        <label for="inv_corporate_name" class="control-label">ชื่อ-นามสกุล / ชื่อบริษัท <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="inv_corporate_name" name="inv_corporate_name" placeholder="" value="<?=$members['inv_corporate_name']?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="inv_corporate_branch" class="control-label">สำนักงานใหญ่ / สาขา  <span class="text-danger">*</span></label>
                                        <div class="styled-select" style="margin-bottom: 0px;">
                                            <select name="inv_corporate_branch" id="inv_corporate_branch" class="form-control">
                                                <option <?=$members['inv_corporate_branch'] == 0 ? "selected" : ""?> value="0">สำนักงานใหญ่</option>
                                                <option <?=$members['inv_corporate_branch'] == 1 ? "selected" : ""?> value="1">สาขา</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="inv_corporate_branch_no" class="control-label">สาขาที่ <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" maxlength-x="5" id="inv_corporate_branch_no" name="inv_corporate_branch_no" placeholder="" value="<?=$members['inv_corporate_branch_no']?>" <?=$members['inv_corporate_branch'] == 0 ? "disabled" : ""?>>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="inv_corporate_tax_id" class="control-label">เลขประจำตัวผู้เสียภาษี <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="inv_corporate_tax_id" name="inv_corporate_tax_id" placeholder="" value="<?=$members['inv_corporate_tax_id']?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="inv_corporate_email" class="control-label">อีเมล์สำหรับจัดส่งใบกำกับภาษี <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="inv_corporate_email" name="inv_corporate_email" placeholder="" value="<?=$members['inv_corporate_email']?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="inv_corporate_tel" class="control-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="inv_corporate_tel" name="inv_corporate_tel" placeholder="" value="<?=$members['inv_corporate_tel']?>">
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="inv_corporate_address" class="control-label">ที่อยู่สำหรับออกใบกำกับภาษี <span class="text-danger">*</span></label>
                                        <textarea class="form-control rs-vertical inv_corporate_address" id="inv_corporate_address" name="inv_corporate_address"><?=$members['inv_corporate_address']?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="inv_corporate_zip_code" class="control-label">รหัสไปรษณีนย์ <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="inv_corporate_zip_code" name="inv_corporate_zip_code" placeholder="" value="<?=$members['inv_corporate_zip_code']?>">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="box-info <?=$alreadyCorporate ? '' : 'hide'?>">
                        <div class="box-info-content"></div>
                        <div class="box-info remark">
                            <div class="text-center m-t-20">(ใบเสร็จรับเงิน/ใบกำกับภาษีจะส่งทางอีเมล์ที่ระบุภายใน 5 วันทำการนับแต่วันที่มีการชำระเงินสำเร็จ)</div>
                            <div class="text-danger text-center f-22 f-bold m-t-10">
                                โปรดตรวจสอบความถูกต้องของข้อมูลให้เรียบร้อยก่อน ยืนยันและชำระเงิน<br>
                                ทั้งนี้ขอสงวนสิทธิ์ในการแก้ไขทุกกรณี
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-xs-4 text-left">
                        <button id="btnToggleFormTaxInvoice" type="button" class="btn btn-style3 f-18"><i class="fa fa-pencil-square-o f-14 top-1" aria-hidden="true"></i> แก้ไขข้อมูล</button>
                    </div>
                    <div class="col-xs-8">
                        <button type="button" class="btn btn-default f-18 hidden-xs" data-dismiss="modal"><!-- <i class="fa fa-times f-14" aria-hidden="true"></i>  -->ยกเลิก</button>
                        <button id="btnUpdateTaxInvoice" type="button" class="btn btn-set f-18 p-l-20 p-r-20" data-course="<?=$courses['id']?>">บันทึกข้อมูล<!--  <i class="fa fa-arrow-right f-14" aria-hidden="true"></i> --></button>
                        <button id="btnCreateOrder" type="button" class="btn btn-set f-18 p-l-20 p-r-20" onclick="createOrders(<?=$courses['id']?>, true)">ยืนยันและชำระเงิน<!--  <i class="fa fa-arrow-right f-14" aria-hidden="true"></i> --></button>
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
    <script src="/bower_components/noty/lib/noty.min.js"></script>
    <script src="/bower_components/jquery-confirm/dist/jquery-confirm.min.js"></script>
    <script src="/js/formvalidation.js"></script>
    <script src="/js/script/config.js"></script>
    <script src="/js/script/functions.js"></script>
    <script src="/js/script/model/members.js"></script>
    <script src="/js/script/model/enroll.js"></script>
    <script src="/js/script/model/filter-courses.js"></script>
    <script src="/js/script/model/orders.js"></script>
    <script src="/js/script/model/payments.js"></script>
    <script src="/js/script/pages/courses-info.js"></script>

    <?php if($courses['review_streaming_url']){?>
    <script src="/js/jwplayer-7.11.3/jwplayer.js"></script>
    <script>jwplayer.key="ysQTVfHC5iQ8flS72k460WTgxEPDzPg90dTu2NzjVT0=";</script>
    <script>
        var playerInstance = jwplayer("player");
        playerInstance.setup({
            file: "<?=$courses['review_streaming_url']?>",
            image: "<?=getImage(constant("_BASE_DIR_COURSES_THUMBNAIL"),$courses['thumbnail'])?>",
            aspectratio: "16:9",
            width: "100%",
            autostart: "false",
            // androidhls: true
        });
    </script>
    <?php } ?>
    <script src="/js/main.js"></script>
</body>
</html>