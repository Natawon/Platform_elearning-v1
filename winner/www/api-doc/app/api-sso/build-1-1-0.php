<?php
// ==================================================================================================================== //
// ================================================== BEGIN SETGroup ================================================== //
// ==================================================================================================================== //

/**
* @api {post} /set/SETGroup/login E1: Single Sign-on
* @apiName E1: Single Sign-on
* @apiGroup 1 SET Member
*
* @apiDescription Login to e-learning site for SET Member.
* @apiVersion 1.1.0
*
* @apiParam {String} company_code รหัสอ้างอิง Company
* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference)
* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
* @apiParam {Varchar(128)} user_profile.email อีเมลล์
* @apiParam {Varchar(13)} user_profile.id_card เลขบัตรประชาชน
* @apiParam {date} user_profile.birth_date วันเกิด
* @apiParam {Varchar(10)} user_profile.mobile_number หมายเลขโทรศัพท์มือถือ, Validate format
* @apiParam {Int(3)} user_profile.education_degree_id วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก
* @apiParam {Varchar(13)} user_profile.occupation_id รหัสพนักงาน
* @apiParam {Varchar(128)} user_profile.job_title อาชีพ
* @apiParam {Varchar(128)} user_profile.income รายได้
* @apiParam {Varchar(255)} user_profile.address ที่อยู่
* @apiParam {Varchar(128)} user_profile.province จังหวัด
* @apiParam {Varchar(8)} user_profile.zip_code รหัสไปรษณีย์
* @apiParam {String} action หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ
*
* @apiSuccess (Success 200) {String} token Access Token ที่ใช้ในการเรียก Service ต่าง ๆ
* @apiSuccessExample {json} Success-Response:
*	HTTP/1.1 200 OK
*	{
*	   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M"
*	}
*
* @apiUse ClientFullErrorTH
*
* @apiUse ServerErrorTH
*/




/**
* @api {post} /set/SETGroup/courses E2: Get Course List
* @apiName E2: Get Course List
* @apiGroup 1 SET Member
*
* @apiDescription Get course list for SET Member.
* @apiVersion 1.1.0
*
* @apiParam {String} company_code รหัสอ้างอิง Company
*
* @apiSuccess {Object[]} courses รายการวิชาเรียน
* @apiSuccess {Int(3)} courses.id ค่ารหัสอ้างอิง Courses
* @apiSuccess {Varchar(50)} courses.code ค่ารหัสอ้างอิง Courses
* @apiSuccess {Varchar(255)} courses.title ค่ารหัสอ้างอิง Courses
* @apiSuccess {Varchar(128)} courses.thumbnail Path รูปภาพ Thumbnail
* @apiSuccess {Int(7)} courses.price ราคาของ Course
* @apiSuccess {Boolean} courses.latest <code>true = course ใหม่</code>, <code>false = ไม่ใช่ course ใหม่</code>
* @apiSuccess {Boolean} courses.recommended <code>true = course แนะนำ</code>, <code>false = ไม่ใช่ course แนะนำ</code>
* @apiSuccess {Boolean} courses.free <code>true = course ฟรี</code>, <code>false = ไม่ใช่ course ฟรี</code>
* @apiSuccess {Varchar(128)} courses.categories กลุ่มของหลักสูตร
* @apiSuccess {Varchar(128)} courses.categories.id ค่ารหัสอ้างอิง ของกลุ่ม
* @apiSuccess {Varchar(128)} courses.categories.title ชื่อของกลุ่ม
* @apiSuccess {Varchar(128)} courses.categories.hex_color ค่าสีของกลุ่ม Ex. #FFFFFF
*
* @apiUse ClientErrorTH
*
* @apiUse ServerErrorTH
*/




