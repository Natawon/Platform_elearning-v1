@if ($dataMembers->is_foreign != 1)
	<h4>สวัสดี คุณ {{ $dataMembers->first_name }} {{ $dataMembers->last_name }}</h4>
@else
	<h4>สวัสดี คุณ {{ $dataMembers->first_name_en }} {{ $dataMembers->last_name_en }}</h4>
@endif
<p>คุณไดร้ ับสิทธิ์เข้าเรียนหลักสูตร <strong>{{ $dataCourses->title }}</strong></p>
<p>ผ่านระบบ <strong>{{ $dataGroups->subject }}</strong></p>
<p><a href="{{ $url }}" target="_blank">คลิกที่นี่เพื่อเข้าหลักสูตร</a></p>
<hr>
<p>สอบถามข้อมูลเพิ่มเติม กรุณาติดต่อ {{config('constants.PROJECT_INFO.CONTACT_CENTER')}} โทร. {{config('constants.PROJECT_INFO.CONTACT_PHONE')}} อีเมล์ : {{config('constants.PROJECT_INFO.CONTACT_MAIL')}}</p>
