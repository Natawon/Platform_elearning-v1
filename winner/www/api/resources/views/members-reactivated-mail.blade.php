@if ($dataMembers->is_foreign != 1)
	<h4>สวัสดี คุณ {{ $dataMembers->first_name }} {{ $dataMembers->last_name }}</h4>
@else
	<h4>สวัสดี คุณ {{ $dataMembers->first_name_en }} {{ $dataMembers->last_name_en }}</h4>
@endif
<p>คุณได้รับการเปิดใช้งานสมาชิก <strong>{{ $dataGroups->subject }}</strong></p>
<p>{{ $dataGroups->meaning_of_sub_groups_id }} <strong>{{ $dataSubGroups->title }}</strong></p>
<p>{{ $dataGroups->meaning_of_level_groups_id }} <strong>{{ $dataLevelGroups->title }}</strong></p>
<p><strong>เรียบร้อยแล้ว</strong></p>
<p><a href="{{ $url }}" target="_blank">คลิกที่นี่เพื่อเข้าสู่ระบบ</a></p>
<hr>
<p>สอบถามข้อมูลเพิ่มเติม กรุณาติดต่อ {{config('constants.PROJECT_INFO.CONTACT_CENTER')}} โทร. {{config('constants.PROJECT_INFO.CONTACT_PHONE')}} อีเมล์ : {{config('constants.PROJECT_INFO.CONTACT_MAIL')}}</p>
