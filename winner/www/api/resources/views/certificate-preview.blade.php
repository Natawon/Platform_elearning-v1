<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
@if ($lang == "en")
    <title>Certificate TESTCERT01</title>
@else
    <title>ตัวอย่างใบวุฒิบัตร TESTCERT01</title>
@endif

<style type="text/css" media="all">
/* Utility */
@page { margin: 0px; }
/*html{margin:0px}*/
body {
    position: relative;
    font-family: 'thsarabunnew';
    font-size: 100%;
    /*line-height: 90%;*/
    /*background-color: #FFA400;*/
    color: #000000;
    padding: 40px 40px 20px 40px;
}
.page-break {
    page-break-after: always;
}
.container {
    padding: 50px 30px;
    background-color: #fff;
    border: 10px solid #fff;
    height: 914px;
    overflow: hidden;
    /*border-radius: 50px;*/
}
.header {
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
.col-4 {
    width: 33.3333333%;
}
.col-6 {
    width: 50%;
}
.col-12 {
    width: 100%;
}
.pull-left {
    float: left;
}
.pull-right {
    float: right;
}
.pull-center {
    margin: 0 auto;
}
.clearfix {
    clear: both;
    margin: 0;
    padding: 0;
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
    font-size: 50px;
}
.heading-1 {
    font-size: 16px;
}
.heading-2 {
    font-size: 18px;
}
.f-30 {
    font-size: 30px;
}
.underline {
    border-bottom: 1px solid;
}
.subject {
    text-align: center;
    margin-bottom: 5px;
    font-size: 40px;
}
a {
    text-decoration: none;
}
.section {
    margin-bottom: 20px;
}
.red {
    color: #d9534f;
}
.pos-tl {
    top: 0;
    left: 0;
}
.pos-tr {
    top: 0;
    right: 0;
}
.pos-bl {
    bottom: 0;
    left: 0;
}
.pos-br {
    bottom: 0;
    right: 0;
}
.text-center {
    text-align: center;
}
.m-t-5 {
    margin-top: 5px;
}
.m-t-10 {
    margin-top: 10px;
}
.m-r-5 {
    margin-right: 5px;
}
.m-r-10 {
    margin-right: 10px;
}
.m-r-20 {
    margin-right: 20px;
}
.m-r-30 {
    margin-right: 30px;
}
.m-r-40 {
    margin-right: 40px;
}
.m-r-50 {
    margin-right: 50px;
}
.m-b-5 {
    margin-bottom: 5px;
}
.m-b-10 {
    margin-bottom: 10px;
}
.m-l-5 {
    margin-left: 5px;
}
.m-l-10 {
    margin-left: 10px;
}
.m-l-20 {
    margin-left: 20px;
}
.m-l-30 {
    margin-left: 30px;
}
.m-l-40 {
    margin-left: 40px;
}
.m-l-50 {
    margin-left: 50px;
}
.p-t-5 {
    padding-top: 5px;
}
.p-t-10 {
    padding-top: 10px;
}
.inline-block {
    display: inline-block;
}
.w-100 {
    width: 100% !important;
    /*border: 1px solid green;*/
}
/* End Utility */

/* Content */
.header {
    /*border-bottom: 1px solid;*/
    margin-bottom: 90px;
    position: relative;
}
.header .tbl-header {
    /*border: 1px solid blue;*/
    margin-bottom: 10px;
}
.logo {
    /*height: 100px;*/
    /*border: 1px solid red;*/
}
.logo.no-logo {
    height: 110px;
}
.logo-pos {
    position: relative;
    top: 20px;
}
.logo img {
    /* width: 135px; */
    /*height: 75pt;*/
    height: 110px;
    /*border: 1px solid green;*/
}
.signature {
    /*height: 100px;*/
    /*border: 1px solid red;*/
    margin-bottom: 10px;
}
.signature.no-signature {
    height: 70px;
}
.signature-pos {
    position: relative;
    top: 20px;
}
.signature img {
    /* width: 135px; */
    /*height: 75pt;*/
    height: 60px;
    /*border: 1px solid green;*/
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
.footer {
    position: absolute;
    bottom: 20px;
    width: 100%;
    text-align: center;
    /*margin-top: 126.9px;*/
    /*margin-bottom: 40px;*/
    /*border: 1px dotted red;*/
    font-size: 16px;
}
#section-content {
    text-align: center;
    margin-top: 15px;
}
#section-signature {
    margin-top: 50px;
}
.box-signature {
    width: 380px;
    text-align: center;
}
.tbl-signature {
    line-height: 22px;
}
#watermark-preview {
    position: absolute;
    top: 15%;
    width: 100%;
    text-align: center;
    color: red;
    opacity: 0.08;
}
#watermark-preview h3 {
    margin: 0px;
    font-size: 300px;
}
/* End Content */
</style>
</head>

