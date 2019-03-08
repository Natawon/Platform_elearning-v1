<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

echo $scriptSite;

$cHeader = "";
$cBoxLogo = "";
if ($members) {
	$cHeader = "header-member";
	$cBoxLogo = "pull-sm-center-logo";
}

?>

<header class="<?php echo $cHeader; ?>" data-group-site="<?=groupKey($groupKey)?>" data-course="<?php echo isset($courses) && isset($courses['id']) ? $courses['id'] : '' ; ?>">
	<div class="container">
		<div class="row">
			<div class="col-xxs-6 col-xxs-offset-3 col-xs-4 col-xs-offset-4 col-sm-3 col-sm-offset-0 col-md-2 col-md-offset-0 <?php echo $cBoxLogo; ?>">
				<!-- <div class="col-md-9 col-sm-4 col-sm-offset-4 col-xs-6 col-xs-offset-3"> -->
				<div class="text-center visible-xs visible-sm">
					<a href="<?=groupKey($groupKey)?>">
						<img src="<?=constant("_BASE_DIR_LOGO").$configuration['logo']?>" id="logo" class="img-responsive hidden-webview">
					</a>
				</div>
				<div class="visible-md visible-lg">
					<a href="<?=groupKey($groupKey)?>">
						<img src="<?=constant("_BASE_DIR_LOGO").$configuration['logo']?>" id="logo" class="img-responsive hidden-webview">
					</a>
				</div>
			</div>
			<?php
			if ($members) {
				if ($members['is_foreign'] != 1) {
					$fullName = $members['first_name']." ".$members['last_name'];
				} else {
					$fullName = $members['first_name_en']." ".$members['last_name_en'];
				}
			?>
			<div class="col-xs-12 col-sm-12 col-md-10 col-lg-8 pull-right header-profile">
				<div class="row">
					<div class="col-md-3 text-right hidden-xs hidden-sm"><a href="<?=groupKey($groupKey)?>/my-profile">ข้อมูลส่วนตัว</a></div>
					<div class="col-md-3 text-right hidden-xs hidden-sm"><a href="<?=groupKey($groupKey)?>/my-profile/courses">หลักสูตรที่ลงทะเบียน</a></div>
					<div class="col-md-4 text-center hidden-xs hidden-sm"><div class="inline-block head-avatar"><?=$head_avatar?></div> <?=$fullName?></div>
					<div class="col-md-2 text-center hidden-xs hidden-sm"><button id="btn-logout" class="btn btn-style3">ออกจากระบบ</button></div>
					<div class="col-xs-12 col-sm-8 visible-xs visible-sm">
						<div class="inline-block head-avatar p-r-5"><?=$head_avatar?></div> <?=$fullName?>
					</div>
					<div class="col-sm-8 visible-xs visible-sm">
						<a href="<?=groupKey($groupKey)?>/my-profile" class="p-r-15">ข้อมูลส่วนตัว</a>
						<a href="<?=groupKey($groupKey)?>/my-profile/courses">หลักสูตรที่ลงทะเบียน</a>
					</div>
					<div class="col-sm-4 visible-sm">
						<div class="text-right">
							<button id="btn-logout" class="btn btn-style3">ออกจากระบบ</button>
						</div>
					</div>
				</div>
			</div>
			<?php } else { ?>
			<div class="col-xs-8 col-sm-9 col-md-4 col-md-offset-6 col-lg-3 col-lg-offset-7 hidden-webview">
				<div class="vspace-30 visible-xs"></div>
				<div class="text-xs-left text-sm-right">
                    <?php if($groups['internal']){?>
                        <a href="<?=groupKey($groupKey)?>/login" class="btn btn-style1 btn-trigger-login">เข้าสู่ระบบ</a>
                        <?php if ($groups['is_show_register_btn'] == 1) { ?>
                        	<a href="<?=groupKey($groupKey)?>/register" class="btn btn-style2"> สมัครสมาชิก</a>
                        <?php } ?>
                    <?php }else{ ?>
					    <button class="btn btn-style1 btn-login btn-trigger-login" data-group="<?php echo $groupKey; ?>">เข้าสู่ระบบ</button>
					    <?php
					    if ($groups['is_show_register_btn'] == 1) {
					    	?>
					    	<button class="btn btn-style2 btn-register" data-group="<?php echo $groupKey; ?>"> สมัครสมาชิก</button>
					    	<?php
					    }
					    ?>
                    <?php } ?>
				</div>
				<span class="pull-xs-left pull-sm-right"><a href="<?php echo $oFunc->constArr("_BASE_SET")[$groupKey]['forgot']; ?>" title="ลืมรหัสผ่าน ?">ลืมรหัสผ่าน ?</a></span>
			</div>
			<?php } ?>
		</div>
	</div>
</header><!-- End header -->

<nav>
    <div class="container container-fluid-force">
        <div class="row">
            <div class="col-md-9">
                <div id="mobnav-btn"><i class="fa fa-bars" aria-hidden="true"></i></div>
                <ul class="sf-menu">
                    <li class="<?=$activeMenu == "home" ? "current" : ""?>"><a href="<?=groupKey($groupKey)?>"> หน้าแรก</a></li>
                    <li class="normal_drop_down <?=$activeMenu == "courses" ? "current" : ""?>">
                        <a href="<?=groupKey($groupKey)?>/list"> หลักสูตร</a>
                        <div class="mobnav-subarrow"></div>
                        <ul>
                            <li><a href="<?=groupKey($groupKey)?>/list">หลักสูตรทั้งหมด</a></li>
                            <?php foreach($categories as $rs_categories){ ?>
                            	<li><a href="<?=groupKey($groupKey)?>/categories/<?=$rs_categories['id']?>"><?=$rs_categories['title']?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                    <li class="<?=$activeMenu == "about" ? "current" : ""?>"><a href="<?=groupKey($groupKey)?>/about"> เกี่ยวกับเรา</a></li>
                    <li class="<?=$activeMenu == "qa" ? "current" : ""?>"><a href="<?=groupKey($groupKey)?>/qa"> คำถามที่พบบ่อย</a></li>
                    <?php
                    if ($members) {
                      ?>
                      <li class="menu-logout visible-xs"><a href="#" id="btn-logout" ><i class="fa fa-sign-out" aria-hidden="true"></i> ออกจากระบบ</a></li>
                      <?php
                    }
                    ?>
                </ul>
            </div>
            <div class="clearfix visible-sm"></div>
            <div class="col-md-3">
            	<div class="wrapper-search-courses">
            		<form id="frm-search-courses">
            			<div class="form-group m-b-0">
            				<div class="prepend-icon prepend-icon-sm">
                                <input id="search_keyword" type="text" class="form-control form-white" name="search_keyword" placeholder="ค้นหาหลักสูตร (อย่างน้อย 3 ตัวอักษร)" value="<?=$keyword?>">
                                <i class="fa fa-search c-main" aria-hidden="true"></i>
                            </div>
            				<!-- <div class="input-group">
								<span class="input-group-addon c-main" id="basic-addon1"><i class="fa fa-search" aria-hidden="true"></i></span>
								<input type="text" class="form-control" placeholder="Username" aria-describedby="basic-addon1">
							</div> -->
						</div>
            		</form>
            	</div>
            </div>
        </div><!-- End row -->
    </div><!-- End container -->
</nav>







