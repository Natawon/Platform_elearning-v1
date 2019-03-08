<h4>ถึง ผู้ดูแล</h4>
<p>ระบบได้พบว่ามีการสร้าง{{ $dataGroup->meaning_of_level_groups_id }} <strong>{{ $dataGroup->subject }}</strong> โดยมีรายละเอียด ดังนี้</p>
<p>{{ $dataGroup->meaning_of_sub_groups_id }} <strong>{{ $dataSubGroup->title }}</strong></p>
<p>{{ $dataGroup->meaning_of_level_groups_id }} <strong>{{ $dataLevelGroup->title }}</strong></p>
<p>ซึ่งท่านจะต้องทำการตรวจสอบเพื่ออนุมัติ</p>
<p><a href="{{ $url_login }}" target="_blank">คลิกที่นี่เพื่อเข้าสู่ระบบ</a></p>
<hr>
<p>สอบถามข้อมูลเพิ่มเติม กรุณาติดต่อ {{config('constants.PROJECT_INFO.CONTACT_CENTER')}} โทร. {{config('constants.PROJECT_INFO.CONTACT_PHONE')}} อีเมล์ : {{config('constants.PROJECT_INFO.CONTACT_MAIL')}}</p>