@php
    // Setup

    // $_imgDefault = 'assets/images/logo@2x.png';
    $_imgDefault = 'assets/images/no-image.png';
    $_nameOfSignatureDefault = 'xxxxxxx xxxxxxxxx';
    $_positionOfSignatureDefault = 'xxxxxxxxxxxxxxxxxxxxxxxx';
    $_positionOfSerialNumber = "pos-br";

    // if ($data->number_of_logo == 1) {
    //     if ($data->logo_align == "R") {
    //         $_positionOfSerialNumber = "pos-tl";
    //     } else {
    //         $_positionOfSerialNumber = "pos-tr";
    //     }
    // } elseif ($data->number_of_logo == 2) {
    //     if ($data->logo_align == "C") {
    //         $_positionOfSerialNumber = "pos-tr";
    //     } else {
    //         $_positionOfSerialNumber = "pos-bl";
    //     }
    // } elseif ($data->number_of_logo == 3) {
    //     $_positionOfSerialNumber = "pos-tr";
    // } else {
    //     $_positionOfSerialNumber = "pos-tr";
    // }

    if ($lang == "en") {
        $_logo_1 = !empty($data->logo_1_en) ? $data->logo_1_en : '';
        $_logo_2 = !empty($data->logo_2_en) ? $data->logo_2_en : '';
        $_logo_3 = !empty($data->logo_3_en) ? $data->logo_3_en : '';

        $_signature_1                = !empty($data->signature_1_en) ? $data->signature_1_en : '';
        $_name_of_signature_1        = !empty($data->name_of_signature_1_en) ? $data->name_of_signature_1_en : '';
        $_position_of_signature_1    = !empty($data->position_of_signature_1_en) ? $data->position_of_signature_1_en : '';
        $_remark_of_signature_1      = !empty($data->remark_of_signature_1_en) ? $data->remark_of_signature_1_en : '';

        $_signature_2                = !empty($data->signature_2_en) ? $data->signature_2_en : '';
        $_name_of_signature_2        = !empty($data->name_of_signature_2_en) ? $data->name_of_signature_2_en : '';
        $_position_of_signature_2    = !empty($data->position_of_signature_2_en) ? $data->position_of_signature_2_en : '';
        $_remark_of_signature_2      = !empty($data->remark_of_signature_2_en) ? $data->remark_of_signature_2_en : '';

        $_signature_3                = !empty($data->signature_3_en) ? $data->signature_3_en : '';
        $_name_of_signature_3        = !empty($data->name_of_signature_3_en) ? $data->name_of_signature_3_en : '';
        $_position_of_signature_3    = !empty($data->position_of_signature_3_en) ? $data->position_of_signature_3_en : '';
        $_remark_of_signature_3      = !empty($data->remark_of_signature_3_en) ? $data->remark_of_signature_3_en : '';
    } else {
        $_logo_1 = !empty($data->logo_1) ? $data->logo_1 : '';
        $_logo_2 = !empty($data->logo_2) ? $data->logo_2 : '';
        $_logo_3 = !empty($data->logo_3) ? $data->logo_3 : '';

        $_signature_1                = !empty($data->signature_1) ? $data->signature_1 : '';
        $_name_of_signature_1        = !empty($data->name_of_signature_1) ? $data->name_of_signature_1 : '';
        $_position_of_signature_1    = !empty($data->position_of_signature_1) ? $data->position_of_signature_1 : '';
        $_remark_of_signature_1      = !empty($data->remark_of_signature_1) ? $data->remark_of_signature_1 : '';

        $_signature_2                = !empty($data->signature_2) ? $data->signature_2 : '';
        $_name_of_signature_2        = !empty($data->name_of_signature_2) ? $data->name_of_signature_2 : '';
        $_position_of_signature_2    = !empty($data->position_of_signature_2) ? $data->position_of_signature_2 : '';
        $_remark_of_signature_2      = !empty($data->remark_of_signature_2) ? $data->remark_of_signature_2 : '';

        $_signature_3                = !empty($data->signature_3) ? $data->signature_3 : '';
        $_name_of_signature_3        = !empty($data->name_of_signature_3) ? $data->name_of_signature_3 : '';
        $_position_of_signature_3    = !empty($data->position_of_signature_3) ? $data->position_of_signature_3 : '';
        $_remark_of_signature_3      = !empty($data->remark_of_signature_3) ? $data->remark_of_signature_3 : '';
    }
