<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;


class Test2Controller extends Controller
{
	/**
	 * @apiDefine ServerErrorTH
	 * @apiError (Error 5xx) {String} message รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์
	 */

	/**
	* @api {post} /set/login E1: Single Sign-on
	* @apiName E1: Single Sign-on
	* @apiGroup 1 SET Member
	*
	* @apiDescription Login to e-learning site for SET Member.
	* @apiVersion 0.2.0
	*
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Member ได้แก่ <code>SETGroup</code>
	* @apiParam {String} company_code รหัสอ้างอิง Company
	* @apiParam {Int(3)} course_id รหัสอ้างอิงหลักสูตร
	* @apiParam {Long} user_ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
	* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
	* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
	* @apiParam {Varchar(128)} user_profile.email อีเมลล์
	* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {String} action หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ
	*
	* @apiSuccess (Success 302 (Redirect - If session already Redirect to the action page)) -
	*
	* @apiError (Error 4xx (Redirect)) {String} group_id ค่า <code>รหัสอ้างอิงของกลุ่ม Member</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {String} company_code ค่า <code>รหัสอ้างอิงของ Company</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Int(3)} course_id ค่า <code>รหัสอ้างอิงหลักสูตร</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Long} user_ref_id ค่า <code>รหัสอ้างอิงผู้ใช้</code> ที่ส่งมา
	*
	* @apiUse ServerErrorTH
	*/

	/**
	* @api {get} /set/groups/SETGroup/courses E2: Get Course List
	* @apiName E2: Get Course List
	* @apiGroup 1 SET Member
	*
	* @apiDescription Get course list for SET Member.
	* @apiVersion 0.3.0
	*
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Member ได้แก่ <code>SETGroup</code>
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
	* @apiError {String} group_id ไม่พบ <code>รหัสอ้างอิงของกลุ่ม</code>
	*
	* @apiUse ServerErrorTH
	*/

	/**
	* @api {get} /set/groups/SETGroup/courses/:course_id E3: View Course Info
	* @apiName E3: View Course Info
	* @apiGroup 1 SET Member
	*
	* @apiDescription Get course information for SET Member.
	* @apiVersion 0.2.0
	*
	* @apiParam {Int(3)} course_id รหัสอ้างอิงหลักสูตร
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Member ได้แก่ <code>SETGroup</code>
	* @apiParam {String} company_code รหัสอ้างอิง Company
	* @apiParam {Long} user_ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
	* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
	* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
	* @apiParam {Varchar(128)} user_profile.email อีเมลล์
	* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {String} action หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ
	*
	* @apiSuccess (Success 302 (Redirect - If session already Redirect to the action page)) -
	*
	* @apiError (Error 4xx (Redirect)) {String} group_id ค่า <code>รหัสอ้างอิงของกลุ่ม Member</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {String} company_code ค่า <code>รหัสอ้างอิงของ Company</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Int(3)} course_id ค่า <code>รหัสอ้างอิงหลักสูตร</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Long} user_ref_id ค่า <code>รหัสอ้างอิงผู้ใช้</code> ที่ส่งมา
	*
	* @apiUse ServerErrorTH
	*/

	/**
	* @api {get} /set/download/certificate E4: Download Certificate
	* @apiName E4: Download Certificate
	* @apiGroup 1 SET Member
	*
	* @apiDescription Download e-cerfiticate file for SET Member.
	* @apiVersion 0.2.0
	*
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Member ได้แก่ <code>SETGroup</code>
	* @apiParam {String} company_code รหัสอ้างอิง Company
	* @apiParam {Int(3)} course_id รหัสอ้างอิงหลักสูตร
	* @apiParam {Long} user_ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
	* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
	* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
	* @apiParam {Varchar(128)} user_profile.email อีเมลล์
	* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	*
	* @apiSuccess (Success 200 (Download e-Certificate PDF File)) -
	*
	* @apiError {String} message คำอธิบายข้อผิดพลาด
	* @apiError {Object} error_info ข้อมูลข้อผิดพลาด
	* @apiError {String} error_info.group_id ค่า <code>รหัสอ้างอิงของกลุ่ม Listed Company</code> ที่ส่งมา
	* @apiError {String} error_info.company_code ค่า <code>รหัสอ้างอิงของ Company</code> ที่ส่งมา
	* @apiError {Int(3)} error_info.course_id ค่า <code>รหัสอ้างอิงหลักสูตร</code> ที่ส่งมา
	* @apiError {Long} error_info.user_ref_id ค่า <code>รหัสอ้างอิงผู้ใช้</code> ที่ส่งมา
	*
	* @apiUse ServerErrorTH
	*/