/**
* @api {post} /set/SETGroup/courses/:course_id E3: View Course Info
* @apiName E3: View Course Info
* @apiGroup 1 SET Member
*
* @apiDescription Get course information for SET Member.
* @apiVersion 1.1.0
*
* @apiParam {Int(3)} :course_id รหัสอ้างอิงหลักสูตร
* @apiParam {String} company_code รหัสอ้างอิง Company
* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference)
* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
* @apiParam {Varchar(128)} user_profile.email อีเมลล์
* @apiParam {Varchar(13)} user_profile.id_card เลขบัตรประชาชน
* @apiParam {date} user_profile.birth_date วันเกิด
* @apiParam {Varchar(10)} user_profile.mobile_number หมายเลขโทรศัพท์มือถือ, Validate format
* @apiParam {Int(3)} user_profile.education_degree_id วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก
* @apiParam {Varchar(13)} user_profile.occupation_id รหัสพนักงาน
* @apiParam {Varchar(128)} user_profile.job_title อาชีพ
* @apiParam {Varchar(128)} user_profile.income รายได้
* @apiParam {Varchar(255)} user_profile.address ที่อยู่
* @apiParam {Varchar(128)} user_profile.province จังหวัด
* @apiParam {Varchar(8)} user_profile.zip_code รหัสไปรษณีย์
* @apiParam {String} action หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ
*
* @apiSuccess (Success 200) {String} token Access Token ที่ใช้ในการเรียก Service ต่าง ๆ
* @apiSuccessExample {json} Success-Response:
*	HTTP/1.1 200 OK
*	{
*	   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M"
*	}
*
* @apiUse ClientFullErrorTH
*
* @apiUse ServerErrorTH
*/




/**
* @api {post} /set/SETGroup/courses/:course_id/download/certificate E4: Download Certificate
* @apiName E4: Download Certificate
* @apiGroup 1 SET Member
*
* @apiDescription Download e-cerfiticate file for SET Member.
* @apiVersion 1.1.0
*
* @apiParam {Int(3)} :course_id รหัสอ้างอิงหลักสูตร
* @apiParam {String} company_code รหัสอ้างอิง Company
* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference)
* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
* @apiParam {Varchar(128)} user_profile.email อีเมลล์
* @apiParam {Varchar(13)} user_profile.id_card เลขบัตรประชาชน
* @apiParam {date} user_profile.birth_date วันเกิด
* @apiParam {Varchar(10)} user_profile.mobile_number หมายเลขโทรศัพท์มือถือ, Validate format
* @apiParam {Int(3)} user_profile.education_degree_id วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก
* @apiParam {Varchar(13)} user_profile.occupation_id รหัสพนักงาน
* @apiParam {Varchar(128)} user_profile.job_title อาชีพ
* @apiParam {Varchar(128)} user_profile.income รายได้
* @apiParam {Varchar(255)} user_profile.address ที่อยู่
* @apiParam {Varchar(128)} user_profile.province จังหวัด
* @apiParam {Varchar(8)} user_profile.zip_code รหัสไปรษณีย์
*
* @apiSuccess (Success 200 (Download e-Certificate PDF File)) -
*
* @apiUse ClientFullErrorTH
*
* @apiUse ServerErrorTH
*/


// ==================================================================================================================== //
// ============================================== BEGIN SETListedCompany ============================================== //
// ==================================================================================================================== //


/**
* @api {post} /set/SETListedCompany/login E1: Single Sign-on
* @apiName E1: Single Sign-on
* @apiGroup 2 SET Listed Company
*
* @apiDescription Login to e-learning site for Listed Company.
* @apiVersion 1.1.0
*
* @apiParam {String} company_code รหัสอ้างอิง Company
* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference), ใช้เพื่อ link กับ SET Regis
* @apiParam {Varchar(128)} user_profile.name_title คำนำหน้าชื่อ
* @apiParam {Varchar(1)} user_profile.gender เพศ<br>F = หญิง<br>M = ชาย
* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
* @apiParam {Varchar(128)} user_profile.email อีเมลล์
* @apiParam {Varchar(13)} user_profile.id_card เลขบัตรประชาชน
* @apiParam {Varchar(10)} user_profile.mobile_number หมายเลขโทรศัพท์มือถือ, Validate format
* @apiParam {Varchar(128)} user_profile.institution_id รหัสบริษัทจดทะเบียน เช่น BBL, KGI
* @apiParam {Int(3)} user_profile.position_id ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office
* @apiParam {Varchar(128)} user_profile.department ฝ่ายงาน
* @apiParam {Varchar(128)} user_profile.role บทบาทในระบบต้นทาง
* @apiParam {String} action หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ
*
* @apiSuccess (Success 200) {String} token Access Token ที่ใช้ในการเรียก Service ต่าง ๆ
* @apiSuccessExample {json} Success-Response:
*	HTTP/1.1 200 OK
*	{
*	   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M"
*	}
*
* @apiUse ClientFullErrorTH
*
* @apiUse ServerErrorTH
*/




