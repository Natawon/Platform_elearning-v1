<h4>สวัสดี คุณ {{ $dataAdmin->first_name }} {{ $dataAdmin->last_name }}</h4>
<p>บัญชีผู้ใช้ <strong>{{ $dataAdmin->username }}</strong> ของคุณได้เปิดใช้งานสมาชิก</p>
<p><strong>เรียบร้อยแล้ว</strong></p>
<p><a href="{{ $url }}" target="_blank">คลิกที่นี่เพื่อเข้าสู่ระบบ</a></p>
<hr>
<p>สอบถามข้อมูลเพิ่มเติม กรุณาติดต่อ {{config('constants.PROJECT_INFO.CONTACT_CENTER')}} โทร. {{config('constants.PROJECT_INFO.CONTACT_PHONE')}} อีเมล์ : {{config('constants.PROJECT_INFO.CONTACT_MAIL')}}</p>