	/**
	* @api {post} /set/login E1: Single Sign-on
	* @apiName E1: Single Sign-on
	* @apiGroup 2 SET Listed Company
	*
	* @apiDescription Login to e-learning site for Listed Company.
	* @apiVersion 0.2.0
	*
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Listed Company ได้แก่ <code>SETLISTEDCOMPANY</code>
	* @apiParam {Int(3)} course_id รหัสอ้างอิงหลักสูตร
	* @apiParam {Long} user_ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
	* @apiParam {Varchar(128)} user_profile.name_title คำนำหน้าชื่อ
	* @apiParam {Varchar(1)} user_profile.gender เพศ<br>F = หญิง<br>M = ชาย
	* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
	* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
	* @apiParam {Varchar(128)} user_profile.email อีเมลล์
	* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference), ใช้เพื่อ link กับ SET Regis
	* @apiParam {Varchar(13)} user_profile.id_card เลขบัตรประชาชน
	* @apiParam {Varchar(10)} user_profile.mobile_number หมายเลขโทรศัพท์มือถือ, Validate format
	* @apiParam {Varchar(128)} user_profile.institution_id รหัสบริษัทจดทะเบียน เช่น BBL, KGI
	* @apiParam {Int(3)} user_profile.position_id ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office
	* @apiParam {Varchar(128)} user_profile.department ฝ่ายงาน
	* @apiParam {Varchar(128)} user_profile.role บทบาทในระบบต้นทาง
	* @apiParam {String} action หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ
	*
	* @apiSuccess (Success 302 (Redirect - If session already Redirect to the action page)) -
	*
	* @apiError (Error 4xx (Redirect)) {String} group_id ค่า <code>รหัสอ้างอิงของกลุ่ม Listed Company</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {String} company_code ค่า <code>รหัสอ้างอิงของ Company</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Int(3)} course_id ค่า <code>รหัสอ้างอิงหลักสูตร</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Long} user_ref_id ค่า <code>รหัสอ้างอิงผู้ใช้</code> ที่ส่งมา
	* @apiUse ServerErrorTH
	*/

	/**
	* @api {get} /set/groups/SETLISTEDCOMPANY/courses E2: Get Course List
	* @apiName E2: Get Course List
	* @apiGroup 2 SET Listed Company
	*
	* @apiDescription Get course list for Listed Company.
	* @apiVersion 0.2.0
	*
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Listed Company ได้แก่ <code>SETLISTEDCOMPANY</code>
	*
	* @apiSuccess {Object[]} courses รายการวิชาเรียน
	*
	* @apiError {String} group_id ไม่พบ <code>รหัสอ้างอิงของกลุ่ม</code>
	*
	* @apiUse ServerErrorTH
	*/

	/**
	* @api {get} /set/groups/SETLISTEDCOMPANY/courses/:course_id E3: View Course Info
	* @apiName E3: View Course Info
	* @apiGroup 2 SET Listed Company
	*
	* @apiDescription Get course information for Listed Company.
	* @apiVersion 0.2.0
	*
	* @apiParam {Int(3)} course_id รหัสอ้างอิงหลักสูตร
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Listed Company ได้แก่ <code>SETLISTEDCOMPANY</code>
	* @apiParam {Long} user_ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
	* @apiParam {Varchar(128)} user_profile.name_title คำนำหน้าชื่อ
	* @apiParam {Varchar(1)} user_profile.gender เพศ<br>F = หญิง<br>M = ชาย
	* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
	* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
	* @apiParam {Varchar(128)} user_profile.email อีเมลล์
	* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference), ใช้เพื่อ link กับ SET Regis
	* @apiParam {Varchar(13)} user_profile.id_card เลขบัตรประชาชน
	* @apiParam {Varchar(10)} user_profile.mobile_number หมายเลขโทรศัพท์มือถือ, Validate format
	* @apiParam {Varchar(128)} user_profile.institution_id รหัสบริษัทจดทะเบียน เช่น BBL, KGI
	* @apiParam {Int(3)} user_profile.position_id ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office
	* @apiParam {Varchar(128)} user_profile.department ฝ่ายงาน
	* @apiParam {Varchar(128)} user_profile.role บทบาทในระบบต้นทาง
	* @apiParam {String} action หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ
	*
	* @apiSuccess (Success 302 (Redirect - If session already Redirect to the action page)) -
	*
	* @apiError (Error 4xx (Redirect)) {String} group_id ค่า <code>รหัสอ้างอิงของกลุ่ม Listed Company</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {String} company_code ค่า <code>รหัสอ้างอิงของ Company</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Int(3)} course_id ค่า <code>รหัสอ้างอิงหลักสูตร</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Long} user_ref_id ค่า <code>รหัสอ้างอิงผู้ใช้</code> ที่ส่งมา
	* @apiUse ServerErrorTH
	*/

