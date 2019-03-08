@if ($dataMember->is_foreign != 1)
	<h4>สวัสดี คุณ {{ $dataMember->first_name }} {{ $dataMember->last_name }}</h4>
@else
	<h4>สวัสดี คุณ {{ $dataMember->first_name_en }} {{ $dataMember->last_name_en }}</h4>
@endif
<p>คุณได้เปลี่ยนสถานะการเป็นสมาชิก</p>
<p>จาก <strong>{{ $dataGroups->subject }}</strong></p>
<p>{{ $dataGroups->meaning_of_sub_groups_id }} <strong>{{ $dataOldSubGroups->title }}</strong></p>
<p>เป็น <strong>{{ $dataGroups->subject }}</strong></p>
<p>{{ $dataGroups->meaning_of_sub_groups_id }} <strong>{{ $dataNewSubGroups->title }}</strong></p>
<p>โดยผู้ดูแลระบบ</p>
<p>กรุณายืนยันการเปลี่ยนแปลงโดยเข้าสู่ระบบสมาชิกด้วยอีเมล์ <strong>{{ $dataMember->email }}</strong> ตาม Link ด้านล่างนี้</p>
<p><a href="{{ $url_login }}" target="_blank">คลิกที่นี่เพื่อเข้าสู่ระบบ</a></p>
<hr>
<p>และคุณไดรับสิทธิ์เข้าเรียนหลักสูตร <strong>{{ $dataCourses->title }}</strong></p>
<p>ผ่านระบบ <strong>{{ $dataGroups->subject }}</strong></p>
<p><a href="{{ $url_courses }}" target="_blank">คลิกที่นี่เพื่อเข้าหลักสูตร</a></p>
<hr>
<p>สอบถามข้อมูลเพิ่มเติม กรุณาติดต่อ {{config('constants.PROJECT_INFO.CONTACT_CENTER')}} โทร. {{config('constants.PROJECT_INFO.CONTACT_PHONE')}} อีเมล์ : {{config('constants.PROJECT_INFO.CONTACT_MAIL')}}</p>