/**
* @api {post} /set/SETListedCompany/courses E2: Get Course List
* @apiName E2: Get Course List
* @apiGroup 2 SET Listed Company
*
* @apiDescription Get course list for Listed Company.
* @apiVersion 1.1.0
*
* @apiParam {String} company_code รหัสอ้างอิง Company
*
* @apiSuccess {Object[]} courses รายการวิชาเรียน
* @apiSuccess {Int(3)} courses.id ค่ารหัสอ้างอิง Courses
* @apiSuccess {Varchar(50)} courses.code ค่ารหัสอ้างอิง Courses
* @apiSuccess {Varchar(255)} courses.title ค่ารหัสอ้างอิง Courses
* @apiSuccess {Varchar(128)} courses.thumbnail Path รูปภาพ Thumbnail
* @apiSuccess {Int(7)} courses.price ราคาของ Course
* @apiSuccess {Boolean} courses.latest <code>true = course ใหม่</code>, <code>false = ไม่ใช่ course ใหม่</code>
* @apiSuccess {Boolean} courses.recommended <code>true = course แนะนำ</code>, <code>false = ไม่ใช่ course แนะนำ</code>
* @apiSuccess {Boolean} courses.free <code>true = course ฟรี</code>, <code>false = ไม่ใช่ course ฟรี</code>
* @apiSuccess {Varchar(128)} courses.categories กลุ่มของหลักสูตร
* @apiSuccess {Varchar(128)} courses.categories.id ค่ารหัสอ้างอิง ของกลุ่ม
* @apiSuccess {Varchar(128)} courses.categories.title ชื่อของกลุ่ม
* @apiSuccess {Varchar(128)} courses.categories.hex_color ค่าสีของกลุ่ม Ex. #FFFFFF
*
* @apiUse ClientErrorTH
*
* @apiUse ServerErrorTH
*/




/**
* @api {post} /set/SETListedCompany/courses/:course_id E3: View Course Info
* @apiName E3: View Course Info
* @apiGroup 2 SET Listed Company
*
* @apiDescription Get course information for Listed Company.
* @apiVersion 1.1.0
*
* @apiParam {Int(3)} :course_id รหัสอ้างอิงหลักสูตร
* @apiParam {String} company_code รหัสอ้างอิง Company
* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference), ใช้เพื่อ link กับ SET Regis
* @apiParam {Varchar(128)} user_profile.name_title คำนำหน้าชื่อ
* @apiParam {Varchar(1)} user_profile.gender เพศ<br>F = หญิง<br>M = ชาย
* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
* @apiParam {Varchar(128)} user_profile.email อีเมลล์
* @apiParam {Varchar(13)} user_profile.id_card เลขบัตรประชาชน
* @apiParam {Varchar(10)} user_profile.mobile_number หมายเลขโทรศัพท์มือถือ, Validate format
* @apiParam {Varchar(128)} user_profile.institution_id รหัสบริษัทจดทะเบียน เช่น BBL, KGI
* @apiParam {Int(3)} user_profile.position_id ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office
* @apiParam {Varchar(128)} user_profile.department ฝ่ายงาน
* @apiParam {Varchar(128)} user_profile.role บทบาทในระบบต้นทาง
* @apiParam {String} action หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ
*
* @apiSuccess (Success 200) {String} token Access Token ที่ใช้ในการเรียก Service ต่าง ๆ
* @apiSuccessExample {json} Success-Response:
*	HTTP/1.1 200 OK
*	{
*	   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M"
*	}
*
* @apiUse ClientFullErrorTH
*
* @apiUse ServerErrorTH
*/




/**
* @api {post} /set/SETListedCompany/courses/:course_id/download/certificate E4: Download Certificate
* @apiName E4: Download Certificate
* @apiGroup 2 SET Listed Company
*
* @apiDescription Download e-cerfiticate file for Listed Company.
* @apiVersion 1.1.0
*
* @apiParam {Int(3)} :course_id รหัสอ้างอิงหลักสูตร
* @apiParam {String} company_code รหัสอ้างอิง Company
* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference), ใช้เพื่อ link กับ SET Regis
* @apiParam {Varchar(128)} user_profile.name_title คำนำหน้าชื่อ
* @apiParam {Varchar(1)} user_profile.gender เพศ<br>F = หญิง<br>M = ชาย
* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
* @apiParam {Varchar(128)} user_profile.email อีเมลล์
* @apiParam {Varchar(13)} user_profile.id_card เลขบัตรประชาชน
* @apiParam {Varchar(10)} user_profile.mobile_number หมายเลขโทรศัพท์มือถือ, Validate format
* @apiParam {Varchar(128)} user_profile.institution_id รหัสบริษัทจดทะเบียน เช่น BBL, KGI
* @apiParam {Int(3)} user_profile.position_id ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office
* @apiParam {Varchar(128)} user_profile.department ฝ่ายงาน
* @apiParam {Varchar(128)} user_profile.role บทบาทในระบบต้นทาง
*
* @apiSuccess (Success 200 (Download e-Certificate PDF File)) -
*
* @apiUse ClientFullErrorTH
*
* @apiUse ServerErrorTH
*/