	/**
	* @api {get} /set/download/certificate E4: Download Certificate
	* @apiName E4: Download Certificate
	* @apiGroup 2 SET Listed Company
	*
	* @apiDescription Download e-cerfiticate file for Listed Company.
	* @apiVersion 0.2.0
	*
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Listed Company ได้แก่ <code>SETLISTEDCOMPANY</code>
	* @apiParam {Int(3)} course_id รหัสอ้างอิงหลักสูตร
	* @apiParam {Long} user_ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
	* @apiParam {Varchar(128)} user_profile.name_title คำนำหน้าชื่อ
	* @apiParam {Varchar(1)} user_profile.gender เพศ<br>F = หญิง<br>M = ชาย
	* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
	* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
	* @apiParam {Varchar(128)} user_profile.email อีเมลล์
	* @apiParam {Long} user_profile.ref_id รหัสอ้างอิงผู้ใช้ (User reference), ใช้เพื่อ link กับ SET Regis
	* @apiParam {Varchar(13)} user_profile.id_card เลขบัตรประชาชน
	* @apiParam {Varchar(10)} user_profile.mobile_number หมายเลขโทรศัพท์มือถือ, Validate format
	* @apiParam {Varchar(128)} user_profile.institution_id รหัสบริษัทจดทะเบียน เช่น BBL, KGI
	* @apiParam {Int(3)} user_profile.position_id ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office
	* @apiParam {Varchar(128)} user_profile.department ฝ่ายงาน
	* @apiParam {Varchar(128)} user_profile.role บทบาทในระบบต้นทาง
	*
	* @apiSuccess (Success 200 (Download e-Certificate PDF File)) -
	*
	* @apiError {String} message คำอธิบายข้อผิดพลาด
	* @apiError {Object} error_info ข้อมูลข้อผิดพลาด
	* @apiError {String} error_info.group_id ค่า <code>รหัสอ้างอิงของกลุ่ม Listed Company</code> ที่ส่งมา
	* @apiError {String} error_info.company_code ค่า <code>รหัสอ้างอิงของ Company</code> ที่ส่งมา
	* @apiError {Int(3)} error_info.course_id ค่า <code>รหัสอ้างอิงหลักสูตร</code> ที่ส่งมา
	* @apiError {Long} error_info.user_ref_id ค่า <code>รหัสอ้างอิงผู้ใช้</code> ที่ส่งมา
	*
	* @apiUse ServerErrorTH
	*/

	/**
	* @api {post} /set/login E1: Single Sign-on
	* @apiName E1: Single Sign-on
	* @apiGroup 3 SET Broker
	*
	* @apiDescription Login to e-learning site for Broker.
	* @apiVersion 0.2.0
	*
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Broker ได้แก่ <code>SETBROKER</code>
	* @apiParam {Int(3)} course_id รหัสอ้างอิงหลักสูตร
	* @apiParam {Long} user_ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
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
	* @apiSuccess (Success 302 (Redirect - If session already Redirect to the action page)) -
	*
	* @apiError (Error 4xx (Redirect)) {String} group_id ค่า <code>รหัสอ้างอิงของกลุ่ม Broker</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {String} company_code ค่า <code>รหัสอ้างอิงของ Company</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Int(3)} course_id ค่า <code>รหัสอ้างอิงหลักสูตร</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Long} user_ref_id ค่า <code>รหัสอ้างอิงผู้ใช้</code> ที่ส่งมา
	* @apiUse ServerErrorTH
	*/

