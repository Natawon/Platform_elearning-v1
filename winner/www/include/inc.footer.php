<footer>
    <?=$configuration['footer']?>
    <p class="powered-by">Powered by <a href="#" target="_blank">Athena</a></p>
</footer>

<div id="toTop"><i class="fa fa-arrow-up" aria-hidden="true"></i></div>

<?php
$filterCoursesPopup = false;
if (!empty($members) && $members['action_login'] == true && $members['filter_courses_status'] == 0 && !empty($groups['questionnaire_packs']) && !empty($groups['questionnaire_packs']['questionnaires'])) {
    $filterCoursesPopup = true;
}
?>

<!-- Modal -->
<div class="modal fade" id="filterCoursesModal" tabindex="-1" role="dialog" aria-labelledby="filterCoursesModalLabel" data-popup="<?=$filterCoursesPopup?>">
    <div class="modal-dialog modal-lg-disabled" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="filterCoursesModalLabel">ค้นหาหลักสูตรที่เหมาะสมกับคุณ</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="wrapper-questionnaires">
                            <form id="filter-courses-form" class="filter-courses-form" role="form" data-toggle="validator" enctype="multipart/form-data">
                                <input type="hidden" name="qpid" id="qpid" value="<?=$groups['questionnaire_packs']['id']?>">
                                <?php if (isset($groups['questionnaire_packs']) && !empty($groups['questionnaire_packs']['questionnaires'])) {
                                    $i=0; foreach($groups['questionnaire_packs']['questionnaires'] as $rs_questionnaires){ $i++;?>
                                            <div class="col-md-12 col-sm-12">
                                                <h4><?=$i.". ".clean_tag_p($rs_questionnaires['question'])?></h4>
                                                <div class="col-md-12 col-xs-12 col-sm-12">
                                                    <div class="row">
                                                        <ol>
                                                            <?php if($rs_questionnaires['type'] == 1) {?>
                                                                <?php foreach($rs_questionnaires['questionnaire_choices'] as $rs_questionnaire_choices){ ?>
                                                                    <li>
                                                                        <div class="radio">
                                                                            <label>
                                                                                <input type="radio" name="<?=$rs_questionnaires['id']?>" id="<?=$rs_questionnaires['id']?>" data-questions-no="<?=$i?>" value="<?=$rs_questionnaire_choices['id']?>"> <?=$rs_questionnaire_choices['answer']?>
                                                                            </label>
                                                                        </div>
                                                                    </li>
                                                                <?php } ?>
                                                            <?php } ?>
                                                            <?php if($rs_questionnaires['type'] == 2) {?>
                                                                <?php foreach($rs_questionnaires['questionnaire_choices'] as $rs_questionnaire_choices){ ?>
                                                                    <li>
                                                                        <label class="checkbox-inline">
                                                                            <input type="checkbox" name="<?=$rs_questionnaires['id']?>" id="<?=$rs_questionnaires['id']?>" data-questions-no="<?=$i?>" value="<?=$rs_questionnaire_choices['id']?>"> <?=$rs_questionnaire_choices['answer']?>
                                                                        </label>
                                                                    </li>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                    <?php } ?>
                                <?php } ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="form-inline">
                    <div class="row">
                        <?php if ($filterCoursesPopup == true) { ?>
                            <div class="col-xs-8 text-left">
                                <button id="btnCloseWithoutAgain" type="button" class="btn btn-default f-18 m-b-10 m-r-5 hover">ไม่ต้องแสดงกล่องค้นหานี้ให้ฉันอีก</button>
                                <button id="btnCloseWithAgain" type="button" class="btn btn-default f-18 m-b-10" data-dismiss="modal" style="margin: 0 0 10px 0;">แสดงฉันในภายหลัง</button>
                            </div>
                            <div class="col-xs-4 text-right">
                                <button id="btnFilterCourses" type="button" class="btn btn-set f-18 m-b-10 p-l-20 p-r-20" style="margin: 0 0 10px 0;">ค้นหา</button>
                            </div>
                            <div class="col-xs-12 text-left">
                                <div class="w-100 text-muted f-16 inline-block text-left"><b>หมายเหตุ: </b>หากคุณเลือก <b>"ไม่ต้องแสดงกล่องค้นหานี้ให้ฉันอีก"</b> คุณสามารถเข้าไปหาหลักสูตรที่เหมาะสมภายหลังได้จากเมนู<br><b>ข้อมูลส่วนตัว</b> > <b>แท็บ หลักสูตรที่เหมาะสม</b> > <b>คลิก ค้นหาหลักสูตรที่เหมาะสม</b></div>
                            </div>
                        <?php } else { ?>
                            <div class="col-xs-12">
                                <button type="button" class="btn btn-default f-18" data-dismiss="modal">ยกเลิก</button>
                                <button id="btnFilterCourses" type="button" class="btn btn-set f-18 p-l-20 p-r-20">ค้นหา</button>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