// ==================================================================================================================== //
// ================================================= BEGIN SETBroker ================================================== //
// ==================================================================================================================== //


/**
* @api {post} /set/SETBroker/login E1: Single Sign-on
* @apiName E1: Single Sign-on
* @apiGroup 3 SET Broker
*
* @apiDescription Login to e-learning site for Broker.
* @apiVersion 1.1.0
*
* @apiParam {String} company_code รหัสอ้างอิง Company
* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference)
* @apiParam {Varchar(128)} user_profile.name_title คำนำหน้าชื่อ
* @apiParam {Varchar(1)} user_profile.gender เพศ<br>F = หญิง<br>M = ชาย
* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
* @apiParam {Varchar(128)} user_profile.email อีเมลล์
* @apiParam {Varchar(13)} user_profile.id_card เลขบัตรประชาชน
* @apiParam {date} user_profile.birth_date วันเกิด
* @apiParam {Varchar(10)} user_profile.mobile_number หมายเลขโทรศัพท์มือถือ, Validate format
* @apiParam {Varchar(128)} user_profile.institution_id รหัสบริษัทหลักทรัพย์
* @apiParam {Int(3)} user_profile.license_type_id ประเภทใบอนุญาต<br>0 = ไม่มี<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = รหัสพนักงาน
* @apiParam {Int(3)} user_profile.license_id เลขที่ใบอนุญาต
* @apiParam {Int(3)} user_profile.position_id ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office
* @apiParam {Varchar(128)} user_profile.department ฝ่ายงาน
* @apiParam {Int(3)} user_profile.education_degree_id วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก
* @apiParam {Varchar(13)} user_profile.occupation_id รหัสพนักงาน
* @apiParam {String} action หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ
*
* @apiSuccess (Success 200) {String} token Access Token ที่ใช้ในการเรียก Service ต่าง ๆ
* @apiSuccessExample {json} Success-Response:
*	HTTP/1.1 200 OK
*	{
*	   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M"
*	}
*
* @apiUse ClientFullErrorTH
*
* @apiUse ServerErrorTH
*/




/**
* @api {post} /set/SETBroker/courses E2: Get Course List
* @apiName E2: Get Course List
* @apiGroup 3 SET Broker
*
* @apiDescription Get course list for Broker.
* @apiVersion 1.1.0
*
* @apiParam {String} company_code รหัสอ้างอิง Company
*
* @apiSuccess {Object[]} courses รายการวิชาเรียน
* @apiSuccess {Int(3)} courses.id ค่ารหัสอ้างอิง Courses
* @apiSuccess {Varchar(50)} courses.code ค่ารหัสอ้างอิง Courses
* @apiSuccess {Varchar(255)} courses.title ค่ารหัสอ้างอิง Courses
* @apiSuccess {Varchar(128)} courses.thumbnail Path รูปภาพ Thumbnail
* @apiSuccess {Int(7)} courses.price ราคาของ Course
* @apiSuccess {Boolean} courses.latest <code>true = course ใหม่</code>, <code>false = ไม่ใช่ course ใหม่</code>
* @apiSuccess {Boolean} courses.recommended <code>true = course แนะนำ</code>, <code>false = ไม่ใช่ course แนะนำ</code>
* @apiSuccess {Boolean} courses.free <code>true = course ฟรี</code>, <code>false = ไม่ใช่ course ฟรี</code>
* @apiSuccess {Varchar(128)} courses.categories กลุ่มของหลักสูตร
* @apiSuccess {Varchar(128)} courses.categories.id ค่ารหัสอ้างอิง ของกลุ่ม
* @apiSuccess {Varchar(128)} courses.categories.title ชื่อของกลุ่ม
* @apiSuccess {Varchar(128)} courses.categories.hex_color ค่าสีของกลุ่ม Ex. #FFFFFF
*
* @apiUse ClientErrorTH
*
* @apiUse ServerErrorTH
*/