	/**
	* @api {get} /set/groups/SETBROKER/courses E2: Get Course List
	* @apiName E2: Get Course List
	* @apiGroup 3 SET Broker
	*
	* @apiDescription Get course list for Broker.
	* @apiVersion 0.2.0
	*
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Broker ได้แก่ <code>SETBROKER</code>
	*
	* @apiSuccess {Object[]} courses รายการวิชาเรียน
	*
	* @apiError {String} group_id ไม่พบ <code>รหัสอ้างอิงของกลุ่ม</code>
	*
	* @apiUse ServerErrorTH
	*/

	/**
	* @api {get} /set/groups/SETBROKER/courses/:course_id E3: View Course Info
	* @apiName E3: View Course Info
	* @apiGroup 3 SET Broker
	*
	* @apiDescription Get course information for Broker.
	* @apiVersion 0.2.0
	*
	* @apiParam {Int(3)} course_id รหัสอ้างอิงหลักสูตร
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Broker ได้แก่ <code>SETBROKER</code>
	* @apiParam {Long} user_ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
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
	* @apiSuccess (Success 302 (Redirect - If session already Redirect to the action page)) -
	*
	* @apiError (Error 4xx (Redirect)) {String} group_id ค่า <code>รหัสอ้างอิงของกลุ่ม Broker</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {String} company_code ค่า <code>รหัสอ้างอิงของ Company</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Int(3)} course_id ค่า <code>รหัสอ้างอิงหลักสูตร</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Long} user_ref_id ค่า <code>รหัสอ้างอิงผู้ใช้</code> ที่ส่งมา
	* @apiUse ServerErrorTH
	*/

	/**
	* @api {get} /set/download/certificate E4: Download Certificate
	* @apiName E4: Download Certificate
	* @apiGroup 3 SET Broker
	*
	* @apiDescription Download e-cerfiticate file for Broker.
	* @apiVersion 0.2.0
	*
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Broker ได้แก่ <code>SETBROKER</code>
	* @apiParam {Int(3)} course_id รหัสอ้างอิงหลักสูตร
	* @apiParam {Long} user_ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
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
	* @apiError {String} message คำอธิบายข้อผิดพลาด
	* @apiError {Object} error_info ข้อมูลข้อผิดพลาด
	* @apiError {String} error_info.group_id ค่า <code>รหัสอ้างอิงของกลุ่ม Listed Company</code> ที่ส่งมา
	* @apiError {String} error_info.company_code ค่า <code>รหัสอ้างอิงของ Company</code> ที่ส่งมา
	* @apiError {Int(3)} error_info.course_id ค่า <code>รหัสอ้างอิงหลักสูตร</code> ที่ส่งมา
	* @apiError {Long} error_info.user_ref_id ค่า <code>รหัสอ้างอิงผู้ใช้</code> ที่ส่งมา
	*
	* @apiUse ServerErrorTH
	*/



	/**
	* @api {post} /set/login E1: Single Sign-on
	* @apiName E1: Single Sign-on
	* @apiGroup 4 SET Student
	*
	* @apiDescription Login to e-learning site for Student.
	* @apiVersion 0.2.0
	*
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Student ได้แก่ <code>SETSTUDENT</code>
	* @apiParam {Int(3)} course_id รหัสอ้างอิงหลักสูตร
	* @apiParam {Long} user_ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
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
	* @apiSuccess (Success 302 (Redirect - If session already Redirect to the action page)) -
	*
	* @apiError (Error 4xx (Redirect)) {String} group_id ค่า <code>รหัสอ้างอิงของกลุ่ม Student</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {String} company_code ค่า <code>รหัสอ้างอิงของ Company</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Int(3)} course_id ค่า <code>รหัสอ้างอิงหลักสูตร</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Long} user_ref_id ค่า <code>รหัสอ้างอิงผู้ใช้</code> ที่ส่งมา
	* @apiUse ServerErrorTH
	*/

