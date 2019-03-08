<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
@if ($lang == "en")
    <title>Certificate {{$data->courses->code}}</title>
@else
    <title>ใบวุฒิบัตร {{$data->courses->code}}</title>
@endif

<style>
/* Utility */
@page { margin: 0px; }
/*html{margin:0px}*/
body {
    font-family: 'thsarabunnew';
    font-size: 100%;
    background-color: #2a8039;
    padding: 40px 40px 20px 40px;
}
.page-break {
    page-break-after: always;
}
.container {
    padding: 50px 30px;
    background-color: #fff;
    border: 10px solid #fff;
    border-radius: 50px;
}
header {
    margin-bottom: 50px;
}
table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    margin-bottom: 20px;
}
strong {
    line-height: 0;
}
.fw-500 {
    font-weight: 500;
}
.fw-bold {
    font-weight: bold;
}
.big-heading {
    font-size: 32px;
}
.big-heading-1 {
    font-size: 52px;
}
.heading-1 {
    font-size: 18px;
}
.heading-2 {
    font-size: 20px;
}
.underline {
    border-bottom: 1px solid;
}
.subject {
    text-align: center;
    margin-bottom: 5px;
    font-size: 42px;
}
a {
    text-decoration: none;
}
section {
    margin-bottom: 20px;
}
.red {
    color: #d9534f;
}
/* End Utility */

/* Content */
header {
    /*border-bottom: 1px solid;*/
    margin-bottom: 100px;
}
header .tbl-header {
    margin-bottom: 10px;
}
header .logo img {
    /* width: 135px; */
    height: 100px;
    /* border: 1px solid; */
}
/* header .our_company_info .company_name {
    font-size: 18px;
} */
header .header_right .code  {
    font-size: 18px;
}
.box_serial_number {
    position: absolute;
}
.box_serial_number .code  {
    font-size: 16px;
}
.box_serial_number.pos-tl {
    top: 80px;
    left: 80px;
}
.box_serial_number.pos-tr {
    top: 80px;
    right: 80px;
}
.box_serial_number.pos-bl {
    bottom: 10px;
    left: 80px;
}
.box_serial_number.pos-br {
    bottom: 10px;
    right: 80px;
}
.notice {
    text-align: right;
}
footer {
    text-align: center;
    margin-top: 126.9px;
    margin-bottom: 40px;
    /*position: fixed;*/
    /*bottom: 20;*/
    /*border-top: 1px solid;*/
    font-size: 16px;
}
#section-content {
    text-align: center;
    margin-top: 20px;
}
/* End Content */
</style>
</head>
<body>
    <div class="box_serial_number pos-br">
        <strong class="code heading-2">{{$data->certificate_reference_number}}</strong><br>
    </div>
    <div class="container">
        <header>
            <table class="tbl-header">
                <tr>
                    <td width="30%">
                        <div class="logo">
                            <img src="assets/images/logo@2x.jpg" />
                        </div>
                    </td>
                </tr>
            </table>
        </header>

        <section id="section-content">
            <span class="big-heading-1 fw-500">
                @if ($lang == "en")
                    This certificate is provided to show that
                @else
                    วุฒิบัตรฉบับนี้ให้ไว้เพื่อแสดงว่า
                @endif
            </span><br><br>
            <!-- <b class="big-heading-1 fw-bold">คุณ{{!!$data->member->first_name!!}} {{$data->member->last_name}}</b><br><br> -->
            <b class="big-heading-1 fw-bold">
                @if ($lang == "en")
                    {{$data->member->name_title_en}} {{$data->member->first_name_en}} {{$data->member->last_name_en}}
                @else
                    @if (!empty($data->member->name_title))
                        {{$data->member->name_title}}{{$data->member->first_name}} {{$data->member->last_name}}
                    @else
                        คุณ{{$data->member->first_name}} {{$data->member->last_name}}
                    @endif
                @endif
            </b><br><br>
            <span class="big-heading-1 fw-500">
                @if ($lang == "en")
                    Through Winner Estate Education course
                @else
                    ผ่านการเรียน Winner Estate Education ครบถ้วนตามหลักสูตร
                @endif
            </span><br><br>
            <b class="big-heading-1 fw-bold">{{$data->courses->title}}</b><br>
            <span class="big-heading fw-500">{{$data->datetime_full_format}}</span><br>
        </section>

        <footer class="fw-bold">
            @if ($lang == "en")
                This certificate was created by automated system. <br>
                For more information, please contact {{config('constants.PROJECT_INFO.CONTACT_PHONE')}} or E-Mail: {{config('constants.PROJECT_INFO.CONTACT_MAIL')}}. Website: {{config('constants.PROJECT_INFO.CONTACT_WEBSITE')}}
            @else
                วุฒิบัตรฉบับนี้ถูกสร้างขึ้นโดยระบบอัตโนมัติ<br>
                สอบถามข้อมูลเพิ่มเติมกรุณาติดต่อ {{config('constants.PROJECT_INFO.CONTACT_CENTER')}} โทรศัพท์ {{config('constants.PROJECT_INFO.CONTACT_PHONE')}} หรือ e-mail: {{config('constants.PROJECT_INFO.CONTACT_MAIL')}} เว็บไซต์ {{config('constants.PROJECT_INFO.CONTACT_WEBSITE')}}
            @endif
        </footer>
    </div>
</body>
</html>