/**
* @api {post} /set/SETBroker/courses/:course_id E3: View Course Info
* @apiName E3: View Course Info
* @apiGroup 3 SET Broker
*
* @apiDescription Get course information for Broker.
* @apiVersion 1.1.0
*
* @apiParam {Int(3)} :course_id รหัสอ้างอิงหลักสูตร
* @apiParam {String} company_code รหัสอ้างอิง Company
* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference)
* @apiParam {Varchar(128)} user_profile.name_title คำนำหน้าชื่อ
* @apiParam {Varchar(1)} user_profile.gender เพศ<br>F = หญิง<br>M = ชาย
* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
* @apiParam {Varchar(128)} user_profile.email อีเมลล์
* @apiParam {Varchar(13)} user_profile.id_card เลขบัตรประชาชน
* @apiParam {date} user_profile.birth_date วันเกิด
* @apiParam {Varchar(10)} user_profile.mobile_number หมายเลขโทรศัพท์มือถือ, Validate format
* @apiParam {Varchar(128)} user_profile.institution_id รหัสบริษัทหลักทรัพย์
* @apiParam {Int(3)} user_profile.license_type_id ประเภทใบอนุญาต<br>0 = ไม่มี<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = รหัสพนักงาน
* @apiParam {Int(3)} user_profile.license_id เลขที่ใบอนุญาต
* @apiParam {Int(3)} user_profile.position_id ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office
* @apiParam {Varchar(128)} user_profile.department ฝ่ายงาน
* @apiParam {Int(3)} user_profile.education_degree_id วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก
* @apiParam {Varchar(13)} user_profile.occupation_id รหัสพนักงาน
* @apiParam {String} action หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ
*
* @apiSuccess (Success 200) {String} token Access Token ที่ใช้ในการเรียก Service ต่าง ๆ
* @apiSuccessExample {json} Success-Response:
*	HTTP/1.1 200 OK
*	{
*	   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M"
*	}
*
* @apiUse ClientFullErrorTH
*
* @apiUse ServerErrorTH
*/




/**
* @api {post} /set/SETBroker/courses/:course_id/download/certificate E4: Download Certificate
* @apiName E4: Download Certificate
* @apiGroup 3 SET Broker
*
* @apiDescription Download e-cerfiticate file for Broker.
* @apiVersion 1.1.0
*
* @apiParam {Int(3)} :course_id รหัสอ้างอิงหลักสูตร
* @apiParam {String} company_code รหัสอ้างอิง Company
* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference)
* @apiParam {Varchar(128)} user_profile.name_title คำนำหน้าชื่อ
* @apiParam {Varchar(1)} user_profile.gender เพศ<br>F = หญิง<br>M = ชาย
* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
* @apiParam {Varchar(128)} user_profile.email อีเมลล์
* @apiParam {Varchar(13)} user_profile.id_card เลขบัตรประชาชน
* @apiParam {date} user_profile.birth_date วันเกิด
* @apiParam {Varchar(10)} user_profile.mobile_number หมายเลขโทรศัพท์มือถือ, Validate format
* @apiParam {Varchar(128)} user_profile.institution_id รหัสบริษัทหลักทรัพย์
* @apiParam {Int(3)} user_profile.license_type_id ประเภทใบอนุญาต<br>0 = ไม่มี<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = รหัสพนักงาน
* @apiParam {Int(3)} user_profile.license_id เลขที่ใบอนุญาต
* @apiParam {Int(3)} user_profile.position_id ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office
* @apiParam {Varchar(128)} user_profile.department ฝ่ายงาน
* @apiParam {Int(3)} user_profile.education_degree_id วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก
* @apiParam {Varchar(13)} user_profile.occupation_id รหัสพนักงาน
*
* @apiSuccess (Success 200 (Download e-Certificate PDF File)) -
*
* @apiUse ClientFullErrorTH
*
* @apiUse ServerErrorTH
*/


// ==================================================================================================================== //
// ================================================= BEGIN SETStudent ================================================= //
// ==================================================================================================================== //


