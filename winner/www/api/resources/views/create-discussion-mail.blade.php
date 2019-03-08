<h4>ถึง ผู้ดูแลหลักสูตรและผู้เกี่ยวข้อง</h4>
<p>ระบบได้พบว่ามีการตั้งหัวเรื่องสนทนาใหม่ โดยมีรายละเอียดดังนี้</p>
<p>หลักสูตร : <strong>{{ $dataCourse->code }} - {{ $dataCourse->title }}</strong></p>
<p>หัวเรื่อง : <strong>{{ $dataDiscussion->topic }}</strong></p>
<p>ข้อความ :
	<div style="border: 1px solid #333; padding: 20px; border-radius: 2px;">
		@if ($dataDiscussion->file)
			<img src="{{asset('data-file/discussion/'.$dataDiscussion->file)}}" style="max-width: 35%; margin-bottom: 10px;"><br>
		@endif
		<strong>{{ $dataDiscussion->description }}</strong>
	</div>
</p>
<p>โดย : <strong>คุณ{{ $dataMember->first_name }} {{ $dataMember->last_name }}</strong></p>
<p>วันและเวลาที่ตั้งหัวเรื่อง: <strong>{{ $dataDiscussion->create_datetime }}</strong></p>
<hr>
<p>สอบถามข้อมูลเพิ่มเติม กรุณาติดต่อ {{config('constants.PROJECT_INFO.CONTACT_CENTER')}} โทร. {{config('constants.PROJECT_INFO.CONTACT_PHONE')}} อีเมล์ : {{config('constants.PROJECT_INFO.CONTACT_MAIL')}}</p>