	/**
	* @api {get} /set/groups/SETSTUDENT/courses E2: Get Course List
	* @apiName E2: Get Course List
	* @apiGroup 4 SET Student
	*
	* @apiDescription Get course list for Student.
	* @apiVersion 0.2.0
	*
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Student ได้แก่ <code>SETSTUDENT</code>
	*
	* @apiSuccess {Object[]} courses รายการวิชาเรียน
	*
	* @apiError {String} group_id ไม่พบ <code>รหัสอ้างอิงของกลุ่ม</code>
	*
	* @apiUse ServerErrorTH
	*/

	/**
	* @api {get} /set/groups/SETSTUDENT/courses/:course_id E3: View Course Info
	* @apiName E3: View Course Info
	* @apiGroup 4 SET Student
	*
	* @apiDescription Get course information for Student.
	* @apiVersion 0.2.0
	*
	* @apiParam {Int(3)} course_id รหัสอ้างอิงหลักสูตร
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Student ได้แก่ <code>SETSTUDENT</code>
	* @apiParam {Long} user_ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
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
	* @apiSuccess (Success 302 (Redirect - If session already Redirect to the action page)) -
	*
	* @apiError (Error 4xx (Redirect)) {String} group_id ค่า <code>รหัสอ้างอิงของกลุ่ม Student</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {String} company_code ค่า <code>รหัสอ้างอิงของ Company</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Int(3)} course_id ค่า <code>รหัสอ้างอิงหลักสูตร</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Long} user_ref_id ค่า <code>รหัสอ้างอิงผู้ใช้</code> ที่ส่งมา
	* @apiUse ServerErrorTH
	*/

	/**
	* @api {get} /set/download/certificate E4: Download Certificate
	* @apiName E4: Download Certificate
	* @apiGroup 4 SET Student
	*
	* @apiDescription Download e-cerfiticate file for Student.
	* @apiVersion 0.2.0
	*
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Student ได้แก่ <code>SETSTUDENT</code>
	* @apiParam {Int(3)} course_id รหัสอ้างอิงหลักสูตร
	* @apiParam {Long} user_ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
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
	* @apiError {String} message คำอธิบายข้อผิดพลาด
	* @apiError {Object} error_info ข้อมูลข้อผิดพลาด
	* @apiError {String} error_info.group_id ค่า <code>รหัสอ้างอิงของกลุ่ม Listed Company</code> ที่ส่งมา
	* @apiError {String} error_info.company_code ค่า <code>รหัสอ้างอิงของ Company</code> ที่ส่งมา
	* @apiError {Int(3)} error_info.course_id ค่า <code>รหัสอ้างอิงหลักสูตร</code> ที่ส่งมา
	* @apiError {Long} error_info.user_ref_id ค่า <code>รหัสอ้างอิงผู้ใช้</code> ที่ส่งมา
	*
	* @apiUse ServerErrorTH
	*/


	/**
	* @api {post} /set/login E1: Single Sign-on
	* @apiName E1: Single Sign-on
	* @apiGroup 5 SET Employee
	*
	* @apiDescription Login to e-learning site for Employee.
	* @apiVersion 0.2.0
	*
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Employee ได้แก่ <code>SETEMPLOYEE</code>
	* @apiParam {Int(3)} course_id รหัสอ้างอิงหลักสูตร
	* @apiParam {Long} user_ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
	* @apiParam {Varchar(128)} user_profile.name_title คำนำหน้าชื่อ
	* @apiParam {Varchar(1)} user_profile.gender เพศ<br>F = หญิง<br>M = ชาย
	* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
	* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
	* @apiParam {Varchar(128)} user_profile.email อีเมลล์
	* @apiParam {Varchar(128)} user_profile.role บทบาทในระบบต้นทาง
	* @apiParam {String} action หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ
	*
	* @apiSuccess (Success 302 (Redirect - If session already Redirect to the action page)) -
	*
	* @apiError (Error 4xx (Redirect)) {String} group_id ค่า <code>รหัสอ้างอิงของกลุ่ม Employee</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {String} company_code ค่า <code>รหัสอ้างอิงของ Company</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Int(3)} course_id ค่า <code>รหัสอ้างอิงหลักสูตร</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Long} user_ref_id ค่า <code>รหัสอ้างอิงผู้ใช้</code> ที่ส่งมา
	* @apiUse ServerErrorTH
	*/

