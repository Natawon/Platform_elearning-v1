<?php
echo $scriptSite;

$cHeader = "";
$cBoxLogo = "";
if ($instructor) {
	$cHeader = "header-member";
	$cBoxLogo = "pull-sm-center-logo";
}

?>
<!-- <div class="notice-system">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h3>ประกาศปิดปรับปรุงระบบ <span style="font-weight: bold;">วันศุกร์ ที่ 25 พฤษภาคม 2561 เวลา 18:00 - 22:00</span> กรุณาออกจากระบบก่อนเวลาดังกล่าว เพื่อป้องกันข้อมูลสูญหาย ขออภัยในความไม่สะดวกใน ณ ที่นี้.</h3>
            </div>
        </div>
    </div>
</div>

<style type="text/css">.powered-by { margin-bottom: 30px; }</style> -->

<header class="<?php echo $cHeader; ?>" data-group-site="<?=groupKey($groupKey)?>">
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
			<?php if ($instructor) { ?>
				<div class="col-xs-12 col-sm-12 col-md-10 col-lg-8 pull-right header-profile">
					<div class="row">
						<div class="col-md-4 col-md-offset-6 text-center hidden-xs hidden-sm"><div class="inline-block head-avatar"><i class="fa fa-user-circle-o"></i></div> <?=$instructor['title']?></div>
						<div class="col-md-2 text-center hidden-xs hidden-sm"><button id="btn-instructors-logout" class="btn btn-style3">ออกจากระบบ</button></div>
						<div class="col-xs-6 col-sm-8 visible-xs visible-sm">
							<div class="inline-block head-avatar p-r-5"><i class="fa fa-user-circle-o"></i></div> <?=$instructor['title']?>
						</div>
						<div class="col-xs-6 col-sm-4 visible-xs visible-sm">
							<div class="text-right">
								<button id="btn-instructors-logout" class="btn btn-style3">ออกจากระบบ</button>
							</div>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</header><!-- End header -->









