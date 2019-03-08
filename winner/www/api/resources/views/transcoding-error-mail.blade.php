<h4>ถึง คุณ {{ $dataAdmin->first_name }} {{ $dataAdmin->last_name }}</h4>
<p>ระบบตรวจสอบพบว่ามีการ Convert Transcoding ล้มเหลว โดยมีรายละเอียดดังนี้</p>
<p>หลักสูตร : <strong>{{ $dataCourse->code }} - {{ $dataCourse->title }}</strong></p>
@if ($dataParentTopic)
<p>หัวข้อหลัก : <strong>{{ $dataParentTopic->title }}</strong></p>
@endif
@if ($dataTopic)
<p>หัวข้อย่อย : <strong>{{ $dataTopic->title }}</strong></p>
@endif
<p>ไฟล์ต้นฉบับ : <strong>{{ $dataVideo->name }}</strong></p>
<p>Bit Rate : <strong>{{ $dataTranscoding->title }}P</strong></p>
<p>ปัญหาที่พบ : <strong style="color: red;">{{ $dataTranscoding->transcode_status_remark }}</strong></p>
<p>วันและเวลาที่พบปัญหา : <strong>{{ $dataTranscoding->modify_datetime }}</strong></p>
<!-- <p><strong>กรุณาติดต่อผู้ดูแลระบบ</strong></p> -->
<!-- <p><a href="{{-- $url --}}" target="_blank">คลิกที่นี่เพื่อตรวจสอบ</a></p> -->
<hr>
<p>สอบถามข้อมูลเพิ่มเติม กรุณาติดต่อ {{config('constants.PROJECT_INFO.CONTACT_CENTER')}} โทร. {{config('constants.PROJECT_INFO.CONTACT_PHONE')}} อีเมล์ : {{config('constants.PROJECT_INFO.CONTACT_MAIL')}}</p>