	/**
	* @api {get} /set/groups/SETEMPLOYEE/courses E2: Get Course List
	* @apiName E2: Get Course List
	* @apiGroup 5 SET Employee
	*
	* @apiDescription Get course list for Employee.
	* @apiVersion 0.2.0
	*
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Employee ได้แก่ <code>SETEMPLOYEE</code>
	*
	* @apiSuccess {Object[]} courses รายการวิชาเรียน
	*
	* @apiError {String} group_id ไม่พบ <code>รหัสอ้างอิงของกลุ่ม</code>
	*
	* @apiUse ServerErrorTH
	*/

	/**
	* @api {get} /set/groups/SETEMPLOYEE/courses/:course_id E3: View Course Info
	* @apiName E3: View Course Info
	* @apiGroup 5 SET Employee
	*
	* @apiDescription Get course information for Employee.
	* @apiVersion 0.2.0
	*
	* @apiParam {Int(3)} course_id รหัสอ้างอิงหลักสูตร
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Employee ได้แก่ <code>SETEMPLOYEE</code>
	* @apiParam {Long} user_ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
	* @apiParam {Varchar(128)} user_profile.name_title คำนำหน้าชื่อ
	* @apiParam {Varchar(1)} user_profile.gender เพศ<br>F = หญิง<br>M = ชาย
	* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
	* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
	* @apiParam {Varchar(128)} user_profile.email อีเมลล์
	* @apiParam {Varchar(128)} user_profile.role บทบาทในระบบต้นทาง
	* @apiParam {String} action หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ
	*
	* @apiSuccess (Success 302 (Redirect - If session already Redirect to the action page)) -
	*
	* @apiError (Error 4xx (Redirect)) {String} group_id ค่า <code>รหัสอ้างอิงของกลุ่ม Employee</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {String} company_code ค่า <code>รหัสอ้างอิงของ Company</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Int(3)} course_id ค่า <code>รหัสอ้างอิงหลักสูตร</code> ที่ส่งมา
	* @apiError (Error 4xx (Redirect)) {Long} user_ref_id ค่า <code>รหัสอ้างอิงผู้ใช้</code> ที่ส่งมา
	* @apiUse ServerErrorTH
	*/

	/**
	* @api {get} /set/download/certificate E4: Download Certificate
	* @apiName E4: Download Certificate
	* @apiGroup 5 SET Employee
	*
	* @apiDescription Download e-cerfiticate file for Employee.
	* @apiVersion 0.2.0
	*
	* @apiParam {String} group_id รหัสอ้างอิงของกลุ่ม Employee ได้แก่ <code>SETEMPLOYEE</code>
	* @apiParam {Int(3)} course_id รหัสอ้างอิงหลักสูตร
	* @apiParam {Long} user_ref_id รหัสอ้างอิงผู้ใช้ (User reference)
	* @apiParam {Object} user_profile ข้อมูลผู้ใช้งาน
	* @apiParam {Varchar(128)} user_profile.name_title คำนำหน้าชื่อ
	* @apiParam {Varchar(1)} user_profile.gender เพศ<br>F = หญิง<br>M = ชาย
	* @apiParam {Varchar(128)} user_profile.first_name ชื่อ
	* @apiParam {Varchar(128)} user_profile.last_name นามสกุล
	* @apiParam {Varchar(128)} user_profile.email อีเมลล์
	* @apiParam {Varchar(128)} user_profile.role บทบาทในระบบต้นทาง
	*
	* @apiSuccess (Success 200 (Download e-Certificate PDF File)) -

	*
	* @apiError {String} message คำอธิบายข้อผิดพลาด
	* @apiError {Object} error_info ข้อมูลข้อผิดพลาด
	* @apiError {String} error_info.group_id ค่า <code>รหัสอ้างอิงของกลุ่ม Listed Company</code> ที่ส่งมา
	* @apiError {String} error_info.company_code ค่า <code>รหัสอ้างอิงของ Company</code> ที่ส่งมา
	* @apiError {Int(3)} error_info.course_id ค่า <code>รหัสอ้างอิงหลักสูตร</code> ที่ส่งมา
	* @apiError {Long} error_info.user_ref_id ค่า <code>รหัสอ้างอิงผู้ใช้</code> ที่ส่งมา
	*
	* @apiUse ServerErrorTH
	*/
 	


    
}