/**
* @api {post} /set/SETStudent/login E1: Single Sign-on
* @apiName E1: Single Sign-on
* @apiGroup 4 SET Student
*
* @apiDescription Login to e-learning site for Student.
* @apiVersion 1.1.0
*
* @apiParam {String} company_code รหัสอ้างอิง Company
* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference)
* @apiParam {Varchar(128)} user_profile.name_title คำนำหน้าชื่อ
* @apiParam {Varchar(1)} user_profile.gender เพศ<br>F = หญิง<br>M = ชาย
* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
* @apiParam {Varchar(128)} user_profile.email อีเมลล์
* @apiParam {Varchar(13)} user_profile.id_card เลขบัตรประชาชน
* @apiParam {date} user_profile.birth_date วันเกิด
* @apiParam {Varchar(10)} user_profile.mobile_number หมายเลขโทรศัพท์มือถือ, Validate format
* @apiParam {Varchar(128)} user_profile.institution_id รหัสมหาวิทยาลัย<br>21 = มหาวิทยาลัยเกษตรศาสตร์<br>...
* @apiParam {Int(3)} user_profile.faculty_id รหัสคณะ<br>32 = คณะเศรษฐศาสตร์<br>...
* @apiParam {Varchar(13)} user_profile.occupation_id รหัสนักศึกษา
* @apiParam {Int(3)} user_profile.field_study_id รหัสสาขาวิชา<br>192 = คณะเศรษฐศาสตร์<br>...
* @apiParam {Int(3)} user_profile.education_level_id ระดับการศึกษา<br>1 = ปริญญาตรี<br>2 = ปริญญาโท<br>...
* @apiParam {String} action หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ
*
* @apiSuccess (Success 200) {String} token Access Token ที่ใช้ในการเรียก Service ต่าง ๆ
* @apiSuccessExample {json} Success-Response:
*	HTTP/1.1 200 OK
*	{
*	   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M"
*	}
*
* @apiUse ClientFullErrorTH
*
* @apiUse ServerErrorTH
*/




/**
* @api {post} /set/SETStudent/courses E2: Get Course List
* @apiName E2: Get Course List
* @apiGroup 4 SET Student
*
* @apiDescription Get course list for Student.
* @apiVersion 1.1.0
*
* @apiParam {String} company_code รหัสอ้างอิง Company
*
* @apiSuccess {Object[]} courses รายการวิชาเรียน
* @apiSuccess {Int(3)} courses.id ค่ารหัสอ้างอิง Courses
* @apiSuccess {Varchar(50)} courses.code ค่ารหัสอ้างอิง Courses
* @apiSuccess {Varchar(255)} courses.title ค่ารหัสอ้างอิง Courses
* @apiSuccess {Varchar(128)} courses.thumbnail Path รูปภาพ Thumbnail
* @apiSuccess {Int(7)} courses.price ราคาของ Course
* @apiSuccess {Boolean} courses.latest <code>true = course ใหม่</code>, <code>false = ไม่ใช่ course ใหม่</code>
* @apiSuccess {Boolean} courses.recommended <code>true = course แนะนำ</code>, <code>false = ไม่ใช่ course แนะนำ</code>
* @apiSuccess {Boolean} courses.free <code>true = course ฟรี</code>, <code>false = ไม่ใช่ course ฟรี</code>
* @apiSuccess {Varchar(128)} courses.categories กลุ่มของหลักสูตร
* @apiSuccess {Varchar(128)} courses.categories.id ค่ารหัสอ้างอิง ของกลุ่ม
* @apiSuccess {Varchar(128)} courses.categories.title ชื่อของกลุ่ม
* @apiSuccess {Varchar(128)} courses.categories.hex_color ค่าสีของกลุ่ม Ex. #FFFFFF
*
* @apiUse ClientErrorTH
*
* @apiUse ServerErrorTH
*/




