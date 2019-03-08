@if ($dataMember->is_foreign != 1)
	<h4>สวัสดี คุณ {{ $dataMember->first_name }} {{ $dataMember->last_name }}</h4>
@else
	<h4>สวัสดี คุณ {{ $dataMember->first_name_en }} {{ $dataMember->last_name_en }}</h4>
@endif
<p>รหัสผ่านสำหรับ <strong>{{ $dataGroup->subject }}</strong></p>
<p>รหัสผ่าน (password) สำหรับการเข้าใช้งาน elearning.set.or.th/{{ $dataGroup->key }} คือ <strong>{{ $dataMember->password }}</strong></p>
<p>สามารถนำรหัสผ่าน (password) ที่ได้รับจากอีเมล์ฉบับนี้ เข้าใช้งาน elearning.set.or.th/{{ $dataGroup->key }} ได้ทันที</p>
<hr>
<p>สอบถามข้อมูลเพิ่มเติม กรุณาติดต่อ {{config('constants.PROJECT_INFO.CONTACT_CENTER')}} โทร. {{config('constants.PROJECT_INFO.CONTACT_PHONE')}} อีเมล์ : {{config('constants.PROJECT_INFO.CONTACT_MAIL')}}</p>
