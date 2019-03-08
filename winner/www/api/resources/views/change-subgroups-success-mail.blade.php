@if ($dataMember->is_foreign != 1)
	<h4>สวัสดี คุณ {{ $dataMember->first_name }} {{ $dataMember->last_name }}</h4>
@else
	<h4>สวัสดี คุณ {{ $dataMember->first_name_en }} {{ $dataMember->last_name_en }}</h4>
@endif
<p>คุณได้เปลี่ยนสถานะการเป็นสมาชิก {{ $dataGroup->subject }}</p>
<p>จาก {{ $dataGroup->meaning_of_sub_groups_id }} <strong>{{ $dataOldSubGroup->title }}</strong></p>
<p>เป็น {{ $dataGroup->meaning_of_sub_groups_id }} <strong>{{ $dataSubGroup->title }}</strong></p>
<p><strong>เรียบร้อยแล้ว</strong></p>
<p>หลักจากนี้กรุณาเข้าสู่ระบบด้วยอีเมล์ <strong>{{ $dataMember->email }}</strong></p>
<p><a href="{{ $url }}" target="_blank">คลิกที่นี่เพื่อเข้าสู่ระบบ</a></p>
<hr>
<p>สอบถามข้อมูลเพิ่มเติม กรุณาติดต่อ {{config('constants.PROJECT_INFO.CONTACT_CENTER')}} โทร. {{config('constants.PROJECT_INFO.CONTACT_PHONE')}} อีเมล์ : {{config('constants.PROJECT_INFO.CONTACT_MAIL')}}</p>