/**
* @api {post} /set/SETStudent/courses/:course_id E3: View Course Info
* @apiName E3: View Course Info
* @apiGroup 4 SET Student
*
* @apiDescription Get course information for Student.
* @apiVersion 1.1.0
*
* @apiParam {Int(3)} :course_id รหัสอ้างอิงหลักสูตร
* @apiParam {String} company_code รหัสอ้างอิง Company
* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference)
* @apiParam {Varchar(128)} user_profile.name_title คำนำหน้าชื่อ
* @apiParam {Varchar(1)} user_profile.gender เพศ<br>F = หญิง<br>M = ชาย
* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
* @apiParam {Varchar(128)} user_profile.email อีเมลล์
* @apiParam {Varchar(13)} user_profile.id_card เลขบัตรประชาชน
* @apiParam {date} user_profile.birth_date วันเกิด
* @apiParam {Varchar(10)} user_profile.mobile_number หมายเลขโทรศัพท์มือถือ, Validate format
* @apiParam {Varchar(128)} user_profile.institution_id รหัสมหาวิทยาลัย<br>21 = มหาวิทยาลัยเกษตรศาสตร์<br>...
* @apiParam {Int(3)} user_profile.faculty_id รหัสคณะ<br>32 = คณะเศรษฐศาสตร์<br>...
* @apiParam {Varchar(13)} user_profile.occupation_id รหัสนักศึกษา
* @apiParam {Int(3)} user_profile.field_study_id รหัสสาขาวิชา<br>192 = คณะเศรษฐศาสตร์<br>...
* @apiParam {Int(3)} user_profile.education_level_id ระดับการศึกษา<br>1 = ปริญญาตรี<br>2 = ปริญญาโท<br>...
* @apiParam {String} action หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ
*
* @apiSuccess (Success 200) {String} token Access Token ที่ใช้ในการเรียก Service ต่าง ๆ
* @apiSuccessExample {json} Success-Response:
*	HTTP/1.1 200 OK
*	{
*	   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M"
*	}
*
* @apiUse ClientFullErrorTH
*
* @apiUse ServerErrorTH
*/




/**
* @api {post} /set/SETStudent/courses/:course_id/download/certificate E4: Download Certificate
* @apiName E4: Download Certificate
* @apiGroup 4 SET Student
*
* @apiDescription Download e-cerfiticate file for Student.
* @apiVersion 1.1.0
*
* @apiParam {Int(3)} :course_id รหัสอ้างอิงหลักสูตร
* @apiParam {String} company_code รหัสอ้างอิง Company
* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference)
* @apiParam {Varchar(128)} user_profile.name_title คำนำหน้าชื่อ
* @apiParam {Varchar(1)} user_profile.gender เพศ<br>F = หญิง<br>M = ชาย
* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
* @apiParam {Varchar(128)} user_profile.email อีเมลล์
* @apiParam {Varchar(13)} user_profile.id_card เลขบัตรประชาชน
* @apiParam {date} user_profile.birth_date วันเกิด
* @apiParam {Varchar(10)} user_profile.mobile_number หมายเลขโทรศัพท์มือถือ, Validate format
* @apiParam {Varchar(128)} user_profile.institution_id รหัสมหาวิทยาลัย<br>21 = มหาวิทยาลัยเกษตรศาสตร์<br>...
* @apiParam {Int(3)} user_profile.faculty_id รหัสคณะ<br>32 = คณะเศรษฐศาสตร์<br>...
* @apiParam {Varchar(13)} user_profile.occupation_id รหัสนักศึกษา
* @apiParam {Int(3)} user_profile.field_study_id รหัสสาขาวิชา<br>192 = คณะเศรษฐศาสตร์<br>...
* @apiParam {Int(3)} user_profile.education_level_id ระดับการศึกษา<br>1 = ปริญญาตรี<br>2 = ปริญญาโท<br>...
*
* @apiSuccess (Success 200 (Download e-Certificate PDF File)) -
*
* @apiUse ClientFullErrorTH
*
* @apiUse ServerErrorTH
*/


// ==================================================================================================================== //
// ================================================= BEGIN SETEmployee ================================================ //
// ==================================================================================================================== //


/**
* @api {post} /set/SETEmployee/login E1: Single Sign-on
* @apiName E1: Single Sign-on
* @apiGroup 5 SET Employee
*
* @apiDescription Login to e-learning site for Employee.
* @apiVersion 1.1.0
*
* @apiParam {String} company_code รหัสอ้างอิง Company
* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference)
* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
* @apiParam {Varchar(128)} user_profile.email อีเมลล์
* @apiParam {Varchar(128)} user_profile.position_id ตำแหน่งงาน
* @apiParam {Varchar(128)} user_profile.department ฝ่ายงาน
* @apiParam {Varchar(13)} user_profile.occupation_id รหัสพนักงาน
* @apiParam {Varchar(128)} user_profile.table_number เบอร์โต๊ะ
* @apiParam {Varchar(128)} user_profile.chief_name ชื่อ นามสกุล หัวหน้า
* @apiParam {String} action หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ
*
* @apiSuccess (Success 200) {String} token Access Token ที่ใช้ในการเรียก Service ต่าง ๆ
* @apiSuccessExample {json} Success-Response:
*	HTTP/1.1 200 OK
*	{
*	   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M"
*	}
*
* @apiUse ClientFullErrorTH
*
* @apiUse ServerErrorTH
*/




