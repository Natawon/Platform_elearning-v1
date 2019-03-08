@if ($dataMember->is_foreign != 1)
	<h4>ยินดีด้วย คุณ {{ $dataMember->first_name }} {{ $dataMember->last_name }}</h4>
@else
	<h4>ยินดีด้วย คุณ {{ $dataMember->first_name_en }} {{ $dataMember->last_name_en }}</h4>
@endif
<p>คุณได้เป็นสมาชิก <strong>{{ $dataGroup->subject }}</strong></p>
<p>{{ $dataGroup->meaning_of_sub_groups_id }} <strong>{{ $dataSubGroup->title }}</strong></p>
<p>{{ $dataGroup->meaning_of_level_groups_id }} <strong>{{ $dataLevelGroups->title }}</strong></p>
<p><strong>เรียบร้อยแล้ว</strong></p>
<p><a href="{{ $url }}" target="_blank">คลิกที่นี่เพื่อเข้าสู่ระบบ</a></p>
<hr>
<p>สอบถามข้อมูลเพิ่มเติม กรุณาติดต่อ {{config('constants.PROJECT_INFO.CONTACT_CENTER')}} โทร. {{config('constants.PROJECT_INFO.CONTACT_PHONE')}} อีเมล์ : {{config('constants.PROJECT_INFO.CONTACT_MAIL')}}</p>
<hr style="border: 1px dashed; margin: 50px 0px;">
@if ($dataMember->is_foreign != 1)
	<h4>ยินดีด้วย คุณ {{ $dataMember->first_name }} {{ $dataMember->last_name }}</h4>
@else
	<h4>ยินดีด้วย คุณ {{ $dataMember->first_name_en }} {{ $dataMember->last_name_en }}</h4>
@endif
<p>คุณได้เป็นสมาชิก <strong>{{ $dataGroup->subject }}</strong></p>
<p>{{ $dataGroup->meaning_of_sub_groups_id }} <strong>{{ $dataSubGroup->title }}</strong></p>
<p>{{ $dataGroup->meaning_of_level_groups_id }} <strong>{{ $dataLevelGroups->title }}</strong></p>
<p><strong>เรียบร้อยแล้ว</strong></p>
<p><a href="{{ $url }}" target="_blank">คลิกที่นี่เพื่อเข้าสู่ระบบ</a></p>
<hr>
<p>สอบถามข้อมูลเพิ่มเติม กรุณาติดต่อ {{config('constants.PROJECT_INFO.CONTACT_CENTER')}} โทร. {{config('constants.PROJECT_INFO.CONTACT_PHONE')}} อีเมล์ : {{config('constants.PROJECT_INFO.CONTACT_MAIL')}}</p>