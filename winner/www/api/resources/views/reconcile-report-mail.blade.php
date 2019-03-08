<h4>ถึง ผู้ดูแลระบบและผู้เกี่ยวข้อง</h4>
<div>ระบบได้ทำการตรวจสอบรายการการชำระค่าธรรมเนียมสำหรับการเข้าเรียนหลักสูตรใน Group ของท่าน ตามรายละเอียดดังนี้</div>
<p><strong>Group : </strong>{{ $dataGroup->title }}</p>
<p><strong>วันที่ทำรายการ : </strong>{{ $dataReconcile['date_short'] }}</p>
<p><strong>จำนวนรายการทั้งหมด : </strong>{{ number_format($dataReconcile['total']) }}</p>
<p><strong>จำนวนรายการที่ Complete : </strong>{{ number_format($dataReconcile['valid_total']) }}</p>
<p><strong>จำนวนรายการที่ Incomplete : </strong>{{ number_format($dataReconcile['invalid_total']) }}</p>
@if ($dataReconcile['invalid_string'] != "")
	<p><strong>Order ID ที่ Incomplete : </strong>{{ $dataReconcile['invalid_string'] }}</p>
@else
	<p><strong>Order ID ที่ Incomplete : </strong>-</p>
@endif
<p>ท่านสามารถตรวจสอบรายการทั้งหมดได้จากไฟล์ที่แนบมาด้วย</p>
<hr>
<p>สอบถามข้อมูลเพิ่มเติม กรุณาติดต่อ {{config('constants.PROJECT_INFO.CONTACT_CENTER')}} โทร. {{config('constants.PROJECT_INFO.CONTACT_PHONE')}} อีเมล์ : {{config('constants.PROJECT_INFO.CONTACT_MAIL')}}</p>