/**
* @api {post} /set/SETEmployee/courses E2: Get Course List
* @apiName E2: Get Course List
* @apiGroup 5 SET Employee
*
* @apiDescription Get course list for Employee.
* @apiVersion 1.1.0
*
* @apiParam {String} company_code รหัสอ้างอิง Company
*
* @apiSuccess {Object[]} courses รายการวิชาเรียน
* @apiSuccess {Int(3)} courses.id ค่ารหัสอ้างอิง Courses
* @apiSuccess {Varchar(50)} courses.code ค่ารหัสอ้างอิง Courses
* @apiSuccess {Varchar(255)} courses.title ค่ารหัสอ้างอิง Courses
* @apiSuccess {Varchar(128)} courses.thumbnail Path รูปภาพ Thumbnail
* @apiSuccess {Int(7)} courses.price ราคาของ Course
* @apiSuccess {Boolean} courses.latest <code>true = course ใหม่</code>, <code>false = ไม่ใช่ course ใหม่</code>
* @apiSuccess {Boolean} courses.recommended <code>true = course แนะนำ</code>, <code>false = ไม่ใช่ course แนะนำ</code>
* @apiSuccess {Boolean} courses.free <code>true = course ฟรี</code>, <code>false = ไม่ใช่ course ฟรี</code>
* @apiSuccess {Varchar(128)} courses.categories กลุ่มของหลักสูตร
* @apiSuccess {Varchar(128)} courses.categories.id ค่ารหัสอ้างอิง ของกลุ่ม
* @apiSuccess {Varchar(128)} courses.categories.title ชื่อของกลุ่ม
* @apiSuccess {Varchar(128)} courses.categories.hex_color ค่าสีของกลุ่ม Ex. #FFFFFF
*
* @apiUse ClientErrorTH
*
* @apiUse ServerErrorTH
*/




/**
* @api {post} /set/SETEmployee/courses/:course_id E3: View Course Info
* @apiName E3: View Course Info
* @apiGroup 5 SET Employee
*
* @apiDescription Get course information for Employee.
* @apiVersion 1.1.0
*
* @apiParam {Int(3)} :course_id รหัสอ้างอิงหลักสูตร
* @apiParam {String} company_code รหัสอ้างอิง Company
* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference)
* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
* @apiParam {Varchar(128)} user_profile.email อีเมลล์
* @apiParam {Varchar(128)} user_profile.position_id ตำแหน่งงาน
* @apiParam {Varchar(128)} user_profile.department ฝ่ายงาน
* @apiParam {Varchar(13)} user_profile.occupation_id รหัสพนักงาน
* @apiParam {Varchar(128)} user_profile.table_number เบอร์โต๊ะ
* @apiParam {Varchar(128)} user_profile.chief_name ชื่อ นามสกุล หัวหน้า
* @apiParam {String} action หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ
*
* @apiSuccess (Success 200) {String} token Access Token ที่ใช้ในการเรียก Service ต่าง ๆ
* @apiSuccessExample {json} Success-Response:
*	HTTP/1.1 200 OK
*	{
*	   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M"
*	}
*
* @apiUse ClientFullErrorTH
*
* @apiUse ServerErrorTH
*/




/**
* @api {post} /set/SETEmployee/courses/:course_id/download/certificate E4: Download Certificate
* @apiName E4: Download Certificate
* @apiGroup 5 SET Employee
*
* @apiDescription Download e-cerfiticate file for Employee.
* @apiVersion 1.1.0
*
* @apiParam {Int(3)} :course_id รหัสอ้างอิงหลักสูตร
* @apiParam {String} company_code รหัสอ้างอิง Company
* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference)
* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
* @apiParam {Varchar(128)} user_profile.email อีเมลล์
* @apiParam {Varchar(128)} user_profile.position_id ตำแหน่งงาน
* @apiParam {Varchar(128)} user_profile.department ฝ่ายงาน
* @apiParam {Varchar(13)} user_profile.occupation_id รหัสพนักงาน
* @apiParam {Varchar(128)} user_profile.table_number เบอร์โต๊ะ
* @apiParam {Varchar(128)} user_profile.chief_name ชื่อ นามสกุล หัวหน้า
*
* @apiSuccess (Success 200 (Download e-Certificate PDF File)) -

*
* @apiUse ClientFullErrorTH
*
* @apiUse ServerErrorTH
*/













