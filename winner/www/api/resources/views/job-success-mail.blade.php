<h4>ถึง ผู้ดูแลหลักสูตรและผู้เกี่ยวข้อง</h4>
<p>ระบบได้ทำการแปลงไฟล์วีดีโอสำเร็จเรียบร้อย โดยมีรายละเอียดดังนี้</p>
<p>หลักสูตร : <strong>{{ $dataCourse->code }} - {{ $dataCourse->title }}</strong></p>
@if ($dataParentTopic)
<p>หัวข้อหลัก : <strong>{{ $dataParentTopic->title }}</strong></p>
@endif
@if ($dataTopic)
<p>หัวข้อย่อย : <strong>{{ $dataTopic->title }}</strong></p>
@endif
<p>ไฟล์ต้นฉบับ : <strong>{{ $dataVideo->name }}</strong></p>
<p>วันและเวลาที่เริ่มแปลงไฟล์ : <strong>{{ $dataJob->sc_job_start_time }}</strong></p>
<p>วันและเวลาที่แปลงไฟล์สำเร็จ : <strong>{{ $dataJob->modify_datetime }}</strong></p>
<p>ดำเนินการโดย : <strong>{{ $action_by }}</strong></p>
<!-- <p><strong>กรุณาติดต่อผู้ดูแลระบบ</strong></p> -->
<!-- <p><a href="{{-- $url --}}" target="_blank">คลิกที่นี่เพื่อตรวจสอบ</a></p> -->
<hr>
<p>สอบถามข้อมูลเพิ่มเติม กรุณาติดต่อ {{config('constants.PROJECT_INFO.CONTACT_CENTER')}} โทร. {{config('constants.PROJECT_INFO.CONTACT_PHONE')}} อีเมล์ : {{config('constants.PROJECT_INFO.CONTACT_MAIL')}}</p>