@endphp


<body style="background-color: {{$data->is_border == 1 ? $data->border_color : $data->background_color}}; color: {{$data->text_color}};">
    <div id="watermark-preview"><h3>- ตัวอย่าง -</h3></div>
    <div class="box_serial_number {{$_positionOfSerialNumber}}">
        <strong class="code heading-2">CER: TESTCERT0100012560533962</strong><br>
    </div>
    <div class="container" style="background-color: {{$data->background_color}}; border-color: {{$data->background_color}}; border-radius: {{$data->is_border == 1 && $data->border_style == 'radius' ? '50px' : '0px'}};">
        <div class="header">
            @if ($data->number_of_logo == 1)
                <table class="tbl-header">
                    <tr>
                        <td width="100%" align="{{($data->logo_align == 'R' ? 'right' : ($data->logo_align == 'C' ? 'center' : 'left'))}}">
                            <div class="logo">
                                <img src="{{!empty($_logo_1) ? asset('data-file/images/logo/'.$_logo_1) : $_imgDefault}}"/>
                            </div>
                        </td>
                    </tr>
                </table>
            @elseif ($data->number_of_logo == 2)
                <table class="tbl-header">
                    <tr>
                        @if ($data->logo_align == 'C')
                            <td width="100%" align="center">
                                <div class="logo logo-pos">
                                    <img class="" src="{{!empty($_logo_1) ? asset('data-file/images/logo/'.$_logo_1) : $_imgDefault}}"/>
                                    <img class="m-l-50" src="{{!empty($_logo_2) ? asset('data-file/images/logo/'.$_logo_2) : $_imgDefault}}"/>
                                </div>
                            </td>
                        @else
                            <td width="50%" align="left">
                                <div class="logo">
                                    <img class="" src="{{!empty($_logo_1) ? asset('data-file/images/logo/'.$_logo_1) : $_imgDefault}}"/>
                                </div>
                            </td>
                            <td width="50%" align="right">
                                <div class="logo">
                                    <img class="" src="{{!empty($_logo_2) ? asset('data-file/images/logo/'.$_logo_2) : $_imgDefault}}"/>
                                </div>
                            </td>
                        @endif
                    </tr>
                </table>
            @elseif ($data->number_of_logo == 3)
                <table class="tbl-header">
                    <tr>
                        <td width="100%" align="center">
                            <div class="logo logo-pos">
                                <img class="" src="{{!empty($_logo_1) ? asset('data-file/images/logo/'.$_logo_1) : $_imgDefault}}"/>
                                <img class="m-l-50 m-r-50" src="{{!empty($_logo_2) ? asset('data-file/images/logo/'.$_logo_2) : $_imgDefault}}"/>
                                <img class="" src="{{!empty($_logo_3) ? asset('data-file/images/logo/'.$_logo_3) : $_imgDefault}}"/>
                            </div>
                        </td>
                    </tr>
                </table>
            @else
                <table class="tbl-header">
                    <tr>
                        <td width="30%">
                            <div class="logo no-logo"></div>
                        </td>
                    </tr>
                </table>
            @endif
        </div>

        <div id="section-content" class="section">
            <span class="big-heading-1 fw-500">
                @if ($lang == "en")
                    {{$data->body_text_1_en}}
                @else
                    {{$data->body_text_1}}
                @endif
            </span><br>
            <b class="big-heading-1 fw-bold">
                @if ($lang == "en")
                    Mr. John Doe
                @else
                    นายขจรศักดิ์ โดมณี
                @endif
            </b><br>
            <span class="big-heading-1 fw-500">
                @if ($lang == "en")
                    {{$data->body_text_2_en}}
                @else
                    {{$data->body_text_2}}
                @endif
            </span><br>
            <b class="big-heading-1 fw-bold">Gold Flows เงินทองไหลมา</b><br>
            @if ($lang == "en")
                <span class="big-heading fw-500">17 October 2017</span><br>
            @else
                <span class="big-heading fw-500">17 ตุลาคม 2560</span><br>
            @endif

        </div>

        <div id="section-signature" class="section">
            @if ($data->number_of_signature == 1)
                <table class="tbl-signature">
                    <tr>
                        <td class="col-12" align="{{($data->signature_align == 'R' ? 'right' : ($data->signature_align == 'C' ? 'center' : 'left'))}}" valign="top">
                            <div class="box-signature {{($data->signature_align == 'R' ? 'pull-right' : ($data->signature_align == 'C' ? 'pull-center w-100' : 'pull-left'))}}">
                                <div class="signature">
                                    <img class="" src="{{!empty($_signature_1) ? asset('data-file/images/signature/'.$_signature_1) : $_imgDefault}}"/>
                                </div>
                                <span class="f-30">{{'( '.(!empty($_name_of_signature_1) ? $_name_of_signature_1 : $_nameOfSignatureDefault).' )'}}</span><br>
                                <span class="f-30">{{!empty($_position_of_signature_1) ? $_position_of_signature_1 : $_positionOfSignatureDefault}}</span><br>
                                <span class="f-30">{{!empty($_remark_of_signature_1) ? $_remark_of_signature_1 : ''}}</span>
                            </div>
                        </td>
                    </tr>
                </table>
            @elseif ($data->number_of_signature == 2)
                <table class="tbl-signature">
                    <tr>
                        @if ($data->signature_align == "C")
                            <td class="col-6" align="center" valign="top">
                                <div class="box-signature pull-right">
                                    <div class="signature">
                                        <img class="" src="{{!empty($_signature_1) ? asset('data-file/images/signature/'.$_signature_1) : $_imgDefault}}"/>
                                    </div>
                                    <span class="f-30">{{'( '.(!empty($_name_of_signature_1) ? $_name_of_signature_1 : $_nameOfSignatureDefault).' )'}}</span><br>
                                    <span class="f-30">{{!empty($_position_of_signature_1) ? $_position_of_signature_1 : $_positionOfSignatureDefault}}</span><br>
                                    <span class="f-30">{{!empty($_remark_of_signature_1) ? $_remark_of_signature_1 : ''}}</span>
                                </div>
                            </td>
                            <td class="col-6" align="center" valign="top">
                                <div class="box-signature pull-left">
                                    <div class="signature">
                                        <img class="" src="{{!empty($_signature_2) ? asset('data-file/images/signature/'.$_signature_2) : $_imgDefault}}"/>
                                    </div>
                                    <span class="f-30">{{'( '.(!empty($_name_of_signature_2) ? $_name_of_signature_2 : $_nameOfSignatureDefault).' )'}}</span><br>
                                    <span class="f-30">{{!empty($_position_of_signature_2) ? $_position_of_signature_2 : $_positionOfSignatureDefault}}</span><br>
                                    <span class="f-30">{{!empty($_remark_of_signature_2) ? $_remark_of_signature_2 : ''}}</span>
                                </div>
                            </td>
                        @else
                            <td class="col-6" align="center" valign="top">
                                <div class="signature">
                                    <img class="" src="{{!empty($_signature_1) ? asset('data-file/images/signature/'.$_signature_1) : $_imgDefault}}"/>
                                </div>
                                <span class="f-30">{{'( '.(!empty($_name_of_signature_1) ? $_name_of_signature_1 : $_nameOfSignatureDefault).' )'}}</span><br>
                                <span class="f-30">{{!empty($_position_of_signature_1) ? $_position_of_signature_1 : $_positionOfSignatureDefault}}</span><br>
                                <span class="f-30">{{!empty($_remark_of_signature_1) ? $_remark_of_signature_1 : ''}}</span>
                            </td>
                            <td class="col-6" align="center" valign="top">
                                <div class="signature">
                                    <img class="" src="{{!empty($_signature_2) ? asset('data-file/images/signature/'.$_signature_2) : $_imgDefault}}"/>
                                </div>
                                <span class="f-30">{{'( '.(!empty($_name_of_signature_2) ? $_name_of_signature_2 : $_nameOfSignatureDefault).' )'}}</span><br>
                                <span class="f-30">{{!empty($_position_of_signature_2) ? $_position_of_signature_2 : $_positionOfSignatureDefault}}</span><br>
                                <span class="f-30">{{!empty($_remark_of_signature_2) ? $_remark_of_signature_2 : ''}}</span>
                            </td>
                        @endif
                    </tr>
                </table>
            @elseif ($data->number_of_signature == 3)
                <table class="tbl-signature">
                    <tr>
                        <td class="col-4" align="center" valign="top">
                            <div class="signature">
                                <img class="" src="{{!empty($_signature_1) ? asset('data-file/images/signature/'.$_signature_1) : $_imgDefault}}"/>
                            </div>
                            <span class="f-30">{{'( '.(!empty($_name_of_signature_1) ? $_name_of_signature_1 : $_nameOfSignatureDefault).' )'}}</span><br>
                            <span class="f-30">{{!empty($_position_of_signature_1) ? $_position_of_signature_1 : $_positionOfSignatureDefault}}</span><br>
                            <span class="f-30">{{!empty($_remark_of_signature_1) ? $_remark_of_signature_1 : ''}}</span>
                        </td>
                        <td class="col-4" align="center" valign="top">
                            <div class="signature">
                                <img class="" src="{{!empty($_signature_2) ? asset('data-file/images/signature/'.$_signature_2) : $_imgDefault}}"/>
                            </div>
                            <span class="f-30">{{'( '.(!empty($_name_of_signature_2) ? $_name_of_signature_2 : $_nameOfSignatureDefault).' )'}}</span><br>
                            <span class="f-30">{{!empty($_position_of_signature_2) ? $_position_of_signature_2 : $_positionOfSignatureDefault}}</span><br>
                            <span class="f-30">{{!empty($_remark_of_signature_2) ? $_remark_of_signature_2 : ''}}</span>
                        </td>
                        <td class="col-4" align="center" valign="top">
                            <div class="signature">
                                <img class="" src="{{!empty($_signature_3) ? asset('data-file/images/signature/'.$_signature_3) : $_imgDefault}}"/>
                            </div>
                            <span class="f-30">{{'( '.(!empty($_name_of_signature_3) ? $_name_of_signature_3 : $_nameOfSignatureDefault).' )'}}</span><br>
                            <span class="f-30">{{!empty($_position_of_signature_3) ? $_position_of_signature_3 : $_positionOfSignatureDefault}}</span><br>
                            <span class="f-30">{{!empty($_remark_of_signature_3) ? $_remark_of_signature_3 : ''}}</span>
                        </td>
                    </tr>
                </table>
            @else
            @endif
        </div>

        <div class="clearfix"></div>

        <div class="footer fw-bold">
            @if ($lang == "en")
                This certificate was created by automated system. <br>
                {{$data->footer_text_en}}
            @else
                วุฒิบัตรฉบับนี้ถูกสร้างขึ้นโดยระบบอัตโนมัติ <br>
                {{$data->footer_text}}
            @endif
        </div>
    </div>
</body>
</html>



