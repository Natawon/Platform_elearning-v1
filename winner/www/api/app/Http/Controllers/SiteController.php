<?php

namespace App\Http\Controllers;

use App\Models\LevelGroups;
use Hamcrest\Core\IsNull;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Cache;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as httpClient;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

use App\Models\Configuration;
use App\Models\Admins;
use App\Models\Members;
use App\Models\MembersPreApproved;
use App\Models\QA;
use App\Models\Highlights;
use App\Models\Categories;
use App\Models\Courses;
use App\Models\Logs;
use App\Models\Topics;
use App\Models\Groups;
use App\Models\Enroll;
use App\Models\Enroll2Quiz;
use App\Models\Quiz;
use App\Models\Questions2Answer;
use App\Models\Questions;
use App\Models\Answer;
use App\Models\Quiz2Score;
use App\Models\Enroll2Topic;
use App\Models\Avatars;
use App\Models\SubGroups;
use App\Models\PasswordHistories;
use App\Models\LicenseTypes;
use App\Models\Certificates;
use App\Models\FilterCourses;
use App\Models\FilterCoursesDetail;
use App\Models\QuestionnairePacks;
use App\Models\Questionnaires;
use App\Models\QuestionnaireChoices;
use App\Models\Orders;
use App\Models\Payments;
use App\Models\Methods;
use App\Models\Discussions;
use App\Models\Slides;
use App\Models\Enroll2TopicLive;
use App\Models\Instructors;
use App\Models\Videos;
use App\Models\Subtitles;
use App\Models\Member2Live;
use App\Models\Bandwidths;
use App\Models\Views;

use Auth;
use Hash;
use Input;
use Torann\GeoIP\Facades\GeoIP as GeoIP;
use Mail;
use PDF;
use DB;
use Validator;
use DateTime;
use DateTimeZone;
// use Cache;
use Carbon\Carbon;

class SiteController extends Controller
{

    public function mailTest(Request $request)
    {
        //Send Mail Change Sub Groups Success
        $dataMail = array(
            'data'=>'Success',
        );
        Mail::send('test-mail', $dataMail, function($mail) use ($dataMail) {
            // $mail->to('keatiyos.w@dootvmedia.com', "Keatiyos Wirischai")->subject('Test Email');
            $mail->to('nawee.ku.dootvmedia@gmail.com', "Nawee Kunrod")->subject('Test Email');
            $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
        });

        if(count(Mail::failures()) == 0 ) {
            return response()->json(array('status' => 'OK'), 200);
        } else {
            return response()->json(array('status' => 'Failed.'), 200);
        }

    }

    public function memberTest(Request $request)
    {
        $data = Members::find($request['id']);
        dd($data->sub_groups);
        return response()->json($data, 200);
    }

    public function loadTest(Request $request)
    {
        // return response('debug', 200);
        // return response()->json(["time_debug" => time()], 200);
        $data = Cache::remember('data', 1, function() {
            return Configuration::find(1);
        });
        // $data = Configuration::find(1);
        return response()->json($data, 200);
    }

    public function configuration()
    {
        $data = Configuration::find(1);
        // // $data = DB::table('configuration')->find(1);
        return response()->json($data, 200);

        // return Cache::remember('Configuration', 15/60, function () {
        //     return Configuration::find(1);
        // });
        return response('', 200);
    }

    private function swappingSession($user) {
        $new_sessid   = session()->getId(); //get new my_session_id after user sign in

        // if ($user->my_session_id) {
        //     $last_session = session()->getHandler()->read($user->my_session_id); // retrive last session

        //     if ($last_session) {
        //         if (session()->getHandler()->destroy($user->my_session_id)) {
        //             // session was destroyed
        //         }
        //     }
        // }

        $user->my_session_id = $new_sessid;
        $user->save();
    }

    public function updateTaxInvoice(Request $request, SiteController $_site)
    {
        //
        $validator = Validator::make($request->all(), [
            'latest_type_tax' => 'required|in:personal,corporate',
            'inv_personal_first_name' => 'required_if:latest_type_tax,personal|max:255',
            'inv_personal_last_name' => 'required_if:latest_type_tax,personal|max:255',
            'inv_personal_tax_id' => 'required_if:latest_type_tax,personal|max:13',
            'inv_personal_email' => 'required_if:latest_type_tax,personal|email|max:128',
            'inv_personal_tel' => 'required_if:latest_type_tax,personal|max:10',
            'inv_personal_address' => 'required_if:latest_type_tax,personal|max:220',
            'inv_personal_zip_code' => 'required_if:latest_type_tax,personal|max:5',
            'inv_corporate_name' => 'required_if:latest_type_tax,corporate|max:70',
            'inv_corporate_branch' => 'required_if:latest_type_tax,corporate|max:1',
            'inv_corporate_branch_no' => 'required_if:inv_corporate_branch,1|max:5',
            'inv_corporate_tax_id' => 'required_if:latest_type_tax,corporate|max:13',
            'inv_corporate_email' => 'required_if:latest_type_tax,corporate|email|max:128',
            'inv_corporate_tel' => 'required_if:latest_type_tax,corporate|max:10',
            'inv_corporate_address' => 'required_if:latest_type_tax,corporate|max:220',
            'inv_corporate_zip_code' => 'required_if:latest_type_tax,corporate|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $oFunc = new _FunctionsController;
        $dataSession = session()->get('_user');

        if ($dataSession['is_foreign'] != 1 && $request['latest_type_tax'] == 'personal') {
            $checkIDCard = $oFunc->checkIDCard($request['inv_personal_tax_id']);

            if ($checkIDCard == false) {
                $is_success = false;
                $message = "กรุณากรอกเลขประจำตัวผู้เสียภาษีให้ถูกต้อง";
                return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
            }
        }

        $data = new Members;
        $data = $data->find($dataSession['id']);
        $input = $request->all();
        $data->fill($input);

        if ($data->inv_corporate_branch == 0) {
            $data->inv_corporate_branch_no = '00000';
        }

        $dataGeoIP = GeoIP::getLocation();
        $agent = new Agent();

        $data->ip = ($dataGeoIP['ip'] != "Unknown") ? $dataGeoIP['ip'] : $_SERVER['REMOTE_ADDR'];
        $data->country = $dataGeoIP['country'];
        $data->city = $dataGeoIP['city'];
        $data->device = $agent->device();
        $data->platform = $agent->platform();
        $data->platform_version = $agent->version($data->platform);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $is_success = $data->save();

        if ($is_success) {
            $member = $data->find($dataSession['id']);
            $request->session()->regenerate();
            $request->session()->put('_user', $member);
            $this->swappingSession($member);
        }

        $message = "บันทึกข้อมูลเรียบร้อย";

        $_site->logs('update-tax-invoice', '{"alert_msg":"success"}', 200, '', $data['id'], json_encode($request->all(), JSON_UNESCAPED_UNICODE), $data->groups_id, $data->sub_groups_id, '', '');

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function getSessionTaxInvoice(Request $request)
    {
        $_user = session()->get('_user');

        if (!$_user) {
            return response()->json(["message" => "Unauthorized Access."], 401);
        }

        $dataMember = Members::find($_user['id']);

        if ($_user['my_session_id'] != $dataMember->my_session_id) {
            session()->forget('_user');
            return response()->json(["message" => "Unauthorized Access."], 401);
        }

        // $request->session()->regenerate();
        $request->session()->put('_user', $dataMember);
        $this->swappingSession($dataMember);

        // return response()->json($dataMember->toArray(), 500);

        $dataTaxInvoice = [
            'latest_type_tax' => $dataMember->latest_type_tax,
            'inv_personal_first_name' => $dataMember->inv_personal_first_name,
            'inv_personal_last_name' => $dataMember->inv_personal_last_name,
            'inv_personal_tax_id' => $dataMember->inv_personal_tax_id,
            'inv_personal_email' => $dataMember->inv_personal_email,
            'inv_personal_tel' => $dataMember->inv_personal_tel,
            'inv_personal_address' => $dataMember->inv_personal_address,
            'inv_personal_zip_code' => $dataMember->inv_personal_zip_code,
            'inv_corporate_name' => $dataMember->inv_corporate_name,
            'inv_corporate_branch' => $dataMember->inv_corporate_branch,
            'inv_corporate_branch_no' => $dataMember->inv_corporate_branch_no,
            'inv_corporate_tax_id' => $dataMember->inv_corporate_tax_id,
            'inv_corporate_email' => $dataMember->inv_corporate_email,
            'inv_corporate_tel' => $dataMember->inv_corporate_tel,
            'inv_corporate_address' => $dataMember->inv_corporate_address,
            'inv_corporate_zip_code' => $dataMember->inv_corporate_zip_code
        ];

        return response()->json($dataTaxInvoice, 200);
    }

    public function edit_profile(Request $request, SiteController $_site)
    {
        //
        $validator = Validator::make($request->all(), [
            'birth_date' => 'required|date|date_format:Y-m-d',
            'nationality' => 'required|max:255',
            'mobile_number' => 'required|max:255',
            // 'license_id' => 'required_if:groups_id,3|max:255',
            // 'position_id' => 'required_if:groups_id,3',
            // 'education_level_id' => 'required_if:groups_id,3|required_if:groups_id,4|max:255',
        ],[
            // 'license_id.required_if' => 'The :attribute field is required.',
            // 'position_id.required_if' => 'The :attribute field is required.',
            // 'education_level_id.required_if' => 'The :attribute field is required.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $oFunc = new _FunctionsController;
        $dataSession = session()->get('_user');

        $data = new Members;
        $data = $data->find($dataSession['id']);
        $input = $request->all();
        $data->fill($input);

        $dataGeoIP = GeoIP::getLocation();
        $agent = new Agent();

        $data->ip = ($dataGeoIP['ip'] != "Unknown") ? $dataGeoIP['ip'] : $_SERVER['REMOTE_ADDR'];
        $data->country = $dataGeoIP['country'];
        $data->city = $dataGeoIP['city'];
        $data->device = $agent->device();
        $data->platform = $agent->platform();
        $data->platform_version = $agent->version($data->platform);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $is_success = $data->save();

        if ($is_success) {
            $member = $data->find($dataSession['id']);
            $request->session()->regenerate();
            $request->session()->put('_user', $member);
            $this->swappingSession($member);
        }

        $message = "แก้ไขข้อมูลเรียบร้อยแล้ว";

        $_site->logs('edit profile', '{"alert_msg":"success"}', 200, '', $data['id'], json_encode($request->all(), JSON_UNESCAPED_UNICODE), $data->groups_id, $data->sub_groups_id, '', '');

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function register(Request $request, SiteController $_site)
    {
        //
        $validator = Validator::make($request->all(), [
            'groups_id' => 'required|numeric',
            'email' => 'required|email|max:255',
            'password' => 'required|between:8,255|numbers|not_contain_credentials:'.$request['email'],
            'confirmPassword' => 'required|max:255',
            'name_title' => 'required_without_all:name_title_en|max:255',
            'name_title_en' => 'required_without_all:name_title|max:255',
            'first_name' => 'required_without_all:first_name_en|max:255',
            'first_name_en' => 'required_without_all:first_name|max:255',
            'last_name' => 'required_without_all:last_name_en|max:255',
            'last_name_en' => 'required_without_all:last_name|max:255',
            // 'birth_date' => 'required|date|date_format:Y-m-d',
            // 'id_card' => 'required|max:20',
            // 'nationality' => 'required|max:255',
            'mobile_number' => 'required|max:10',
            'sub_groups_id' => 'required|numeric',
            'level_groups_id' => 'required|numeric',
            // 'occupation_id' => 'required|max:255',
            // 'license_types_id' => 'required_if:groups_id,3',
            // 'license_id' => 'required_if:groups_id,3',
            // 'position_id' => 'required_if:groups_id,3',
            'captcha' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $message = "invalid fields.";
            $_site->logs('register', '{"alert_msg":"'.$message.'"}', 422, '', '', json_encode($request->all(), JSON_UNESCAPED_UNICODE), $request['groups_id'], $request['sub_groups_id'], '', '');
            return response()->json($validator->messages(), 422);
        }

        // return response()->json($request->all(), 422);

        $oFunc = new _FunctionsController;
        $data = new Members;
        $dataGeoIP = GeoIP::getLocation();
        $agent = new Agent();

        $isLoggedIn = false;

        session_start();
        if ($_SESSION['captcha']['code'] != $request['captcha']) {
            $is_success = false;
            $message = "กรุณากรอกรหัสยืนยันให้ถูกต้อง";
            $_site->logs('register', '{"alert_msg":"'.$message.'"}', 422, '', '', json_encode($request->all(), JSON_UNESCAPED_UNICODE), $request['groups_id'], $request['sub_groups_id'], '', '');
            return response()->json(array('is_error' => !$is_success, 'message' => $message, 'isLoggedIn' => $isLoggedIn), 200);
        }

        $dataGroup = Groups::find($request['groups_id']);
        if (!$dataGroup) {
            $message = "ไม่พบกลุ่มดังกล่าว";
            $_site->logs('register', '{"alert_msg":"'.$message.'"}', 404, '', '', json_encode($request->all(), JSON_UNESCAPED_UNICODE), $request['groups_id'], $request['sub_groups_id'], '', '');
            return response()->json(array('is_error' => true, 'message' => $message, 'isLoggedIn' => $isLoggedIn), 404);
        }

        $dataLevelGroups = LevelGroups::find($request['level_groups_id']);
        if (!$dataLevelGroups) {
            $message = "ไม่พบ".$dataGroup->meaning_of_level_groups_id."ดังกล่าว";
            $_site->logs('register', '{"alert_msg":"'.$message.'"}', 404, '', '', json_encode($request->all(), JSON_UNESCAPED_UNICODE), $request['groups_id'], $request['sub_groups_id'], '', '');
            return response()->json(array('is_error' => true, 'message' => $message, 'isLoggedIn' => $isLoggedIn), 404);
        }

        $dataSubGroup = $dataGroup->sub_groups()->find($request['sub_groups_id']);

        if ($dataSubGroup) {
            $dataDomainExist = $dataSubGroup->domains()->where('domains.title', explode('@', $request['email'])[1])->first();
        } else {
            $dataDomainExist = false;
        }

        if (!$dataSubGroup) {
            $message = "ไม่พบ".$dataGroup->meaning_of_sub_groups_id."ดังกล่าว";
        } else if ($dataSubGroup->restriction_mode == "allow" && !$dataDomainExist) {
            $message = "โดเมนอีเมลไม่ได้รับอนุญาต";
        } else if ($dataSubGroup->restriction_mode == "deny" && $dataDomainExist) {
            $message = "โดเมนอีเมลไม่ได้รับอนุญาต";
        }

        if (!empty($message)) {
            $_site->logs('register', '{"alert_msg":"'.$message.'"}', 422, '', '', json_encode($request->all(), JSON_UNESCAPED_UNICODE), $request['groups_id'], $request['sub_groups_id'], '', '');
            return response()->json(array('is_error' => true, 'message' => $message, 'isLoggedIn' => $isLoggedIn), 422);
        }

        $checkEmail = Members::where('email', $request['email'])->where('groups_id', $request['groups_id'])->first();
        if ($checkEmail) {
            $group = Groups::where('id', $request['groups_id'])->first();
            $is_success = false;
            // $message = "อีเมล์ ".$request['email']." ถูกใช้สมัครใน ".$group->title." ไปแล้ว";
            $message = "อีเมล์ ".$request['email']." ถูกใช้ไปแล้ว";
        } else {
            $checkIDCardDuplicated = Members::where('id_card', $request['id_card'])->where('groups_id', $request['groups_id'])->first();
            if ($checkIDCardDuplicated) {
                $group = Groups::where('id', $request['groups_id'])->first();
                $is_success = false;

                // $message = "เลขบัตรประชาชน ".$request['id_card']." ถูกใช้สมัครใน ".$group->title." ไปแล้ว";
                if ($request['is_foreign'] != 1) {
                    $message = "เลขบัตรประชาชน ".$request['id_card']." ถูกใช้ไปแล้ว";
                } else {
                    $message = "Passport No. ".$request['id_card']." already exists.";
                }

                $_site->logs('register', '{"alert_msg":"'.$message.'"}', 422, '', '', json_encode($request->all(), JSON_UNESCAPED_UNICODE), $request['groups_id'], $request['sub_groups_id'], '', '');
                return response()->json(array('is_error' => !$is_success, 'message' => $message, 'isLoggedIn' => $isLoggedIn), 200);
            }

            // if ($request['is_foreign'] != 1) {
            //     $checkIDCard = $oFunc->checkIDCard($request['id_card']);

            //     if ($checkIDCard == false) {
            //         $is_success = false;
            //         $message = "กรุณากรอกรหัสบัตรประชาชนให้ถูกต้อง";
            //         $_site->logs('register', '{"alert_msg":"'.$message.'"}', 422, '', '', json_encode($request->all(), JSON_UNESCAPED_UNICODE), $request['groups_id'], $request['sub_groups_id'], '', '');
            //         return response()->json(array('is_error' => !$is_success, 'message' => $message, 'isLoggedIn' => $isLoggedIn), 200);
            //     }
            // }

            $data->fill($request->except(['name_title_other', 'license_types_id']));

            if ($data->name_title == "other" && isset($request['name_title_other'])) {
                $data->name_title = $request['name_title_other'];
            }

            if ($data->name_title_en == "other" && isset($request['name_title_other_en'])) {
                $data->name_title_en = $request['name_title_other_en'];
            }

            $data->created_type = 1;
            $data->status = 1;
            $data->ip = ($dataGeoIP['ip'] != "Unknown") ? $dataGeoIP['ip'] : $_SERVER['REMOTE_ADDR'];
            $data->encrypt_password = Hash::make($request['password']);
            $data->country = $dataGeoIP['country'];
            $data->city = $dataGeoIP['city'];
            $data->device = $agent->device();
            $data->platform = $agent->platform();
            $data->platform_version = $agent->version($data->platform);
            $data->create_datetime = date('Y-m-d H:i:s');
            $data->modify_datetime = date('Y-m-d H:i:s');
            $data->last_changed_password = date('Y-m-d H:i:s');
            $is_success = $data->save();

            $member2level_group = array("0" => $request['level_groups_id']);
            $data->level_groups()->sync($member2level_group);
            $data->sub_groupsList()->sync([$request['sub_groups_id'] => ['active' => 1]]);

            if ($is_success) {
                $dataPwdHistory = new PasswordHistories;
                $dataPwdHistory->member_id = $data->id;
                $dataPwdHistory->password = $data->password;
                $dataPwdHistory->create_datetime = date('Y-m-d H:i:s');
                $dataPwdHistory->modify_datetime = date('Y-m-d H:i:s');
                $dataPwdHistory->save();

                $dataMember = Members::where("email", $request['email'])->where('groups_id', $request['groups_id'])->where("status", 1)->first();
                if (Hash::check($request['password'], $dataMember->encrypt_password)) {

                        // Check Pre-Approved
                        if ($dataLevelGroups) {
                            $isApprove = true;

                            if ($dataMember->groups->need_approval == 1) {
                                if ($dataGroup->id == 3) {
                                    if ($dataGroup->field_approval == "full_name") {
                                        $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataMember->groups_id)->where('first_name', $dataMember->first_name)->where('last_name', $dataMember->last_name)->first();
                                    } else if ($dataGroup->field_approval == "id_card") {
                                        $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataMember->groups_id)->where($dataGroup->field_approval, $dataMember->id_card)->first();
                                    } else if ($dataGroup->field_approval == "occupation_id") {
                                        $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataMember->groups_id)->where($dataGroup->field_approval, $dataMember->occupation_id)->first();
                                    } else {
                                        $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataMember->groups_id)->where('email', $dataMember->email)->first();
                                    }
                                } else {
                                    if ($dataGroup->field_approval == "full_name") {
                                        $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataMember->groups_id)->where('first_name', $dataMember->first_name)->where('last_name', $dataMember->last_name)->first();
                                    } else if ($dataGroup->field_approval == "id_card") {
                                        $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataMember->groups_id)->where($dataGroup->field_approval, $dataMember->id_card)->first();
                                    } else if ($dataGroup->field_approval == "occupation_id") {
                                        $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataMember->groups_id)->where('sub_groups_id', $dataMember->sub_groups_id)->where($dataGroup->field_approval, $dataMember->occupation_id)->first();
                                    } else {
                                        $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataMember->groups_id)->where('email', $dataMember->email)->first();
                                    }
                                }

                                if ($dataPreApprovedExist) {
                                    $dataMember->approved_type = 1;
                                    $dataMember->approved_by = $dataPreApprovedExist->created_by;
                                    $dataMember->approved_field = $dataGroup->field_approval;

                                    if ($dataPreApprovedExist->courses) {
                                        $dataMember->courses()->syncWithoutDetaching(array_pluck($dataPreApprovedExist->courses, 'id'));
                                        $dataPreApprovedExist->courses()->detach();
                                    }

                                    if ($dataPreApprovedExist->classrooms) {
                                        $dataMember->classrooms()->syncWithoutDetaching(array_pluck($dataPreApprovedExist->classrooms, 'id'));
                                        $dataPreApprovedExist->classrooms()->detach();
                                    }

                                    $dataPreApprovedExist->delete();
                                    $dataPreApprovedExist->level_groups()->detach();

                                } else {

                                    $isApprove = false;

                                }

                            } else {
                                $dataMember->approved_type = 3;
                            }

                            if ($isApprove) {

                                $dataMember->active = 1;
                                $dataMember->approved_datetime = date('Y-m-d H:i:s');
                                $dataMember->modify_datetime = date('Y-m-d H:i:s');
                                $is_success = $dataMember->save();

                                $request->session()->regenerate();
                                $request->session()->put('_user', $data);
                                $this->swappingSession($data);

                                //Send Mail Register Approve Success
                                $url = config('constants._BASE_URL').$dataGroup->key."/login";
                                $dataMail = array(
                                    'dataMember'=>$dataMember,
                                    'dataGroup'=> $dataGroup,
                                    'dataSubGroup' => $dataSubGroup,
                                    'dataLevelGroups' => $dataLevelGroups,
                                    'url' => $url
                                );

                                try {
                                    Mail::send('register-success-mail', $dataMail, function($mail) use ($dataMail) {
                                        if ($dataMail['dataMember']['is_foreign'] != 1) {
                                            $receiverName = $dataMail['dataMember']['first_name']." ".$dataMail['dataMember']['last_name'];
                                        } else {
                                            $receiverName = $dataMail['dataMember']['first_name_en']." ".$dataMail['dataMember']['last_name_en'];
                                        }
                                        $mail->to($dataMail['dataMember']['email'], $receiverName)->subject('แจ้งยืนยันการเป็นสมาชิก '.$dataMail['dataGroup']['subject']);
                                        $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                        $mail->bcc('nawee.ku.dootvmedia@gmail.com', 'Nawee Kunrod');
                                    });

                                    if( count(Mail::failures()) > 0 ) {
                                        $_site->logs('register', '{"alert_msg":"Delivery mail failure."}', 500, '', '', json_encode($request->all(), JSON_UNESCAPED_UNICODE), $request['groups_id'], $request['sub_groups_id'], '', '');
                                    }
                                } catch(\Exception $e) {
                                    $_site->logs('register', '{"alert_msg":"'.$e->getMessage().'"}', 500, '', '', json_encode($request->all(), JSON_UNESCAPED_UNICODE), $request['groups_id'], $request['sub_groups_id'], '', '');
                                }

                                $message = "ยินดีต้อนรับเข้าสู่ระบบ ท่านสามารถเข้าใช้ระบบ e-Learning ได้โดยผ่านการอนุมัติจากทางผู้ดูแลระบบเรียบร้อยแล้ว" ;
                                //End

                                $isLoggedIn = true;

                            } else {

                                //Send Mail Register Approve Waiting
                                $url = config('constants._BASE_URL').$dataGroup->key."/login";
                                $dataMail = array(
                                    'dataMember'=>$dataMember,
                                    'dataGroup'=> $dataGroup,
                                    'dataSubGroup' => $dataSubGroup,
                                    'dataLevelGroups' => $dataLevelGroups,
                                    'url' => $url
                                );

                                try {
                                    Mail::send('register-waiting-mail', $dataMail, function($mail) use ($dataMail) {
                                        if ($dataMail['dataMember']['is_foreign'] != 1) {
                                            $receiverName = $dataMail['dataMember']['first_name']." ".$dataMail['dataMember']['last_name'];
                                        } else {
                                            $receiverName = $dataMail['dataMember']['first_name_en']." ".$dataMail['dataMember']['last_name_en'];
                                        }
                                        $mail->to($dataMail['dataMember']['email'], $receiverName)->subject('แจ้งการสมคัรสมาชิกเข้าสู่'.$dataMail['dataGroup']['subject']);
                                        $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                        $mail->bcc('nawee.ku.dootvmedia@gmail.com', 'Nawee Kunrod');
                                    });

                                    if( count(Mail::failures()) > 0 ) {
                                        $_site->logs('register', '{"alert_msg":"Delivery mail failure."}', 500, '', '', json_encode($request->all(), JSON_UNESCAPED_UNICODE), $request['groups_id'], $request['sub_groups_id'], '', '');
                                    }
                                } catch(\Exception $e) {
                                    $_site->logs('register', '{"alert_msg":"'.$e->getMessage().'"}', 500, '', '', json_encode($request->all(), JSON_UNESCAPED_UNICODE), $request['groups_id'], $request['sub_groups_id'], '', '');
                                }
                                //End

                                $message = "ขอบคุณที่สมัครสมาชิก ท่านสามารถเข้าใช้ระบบ e-Learning ได้ทันทีเมื่อได้รับการอนุมัติจากทางผู้ดูแลระบบ";
                            }
                        }
                        // End Check Pre-Approved

                }
            } else {
                $message = "เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง";
            }
        }

        $status = !$is_success ? 422 : 200;
        $_site->logs('register', '{"alert_msg":"'.$message.'"}', $status, '', '', json_encode($request->all(), JSON_UNESCAPED_UNICODE), $request['groups_id'], $request['sub_groups_id'], '', '');
        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'isLoggedIn' => $isLoggedIn), 200);
    }

    public function login(Request $request, $isLogin = true, SiteController $_site)
    {
        if ($request->has('forceLogin') && $request->forceLogin == true) {
            $tempUser = session()->get('temp_user');
            $requestData = $tempUser;
        } else {
            $requestData = $request->all();
        }

        // $referer = config('constants.URL.HOME');
        $site = isset($requestData['site']) ? '?site='.$requestData['site'] : '';
        // $referer .= $site;

        // $data = Members::where("email", $request['email'])->where('groups_id', $request['groups_id'])->groups()->where('groups.internal', 1)->first();
        $data = Members::where("email", $requestData['email'])->where('groups_id', $requestData['groups_id'])->first();
        if($data) {
            if ($data->groups->incorrect_password_limit > 0 && $data->incorrect_password >= $data->groups->incorrect_password_limit) {
                $data->active = 2;
                $data->suspended_datetime = date('Y-m-d H:i:s');
                $data->save();

                $message = 'บัญชีผู้ใช้ของท่านได้ถูกระงับการใช้งาน เนื่องจากท่านใส่รหัสผ่านผิดเกินจำนวนที่กำหนด';
                $_site->logs('login', '{"alert_msg":"'.$message.'"}', 401, '', '', '{"email":"'.$requestData['email'].'", "password":"'.$requestData['password'].'"}', $requestData['groups_id'], '', '', '');
                return response()->json(array('is_error' => true, 'message'=>$message, 'isContact' => true), 200);
            }

            if (Hash::check($requestData['password'], $data->encrypt_password)) {

                $data->incorrect_password = 0;

                if($data->status == 1) {
                        if ($data->groups->max_account_age > 0) {
                            $dateNow = Carbon::now();
                            $dateLastLogin = Carbon::parse($data->last_login);

                            if ($data->active_remark != 3 && $dateNow->diffInDays($dateLastLogin) >= $data->groups->max_account_age) {
                                $data->active = 2;
                                $data->suspended_datetime = date('Y-m-d H:i:s');
                                $data->save();

                                $message = 'บัญชีผู้ใช้ของท่านได้ถูกระงับการใช้งาน เนื่องจากท่านไม่ได้ใช้งานเกินระยเวลาที่กำหนด';
                                $_site->logs('login', '{"alert_msg":"'.$message.'"}', 401, '', '', '{"email":"'.$requestData['email'].'", "password":"'.$requestData['password'].'"}', $requestData['groups_id'], '', '', '');
                                return response()->json(array('is_error' => true, 'message'=>$message, 'isContact' => true), 200);
                            }
                        }

                        if ($data->reject_status == 0) {
                            if($data->approved_type != null) {
                                if ($data->active == 1) {
                                    // $_user = session()->get('_user');

                                    // if ($data->my_session_id && (!$request->has('forceLogin') || $request->forceLogin === false)) {
                                    //     if (is_null($_user) || (!is_null($_user) && $_user['my_session_id'] != $data->my_session_id)) {
                                    //         $dataMySessionId = unserialize(session()->getHandler()->read($data->my_session_id));
                                    //         if (!empty($dataMySessionId['_user_session']) && (is_object($dataMySessionId['_user_session']) || is_array($dataMySessionId['_user_session']))) {
                                    //             $request->session()->put('temp_user', $request->all());
                                    //             $pageSessionRedirect = str_replace("{GROUP_KEY}", $data->groups->key, config('constants.URL_GROUP.SESSION_EXISTS'));

                                    //             $redirectPage = $pageSessionRedirect.$site.($site == '' ? '?' : '&')."redirectPage=".urlencode(url()->current());

                                    //             $message = 'บัญชีผู้ใช้ของท่านได้มีการใช้งานอยู่ในขณะนี้';
                                    //             $_site->logs('login', '{"alert_msg":"'.$message.'"}', 401, '', '', '{"email":"'.$requestData['email'].'", "password":"'.$requestData['password'].'"}', $requestData['groups_id'], '', '', '');
                                    //             return response()->json(array('is_error' => true, 'message'=>$message, 'redirectPage' => $redirectPage), 200);
                                    //         }
                                    //     }
                                    // }

                                    // return response()->json(['debug'], 500);

                                    $dataGeoIP = GeoIP::getLocation();
                                    $agent = new Agent();

                                    $data->ip = ($dataGeoIP['ip'] != "Unknown") ? $dataGeoIP['ip'] : $_SERVER['REMOTE_ADDR'];
                                    $data->country = $dataGeoIP['country'];
                                    $data->city = $dataGeoIP['city'];
                                    $data->device = $agent->device();
                                    $data->platform = $agent->platform();
                                    $data->platform_version = $agent->version($data->platform);
                                    $data->modify_datetime = date('Y-m-d H:i:s');

                                    if ($isLogin) { $data->last_login = date('Y-m-d H:i:s'); }

                                    $is_success = $data->save();
                                    $request->session()->put('_user_session', $data);

                                    $conflict = $data->sub_groupsList()->withPivot('email')->where('active', 2)->get();

                                    if ($is_success) {
                                        $request->session()->regenerate();
                                        $request->session()->put('_user', $data);
                                        $this->swappingSession($data);
                                    }

                                    $_site->logs('login', '{"alert_msg":"success"}', 200, '', $data->id, '{"email":"'.$requestData['email'].'", "password":"'.$requestData['password'].'"}', $requestData['groups_id'], '', '', '');

                                    $isChangePassword = false;
                                    if ($data->groups->max_password_age > 0) {
                                        $dateNow = Carbon::now();
                                        $dateLastChangedPassword = Carbon::parse($data->last_changed_password);
                                        $isChangePassword = $dateNow->diffInDays($dateLastChangedPassword) >= $data->groups->max_password_age;
                                    }

                                    if ($data->active_remark == 0 || $isChangePassword) {
                                        $option = 'change-password';
                                        $message = 'ยินดีต้อนรับเข้าสู่ระบบ e-Learning';
                                        return response()->json(array('is_error' => false, 'message'=>$message, 'option' => $option), 200);
                                    } else if ($data->active_remark == 3) {
                                        $option = 're-active';
                                        $message = 'ยินดีต้อนรับเข้าสู่ระบบ e-Learning';
                                        return response()->json(array('is_error' => false, 'message'=>$message, 'option' => $option), 200);
                                    } else if ($data->active_remark == 2 || $conflict->count() > 0) {
                                        $option = 'has-group-changing';
                                        $active = $data->sub_groupsList()->where('active', 1)->first();
                                        $message = 'ยินดีต้อนรับเข้าสู่ระบบ e-Learning';
                                        return response()->json(array('is_error' => false, 'message'=>$message, 'option' => $option, 'active' => $active, 'conflict' => $conflict), 200);
                                    } else {
                                        $message = 'ยินดีต้อนรับเข้าสู่ระบบ e-Learning';
                                        return response()->json(array('is_error' => false, 'message'=>$message), 200);
                                    }
                                } else if ($data->active == 2) {
                                    $message = 'บัญชีผู้ใช้ของท่านได้ถูกระงับการใช้งาน';
                                    $_site->logs('login', '{"alert_msg":"'.$message.'"}', 401, '', $data->id, '{"email":"'.$requestData['email'].'", "password":"'.$requestData['password'].'"}', $requestData['groups_id'], '', '', '');
                                    return response()->json(array('is_error' => true, 'message'=>$message, 'isContact' => true), 200);
                                } else {
                                    $message = 'บัญชีผู้ใช้ของท่านยังไม่ได้เปิดการใช้งาน';
                                    $_site->logs('login', '{"alert_msg":"'.$message.'"}', 401, '', $data->id, '{"email":"'.$requestData['email'].'", "password":"'.$requestData['password'].'"}', $requestData['groups_id'], '', '', '');
                                    return response()->json(array('is_error' => true, 'message'=>$message, 'isContact' => true), 200);
                                }
                            }else{
                                $message = 'ท่านอยุ่ระหว่ารอการอนุมัติจากผู้ดูแลระบบ';
                                $_site->logs('login', '{"alert_msg":"'.$message.'"}', 401, '', $data->id, '{"email":"'.$requestData['email'].'", "password":"'.$requestData['password'].'"}', $requestData['groups_id'], '', '', '');
                                return response()->json(array('is_error' => true, 'message'=>$message, 'isContact' => false), 200);
                            }
                        } else {
                            $message = 'ท่านถูกปฏิเสธในการสมัครสมาชิก';
                            $_site->logs('login', '{"alert_msg":"'.$message.'"}', 401, '', $data->id, json_encode($requestData, JSON_UNESCAPED_UNICODE), $requestData['groups_id'], '', '', '');
                            return response()->json(array('is_error' => true, 'message'=>$message, 'isContact' => true), 200);
                        }

                }else{
                    $message = 'เกิดข้อผิดพลาด ไม่สามารถเข้าสู่ระบบได้';
                    $_site->logs('login', '{"alert_msg":"'.$message.'"}', 401, '', '', '{"email":"'.$requestData['email'].'", "password":"'.$requestData['password'].'"}', $requestData['groups_id'], '', '', '');
                    return response()->json(array('is_error' => true, 'message'=>$message, 'isContact' => true), 200);
                }
            } else {
                $data->increment('incorrect_password');
                $message = 'เกิดข้อผิดพลาด อีเมล์ รหัสผ่านไม่ตรงกัน กรุณาลองใหม่อีกครั้ง';
                $_site->logs('login', '{"alert_msg":"'.$message.'"}', 401, '', '', '{"email":"'.$requestData['email'].'", "password":"'.$requestData['password'].'"}', $requestData['groups_id'], '', '', '');
                return response()->json(array('is_error' => true, 'message'=>$message, 'isContact' => false), 200);
            }

        } else {
            $message = 'เกิดข้อผิดพลาด ไม่พบอีเมล์ในระบบ กรุณาตรวจสอบอีกครั้ง';
            $_site->logs('login', '{"alert_msg":"'.$message.'"}', 401, '', '', '{"email":"'.$requestData['email'].'", "password":"'.$requestData['password'].'"}', $requestData['groups_id'], '', '', '');
            return response()->json(array('is_error' => true, 'message'=>$message, 'isContact' => false), 200);
        }

    }

    public function use_sub_group(Request $request, SiteController $_site){
        $dataSession = session()->get('_user');
        $data = Members::find($dataSession['id']);
        $dataSubGroupPivot = $data->sub_groupsList()->withPivot('email')->where('sub_groups_id', $request['sgID'])->first();
        $data->email = $dataSubGroupPivot->pivot->email;
        $data->sub_groups_id = $request['sgID'];
        $data->active_remark = 1;
        $is_success = $data->save();

        if ($is_success) {

            $data->sub_groupsList()->where('active', 1)->update(['active' => 3]);
            $data->sub_groupsList()->where('active', 2)->update(['active' => 4]);
            $data->sub_groupsList()->syncWithoutDetaching([$request['sgID'] => ['active' => 1]]);

            $request->session()->regenerate();
            $request->session()->put('_user', $data);
            $this->swappingSession($data);

            $status = 200;
            $message = "ท่านได้ทำการเปลี่ยนกลุ่มเรียบร้อย.";
            $alert_msg = "success";

            $dataOldSubGroup = $data->sub_groupsList()->where('members_id', $data->id)->where('active', 3)->orderBy('id', 'desc')->first();
            $dataGroup = $data->groups()->first();
            $dataSubGroup = $data->sub_groups()->first();
            $url = config('constants._BASE_URL').$dataGroup->key."/login";
            //Send Mail Change Sub Groups Success
            $dataMail = array(
                'dataMember'=>$data,
                'dataGroup'=> $dataGroup,
                'dataOldSubGroup' => $dataOldSubGroup,
                'dataSubGroup' => $dataSubGroup,
                'url' => $url
            );
            Mail::send('change-subgroups-success-mail', $dataMail, function($mail) use ($dataMail) {
                if ($dataMail['dataMember']['is_foreign'] != 1) {
                    $receiverName = $dataMail['dataMember']['first_name']." ".$dataMail['dataMember']['last_name'];
                } else {
                    $receiverName = $dataMail['dataMember']['first_name_en']." ".$dataMail['dataMember']['last_name_en'];
                }
                $mail->to($dataMail['dataMember']['email'], $receiverName)->subject('แจ้งเปลี่ยนสถานะการเป็นสมาชิกจาก '.$dataMail['dataOldSubGroup']['title'].' เป็น '.$dataMail['dataSubGroup']['title']);
                $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
            });

        } else {

            $status = 401;
            $message = "เกิดข้อผิดพลาด ไม่สามารถเปลี่ยนกลุ่มได้";
            $alert_msg = $message;

        }

        $_site->logs('use sub group', '{"alert_msg":"'.$alert_msg.'"}', $status, '', $data->id, '{"sub_groups_id":"'.$request['sgID'].'"}', $data->groups_id, '', '', '');
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function change_password(Request $request, SiteController $_site)
    {
        $validator = Validator::make($request->all(), [
            'change_password' => 'required|between:8,255|case_diff|numbers|letters'
        ]);

        if ($validator->fails()) {
            // return response()->json($validator->messages(), 422);
            return response()->json(['is_error' => true, 'message' => 'รหัาผ่านไม่ถูกต้องตามเงื่อนไขความปลอดภัย'], 200);
        }

        $dataSession = session()->get('_user');

        $dataPwdExist = PasswordHistories::where('member_id', $dataSession['id'])->whereNull('create_by')->orderBy('id', 'desc')->limit(5)->get()->first(function ($pwd) use ($request) {
            return $pwd->password == $request['change_password'];
        });

        if ($dataPwdExist) {
            $status = 422;
            $message = "รหัสผ่านดังกล่าวเคยถูกใช้ไปแล้ว";
            $alert_msg = $message;

            $_site->logs('change password', '{"alert_msg":"'.$alert_msg.'"}', $status, '', $dataSession['id'], '{"password":"'.$dataPwdExist->password.'", "new password":"'.$request['change_password'].'"}', $dataSession['groups_id'], $dataSession['sub_groups_id'], '', '');
            return response()->json(array('is_error' => true, 'message' => $message), 200);
        }

        $data = new Members;
        $data = $data->find($dataSession['id']);
        $data->password = $request['change_password'];
        $data->encrypt_password = Hash::make($request['change_password']);
        $data->last_changed_password = date('Y-m-d H:i:s');
        $data->active_remark = 1;
        $is_success = $data->save();

        if ($is_success) {
            $dataOldPwd = PasswordHistories::where('active', 1)->update(['active' => 0]);

            $dataPwdHistory = new PasswordHistories;
            $dataPwdHistory->member_id = $data->id;
            $dataPwdHistory->password = $data->password;
            $dataPwdHistory->create_datetime = date('Y-m-d H:i:s');
            $dataPwdHistory->modify_datetime = date('Y-m-d H:i:s');
            $dataPwdHistory->save();

            // $dataMail = array(
            //     'dataMember'=>$data,
            //     'dataGroup'=>$dataSession['groups'],
            //     'change_password'=>$request['change_password']
            // );
            // Mail::send('change-password-mail', $dataMail, function($mail) use ($dataMail) {
            //     if ($dataMail['dataMember']['is_foreign'] != 1) {
            //         $receiverName = $dataMail['dataMember']['first_name']." ".$dataMail['dataMember']['last_name'];
            //     } else {
            //         $receiverName = $dataMail['dataMember']['first_name_en']." ".$dataMail['dataMember']['last_name_en'];
            //     }
            //     $mail->to($dataMail['dataMember']['email'], $receiverName)->subject('แจ้งการเปลี่ยนรหัสผ่าน '.$dataMail['dataGroup']['subject']);
            //     $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
            // });

            $status = 200;
            $message = "เปลี่ยนรหัสผ่านเรียบร้อย";
            $alert_msg = "success";
        } else {
            $status = 401;
            $message = "เกิดข้อผิดพลาด ไม่สามารถเปลี่ยนรหัสผ่านได้";
            $alert_msg = $message;
        }

        $active = $data->sub_groupsList()->where('active', 1)->first();
        $conflict = $data->sub_groupsList()->where('active', 2)->get();
        if ($conflict->count() > 0) {
            $hasGroupChanging = true;
        } else {
            $hasGroupChanging = false;
        }

        $_site->logs('change password', '{"alert_msg":"'.$alert_msg.'"}', $status, '', $data->id, '{"password":"'.$data->password.'", "new password":"'.$request['change_password'].'"}', $data->groups_id, '', '', '');
        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'hasGroupChanging' => $hasGroupChanging, 'active' => $active, 'conflict' => $conflict), 200);

    }

    public function use_old_password(SiteController $_site){

        $dataSession = session()->get('_user');
        $data = new Members;
        $data = $data->find($dataSession['id']);
        $data->active_remark = 1;
        $data->last_changed_password = date('Y-m-d H:i:s');
        $is_success = $data->save();

        if ($is_success) {
            $status = 200;
            $message = "ยืนยันการใช้รหัสผ่านเดิมเรียบร้อย";
            $alert_msg = "success";
        } else {
            $status = 401;
            $message = "เกิดข้อผิดพลาด ไม่สามารถยืนยันการใช้รหัสผ่านเดิมได้";
            $alert_msg = $message;
        }

        $active = $data->sub_groupsList()->where('active', 1)->first();
        $conflict = $data->sub_groupsList()->where('active', 2)->get();
        if ($conflict->count() > 0) {
            $hasGroupChanging = true;
        } else {
            $hasGroupChanging = false;
        }

        $_site->logs('use old password', '{"alert_msg":"'.$alert_msg.'"}', $status, '', $data->id, '{"password":"'.$data->password.'"}', $data->groups_id, '', '', '');
        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'hasGroupChanging' => $hasGroupChanging, 'active' => $active, 'conflict' => $conflict), 200);

    }

    public function changePasswordOnly(Request $request, SiteController $_site)
    {
        $dataSession = session()->get('_user');
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|max:255',
            'new_password' => 'required|between:8,255|case_diff|numbers|letters',
            'password_confirmation' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            $_site->logs('change-password', '{"alert_msg":"Invalid fields."}', 422, '', $dataSession['id'], json_encode($request->all(), JSON_UNESCAPED_UNICODE), $dataSession['groups_id'], $dataSession['sub_groups_id'], '', '');
            return response()->json($validator->messages(), 422);
            // return response()->json(['is_error' => true, 'message' => 'รหัาผ่านไม่ถูกต้องตามเงื่อนไขความปลอดภัย'], 200);
        }

        $dataMember = Members::find($dataSession->id);

        if ($dataMember->password != $request['old_password']) {
            $status = 422;
            $message = "รหัสผ่านเดิมไม่ถูกต้อง";
            $alert_msg = $message;

            $_site->logs('change-password', '{"alert_msg":"'.$alert_msg.'"}', $status, '', $dataSession['id'], json_encode($request->all(), JSON_UNESCAPED_UNICODE), $dataSession['groups_id'], $dataSession['sub_groups_id'], '', '');
            return response()->json(array('is_error' => true, 'message' => $message), 422);
        }

        $dataPwdExist = PasswordHistories::where('member_id', $dataSession['id'])->whereNull('create_by')->orderBy('id', 'desc')->limit(5)->get()->first(function ($pwd) use ($request) {
            return $pwd->password == $request['new_password'];
        });

        if ($dataPwdExist) {
            $status = 422;
            $message = "รหัสผ่านดังกล่าวเคยถูกใช้ไปแล้ว";
            $alert_msg = $message;

            $_site->logs('change-password', '{"alert_msg":"'.$alert_msg.'"}', $status, '', $dataSession['id'], json_encode($request->all(), JSON_UNESCAPED_UNICODE), $dataSession['groups_id'], $dataSession['sub_groups_id'], '', '');
            return response()->json(array('is_error' => true, 'message' => $message), 422);
        }

        $data = new Members;
        $data = $data->find($dataSession['id']);
        $data->password = $request['new_password'];
        $data->encrypt_password = Hash::make($request['new_password']);
        $data->last_changed_password = date('Y-m-d H:i:s');
        $data->active_remark = 1;
        $is_success = $data->save();

        if ($is_success) {
            $dataOldPwd = PasswordHistories::where('active', 1)->update(['active' => 0]);

            $dataPwdHistory = new PasswordHistories;
            $dataPwdHistory->member_id = $data->id;
            $dataPwdHistory->password = $data->password;
            $dataPwdHistory->create_datetime = date('Y-m-d H:i:s');
            $dataPwdHistory->modify_datetime = date('Y-m-d H:i:s');
            $dataPwdHistory->save();

            // $dataMail = array(
            //     'dataMember'=>$data,
            //     'dataGroup'=>$dataSession['groups'],
            //     'new_password'=>$request['new_password']
            // );
            // Mail::send('change-password-mail', $dataMail, function($mail) use ($dataMail) {
            //     if ($dataMail['dataMember']['is_foreign'] != 1) {
            //         $receiverName = $dataMail['dataMember']['first_name']." ".$dataMail['dataMember']['last_name'];
            //     } else {
            //         $receiverName = $dataMail['dataMember']['first_name_en']." ".$dataMail['dataMember']['last_name_en'];
            //     }
            //     $mail->to($dataMail['dataMember']['email'], $receiverName)->subject('แจ้งการเปลี่ยนรหัสผ่าน '.$dataMail['dataGroup']['subject']);
            //     $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
            // });

            $status = 200;
            $message = "เปลี่ยนรหัสผ่านเรียบร้อย";
            $alert_msg = "success";
        } else {
            $status = 401;
            $message = "เกิดข้อผิดพลาด ไม่สามารถเปลี่ยนรหัสผ่านได้";
            $alert_msg = $message;
        }

        $_site->logs('change-password', '{"alert_msg":"'.$alert_msg.'"}', $status, '', $data->id, json_encode($request->all(), JSON_UNESCAPED_UNICODE), $data->groups_id, $data['sub_groups_id'], '', '');
        return response()->json(array('is_error' => !$is_success, 'message' => $message), $status);

    }

    public function forgot(Request $request, SiteController $_site){

        $dataMember = new Members;
        $dataMember = $dataMember->where('email', $request['email'])->where('groups_id', $request['groups_id'])->where('status', 1)->first();
        $dataGroup = $dataMember->groups()->first();
        if(count($dataMember)){
            $dataMail = array(
                'dataMember'=>$dataMember,
                'dataGroup'=>$dataGroup,
            );
            Mail::send('forgot-password-mail', $dataMail, function($mail) use ($dataMail) {
                if ($dataMail['dataMember']['is_foreign'] != 1) {
                    $receiverName = $dataMail['dataMember']['first_name']." ".$dataMail['dataMember']['last_name'];
                } else {
                    $receiverName = $dataMail['dataMember']['first_name_en']." ".$dataMail['dataMember']['last_name_en'];
                }
                $mail->to($dataMail['dataMember']['email'], $receiverName)->subject('แจ้งรหัสผ่าน '.$dataMail['dataGroup']['subject']);
                $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
            });
            $message = 'ระบบส่ง password ไปที่ '.$request['email'].' เรียบร้อยแล้ว. กรุณาตรวจสอบอีเมล์';
            $is_error = false;
            $alert_msg = 'success';
            $status = 200;
        }else{
            $message = 'เกิดข้อผิดพลาด ไม่พบ '.$request['email'].', กรุณาลองใหม่.';
            $is_error = true;
            $alert_msg = $message;
            $status = 401;
        }

        $_site->logs('forgot password', '{"alert_msg":"'.$alert_msg.'"}', $status, '', '', '{"email":"'.$request['email'].'"}', $request['groups_id'], '', '', '');
        return response()->json(array('is_error' => $is_error, 'message'=> $message), 200);

    }

    public function groups($key){
        $data = Groups::where('key', $key)->first();
        $data->sub_groups = $data->sub_groups()->orderByRaw('CONVERT (title USING tis620) ASC')->get();

        $data->questionnaire_packs = $data->questionnaire_packs()->where('status', 1)->first();
        if(count($data->questionnaire_packs)){
            $data->questionnaire_packs->questionnaires = $data->questionnaire_packs->questionnaires()->where('status', 1)->orderBy('order', 'asc')->get();
            for($i=0; $i<count($data->questionnaire_packs->questionnaires); $i++) {
                $data->questionnaire_packs->questionnaires[$i]->questionnaire_choices = $data->questionnaire_packs->questionnaires[$i]->questionnaire_choices()->where('status', 1)->orderBy('order', 'asc')->get();
            }
        }

        return response()->json($data, 200);
    }

    public function groups_id($id){
        $data = Groups::find($id);
        return response()->json($data, 200);
    }

    public function groups404(){
        $data = Groups::where('page', 1)->get();
        return response()->json($data, 200);
    }

    public function sub_groups($id){
        $data = LevelGroups::where('sub_groups_id', $id);
        $data = $data->where('approve', 1);
        $data = $data->orderByRaw('CONVERT (title USING tis620) ASC')->get();
        return response()->json($data, 200);
    }

    public function avatars_list(Request $request){
        $data = Avatars::where('status', 1)->orderBy($request['order_by'],$request['order_direction'])->get();
        return response()->json($data, 200);
    }

    public function avatars($id){
        $data = Avatars::where('id', $id)->where('status', '1')->first();
        return response()->json($data, 200);
    }

    public function changeAvatar(Request $request){
        //
        $dataSession = session()->get('_user');
        $data = Members::find($dataSession['id']);
        $input = $request->json()->all();
        $data->fill($input);
        $is_success = $data->save();
        $request->session()->put('_user', $data);

        if ($is_success) {
            $data = Avatars::where('id', $data->avatar_id)->where('status', '1')->first();
            $message = "The avatar has been updated.";
        } else {
            $path = "";
            $message = "Failed to update the avatar.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'path' => $data->avatar_img), 200);
    }

    public function highlights($groupsKey, Request $request)
    {
        $data = new Groups;
        $data = $data->where('key', $groupsKey)->first();
        $data = $data->highlights();
        $data = $data->whereDate('start_date', '<=', date('Y-m-d'));
        $data = $data->whereDate('end_date', '>=', date('Y-m-d'));
        $data = $data->where('status', '=', '1');
        $data = $data->orderBy($request['order_by'],$request['order_direction']);
        $data = $data->get();
        return response()->json($data, 200);
    }

    public function categories($groupsKey, Request $request)
    {
        $data = new Groups;
        $data = $data->where('key', $groupsKey)->first();
        $data = $data->categories();
        $data = $data->where('status', '=', '1')->orderBy($request['order_by'],$request['order_direction'])->get();
        return response()->json($data, 200);
    }

    public function qa(Request $request)
    {
        $data = QA::where('status', 1)->orderBy($request['order_by'],$request['order_direction'])->get();
        return response()->json($data, 200);
    }

    public function courses_list($groupsKey, Request $request, _FunctionsController $oFunc)
    {
        $per_page = $request->input('per_page', 16);
        $order_by = $request->input('order_by', 'order');
        $order_direction = $request->input('order_direction', 'asc');
        $ignore_state = $request['ignore_state'];

        $_user = session()->get('_user');

        if($_user){
            $_user->groups = $_user->groups()->first();
            if($groupsKey == $_user->groups->key){
                if($_user->level_groups){

                    $groups = new Groups;
                    $groups = $groups->where('key', $groupsKey)->first();
                    $allCoursesIds = array();

                    // BEGIN COURSES (LEVEL PUBLIC)
                    $data = $groups->courses();
                    $data = $data->where('level_public', 1);

                    $allCoursesIds = array_merge($allCoursesIds, $data->where('status', '1')->select('courses.id')->get()->pluck('id')->toArray());
                    // END COURSES (LEVEL PUBLIC)

                    // BEGIN COURSES (CLASSROOM TARGET)
                    $dataMembers = new Members;
                    $dataMembers = $dataMembers->find($_user->id);
                    $classrooms_target = $dataMembers->classrooms()->whereDate('start_datetime', '<=', date('Y-m-d H:i:s'))->whereDate('end_datetime', '>=', date('Y-m-d H:i:s'))->get();
                    for($i=0; $i<count($classrooms_target); $i++) {
                        $dataCoursesClassroomsTargets = $classrooms_target[$i]->courses();
                        if($groups->use_sub_groups_single){
                            $dataCoursesClassroomsTargets = $dataCoursesClassroomsTargets->whereHas('classrooms.sub_groups', function($query) use ($dataMembers) {
                                $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                            });
                        }

                        $classrooms_target[$i]->courses = $dataCoursesClassroomsTargets->where('status', '1')->select('courses.id')->get();
                        $allCoursesIds = array_merge($allCoursesIds, $classrooms_target[$i]->courses->pluck('id')->toArray());
                    }
                    // END COURSES (CLASSROOM TARGET)

                    // BEGIN COURSES (CLASSROOM LEVEL GROUP)
                    $dataMembers = new Members;
                    $dataMembers = $dataMembers->find($_user->id);
                    $classrooms_level_group = $dataMembers->level_groups()->get();
                    for($l=0; $l<count($classrooms_level_group); $l++) {
                        $classrooms_level_group[$l]->classroom = $classrooms_level_group[$l]->classrooms()->whereDate('start_datetime', '<=', date('Y-m-d H:i:s'))->whereDate('end_datetime', '>=', date('Y-m-d H:i:s'))->get();
                        for($i=0; $i<count($classrooms_level_group[$l]->classroom); $i++) {
                            $dataCoursesClassroomsLevelGroups = $classrooms_level_group[$l]->classroom[$i]->courses();

                            if($groups->use_sub_groups_single){
                                $dataCoursesClassroomsLevelGroups = $dataCoursesClassroomsLevelGroups->whereHas('classrooms.sub_groups', function($query) use ($dataMembers) {
                                    $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                                });
                            }

                            $classrooms_level_group[$l]->classroom[$i]->courses = $dataCoursesClassroomsLevelGroups->where('status', '1')->select('courses.id')->get();
                            $allCoursesIds = array_merge($allCoursesIds, $classrooms_level_group[$l]->classroom[$i]->courses->pluck('id')->toArray());
                        }
                    }
                    // END COURSES (CLASSROOM LEVEL GROUP)

                    // BEGIN COURSES (TARGET)
                    $dataMembers = new Members;
                    $dataMembers = $dataMembers->find($_user->id);

                    $dataCoursesTargets = $dataMembers->courses();

                    if($groups->use_sub_groups_single){
                        $dataCoursesTargets = $dataCoursesTargets->whereHas('sub_groups', function($query) use ($dataMembers) {
                            $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                        });
                    }

                    $courses_target = $dataCoursesTargets->where('status', '1')->select('courses.id')->get();
                    $allCoursesIds = array_merge($allCoursesIds, $courses_target->pluck('id')->toArray());
                    // END COURSES (TARGET)

                    // BEGIN COURSES (LEVEL GROUPS)
                    $dataMembers = new Members;
                    $dataMembers = $dataMembers->find($_user->id);
                    $courses_level_groups = $dataMembers->level_groups()->get();
                    for($l=0; $l<count($courses_level_groups); $l++) {
                        $dataCoursesLevelGroups = $courses_level_groups[$l]->courses();

                        if($groups->use_sub_groups_single){
                            $dataCoursesLevelGroups = $dataCoursesLevelGroups->whereHas('sub_groups', function($query) use ($dataMembers) {
                                $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                            });
                        }

                        $courses_level_groups[$l] = $dataCoursesLevelGroups->where('status', '1')->select('courses.id')->get();
                        $allCoursesIds = array_merge($allCoursesIds, $courses_level_groups[$l]->pluck('id')->toArray());
                    }
                    // END COURSES (LEVEL GROUPS)

                    // All Courses
                    $dataAllCourses = Courses::whereIn('id', array_unique($allCoursesIds))->orderBy($order_by, $order_direction)->paginate($per_page);
                    for($i=0; $i<count($dataAllCourses); $i++) {
                        if($dataAllCourses[$i]->latest_end_datetime >= date('Y-m-d')){
                            $dataAllCourses[$i]->latest = 1;
                        }else{
                            $dataAllCourses[$i]->latest = 0;
                        }
                        $dataAllCourses[$i]->categories = $dataAllCourses[$i]->categories()->where('groups_id', $groups->id)->first();

                        if ($ignore_state == 'live') {
                            $dataAllCourses[$i]->topics = null;
                        } else {
                            $dataAllCourses[$i]->topics = $dataAllCourses[$i]->topics()
                                                ->select('id', 'title', 'state', 'live_start_datetime', 'streaming_status', 'status')
                                                ->where('state', 'live')
                                                ->where('streaming_status', 1)
                                                ->where('status', 1)
                                                ->orderBy('live_start_datetime', 'asc')
                                                ->first();

                            if ($dataAllCourses[$i]->topics == null) {
                                $dataAllCourses[$i]->topics = $dataAllCourses[$i]->topics()
                                                    ->select('id', 'title', 'live_start_datetime', 'live_end_datetime', 'state', 'streaming_status', 'status')
                                                    ->where('state', 'live')
                                                    ->where('streaming_status', 0)
                                                    ->where('live_start_datetime', '>', date('Y-m-d H:i:s'))
                                                    ->where('status', 1)
                                                    ->orderBy('live_start_datetime', 'asc')
                                                    ->first();
                            }

                            if ($dataAllCourses[$i]->topics) {
                                $dataAllCourses[$i]->topics->live_datetime = $oFunc->thai_date_fullmonth(strtotime($dataAllCourses[$i]->topics->live_start_datetime)).' เวลา '.date('H:i', strtotime($dataAllCourses[$i]->topics->live_start_datetime)).' - '.date('H:i', strtotime($dataAllCourses[$i]->topics->live_end_datetime)).' น.';
                            }
                        }
                    }

                    return response()->json($dataAllCourses, 200);

                }else{

                    $groups = new Groups;
                    $groups = $groups->where('key', $groupsKey)->first();
                    $data = $groups->courses();
                    $data = $data->where('level_public', 1);
                    $data = $data->where('status', '1')->orderBy($order_by,$order_direction)->paginate($per_page);
                    for($i=0; $i<count($data); $i++) {
                        if($data[$i]->latest_end_datetime >= date('Y-m-d')){
                            $data[$i]->latest = 1;
                        }else{
                            $data[$i]->latest = 0;
                        }
                        $data[$i]->categories = $data[$i]->categories()->where('groups_id', $groups->id)->first();
                        if ($ignore_state == 'live') {
                            $data[$i]->topics = null;
                        } else {
                            $data[$i]->topics = $data[$i]->topics()
                                                ->select('id', 'title', 'state', 'live_start_datetime', 'streaming_status', 'status')
                                                ->where('state', 'live')
                                                ->where('streaming_status', 1)
                                                ->where('status', 1)
                                                ->orderBy('live_start_datetime', 'asc')
                                                ->first();

                            if ($data[$i]->topics == null) {
                                $data[$i]->topics = $data[$i]->topics()
                                                    ->select('id', 'title', 'live_start_datetime', 'live_end_datetime', 'state', 'streaming_status', 'status')
                                                    ->where('state', 'live')
                                                    ->where('streaming_status', 0)
                                                    ->where('live_start_datetime', '>', date('Y-m-d H:i:s'))
                                                    ->where('status', 1)
                                                    ->orderBy('live_start_datetime', 'asc')
                                                    ->first();
                            }

                            if ($data[$i]->topics) {
                                $data[$i]->topics->live_datetime = $oFunc->thai_date_fullmonth(strtotime($data[$i]->topics->live_start_datetime)).' เวลา '.date('H:i', strtotime($data[$i]->topics->live_start_datetime)).' - '.date('H:i', strtotime($data[$i]->topics->live_end_datetime)).' น.';
                            }
                        }
                    }

                    return response()->json($data, 200);
                }

            } else {

                $groups = new Groups;
                $groups = $groups->where('key', $groupsKey)->first();
                $data = $groups->courses();
                $data = $data->where('level_public', 1);
                $data = $data->where('status', '1')->orderBy($order_by,$order_direction)->paginate($per_page);
                for($i=0; $i<count($data); $i++) {
                    if($data[$i]->latest_end_datetime >= date('Y-m-d')){
                        $data[$i]->latest = 1;
                    } else {
                        $data[$i]->latest = 0;
                    }
                    $data[$i]->categories = $data[$i]->categories()->where('groups_id', $groups->id)->first();

                    if ($ignore_state == 'live') {
                        $data[$i]->topics = null;
                    } else {
                        $data[$i]->topics = $data[$i]->topics()
                                            ->select('id', 'title', 'state', 'live_start_datetime', 'streaming_status', 'status')
                                            ->where('state', 'live')
                                            ->where('streaming_status', 1)
                                            ->where('status', 1)
                                            ->orderBy('live_start_datetime', 'asc')
                                            ->first();

                        if ($data[$i]->topics == null) {
                            $data[$i]->topics = $data[$i]->topics()
                                                ->select('id', 'title', 'live_start_datetime', 'live_end_datetime', 'state', 'streaming_status', 'status')
                                                ->where('state', 'live')
                                                ->where('streaming_status', 0)
                                                ->where('live_start_datetime', '>', date('Y-m-d H:i:s'))
                                                ->where('status', 1)
                                                ->orderBy('live_start_datetime', 'asc')
                                                ->first();
                        }

                        if ($data[$i]->topics) {
                            $data[$i]->topics->live_datetime = $oFunc->thai_date_fullmonth(strtotime($data[$i]->topics->live_start_datetime)).' เวลา '.date('H:i', strtotime($data[$i]->topics->live_start_datetime)).' - '.date('H:i', strtotime($data[$i]->topics->live_end_datetime)).' น.';
                        }
                    }
                }

                return response()->json($data, 200);
            }

        } else {
            $groups = new Groups;
            $groups = $groups->where('key', $groupsKey)->first();

            $data = $groups->courses();

            $data = $data->where('level_public', 1);
            $data = $data->where('status', '1');

            $data = $data->orderBy($order_by, $order_direction)->paginate($per_page);

            for ($i=0; $i<count($data); $i++) {
                if($data[$i]->latest_end_datetime >= date('Y-m-d')){
                    $data[$i]->latest = 1;
                }else{
                    $data[$i]->latest = 0;
                }
                $data[$i]->categories = $data[$i]->categories()->where('groups_id', $groups->id)->first();

                if ($ignore_state == 'live') {
                    $data[$i]->topics = null;
                } else {
                    $data[$i]->topics = $data[$i]->topics()
                                        ->select('id', 'title', 'state', 'live_start_datetime', 'streaming_status', 'status')
                                        ->where('state', 'live')
                                        ->where('streaming_status', 1)
                                        ->where('status', 1)
                                        ->orderBy('live_start_datetime', 'asc')
                                        ->first();

                    if ($data[$i]->topics == null) {
                        $data[$i]->topics = $data[$i]->topics()
                                            ->select('id', 'title', 'live_start_datetime', 'live_end_datetime', 'state', 'streaming_status', 'status')
                                            ->where('state', 'live')
                                            ->where('streaming_status', 0)
                                            ->where('live_start_datetime', '>', date('Y-m-d H:i:s'))
                                            ->where('status', 1)
                                            ->orderBy('live_start_datetime', 'asc')
                                            ->first();
                    }

                    if ($data[$i]->topics) {
                        $data[$i]->topics->live_datetime = $oFunc->thai_date_fullmonth(strtotime($data[$i]->topics->live_start_datetime)).' เวลา '.date('H:i', strtotime($data[$i]->topics->live_start_datetime)).' - '.date('H:i', strtotime($data[$i]->topics->live_end_datetime)).' น.';
                    }
                }
            }

            return response()->json($data, 200);
        }
    }

    public function live_courses_list($groupsKey, Request $request, _FunctionsController $oFunc)
    {
        $per_page = $request->input('per_page', 4);
        $order_by = $request->input('order_by', 'order');
        $order_direction = $request->input('order_direction', 'asc');

        $_user = session()->get('_user');

        if ($_user) {
            $_user->groups = $_user->groups()->first();
            if($groupsKey == $_user->groups->key){
                if($_user->level_groups){

                    $groups = new Groups;
                    $groups = $groups->where('key', $groupsKey)->first();
                    $allCoursesIds = array();

                    // BEGIN COURSES (LEVEL PUBLIC)
                    $data = $groups->courses();
                    $data = $data->where('level_public', 1);

                    $allCoursesIds = array_merge($allCoursesIds, $data->where('status', '1')->select('courses.id')->get()->pluck('id')->toArray());
                    // END COURSES (LEVEL PUBLIC)

                    // BEGIN COURSES (CLASSROOM TARGET)
                    $dataMembers = new Members;
                    $dataMembers = $dataMembers->find($_user->id);
                    $classrooms_target = $dataMembers->classrooms()->whereDate('start_datetime', '<=', date('Y-m-d H:i:s'))->whereDate('end_datetime', '>=', date('Y-m-d H:i:s'))->get();
                    for($i=0; $i<count($classrooms_target); $i++) {
                        $dataCoursesClassroomsTargets = $classrooms_target[$i]->courses();
                        if($groups->use_sub_groups_single){
                            $dataCoursesClassroomsTargets = $dataCoursesClassroomsTargets->whereHas('classrooms.sub_groups', function($query) use ($dataMembers) {
                                $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                            });
                        }

                        $classrooms_target[$i]->courses = $dataCoursesClassroomsTargets->where('status', '1')->select('courses.id')->get();
                        $allCoursesIds = array_merge($allCoursesIds, $classrooms_target[$i]->courses->pluck('id')->toArray());
                    }
                    // END COURSES (CLASSROOM TARGET)

                    // BEGIN COURSES (CLASSROOM LEVEL GROUP)
                    $dataMembers = new Members;
                    $dataMembers = $dataMembers->find($_user->id);
                    $classrooms_level_group = $dataMembers->level_groups()->get();
                    for($l=0; $l<count($classrooms_level_group); $l++) {
                        $classrooms_level_group[$l]->classroom = $classrooms_level_group[$l]->classrooms()->whereDate('start_datetime', '<=', date('Y-m-d H:i:s'))->whereDate('end_datetime', '>=', date('Y-m-d H:i:s'))->get();
                        for($i=0; $i<count($classrooms_level_group[$l]->classroom); $i++) {
                            $dataCoursesClassroomsLevelGroups = $classrooms_level_group[$l]->classroom[$i]->courses();

                            if($groups->use_sub_groups_single){
                                $dataCoursesClassroomsLevelGroups = $dataCoursesClassroomsLevelGroups->whereHas('classrooms.sub_groups', function($query) use ($dataMembers) {
                                    $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                                });
                            }

                            $classrooms_level_group[$l]->classroom[$i]->courses = $dataCoursesClassroomsLevelGroups->where('status', '1')->select('courses.id')->get();
                            $allCoursesIds = array_merge($allCoursesIds, $classrooms_level_group[$l]->classroom[$i]->courses->pluck('id')->toArray());
                        }
                    }
                    // END COURSES (CLASSROOM LEVEL GROUP)

                    // BEGIN COURSES (TARGET)
                    $dataMembers = new Members;
                    $dataMembers = $dataMembers->find($_user->id);

                    $dataCoursesTargets = $dataMembers->courses();

                    if($groups->use_sub_groups_single){
                        $dataCoursesTargets = $dataCoursesTargets->whereHas('sub_groups', function($query) use ($dataMembers) {
                            $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                        });
                    }

                    $courses_target = $dataCoursesTargets->where('status', '1')->select('courses.id')->get();
                    $allCoursesIds = array_merge($allCoursesIds, $courses_target->pluck('id')->toArray());
                    // END COURSES (TARGET)

                    // BEGIN COURSES (LEVEL GROUPS)
                    $dataMembers = new Members;
                    $dataMembers = $dataMembers->find($_user->id);
                    $courses_level_groups = $dataMembers->level_groups()->get();
                    for($l=0; $l<count($courses_level_groups); $l++) {
                        $dataCoursesLevelGroups = $courses_level_groups[$l]->courses();

                        if($groups->use_sub_groups_single){
                            $dataCoursesLevelGroups = $dataCoursesLevelGroups->whereHas('sub_groups', function($query) use ($dataMembers) {
                                $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                            });
                        }

                        $courses_level_groups[$l] = $dataCoursesLevelGroups->where('status', '1')->select('courses.id')->get();
                        $allCoursesIds = array_merge($allCoursesIds, $courses_level_groups[$l]->pluck('id')->toArray());
                    }
                    // END COURSES (LEVEL GROUPS)

                    // All Courses
                    // print_r($allCoursesIds);
                    // exit;
                    $dataAllCoursesLive = new Courses;
                    $dataAllCoursesLive = $dataAllCoursesLive->select('courses.*');
                    $dataAllCoursesLive = $dataAllCoursesLive->leftJoin('topics', 'courses.id', '=', 'topics.courses_id');
                    $dataAllCoursesLive = $dataAllCoursesLive->whereIn('courses.id', array_unique($allCoursesIds));
                    $dataAllCoursesLive = $dataAllCoursesLive->where('courses.level_public', 1);
                    $dataAllCoursesLive = $dataAllCoursesLive->where('courses.status', '1');
                    $dataAllCoursesLive = $dataAllCoursesLive->where('topics.state', 'live');
                    $dataAllCoursesLive = $dataAllCoursesLive->where('topics.streaming_status', 1);
                    $dataAllCoursesLive = $dataAllCoursesLive->where('topics.status', 1);
                    $dataAllCoursesLive = $dataAllCoursesLive->orderByRaw(
                        "topics.streaming_status DESC,
                        CASE WHEN topics.streaming_status = 1 THEN topics.state END ASC,
                        CASE WHEN topics.streaming_status != 1 THEN topics.state END DESC,
                        CASE WHEN topics.state = 'live' AND topics.streaming_status = 1 THEN topics.live_start_datetime END DESC,
                        CASE WHEN topics.state = 'live' AND topics.streaming_status != 1 THEN topics.live_start_datetime END ASC,
                        CASE WHEN topics.state = 'vod' AND topics.streaming_status = 1 THEN topics.live_start_datetime END DESC,
                        CASE WHEN topics.state = 'vod' AND topics.streaming_status != 1 THEN topics.live_start_datetime END ASC"
                    );

                    $dataAllCoursesLive = $dataAllCoursesLive->distinct()->take($per_page)->get();

                    $count_live = count($dataAllCoursesLive);

                    $courseWhereNotIn = array();
                    for($i=0; $i<count($dataAllCoursesLive); $i++) {
                        if($dataAllCoursesLive[$i]->latest_end_datetime >= date('Y-m-d')){
                            $dataAllCoursesLive[$i]->latest = 1;
                        }else{
                            $dataAllCoursesLive[$i]->latest = 0;
                        }
                        $dataAllCoursesLive[$i]->categories = $dataAllCoursesLive[$i]->categories()->where('groups_id', $groups->id)->first();
                        $dataAllCoursesLive[$i]->topics = $dataAllCoursesLive[$i]->topics()
                                            ->select('id', 'title', 'state', 'live_start_datetime', 'streaming_status', 'status')
                                            ->where('state', 'live')
                                            ->where('streaming_status', 1)
                                            ->where('status', 1)
                                            ->orderBy('live_start_datetime', 'asc')
                                            ->first();
                        array_push($courseWhereNotIn, $dataAllCoursesLive[$i]->id);
                    }

                    $take_upcoming = $per_page - $count_live;

                    if ($take_upcoming > 0) {
                        $dataAllCoursesUpcoming = new Courses;
                        $dataAllCoursesUpcoming = $dataAllCoursesUpcoming->select('courses.*');
                        $dataAllCoursesUpcoming = $dataAllCoursesUpcoming->leftJoin('topics', 'courses.id', '=', 'topics.courses_id');
                        $dataAllCoursesUpcoming = $dataAllCoursesUpcoming->whereIn('courses.id', array_unique($allCoursesIds));
                        $dataAllCoursesUpcoming = $dataAllCoursesUpcoming->where('courses.level_public', 1);
                        $dataAllCoursesUpcoming = $dataAllCoursesUpcoming->whereNotIn('courses.id', $courseWhereNotIn);
                        $dataAllCoursesUpcoming = $dataAllCoursesUpcoming->where('courses.status', '1');
                        $dataAllCoursesUpcoming = $dataAllCoursesUpcoming->where('topics.state', 'live');
                        $dataAllCoursesUpcoming = $dataAllCoursesUpcoming->where('topics.live_start_datetime', '>', date('Y-m-d H:i:s'));
                        $dataAllCoursesUpcoming = $dataAllCoursesUpcoming->where('topics.status', 1);
                        $dataAllCoursesUpcoming = $dataAllCoursesUpcoming->orderByRaw(
                            "topics.streaming_status DESC,
                            CASE WHEN topics.streaming_status = 1 THEN topics.state END ASC,
                            CASE WHEN topics.streaming_status != 1 THEN topics.state END DESC,
                            CASE WHEN topics.state = 'live' AND topics.streaming_status = 1 THEN topics.live_start_datetime END DESC,
                            CASE WHEN topics.state = 'live' AND topics.streaming_status != 1 THEN topics.live_start_datetime END ASC,
                            CASE WHEN topics.state = 'vod' AND topics.streaming_status = 1 THEN topics.live_start_datetime END DESC,
                            CASE WHEN topics.state = 'vod' AND topics.streaming_status != 1 THEN topics.live_start_datetime END ASC"
                        );

                        $dataAllCoursesUpcoming = $dataAllCoursesUpcoming->distinct()->take($take_upcoming)->get();
                        for($i=0; $i<count($dataAllCoursesUpcoming); $i++) {
                            if($dataAllCoursesUpcoming[$i]->latest_end_datetime >= date('Y-m-d')){
                                $dataAllCoursesUpcoming[$i]->latest = 1;
                            }else{
                                $dataAllCoursesUpcoming[$i]->latest = 0;
                            }
                            $dataAllCoursesUpcoming[$i]->categories = $dataAllCoursesUpcoming[$i]->categories()->where('groups_id', $groups->id)->first();
                            $dataAllCoursesUpcoming[$i]->topics = $dataAllCoursesUpcoming[$i]->topics()
                                                ->select('id', 'title', 'live_start_datetime', 'live_end_datetime', 'state', 'streaming_status', 'status')
                                                ->where('state', 'live')
                                                ->where('streaming_status', 0)
                                                ->where('live_start_datetime', '>', date('Y-m-d H:i:s'))
                                                ->where('status', 1)
                                                ->orderBy('live_start_datetime', 'asc')
                                                ->first();

                            if ($dataAllCoursesUpcoming[$i]->topics) {
                                $dataAllCoursesUpcoming[$i]->topics->start_date = $oFunc->thai_date_fullmonth(strtotime($dataAllCoursesUpcoming[$i]->topics->live_start_datetime));
                                $dataAllCoursesUpcoming[$i]->topics->start_time = Carbon::parse($dataAllCoursesUpcoming[$i]->topics->live_start_datetime)->format('H:i');

                                $dataAllCoursesUpcoming[$i]->topics->end_date = $oFunc->thai_date_fullmonth(strtotime($dataAllCoursesUpcoming[$i]->topics->live_end_datetime));
                                $dataAllCoursesUpcoming[$i]->topics->end_time = Carbon::parse($dataAllCoursesUpcoming[$i]->topics->live_end_datetime)->format('H:i');
                                $dataAllCoursesUpcoming[$i]->topics->live_datetime = $dataAllCoursesUpcoming[$i]->topics->start_date.' เวลา '.$dataAllCoursesUpcoming[$i]->topics->start_time.' น. ถึง '.($dataAllCoursesUpcoming[$i]->topics->start_date != $dataAllCoursesUpcoming[$i]->topics->end_date ? $dataAllCoursesUpcoming[$i]->topics->end_date.' เวลา ' : '').$dataAllCoursesUpcoming[$i]->topics->end_time.' น.';
                                // $dataAllCoursesUpcoming[$i]->topics->live_datetime = $oFunc->thai_date_fullmonth(strtotime($dataAllCoursesUpcoming[$i]->topics->live_start_datetime)).' เวลา '.date('H:i', strtotime($dataAllCoursesUpcoming[$i]->topics->live_start_datetime)).' - '.date('H:i', strtotime($dataAllCoursesUpcoming[$i]->topics->live_end_datetime)).' น.';
                            }
                        }
                    }

                    return response()->json(array('data_live' => $dataAllCoursesLive, 'data' => $dataAllCoursesUpcoming), 200);

                } else {

                    $groups = new Groups;
                    $groups = $groups->where('key', $groupsKey)->first();
                    $dataLive = $groups->courses();

                    $dataLive = $dataLive->leftJoin('topics', 'courses.id', '=', 'topics.courses_id');

                    $dataLive = $dataLive->where('courses.level_public', 1);
                    $dataLive = $dataLive->where('courses.status', '1');
                    $dataLive = $dataLive->where('topics.state', 'live');
                    $dataLive = $dataLive->where('topics.status', 1);
                    $dataLive = $dataLive->where('topics.streaming_status', 1);

                    $dataLive = $dataLive->orderByRaw(
                        "topics.streaming_status DESC,
                        CASE WHEN topics.streaming_status = 1 THEN topics.state END ASC,
                        CASE WHEN topics.streaming_status != 1 THEN topics.state END DESC,
                        CASE WHEN topics.state = 'live' AND topics.streaming_status = 1 THEN topics.live_start_datetime END DESC,
                        CASE WHEN topics.state = 'live' AND topics.streaming_status != 1 THEN topics.live_start_datetime END ASC,
                        CASE WHEN topics.state = 'vod' AND topics.streaming_status = 1 THEN topics.live_start_datetime END DESC,
                        CASE WHEN topics.state = 'vod' AND topics.streaming_status != 1 THEN topics.live_start_datetime END ASC"
                    );

                    $dataLive = $dataLive->distinct()->take($per_page)->get();

                    $courseWhereNotIn = array();
                    for ($i=0; $i<count($dataLive); $i++) {
                        if($dataLive[$i]->latest_end_datetime >= date('Y-m-d')){
                            $dataLive[$i]->latest = 1;
                        }else{
                            $dataLive[$i]->latest = 0;
                        }
                        $dataLive[$i]->categories = $dataLive[$i]->categories()->where('groups_id', $groups->id)->first();
                        $dataLive[$i]->topics = $dataLive[$i]->topics()
                                            ->select('id', 'title', 'state', 'live_start_datetime', 'streaming_status', 'status')
                                            ->where('state', 'live')
                                            ->where('streaming_status', 1)
                                            ->where('status', 1)
                                            ->orderBy('live_start_datetime', 'asc')
                                            ->first();
                        array_push($courseWhereNotIn, $dataLive[$i]->id);
                    }

                    $count_live = count($dataLive);
                    $take_upcoming = $per_page - $count_live;

                    if ($take_upcoming > 0) {
                        $dataUpcoming = $groups->courses();

                        $dataUpcoming = $dataUpcoming->leftJoin('topics', 'courses.id', '=', 'topics.courses_id');

                        $dataUpcoming = $dataUpcoming->where('courses.level_public', 1);
                        $dataUpcoming = $dataUpcoming->whereNotIn('courses.id', $courseWhereNotIn);
                        $dataUpcoming = $dataUpcoming->where('courses.status', 1);
                        $dataUpcoming = $dataUpcoming->where('topics.state', 'live');
                        $dataUpcoming = $dataUpcoming->where('topics.status', 1);
                        $dataUpcoming = $dataUpcoming->where('topics.live_start_datetime', '>', date('Y-m-d H:i:s'));

                        $dataUpcoming = $dataUpcoming->orderByRaw(
                            "topics.streaming_status DESC,
                            CASE WHEN topics.streaming_status = 1 THEN topics.state END ASC,
                            CASE WHEN topics.streaming_status != 1 THEN topics.state END DESC,
                            CASE WHEN topics.state = 'live' AND topics.streaming_status = 1 THEN topics.live_start_datetime END DESC,
                            CASE WHEN topics.state = 'live' AND topics.streaming_status != 1 THEN topics.live_start_datetime END ASC,
                            CASE WHEN topics.state = 'vod' AND topics.streaming_status = 1 THEN topics.live_start_datetime END DESC,
                            CASE WHEN topics.state = 'vod' AND topics.streaming_status != 1 THEN topics.live_start_datetime END ASC"
                        );

                        $dataUpcoming = $dataUpcoming->distinct()->take($take_upcoming)->get();

                        for ($i=0; $i<count($dataUpcoming); $i++) {
                            if($dataUpcoming[$i]->latest_end_datetime >= date('Y-m-d')){
                                $dataUpcoming[$i]->latest = 1;
                            }else{
                                $dataUpcoming[$i]->latest = 0;
                            }
                            $dataUpcoming[$i]->categories = $dataUpcoming[$i]->categories()->where('groups_id', $groups->id)->first();

                            $dataUpcoming[$i]->topics = $dataUpcoming[$i]->topics()
                                                ->select('id', 'title', 'live_start_datetime', 'live_end_datetime', 'state', 'streaming_status', 'status')
                                                ->where('state', 'live')
                                                ->where('streaming_status', 0)
                                                ->where('live_start_datetime', '>', date('Y-m-d H:i:s'))
                                                ->where('status', 1)
                                                ->orderBy('live_start_datetime', 'asc')
                                                ->first();

                            if ($dataUpcoming[$i]->topics) {
                                $dataUpcoming[$i]->topics->start_date = $oFunc->thai_date_fullmonth(strtotime($dataUpcoming[$i]->topics->live_start_datetime));
                                $dataUpcoming[$i]->topics->start_time = Carbon::parse($dataUpcoming[$i]->topics->live_start_datetime)->format('H:i');

                                $dataUpcoming[$i]->topics->end_date = $oFunc->thai_date_fullmonth(strtotime($dataUpcoming[$i]->topics->live_end_datetime));
                                $dataUpcoming[$i]->topics->end_time = Carbon::parse($dataUpcoming[$i]->topics->live_end_datetime)->format('H:i');
                                $dataUpcoming[$i]->topics->live_datetime = $dataUpcoming[$i]->topics->start_date.' เวลา '.$dataUpcoming[$i]->topics->start_time.' น. ถึง '.($dataUpcoming[$i]->topics->start_date != $dataUpcoming[$i]->topics->end_date ? $dataUpcoming[$i]->topics->end_date.' เวลา ' : '').$dataUpcoming[$i]->topics->end_time.' น.';
                                // $dataUpcoming[$i]->topics->live_datetime = $oFunc->thai_date_fullmonth(strtotime($dataUpcoming[$i]->topics->live_start_datetime)).' เวลา '.date('H:i', strtotime($dataUpcoming[$i]->topics->live_start_datetime)).' - '.date('H:i', strtotime($dataUpcoming[$i]->topics->live_end_datetime)).' น.';
                            }
                        }
                    }

                    return response()->json(array('data_live' => $dataLive, 'data' => $dataUpcoming), 200);
                }

            }else{

                $groups = new Groups;
                $groups = $groups->where('key', $groupsKey)->first();
                $dataLive = $groups->courses();

                $dataLive = $dataLive->leftJoin('topics', 'courses.id', '=', 'topics.courses_id');

                $dataLive = $dataLive->where('courses.level_public', 1);
                $dataLive = $dataLive->where('courses.status', '1');
                $dataLive = $dataLive->where('topics.state', 'live');
                $dataLive = $dataLive->where('topics.status', 1);
                $dataLive = $dataLive->where('topics.streaming_status', 1);


                $dataLive = $dataLive->orderByRaw(
                    "topics.streaming_status DESC,
                    CASE WHEN topics.streaming_status = 1 THEN topics.state END ASC,
                    CASE WHEN topics.streaming_status != 1 THEN topics.state END DESC,
                    CASE WHEN topics.state = 'live' AND topics.streaming_status = 1 THEN topics.live_start_datetime END DESC,
                    CASE WHEN topics.state = 'live' AND topics.streaming_status != 1 THEN topics.live_start_datetime END ASC,
                    CASE WHEN topics.state = 'vod' AND topics.streaming_status = 1 THEN topics.live_start_datetime END DESC,
                    CASE WHEN topics.state = 'vod' AND topics.streaming_status != 1 THEN topics.live_start_datetime END ASC"
                );

                $dataLive = $dataLive->distinct()->take($per_page)->get();

                $courseWhereNotIn = array();
                for ($i=0; $i<count($dataLive); $i++) {
                    if($dataLive[$i]->latest_end_datetime >= date('Y-m-d')){
                        $dataLive[$i]->latest = 1;
                    }else{
                        $dataLive[$i]->latest = 0;
                    }
                    $dataLive[$i]->categories = $dataLive[$i]->categories()->where('groups_id', $groups->id)->first();
                    $dataLive[$i]->topics = $dataLive[$i]->topics()
                                        ->select('id', 'title', 'state', 'live_start_datetime', 'streaming_status', 'status')
                                        ->where('state', 'live')
                                        ->where('streaming_status', 1)
                                        ->where('status', 1)
                                        ->orderBy('live_start_datetime', 'asc')
                                        ->first();
                    array_push($courseWhereNotIn, $dataLive[$i]->id);
                }

                $count_live = count($dataLive);
                $take_upcoming = $per_page - $count_live;

                if ($take_upcoming > 0) {
                    $dataUpcoming = $groups->courses();

                    $dataUpcoming = $dataUpcoming->leftJoin('topics', 'courses.id', '=', 'topics.courses_id');

                    $dataUpcoming = $dataUpcoming->where('courses.level_public', 1);
                    $dataUpcoming = $dataUpcoming->whereNotIn('courses.id', $courseWhereNotIn);
                    $dataUpcoming = $dataUpcoming->where('courses.status', '1');
                    $dataUpcoming = $dataUpcoming->where('topics.state', 'live');
                    $dataUpcoming = $dataUpcoming->where('topics.live_start_datetime', '>', date('Y-m-d H:i:s'));

                    $dataUpcoming = $dataUpcoming->orderByRaw(
                        "topics.streaming_status DESC,
                        CASE WHEN topics.streaming_status = 1 THEN topics.state END ASC,
                        CASE WHEN topics.streaming_status != 1 THEN topics.state END DESC,
                        CASE WHEN topics.state = 'live' AND topics.streaming_status = 1 THEN topics.live_start_datetime END DESC,
                        CASE WHEN topics.state = 'live' AND topics.streaming_status != 1 THEN topics.live_start_datetime END ASC,
                        CASE WHEN topics.state = 'vod' AND topics.streaming_status = 1 THEN topics.live_start_datetime END DESC,
                        CASE WHEN topics.state = 'vod' AND topics.streaming_status != 1 THEN topics.live_start_datetime END ASC"
                    );

                    $dataUpcoming = $dataUpcoming->distinct()->take($take_upcoming)->get();

                    for ($i=0; $i<count($dataUpcoming); $i++) {
                        if($dataUpcoming[$i]->latest_end_datetime >= date('Y-m-d')){
                            $dataUpcoming[$i]->latest = 1;
                        }else{
                            $dataUpcoming[$i]->latest = 0;
                        }
                        $dataUpcoming[$i]->categories = $dataUpcoming[$i]->categories()->where('groups_id', $groups->id)->first();
                        $dataUpcoming[$i]->topics = $dataUpcoming[$i]->topics()
                                            ->select('id', 'title', 'live_start_datetime', 'live_end_datetime', 'state', 'streaming_status', 'status')
                                            ->where('state', 'live')
                                            ->where('streaming_status', 0)
                                            ->where('live_start_datetime', '>', date('Y-m-d H:i:s'))
                                            ->where('status', 1)
                                            ->orderBy('live_start_datetime', 'asc')
                                            ->first();

                        if ($dataUpcoming[$i]->topics) {
                            $dataUpcoming[$i]->topics->start_date = $oFunc->thai_date_fullmonth(strtotime($dataUpcoming[$i]->topics->live_start_datetime));
                            $dataUpcoming[$i]->topics->start_time = Carbon::parse($dataUpcoming[$i]->topics->live_start_datetime)->format('H:i');

                            $dataUpcoming[$i]->topics->end_date = $oFunc->thai_date_fullmonth(strtotime($dataUpcoming[$i]->topics->live_end_datetime));
                            $dataUpcoming[$i]->topics->end_time = Carbon::parse($dataUpcoming[$i]->topics->live_end_datetime)->format('H:i');
                            $dataUpcoming[$i]->topics->live_datetime = $dataUpcoming[$i]->topics->start_date.' เวลา '.$dataUpcoming[$i]->topics->start_time.' น. ถึง '.($dataUpcoming[$i]->topics->start_date != $dataUpcoming[$i]->topics->end_date ? $dataUpcoming[$i]->topics->end_date.' เวลา ' : '').$dataUpcoming[$i]->topics->end_time.' น.';
                            // $dataUpcoming[$i]->topics->live_datetime = $oFunc->thai_date_fullmonth(strtotime($dataUpcoming[$i]->topics->live_start_datetime)).' เวลา '.date('H:i', strtotime($dataUpcoming[$i]->topics->live_start_datetime)).' - '.date('H:i', strtotime($dataUpcoming[$i]->topics->live_end_datetime)).' น.';
                        }
                    }
                }

                return response()->json(array('data_live' => $dataLive, 'data' => $dataUpcoming), 200);
            }

        } else {

            $groups = new Groups;
            $groups = $groups->where('key', $groupsKey)->first();
            $dataLive = $groups->courses();

            $dataLive = $dataLive->leftJoin('topics', 'courses.id', '=', 'topics.courses_id');

            $dataLive = $dataLive->where('courses.level_public', 1);
            $dataLive = $dataLive->where('courses.status', '1');
            $dataLive = $dataLive->where('topics.state', 'live');
            $dataLive = $dataLive->where('topics.streaming_status', 1);

            $dataLive = $dataLive->orderByRaw(
                "topics.streaming_status DESC,
                CASE WHEN topics.streaming_status = 1 THEN topics.state END ASC,
                CASE WHEN topics.streaming_status != 1 THEN topics.state END DESC,
                CASE WHEN topics.state = 'live' AND topics.streaming_status = 1 THEN topics.live_start_datetime END DESC,
                CASE WHEN topics.state = 'live' AND topics.streaming_status != 1 THEN topics.live_start_datetime END ASC,
                CASE WHEN topics.state = 'vod' AND topics.streaming_status = 1 THEN topics.live_start_datetime END DESC,
                CASE WHEN topics.state = 'vod' AND topics.streaming_status != 1 THEN topics.live_start_datetime END ASC"
            );

            $dataLive = $dataLive->distinct()->take($per_page)->get();

            $courseWhereNotIn = array();
            for ($i=0; $i<count($dataLive); $i++) {
                if($dataLive[$i]->latest_end_datetime >= date('Y-m-d')){
                    $dataLive[$i]->latest = 1;
                }else{
                    $dataLive[$i]->latest = 0;
                }
                $dataLive[$i]->categories = $dataLive[$i]->categories()->where('groups_id', $groups->id)->first();
                $dataLive[$i]->topics = $dataLive[$i]->topics()
                                    ->select('id', 'title', 'state', 'live_start_datetime', 'streaming_status', 'status')
                                    ->where('state', 'live')
                                    ->where('streaming_status', 1)
                                    ->where('status', 1)
                                    ->orderBy('live_start_datetime', 'asc')
                                    ->first();
                array_push($courseWhereNotIn, $dataLive[$i]->id);
            }

            $count_live = count($dataLive);
            $take_upcoming = $per_page - $count_live;

            if ($take_upcoming > 0) {
                $groups = new Groups;
                $groups = $groups->where('key', $groupsKey)->first();
                $dataUpcoming = $groups->courses();

                $dataUpcoming = $dataUpcoming->leftJoin('topics', 'courses.id', '=', 'topics.courses_id');

                $dataUpcoming = $dataUpcoming->where('courses.level_public', 1);
                $dataUpcoming = $dataUpcoming->whereNotIn('courses.id', $courseWhereNotIn);
                $dataUpcoming = $dataUpcoming->where('courses.status', 1);
                $dataUpcoming = $dataUpcoming->where('topics.state', 'live');
                $dataUpcoming = $dataUpcoming->where('topics.status', 1);
                $dataUpcoming = $dataUpcoming->where('topics.live_start_datetime', '>', date('Y-m-d H:i:s'));

                $dataUpcoming = $dataUpcoming->orderByRaw(
                    "topics.streaming_status DESC,
                    CASE WHEN topics.streaming_status = 1 THEN topics.state END ASC,
                    CASE WHEN topics.streaming_status != 1 THEN topics.state END DESC,
                    CASE WHEN topics.state = 'live' AND topics.streaming_status = 1 THEN topics.live_start_datetime END DESC,
                    CASE WHEN topics.state = 'live' AND topics.streaming_status != 1 THEN topics.live_start_datetime END ASC,
                    CASE WHEN topics.state = 'vod' AND topics.streaming_status = 1 THEN topics.live_start_datetime END DESC,
                    CASE WHEN topics.state = 'vod' AND topics.streaming_status != 1 THEN topics.live_start_datetime END ASC"
                );

                $dataUpcoming = $dataUpcoming->distinct()->take($take_upcoming)->get();

                for ($i=0; $i<count($dataUpcoming); $i++) {
                    if($dataUpcoming[$i]->latest_end_datetime >= date('Y-m-d')){
                        $dataUpcoming[$i]->latest = 1;
                    }else{
                        $dataUpcoming[$i]->latest = 0;
                    }
                    $dataUpcoming[$i]->categories = $dataUpcoming[$i]->categories()->where('groups_id', $groups->id)->first();

                    $dataUpcoming[$i]->topics = $dataUpcoming[$i]->topics()
                                        ->select('id', 'title', 'live_start_datetime', 'live_end_datetime', 'state', 'streaming_status', 'status')
                                        ->where('state', 'live')
                                        ->where('streaming_status', 0)
                                        ->where('live_start_datetime', '>', date('Y-m-d H:i:s'))
                                        ->where('status', 1)
                                        ->orderBy('live_start_datetime', 'asc')
                                        ->first();

                    if ($dataUpcoming[$i]->topics) {
                        $dataUpcoming[$i]->topics->start_date = $oFunc->thai_date_fullmonth(strtotime($dataUpcoming[$i]->topics->live_start_datetime));
                        $dataUpcoming[$i]->topics->start_time = Carbon::parse($dataUpcoming[$i]->topics->live_start_datetime)->format('H:i');

                        $dataUpcoming[$i]->topics->end_date = $oFunc->thai_date_fullmonth(strtotime($dataUpcoming[$i]->topics->live_end_datetime));
                        $dataUpcoming[$i]->topics->end_time = Carbon::parse($dataUpcoming[$i]->topics->live_end_datetime)->format('H:i');
                        $dataUpcoming[$i]->topics->live_datetime = $dataUpcoming[$i]->topics->start_date.' เวลา '.$dataUpcoming[$i]->topics->start_time.' น. ถึง '.($dataUpcoming[$i]->topics->start_date != $dataUpcoming[$i]->topics->end_date ? $dataUpcoming[$i]->topics->end_date.' เวลา ' : '').$dataUpcoming[$i]->topics->end_time.' น.';
                    }
                }
            }

            return response()->json(array('data_live' => $dataLive, 'data' => $dataUpcoming), 200);
        }

    }

    public function searchCourses($groupsKey, $keyword = null, Request $request, _FunctionsController $oFunc)
    {
        $per_page = $request->input('per_page', 120);
        $order_by = $request->input('order_by', 'order');
        $order_direction = $request->input('order_direction', 'asc');

        $_user = session()->get('_user');

        if (empty($keyword) || $oFunc->utf8_strlen($keyword) < 3 || $keyword == "<p>") {
            return response()->json([], 200);
        }

        // $keyword = preg_replace("/[^ \w]+/", "", $keyword);

        if($_user){
            $_user->groups = $_user->groups()->first();
            if($groupsKey == $_user->groups->key){
                if($_user->level_groups){

                    $groups = new Groups;
                    $groups = $groups->where('key', $groupsKey)->first();
                    $allCoursesIds = array();

                    // BEGIN COURSES (LEVEL PUBLIC)
                    $data = $groups->courses();
                    $data = $data->where('level_public', 1);

                    $data = $data->where(function($query) use ($keyword) {
                        $query->where('title', 'like', '%'.$keyword.'%')
                              ->orWhere('code', 'like', '%'.$keyword.'%')
                              ->orWhere('information', 'like', '%'.$keyword.'%')
                              ->orWhere('structure', 'like', '%'.$keyword.'%')
                              ->orWhereHas('instructors', function($query) use ($keyword) {
                                $query->where('title', 'like', '%'.$keyword.'%');
                              });
                    });

                    $allCoursesIds = array_merge($allCoursesIds, $data->where('status', '1')->select('courses.id')->get()->pluck('id')->toArray());
                    // END COURSES (LEVEL PUBLIC)

                    // BEGIN COURSES (CLASSROOM TARGET)
                    $dataMembers = new Members;
                    $dataMembers = $dataMembers->find($_user->id);
                    $classrooms_target = $dataMembers->classrooms()->whereDate('start_datetime', '<=', date('Y-m-d H:i:s'))->whereDate('end_datetime', '>=', date('Y-m-d H:i:s'))->get();
                    for($i=0; $i<count($classrooms_target); $i++) {
                        $dataCoursesClassroomsTargets = $classrooms_target[$i]->courses();
                        if($groups->use_sub_groups_single){
                            $dataCoursesClassroomsTargets = $dataCoursesClassroomsTargets->whereHas('classrooms.sub_groups', function($query) use ($dataMembers) {
                                $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                            });
                        }

                        $dataCoursesClassroomsTargets = $dataCoursesClassroomsTargets->where(function($query) use ($keyword) {
                            $query->where('title', 'like', '%'.$keyword.'%')
                                  ->orWhere('code', 'like', '%'.$keyword.'%')
                                  ->orWhere('information', 'like', '%'.$keyword.'%')
                                  ->orWhere('structure', 'like', '%'.$keyword.'%')
                                  ->orWhereHas('instructors', function($query) use ($keyword) {
                                    $query->where('title', 'like', '%'.$keyword.'%');
                                  });
                        });

                        $classrooms_target[$i]->courses = $dataCoursesClassroomsTargets->where('status', '1')->select('courses.id')->get();
                        $allCoursesIds = array_merge($allCoursesIds, $classrooms_target[$i]->courses->pluck('id')->toArray());
                    }
                    // END COURSES (CLASSROOM TARGET)

                    // BEGIN COURSES (CLASSROOM LEVEL GROUP)
                    $dataMembers = new Members;
                    $dataMembers = $dataMembers->find($_user->id);
                    $classrooms_level_group = $dataMembers->level_groups()->get();
                    for($l=0; $l<count($classrooms_level_group); $l++) {
                        $classrooms_level_group[$l]->classroom = $classrooms_level_group[$l]->classrooms()->whereDate('start_datetime', '<=', date('Y-m-d H:i:s'))->whereDate('end_datetime', '>=', date('Y-m-d H:i:s'))->get();
                        for($i=0; $i<count($classrooms_level_group[$l]->classroom); $i++) {
                            $dataCoursesClassroomsLevelGroups = $classrooms_level_group[$l]->classroom[$i]->courses();

                            if($groups->use_sub_groups_single){
                                $dataCoursesClassroomsLevelGroups = $dataCoursesClassroomsLevelGroups->whereHas('classrooms.sub_groups', function($query) use ($dataMembers) {
                                    $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                                });
                            }

                            $dataCoursesClassroomsLevelGroups = $dataCoursesClassroomsLevelGroups->where(function($query) use ($keyword) {
                                $query->where('title', 'like', '%'.$keyword.'%')
                                      ->orWhere('code', 'like', '%'.$keyword.'%')
                                      ->orWhere('information', 'like', '%'.$keyword.'%')
                                      ->orWhere('structure', 'like', '%'.$keyword.'%')
                                      ->orWhereHas('instructors', function($query) use ($keyword) {
                                        $query->where('title', 'like', '%'.$keyword.'%');
                                      });
                            });

                            $classrooms_level_group[$l]->classroom[$i]->courses = $dataCoursesClassroomsLevelGroups->where('status', '1')->select('courses.id')->get();
                            $allCoursesIds = array_merge($allCoursesIds, $classrooms_level_group[$l]->classroom[$i]->courses->pluck('id')->toArray());
                        }
                    }
                    // END COURSES (CLASSROOM LEVEL GROUP)

                    // BEGIN COURSES (TARGET)
                    $dataMembers = new Members;
                    $dataMembers = $dataMembers->find($_user->id);

                    $dataCoursesTargets = $dataMembers->courses();

                    if($groups->use_sub_groups_single){
                        $dataCoursesTargets = $dataCoursesTargets->whereHas('sub_groups', function($query) use ($dataMembers) {
                            $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                        });
                    }

                    $dataCoursesTargets = $dataCoursesTargets->where(function($query) use ($keyword) {
                        $query->where('title', 'like', '%'.$keyword.'%')
                              ->orWhere('code', 'like', '%'.$keyword.'%')
                              ->orWhere('information', 'like', '%'.$keyword.'%')
                              ->orWhere('structure', 'like', '%'.$keyword.'%')
                              ->orWhereHas('instructors', function($query) use ($keyword) {
                                $query->where('title', 'like', '%'.$keyword.'%');
                              });
                    });

                    $courses_target = $dataCoursesTargets->where('status', '1')->select('courses.id')->get();
                    $allCoursesIds = array_merge($allCoursesIds, $courses_target->pluck('id')->toArray());
                    // END COURSES (TARGET)

                    // BEGIN COURSES (LEVEL GROUPS)
                    $dataMembers = new Members;
                    $dataMembers = $dataMembers->find($_user->id);
                    $courses_level_groups = $dataMembers->level_groups()->get();
                    for($l=0; $l<count($courses_level_groups); $l++) {
                        $dataCoursesLevelGroups = $courses_level_groups[$l]->courses();

                        if($groups->use_sub_groups_single){
                            $dataCoursesLevelGroups = $dataCoursesLevelGroups->whereHas('sub_groups', function($query) use ($dataMembers) {
                                $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                            });
                        }

                        $dataCoursesLevelGroups = $dataCoursesLevelGroups->where(function($query) use ($keyword) {
                            $query->where('title', 'like', '%'.$keyword.'%')
                                  ->orWhere('code', 'like', '%'.$keyword.'%')
                                  ->orWhere('information', 'like', '%'.$keyword.'%')
                                  ->orWhere('structure', 'like', '%'.$keyword.'%')
                                  ->orWhereHas('instructors', function($query) use ($keyword) {
                                    $query->where('title', 'like', '%'.$keyword.'%');
                                  });
                        });

                        $courses_level_groups[$l] = $dataCoursesLevelGroups->where('status', '1')->select('courses.id')->get();
                        $allCoursesIds = array_merge($allCoursesIds, $courses_level_groups[$l]->pluck('id')->toArray());
                    }
                    // END COURSES (LEVEL GROUPS)

                    // All Courses
                    $dataAllCourses = Courses::whereIn('id', array_unique($allCoursesIds))->orderBy($order_by, $order_direction)->paginate($per_page);
                    for($i=0; $i<count($dataAllCourses); $i++) {
                        if($dataAllCourses[$i]->latest_end_datetime >= date('Y-m-d')){
                            $dataAllCourses[$i]->latest = 1;
                        }else{
                            $dataAllCourses[$i]->latest = 0;
                        }
                        $dataAllCourses[$i]->categories = $dataAllCourses[$i]->categories()->where('groups_id', $groups->id)->first();

                        $dataAllCourses[$i]->topics = $dataAllCourses[$i]->topics()
                                            ->select('id', 'title', 'state', 'live_start_datetime', 'streaming_status', 'status')
                                            ->where('state', 'live')
                                            ->where('streaming_status', 1)
                                            ->where('status', 1)
                                            ->orderBy('live_start_datetime', 'asc')
                                            ->first();
                        if (!$dataAllCourses[$i]->topics) {
                            $dataAllCourses[$i]->topics = $dataAllCourses[$i]->topics()
                                            ->select('id', 'title', 'state', 'live_start_datetime', 'streaming_status', 'status')
                                            ->where('state', 'live')
                                            ->where('streaming_status', 0)
                                            ->where('status', 1)
                                            ->orderBy('live_start_datetime', 'asc')
                                            ->first();
                        }
                        // array_push($courseWhereNotIn, $dataAllCourses[$i]->id);
                    }

                    return response()->json($dataAllCourses, 200);

                }else{

                    $groups = new Groups;
                    $groups = $groups->where('key', $groupsKey)->first();
                    $data = $groups->courses();
                    $data = $data->where('level_public', 1);

                    $data = $data->where(function($query) use ($keyword) {
                        $query->where('title', 'like', '%'.$keyword.'%')
                              ->orWhere('code', 'like', '%'.$keyword.'%')
                              ->orWhere('information', 'like', '%'.$keyword.'%')
                              ->orWhere('structure', 'like', '%'.$keyword.'%')
                              ->orWhereHas('instructors', function($query) use ($keyword) {
                                $query->where('title', 'like', '%'.$keyword.'%');
                              });
                    });

                    $data = $data->where('status', '1')->orderBy($order_by,$order_direction)->paginate($per_page);
                    for($i=0; $i<count($data); $i++) {
                        if($data[$i]->latest_end_datetime >= date('Y-m-d')){
                            $data[$i]->latest = 1;
                        }else{
                            $data[$i]->latest = 0;
                        }
                        $data[$i]->categories = $data[$i]->categories()->where('groups_id', $groups->id)->first();
                    }

                    return response()->json($data, 200);
                }

            }else{

                $groups = new Groups;
                $groups = $groups->where('key', $groupsKey)->first();
                $data = $groups->courses();
                $data = $data->where('level_public', 1);

                $data = $data->where(function($query) use ($keyword) {
                    $query->where('title', 'like', '%'.$keyword.'%')
                          ->orWhere('code', 'like', '%'.$keyword.'%')
                          ->orWhere('information', 'like', '%'.$keyword.'%')
                          ->orWhere('structure', 'like', '%'.$keyword.'%')
                          ->orWhereHas('instructors', function($query) use ($keyword) {
                            $query->where('title', 'like', '%'.$keyword.'%');
                          });
                });

                $data = $data->where('status', '1')->orderBy($order_by,$order_direction)->paginate($per_page);
                for($i=0; $i<count($data); $i++) {
                    if($data[$i]->latest_end_datetime >= date('Y-m-d')){
                        $data[$i]->latest = 1;
                    }else{
                        $data[$i]->latest = 0;
                    }
                    $data[$i]->categories = $data[$i]->categories()->where('groups_id', $groups->id)->first();
                }

                return response()->json($data, 200);
            }

        }else{

            $groups = new Groups;
            $groups = $groups->where('key', $groupsKey)->first();
            $data = $groups->courses();
            $data = $data->where('level_public', 1);

            $data = $data->where(function($query) use ($keyword) {
                $query->where('title', 'like', '%'.$keyword.'%')
                      ->orWhere('code', 'like', '%'.$keyword.'%')
                      ->orWhere('information', 'like', '%'.$keyword.'%')
                      ->orWhere('structure', 'like', '%'.$keyword.'%')
                      ->orWhereHas('instructors', function($query) use ($keyword) {
                        $query->where('title', 'like', '%'.$keyword.'%');
                      });
            });

            $data = $data->where('status', '1')->orderBy($order_by,$order_direction)->paginate($per_page);

            for($i=0; $i<count($data); $i++) {
                if($data[$i]->latest_end_datetime >= date('Y-m-d')){
                    $data[$i]->latest = 1;
                }else{
                    $data[$i]->latest = 0;
                }
                $data[$i]->categories = $data[$i]->categories()->where('groups_id', $groups->id)->first();
            }

            return response()->json($data, 200);

        }

    }

    public function filterCoursesList($groupsKey, Request $request)
    {
        $per_page = $request->input('per_page', 50);
        $order_by = $request->input('order_by', 'order');
        $order_direction = $request->input('order_direction', 'asc');

        $_user = session()->get('_user');

        $dataFilterCourse = $_user->filter_courses()->orderBy('datetime', 'desc')->first();
        if (!$dataFilterCourse) {
            return response()->json([], 200);
        }

        $dataFilterCourse->courses = $dataFilterCourse->courses()->orderBy($order_by, $order_direction)->paginate($per_page);

        for ($i = 0; $i < count($dataFilterCourse->courses); $i++) {
            if ($dataFilterCourse->courses[$i]->latest_end_datetime >= date('Y-m-d')) {
                $dataFilterCourse->courses[$i]->latest = 1;
            } else {
                $dataFilterCourse->courses[$i]->latest = 0;
            }
            $dataFilterCourse->courses[$i]->categories = $dataFilterCourse->courses[$i]->categories()->where('groups_id', $_user->groups_id)->first();
        }

        return response()->json($dataFilterCourse, 200);

    }

    public function categories2courses($groupsKey, $categoriesID, Request $request, _FunctionsController $oFunc)
    {
        $categories = new Categories;
        $categories = $categories->find($categoriesID);

        $per_page = $request->input('per_page', 16);
        $order_by = $request->input('order_by', 'order');
        $order_direction = $request->input('order_direction', 'asc');
        $ignore_state = $request['ignore_state'];

        $_user = session()->get('_user');

        if($_user){
            $_user->groups = $_user->groups()->first();
            if($groupsKey == $_user->groups->key){
                if($_user->level_groups){

                    $groups = new Groups;
                    $groups = $groups->where('key', $groupsKey)->first();
                    $allCoursesIds = array();

                    // BEGIN COURSES (LEVEL PUBLIC)
                    $data = $groups->courses();
                    $data = $data->where('level_public', 1);
                    $data = $data->with('categories');
                    $data = $data->whereHas('categories', function($query) use ($categoriesID) {
                        $query->where('categories.id', $categoriesID);
                    });

                    $allCoursesIds = array_merge($allCoursesIds, $data->where('status', '1')->select('courses.id')->get()->pluck('id')->toArray());
                    // END COURSES (LEVEL PUBLIC)

                    // BEGIN COURSES (CLASSROOM TARGET)
                    $dataMembers = new Members;
                    $dataMembers = $dataMembers->find($_user->id);
                    $classrooms_target = $dataMembers->classrooms()->whereDate('start_datetime', '<=', date('Y-m-d H:i:s'))->whereDate('end_datetime', '>=', date('Y-m-d H:i:s'))->get();
                    for($i=0; $i<count($classrooms_target); $i++) {
                        if($groups->use_sub_groups_single){
                            $classrooms_target[$i]->courses = $classrooms_target[$i]->courses()->whereHas('classrooms.sub_groups', function($query) use ($dataMembers) {
                                $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                            })
                                ->with('categories')
                                ->whereHas('categories', function($query) use ($categoriesID) {
                                    $query->where('categories.id', $categoriesID);
                                });
                        }else{
                            $classrooms_target[$i]->courses = $classrooms_target[$i]->courses()
                                ->where('sub_groups_id', $_user->sub_groups_id)
                                ->with('categories')
                                ->whereHas('categories', function($query) use ($categoriesID) {
                                    $query->where('categories.id', $categoriesID);
                                });
                        }

                        $classrooms_target[$i]->courses = $classrooms_target[$i]->courses->where('status', '1')->select('courses.id')->get();
                        $allCoursesIds = array_merge($allCoursesIds, $classrooms_target[$i]->courses->pluck('id')->toArray());
                    }
                    // END COURSES (CLASSROOM TARGET)

                    // BEGIN COURSES (CLASSROOM LEVEL GROUP)
                    $dataMembers = new Members;
                    $dataMembers = $dataMembers->find($_user->id);
                    $classrooms_level_group = $dataMembers->level_groups()->get();
                    for($l=0; $l<count($classrooms_level_group); $l++) {
                        $classrooms_level_group[$l]->classroom = $classrooms_level_group[$l]->classrooms()->whereDate('start_datetime', '<=', date('Y-m-d H:i:s'))->whereDate('end_datetime', '>=', date('Y-m-d H:i:s'))->get();
                        for($i=0; $i<count($classrooms_level_group[$l]->classroom); $i++) {
                            if($groups->use_sub_groups_single){
                                $classrooms_level_group[$l]->classroom[$i]->courses = $classrooms_level_group[$l]->classroom[$i]->courses()->whereHas('classrooms.sub_groups', function($query) use ($dataMembers) {
                                    $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                                })
                                    ->with('categories')
                                    ->whereHas('categories', function($query) use ($categoriesID) {
                                        $query->where('categories.id', $categoriesID);
                                    });
                            }else{
                                $classrooms_level_group[$l]->classroom[$i]->courses = $classrooms_level_group[$l]->classroom[$i]->courses()
                                    ->where('sub_groups_id', $_user->sub_groups_id)
                                    ->with('categories')
                                    ->whereHas('categories', function($query) use ($categoriesID) {
                                        $query->where('categories.id', $categoriesID);
                                    });
                            }

                            $classrooms_level_group[$l]->classroom[$i]->courses = $classrooms_level_group[$l]->classroom[$i]->courses->where('status', '1')->select('courses.id')->get();
                            $allCoursesIds = array_merge($allCoursesIds, $classrooms_level_group[$l]->classroom[$i]->courses->pluck('id')->toArray());
                        }
                    }
                    // END COURSES (CLASSROOM LEVEL GROUP)

                    // BEGIN COURSES (TARGET)
                    $dataMembers = new Members;
                    $dataMembers = $dataMembers->find($_user->id);
                    if($groups->use_sub_groups_single){
                        $courses_target = $dataMembers->courses()->whereHas('sub_groups', function($query) use ($dataMembers) {
                            $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                        });
                    }else{
                        $courses_target = $dataMembers->courses();
                    }
                    $courses_target = $courses_target->with('categories');
                    $courses_target = $courses_target->whereHas('categories', function($query) use ($categoriesID) {
                            $query->where('categories.id', $categoriesID);
                    });

                    $courses_target = $courses_target->where('status', '1')->select('courses.id')->get();
                    $allCoursesIds = array_merge($allCoursesIds, $courses_target->pluck('id')->toArray());
                    // END COURSES (TARGET)

                    // BEGIN COURSES (LEVEL GROUPS)
                    $dataMembers = new Members;
                    $dataMembers = $dataMembers->find($_user->id);
                    $courses_level_groups = $dataMembers->level_groups()->get();
                    for($l=0; $l<count($courses_level_groups); $l++) {

                        if($groups->use_sub_groups_single){
                            $courses_level_groups[$l]->courses = $courses_level_groups[$l]->courses()->whereHas('sub_groups', function($query) use ($dataMembers) {
                                $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                            });
                        }else{
                            $courses_level_groups[$l]->courses = $courses_level_groups[$l]->courses();
                        }
                        $courses_level_groups[$l]->courses = $courses_level_groups[$l]->courses->with('categories');
                        $courses_level_groups[$l]->courses = $courses_level_groups[$l]->courses->whereHas('categories', function($query) use ($categoriesID) {
                            $query->where('categories.id', $categoriesID);
                        });

                        $courses_level_groups[$l]->courses = $courses_level_groups[$l]->courses->where('status', '1')->select('courses.id')->get();
                        $allCoursesIds = array_merge($allCoursesIds, $courses_level_groups[$l]->courses->pluck('id')->toArray());
                    }
                    // END COURSES (LEVEL GROUPS)

                    // All Courses
                    $dataAllCourses = Courses::with('categories')->whereIn('id', array_unique($allCoursesIds))->orderBy($order_by, $order_direction)->paginate($per_page);
                    for($i=0; $i<count($dataAllCourses); $i++) {
                        if($dataAllCourses[$i]->latest_end_datetime >= date('Y-m-d')){
                            $dataAllCourses[$i]->latest = 1;
                        }else{
                            $dataAllCourses[$i]->latest = 0;
                        }
                        $dataAllCourses[$i]->category = $dataAllCourses[$i]->categories()->where('groups_id', $groups->id)->first();
                        if ($ignore_state == 'live') {
                            $dataAllCourses[$i]->topics = null;
                        } else {
                            $dataAllCourses[$i]->topics = $dataAllCourses[$i]->topics()
                                                ->select('id', 'title', 'state', 'live_start_datetime', 'streaming_status', 'status')
                                                ->where('state', 'live')
                                                ->where('streaming_status', 1)
                                                ->where('status', 1)
                                                ->orderBy('live_start_datetime', 'asc')
                                                ->first();

                            if ($dataAllCourses[$i]->topics == null) {
                                $dataAllCourses[$i]->topics = $dataAllCourses[$i]->topics()
                                                    ->select('id', 'title', 'live_start_datetime', 'live_end_datetime', 'state', 'streaming_status', 'status')
                                                    ->where('state', 'live')
                                                    ->where('streaming_status', 0)
                                                    ->where('live_start_datetime', '>', date('Y-m-d H:i:s'))
                                                    ->where('status', 1)
                                                    ->orderBy('live_start_datetime', 'asc')
                                                    ->first();
                            }

                            if ($dataAllCourses[$i]->topics) {
                                $dataAllCourses[$i]->topics->live_datetime = $oFunc->thai_date_fullmonth(strtotime($dataAllCourses[$i]->topics->live_start_datetime)).' เวลา '.date('H:i', strtotime($dataAllCourses[$i]->topics->live_start_datetime)).' - '.date('H:i', strtotime($dataAllCourses[$i]->topics->live_end_datetime)).' น.';
                            }
                        }
                    }

                    return response()->json(["courses" => $dataAllCourses, "categories" => $categories], 200);

                }else{

                    $groups = new Groups;
                    $groups = $groups->where('key', $groupsKey)->first();
                    $data = $groups->courses();
                    $data = $data->where('level_public', 1);
                    $data = $data->with('categories');
                    $data = $data->whereHas('categories', function($query) use ($categoriesID) {
                        $query->where('categories.id', $categoriesID);
                    });
                    $data = $data->where('status', '1')->orderBy($order_by,$order_direction)->paginate($per_page);
                    for($i=0; $i<count($data); $i++) {
                        if($data[$i]->latest_end_datetime >= date('Y-m-d')){
                            $data[$i]->latest = 1;
                        }else{
                            $data[$i]->latest = 0;
                        }
                        $data[$i]->category = $data[$i]->categories()->where('groups_id', $groups->id)->first();
                        if ($ignore_state == 'live') {
                            $data[$i]->topics = null;
                        } else {
                            $data[$i]->topics = $data[$i]->topics()
                                                ->select('id', 'title', 'state', 'live_start_datetime', 'streaming_status', 'status')
                                                ->where('state', 'live')
                                                ->where('streaming_status', 1)
                                                ->where('status', 1)
                                                ->orderBy('live_start_datetime', 'asc')
                                                ->first();

                            if ($data[$i]->topics == null) {
                                $data[$i]->topics = $data[$i]->topics()
                                                    ->select('id', 'title', 'live_start_datetime', 'live_end_datetime', 'state', 'streaming_status', 'status')
                                                    ->where('state', 'live')
                                                    ->where('streaming_status', 0)
                                                    ->where('live_start_datetime', '>', date('Y-m-d H:i:s'))
                                                    ->where('status', 1)
                                                    ->orderBy('live_start_datetime', 'asc')
                                                    ->first();
                            }

                            if ($data[$i]->topics) {
                                $data[$i]->topics->live_datetime = $oFunc->thai_date_fullmonth(strtotime($data[$i]->topics->live_start_datetime)).' เวลา '.date('H:i', strtotime($data[$i]->topics->live_start_datetime)).' - '.date('H:i', strtotime($data[$i]->topics->live_end_datetime)).' น.';
                            }
                        }
                    }

                    return response()->json(array('courses' => $data, 'categories' => $categories), 200);

                }
            }else{

                $groups = new Groups;
                $groups = $groups->where('key', $groupsKey)->first();
                $data = $groups->courses();
                $data = $data->where('level_public', 1);
                $data = $data->with('categories');
                $data = $data->whereHas('categories', function($query) use ($categoriesID) {
                    $query->where('categories.id', $categoriesID);
                });
                $data = $data->where('status', '1')->orderBy($order_by,$order_direction)->paginate($per_page);
                for($i=0; $i<count($data); $i++) {
                    if($data[$i]->latest_end_datetime >= date('Y-m-d')){
                        $data[$i]->latest = 1;
                    }else{
                        $data[$i]->latest = 0;
                    }
                    $data[$i]->category = $data[$i]->categories()->where('groups_id', $groups->id)->first();
                    if ($ignore_state == 'live') {
                        $data[$i]->topics = null;
                    } else {
                        $data[$i]->topics = $data[$i]->topics()
                                            ->select('id', 'title', 'state', 'live_start_datetime', 'streaming_status', 'status')
                                            ->where('state', 'live')
                                            ->where('streaming_status', 1)
                                            ->where('status', 1)
                                            ->orderBy('live_start_datetime', 'asc')
                                            ->first();

                        if ($data[$i]->topics == null) {
                            $data[$i]->topics = $data[$i]->topics()
                                                ->select('id', 'title', 'live_start_datetime', 'live_end_datetime', 'state', 'streaming_status', 'status')
                                                ->where('state', 'live')
                                                ->where('streaming_status', 0)
                                                ->where('live_start_datetime', '>', date('Y-m-d H:i:s'))
                                                ->where('status', 1)
                                                ->orderBy('live_start_datetime', 'asc')
                                                ->first();
                        }

                        if ($data[$i]->topics) {
                            $data[$i]->topics->live_datetime = $oFunc->thai_date_fullmonth(strtotime($data[$i]->topics->live_start_datetime)).' เวลา '.date('H:i', strtotime($data[$i]->topics->live_start_datetime)).' - '.date('H:i', strtotime($data[$i]->topics->live_end_datetime)).' น.';
                        }
                    }
                }

                return response()->json(array('courses' => $data, 'categories' => $categories), 200);

            }
        }else{

            $groups = new Groups;
            $groups = $groups->where('key', $groupsKey)->first();
            $data = $groups->courses();
            $data = $data->where('level_public', 1);
            $data = $data->with('categories');
            $data = $data->whereHas('categories', function($query) use ($categoriesID) {
                $query->where('categories.id', $categoriesID);
            });
            $data = $data->where('status', '1')->orderBy($order_by,$order_direction)->paginate($per_page);
            for($i=0; $i<count($data); $i++) {
                if($data[$i]->latest_end_datetime >= date('Y-m-d')){
                    $data[$i]->latest = 1;
                }else{
                    $data[$i]->latest = 0;
                }
                $data[$i]->category = $data[$i]->categories()->where('groups_id', $groups->id)->first();
                if ($ignore_state == 'live') {
                    $data[$i]->topics = null;
                } else {
                    $data[$i]->topics = $data[$i]->topics()
                                        ->select('id', 'title', 'state', 'live_start_datetime', 'streaming_status', 'status')
                                        ->where('state', 'live')
                                        ->where('streaming_status', 1)
                                        ->where('status', 1)
                                        ->orderBy('live_start_datetime', 'asc')
                                        ->first();

                    if ($data[$i]->topics == null) {
                        $data[$i]->topics = $data[$i]->topics()
                                            ->select('id', 'title', 'live_start_datetime', 'live_end_datetime', 'state', 'streaming_status', 'status')
                                            ->where('state', 'live')
                                            ->where('streaming_status', 0)
                                            ->where('live_start_datetime', '>', date('Y-m-d H:i:s'))
                                            ->where('status', 1)
                                            ->orderBy('live_start_datetime', 'asc')
                                            ->first();
                    }

                    if ($data[$i]->topics) {
                        $data[$i]->topics->live_datetime = $oFunc->thai_date_fullmonth(strtotime($data[$i]->topics->live_start_datetime)).' เวลา '.date('H:i', strtotime($data[$i]->topics->live_start_datetime)).' - '.date('H:i', strtotime($data[$i]->topics->live_end_datetime)).' น.';
                    }
                }
            }

            return response()->json(array('courses' => $data, 'categories' => $categories), 200);
        }

    }

    public function courses($groupsKey, $id, _FunctionsController $oFunc)
    {
        $_user = session()->get('_user');
        $groups = new Groups;
        $groups = $groups->where('key', $groupsKey)->first();

        if (!$groups || (!is_null($_user) && $groups->id != $_user->groups_id)) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Course", config('constants._errorMessage._404'))), 404);
        }

        $data = $groups->courses()->where('courses.id', $id)->first();
        if (!$data) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Course", config('constants._errorMessage._404'))), 404);
        }

        if($_user){
            $_user->groups = $_user->groups()->first();
            if($groupsKey == $_user->groups->key){
                if($_user->level_groups){

                    $data->classrooms_targets = $data->classrooms();
                    $data->classrooms_targets = $data->classrooms_targets->whereDate('start_datetime', '<=', date('Y-m-d H:i:s'));
                    $data->classrooms_targets = $data->classrooms_targets->whereDate('end_datetime', '>=', date('Y-m-d H:i:s'));
                    if($groups->use_sub_groups_single){
                        $data->classrooms_targets = $data->classrooms_targets->whereHas('members', function ($query) use ($_user){
                            $query->where('members_id', $_user->id);
                        })->whereHas('sub_groups', function($sub_query) use ($_user) {
                                $sub_query->where('sub_groups_id', $_user->sub_groups_id);
                        })->first();
                    }else{
                        $data->classrooms_targets = $data->classrooms_targets->whereHas('members', function($query) use ($_user) {
                            $query->where('members_id', $_user->id);
                        })->first();
                    }

                    $data->classrooms_level_groups = $data->classrooms();
                    $data->classrooms_level_groups = $data->classrooms_level_groups->whereDate('start_datetime', '<=', date('Y-m-d H:i:s'));
                    $data->classrooms_level_groups = $data->classrooms_level_groups->whereDate('end_datetime', '>=', date('Y-m-d H:i:s'));
                    if($groups->use_sub_groups_single){
                        $data->classrooms_level_groups = $data->classrooms_level_groups->whereHas('level_groups.members', function($query) use ($_user) {
                            $query->where('members_id', $_user->id);
                        })->whereHas('sub_groups', function($sub_query) use ($_user) {
                            $sub_query->where('sub_groups_id', $_user->sub_groups_id);
                        })->first();
                    }else{
                        $data->classrooms_level_groups = $data->classrooms_level_groups->whereHas('level_groups.members', function($query) use ($_user) {
                            $query->where('members_id', $_user->id);
                        })->first();
                    }

                    if($groups->use_sub_groups_single){
                        $data->targets = $data->members()->where('members.id', $_user->id)->whereHas('courses.sub_groups', function($sub_query) use ($_user) {
                            $sub_query->where('sub_groups_id', $_user->sub_groups_id);
                        })->first();
                    }else{
                        $data->targets = $data->members()->where('members.id', $_user->id)->first();
                    }

                    if($groups->use_sub_groups_single){
                        $data->level_groups = $data->level_groups()->whereHas('members', function($query) use ($_user) {
                            $query->where('members_id', $_user->id);
                        })->whereHas('courses.sub_groups', function($sub_query) use ($_user) {
                            $sub_query->where('sub_groups_id', $_user->sub_groups_id);
                        })->first();
                    }else{
                        $data->level_groups = $data->level_groups()->whereHas('members', function($query) use ($_user) {
                            $query->where('members_id', $_user->id);
                        })->first();
                    }

                }
            }

            if($data->classrooms_targets){
                $data->course2access = true;
            }else if($data->classrooms_level_groups){
                $data->course2access = true;
            }else if($data->targets){
                $data->course2access = true;
            }else if($data->level_groups){
                $data->course2access = true;
            }else if($data->level_public){
                $data->course2access = true;
            }else{
                $data->course2access = false;
            }

        }

        $data->categories = $data->categories()->where('groups_id', $groups->id)->first();

        if (!$data->categories) {
            $data->categories = $data->categories()->first();
        }

        $data->agenda_live = $data->topics()
                            ->select(DB::raw('DATE_FORMAT(live_start_datetime, "%Y-%m-%d") as date'))
                            ->where('state', 'live')
                            ->where('status', 1)
                            // ->where('live_end_datetime', '<', date('Y-m-d 23:59:59'))
                            ->orderBy('live_start_datetime', 'asc')
                            ->groupBy(DB::raw('DATE_FORMAT(live_start_datetime, "%Y-%m-%d")'))
                            ->get();

        for ($i=0; $i < count($data->agenda_live); $i++) {
            $first_start_datetime = Carbon::parse($data->agenda_live[$i]->date)->format('Y-m-d 00:00:00');
            $first_end_datetime = Carbon::parse($data->agenda_live[$i]->date)->format('Y-m-d 23:59:59');
            $topic_start_datetime = $data->topics()->where('state', 'live')->whereBetween('live_start_datetime', array($first_start_datetime, $first_end_datetime))->where('status', 1)->orderBy('live_start_datetime', 'asc')->first();

            $topic_end_datetime = $data->topics()->where('state', 'live')->whereBetween('live_start_datetime', array($first_start_datetime, $first_end_datetime))->where('status', 1)->orderBy('live_end_datetime', 'desc')->first();

            if (isset($topic_start_datetime->live_start_datetime)) {
                $data->agenda_live[$i]->start_date = $oFunc->thai_date_fullmonth(strtotime($topic_start_datetime->live_start_datetime));
                $data->agenda_live[$i]->start_time = Carbon::parse($topic_start_datetime->live_start_datetime)->format('H:i');
            }

            if (isset($topic_end_datetime->live_end_datetime)) {
                $data->agenda_live[$i]->end_date = $oFunc->thai_date_fullmonth(strtotime($topic_end_datetime->live_end_datetime));
                $data->agenda_live[$i]->end_time = Carbon::parse($topic_end_datetime->live_end_datetime)->format('H:i');
            }

            $topic_live = $data->topics()->where('state', 'live')->where('live_start_datetime', '>=', $first_start_datetime)->where('status', 1)->orderBy('live_start_datetime', 'asc')->first();

            if ($topic_live) {
                $data->agenda_live[$i]->title = $topic_live->title;
                $data->agenda_live[$i]->state = $topic_live->state;
                $data->agenda_live[$i]->streaming_status = $topic_live->streaming_status;
                $data->agenda_live[$i]->status = $topic_live->status;
                $data->agenda_live[$i]->end_datetime = $topic_end_datetime->live_end_datetime;
            }
        }

        foreach ($data->agenda_live as $key => $rs) {
            if ($rs['end_datetime'] < date('Y-m-d H:i:s') && $rs['streaming_status'] == 0) {
                unset($data->agenda_live[$key]);
            }
        }

        $data->topics = $data->topics()
                            ->where('state', 'live')
                            ->where('streaming_status', 1)
                            ->where('status', 1)
                            ->orderBy('live_start_datetime', 'asc')
                            ->first();

        if ($data->topics) {
            if ($data->topics->state == 'live' && $data->topics->streaming_status == 1) {
                $data->topics->live_status = 1;
            }

            $data->topics->live_datetime = $oFunc->thai_date_fullmonth(strtotime($data->topics->live_start_datetime)).' เวลา '.date('H:i', strtotime($data->topics->live_start_datetime)).' - '.date('H:i', strtotime($data->topics->live_end_datetime)).' น.';
        } else {
            $topics = array(
                'state' => 'vod'
            );

            $data->topics = $topics;
        }

        $data->instructors = $data->instructors()->get();
        $data->documents = $data->documents()->where('status', 1)->orderBy('order', 'ASC')->get();
        $data->related = $data->related()->whereHas('groups', function($query) use ($groups) {
            $query->where('groups_id', $groups->id);
        })->orderBy('course2related.order', 'ASC')->get();
        for($i=0; $i<count($data->related); $i++) {
            $data->related[$i]->categories = $data->related[$i]->categories()->where('groups_id', $groups->id)->first();
            if (!$data->related[$i]->categories) {
                $data->related[$i]->categories = $data->related[$i]->categories()->first();
            }
        }
        if($data->random_quiz){
            $data->pre_test = $data->quiz()->where('type', 1)->where('status', 1)->inRandomOrder()->first();
        }else{
            $data->pre_test = $data->quiz()->where('type', 1)->where('status', 1)->first();
        }
        if(count($data->pre_test)){
            if($data->pre_test->random_questions){
                $data->pre_test->questions = $data->pre_test->questions()->inRandomOrder()->where('status', 1)->limit($data->pre_test->limit_questions)->get();
            }else{
                $data->pre_test->questions = $data->pre_test->questions()->where('status', 1)->orderBy('order', 'asc')->limit($data->pre_test->limit_questions)->get();
            }
            $data->pre_test->questions_count = 0;
            for($i=0; $i<count($data->pre_test->questions); $i++) {
                if($data->pre_test->random_answer){
                    $data->pre_test->questions[$i]->answer = $data->pre_test->questions[$i]->answer()->where('status', 1)->inRandomOrder()->get();
                }else{
                    $data->pre_test->questions[$i]->answer = $data->pre_test->questions[$i]->answer()->where('status', 1)->orderBy('order', 'asc')->get();
                }

                if ($data->pre_test->questions[$i]->type == 1 || $data->pre_test->questions[$i]->type == 2) {
                    $data->pre_test->questions_count++;
                }
            }
        }

        if($data->random_quiz){
            $data->post_test = $data->quiz()->where('type', 4)->where('status', 1)->inRandomOrder()->first();
        }else{
            $data->post_test = $data->quiz()->where('type', 4)->where('status', 1)->first();
        }
        if(count($data->post_test)){
            if($data->post_test->random_questions){
                $data->post_test->questions = $data->post_test->questions()->inRandomOrder()->where('status', 1)->limit($data->post_test->limit_questions)->get();
            }else{
                $data->post_test->questions = $data->post_test->questions()->where('status', 1)->orderBy('order', 'asc')->limit($data->post_test->limit_questions)->get();
            }
            $data->post_test->questions_count = 0;
            for($i=0; $i<count($data->post_test->questions); $i++) {
                if($data->post_test->random_answer){
                    $data->post_test->questions[$i]->answer = $data->post_test->questions[$i]->answer()->where('status', 1)->inRandomOrder()->get();
                }else{
                    $data->post_test->questions[$i]->answer = $data->post_test->questions[$i]->answer()->where('status', 1)->orderBy('order', 'asc')->get();
                }

                if ($data->post_test->questions[$i]->type == 1 || $data->post_test->questions[$i]->type == 2) {
                    $data->post_test->questions_count++;
                }
            }
        }


        if($data->random_quiz){
            $data->exam = $data->quiz()->where('type', 3)->where('status', 1)->inRandomOrder()->first();
        }else{
            $data->exam = $data->quiz()->where('type', 3)->where('status', 1)->first();
        }
        if(count($data->exam)){
            if($data->exam->random_questions){
                $data->exam->questions = $data->exam->questions()->inRandomOrder()->where('status', 1)->limit($data->exam->limit_questions)->get();
            }else{
                $data->exam->questions = $data->exam->questions()->where('status', 1)->orderBy('order', 'asc')->limit($data->exam->limit_questions)->get();
            }
            $data->exam->questions_count = 0;
            for($i=0; $i<count($data->exam->questions); $i++) {
                if($data->exam->random_answer){
                    $data->exam->questions[$i]->answer = $data->exam->questions[$i]->answer()->where('status', 1)->inRandomOrder()->get();
                }else{
                    $data->exam->questions[$i]->answer = $data->exam->questions[$i]->answer()->where('status', 1)->orderBy('order', 'asc')->get();
                }

                if ($data->exam->questions[$i]->type == 1 || $data->exam->questions[$i]->type == 2) {
                    $data->exam->questions_count++;
                }
            }
        }

        $data->survey = $data->quiz()->where('type', 5)->where('status', 1)->first();
        if(count($data->survey)){
            $data->survey->questions = $data->survey->questions()->where('status', 1)->orderBy('order', 'asc')->get();
            for($i=0; $i<count($data->survey->questions); $i++) {
                $data->survey->questions[$i]->answer = $data->survey->questions[$i]->answer()->where('status', 1)->orderBy('order', 'asc')->get();
            }
        }

        $data->confirm_order = false;
        $data->pending_order = false;
        // $data->canceled_order = false;

        if(session()->get('_user')){
            $_user = session()->get('_user');
            $enroll = Enroll::where('members_id', $_user->id)->where('courses_id', $data->id);
            if($enroll->count()){
                $data->enroll_btn = "เข้าเรียน";
                $data->enroll_msg = "ยินดีต้อนรับเข้าสู่หลักสูตร ".$data->title;
                $data->enroll_chk = true;
            }else{
                $data->enroll_btn = "ลงทะเบียน";
                $data->enroll_msg = "คุณต้องการลงทะเบียนหลักสูตร ".$data->title." ?";
                $data->enroll_chk = false;
            }

            // $orders_canceled = Orders::where('members_id', $_user->id)->where('courses_id', $data->id)->whereHas('payments', function($query) {
            //     $query->where('payments.is_canceled', 1);
            // });
            // if ($orders_canceled->count()) {
            //     $data->canceled_order = true;
            // }

            $orders = Orders::where('members_id', $_user->id)->where('courses_id', $data->id)->whereHas('payments', function($query) {
                // $query->where('payments.payment_status', 'successful')->where('payments.approve_status', 1);
                $query->where('payments.payment_status', 'successful')->where('payments.is_canceled', 0);
            });
            if ($orders->count()) {
                $data->confirm_order = true;
            } else {
                $orders_pending = Orders::where('members_id', $_user->id)->where('courses_id', $data->id)->whereHas('payments', function($query) {
                    $query->where('payments.payment_status', 'pending')->where('payments.is_canceled', 0);
                });
                if ($orders_pending->count()) {
                    $data->pending_order = true;
                }
            }
        }else{
            $data->enroll_btn = "ลงทะเบียน";
            $data->enroll_msg = "คุณต้องการลงทะเบียนหลักสูตร ".$data->title." ?";
            $data->enroll_chk = false;
        }

        return response()->json($data, 200);
        //return response()->jsonProtect($data, 200);
    }

    public function courses_recommended($groupsKey, Request $request)
    {
        $per_page = $request->input('per_page', 10);
        $order_by = $request->input('order_by', 'order');
        $order_direction = $request->input('order_direction', 'asc');

        $data = Groups::find($groupsKey)->courses()->where('status', '1')->where('recommended', '1')->orderBy($order_by,$order_direction)->paginate($per_page);
        for($i=0; $i<count($data); $i++) {
            $data[$i]->categories = $data[$i]->categories()->first();
        }
        return response()->json($data, 200);
    }

    public function logs($type, $return, $status, $courses_id, $members_id, $data, $groups_id, $sub_groups_id, $enroll_type_id, $enroll_type)
    {
        ////Logs////
        $dataGeoIP = GeoIP::getLocation();

        $agent = new Agent();
        $logs = new Logs;
        if($groups_id){ $logs->groups_id = $groups_id; }
        if($sub_groups_id){ $logs->sub_groups_id = $sub_groups_id; }
        if($enroll_type_id){ $logs->enroll_type_id = $enroll_type_id; }
        if($enroll_type){ $logs->enroll_type = $enroll_type; }
        if($members_id){ $logs->members_id = $members_id; }
        if($courses_id){ $logs->courses_id = $courses_id; }
        if($data){ $logs->data = $data; }
        $logs->type = $type;
        $logs->return = $return;
        $logs->status = $status;
        $logs->user_agent = $_SERVER['HTTP_USER_AGENT'];
        $logs->ip = ($dataGeoIP['ip'] != "Unknown") ? $dataGeoIP['ip'] : $_SERVER['REMOTE_ADDR'];
        $logs->isoCode = $dataGeoIP['iso_code'];
        $logs->country = $dataGeoIP['country'];
        $logs->city = $dataGeoIP['city'];
        $logs->state = $dataGeoIP['state_name'];
        $logs->timezone = $dataGeoIP['timezone'];
        $logs->continent = $dataGeoIP['continent'];
        $logs->device = $agent->device();
        $logs->platform = $agent->platform();
        $logs->platform_version = $agent->version($logs->platform);
        $logs->browser = $agent->browser();
        $logs->browser_version = $agent->version($logs->browser);
        $logs->datetime = date('Y-m-d H:i:s');
        $logs->save();
        ////End Logs////
    }

    public function enroll2learning(Request $request, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site) {
        if (!$oFunc->checkSession()) {
            return response()->json(array('message' => config('constants._errorMessage._401')), 401);
        }

        // return response()->json(['message' => 'Test Logic'], 500);
        $_user = session()->get('_user');
        $input = $request->input();
        $data = Enroll::where('members_id', $_user->id)->where('courses_id', $input['cid'])->first();
        $data_course = Courses::find($input['cid']);

        if(count($data)){

            $data->last_datetime = date('Y-m-d H:i:s');
            $is_success = $data->save();

            $enroll = Enroll::find($data['id']);
            $chk_pre_test = $enroll->enroll2quiz()->where('type', 1)->whereNotNull('score')->count();

            $enroll->courses = $enroll->courses()->first();
            $enroll->topics = $enroll->courses->topics()->whereNull('parent')->where('status', 1)->orderBy('order','asc')->get();
            $enroll->duration2percentage = 0;
            for($a=0; $a<count($enroll->topics); $a++) {
                $enroll->topics[$a]->parent = Topics::where('parent', $enroll->topics[$a]->id)->where('status', 1)->orderBy('order','asc')->get();
                for($x=0; $x<count($enroll->topics[$a]->parent); $x++) {

                    $enroll->topics[$a]->parent[$x]->enroll2topic = $enroll->topics[$a]->parent[$x]->enroll2topic()->where('enroll_id', $enroll->id)->first();
                    $enroll->topics[$a]->parent[$x]->duration = (strtotime($enroll->topics[$a]->parent[$x]->end_time) - strtotime('TODAY')) - (strtotime($enroll->topics[$a]->parent[$x]->start_time) - strtotime('TODAY'));

                    if($enroll->topics[$a]->parent[$x]->enroll2topic){
                        if($enroll->topics[$a]->parent[$x]->enroll2topic->status){
                            $enroll->topics[$a]->parent[$x]->duration_enroll = $enroll->topics[$a]->parent[$x]->duration;
                        }else{
                            $enroll->topics[$a]->parent[$x]->duration_enroll = $enroll->topics[$a]->parent[$x]->enroll2topic->duration;
                        }
                    }else{
                        $enroll->topics[$a]->parent[$x]->duration_enroll = 0;
                    }

                    $enroll->topics[$a]->parent[$x]->progress = 0;
                    $enroll->topics[$a]->parent[$x]->percentage = 0;

                    if ($enroll->topics[$a]->parent[$x]->duration_enroll != 0) {
                        $enroll->topics[$a]->parent[$x]->progress = @($enroll->topics[$a]->parent[$x]->duration_enroll/$enroll->topics[$a]->parent[$x]->duration);
                        $enroll->topics[$a]->parent[$x]->percentage = number_format($enroll->topics[$a]->parent[$x]->progress * 100);
                    }

                    $enroll->duration2topic += $enroll->topics[$a]->parent[$x]->duration;
                    $enroll->duration2enroll += $enroll->topics[$a]->parent[$x]->duration_enroll;

                    if ($enroll->topics[$a]->parent[$x]->state == 'vod') {
                        $enroll->duration2progress = $enroll->duration2enroll/$enroll->duration2topic;
                        $enroll->duration2percentage = number_format($enroll->duration2progress * 100);
                    }

                }
            }

            if(100 <= $enroll->duration2percentage){
                $enroll->courses->learning = true;
            }else{
                $enroll->courses->learning = false;
            }

            $chk_post_test = $data->enroll2quiz()->where('type', 4)->whereNotNull('score')->count();
            $chk_exam = $data->enroll2quiz()->where('type', 3)->whereNotNull('score')->count();

            $courses = new Courses;
            $courses = $courses->find($input['cid']);
            $courses->pre_test = $courses->quiz()->where('type', 1)->where('status', 1)->count();
            $courses->post_test = $courses->quiz()->where('type', 4)->where('status', 1)->count();
            $courses->exam = $courses->quiz()->where('type', 3)->where('status', 1)->count();

            if(!$chk_pre_test and $courses->pre_test){ $navigator = "exam/pre-test"; }
            else if($enroll->courses->learning == false){
                $enroll2topic = $enroll->enroll2topic()->whereNotNull('status')->orderBy('id', 'desc')->first();
                if($enroll2topic){
                    $enroll2topic->topics = $enroll2topic->topics()->whereNotNull('quiz_id')->first();
                    if($enroll2topic->topics){
                        $enroll2topic->quiz = $enroll2topic->topics->quiz()->where('status', 1)->first();
                        $enroll2topic->enroll2quiz = $enroll->enroll2quiz()->where('quiz_id', $enroll2topic->topics->quiz_id)->whereNotNull('score')->first();
                        if($enroll2topic->enroll2quiz){
                            $navigator = "course";
                        }else{
                            $navigator = "exam/".$enroll2topic->topics->quiz_id."/quiz";
                        }
                    }else{
                        $navigator = "course";
                    }
                }else{
                    $navigator = "course";
                }
            }
            else if(!$chk_post_test and $courses->post_test){ $navigator = "exam/post-test"; }
            else if(!$chk_exam and $courses->exam){ $navigator = "exam/exam"; }
            else{ $navigator = "course"; }

            $message = "ยินดีต้อนรับกลับสู่หลักสูตร ".$data_course->code." ".$data_course->title;
            $status = "continue";
            $eid = $data['id'];
            $tid = $data['enroll_type_id'];
            $type = $data['enroll_type'];

            $_site->logs('เข้าเรียน', '{"alert_msg":"success"}', 200, $data_course->id, $_user->id, '{"courses_id":"'.$input['cid'].'"}', $_user->groups_id, $_user->sub_groups_id, $tid, $type);

        }else{

            $_user = session()->get('_user');
            $dataGroup = Groups::find($_user['groups_id']);
            if($dataGroup->internal || $dataGroup->is_connect_regis != 1 || true){

                // Callback
                $data = new Enroll;
                $data->groups_id = $_user->groups_id;
                if($_user->sub_groups_id){
                    $data->sub_groups_id = $_user->sub_groups_id;
                }
                $data->members_id = $_user->id;
                $data->courses_id = $input['cid'];
                $data->enroll_type_id = $input['tid'];
                $data->enroll_type = $input['type'];
                $data->enroll_datetime = date('Y-m-d H:i:s');
                $data->last_datetime = date('Y-m-d H:i:s');
                $is_success = $data->save();

                $courses = new Courses;
                $courses = $courses->find($input['cid']);
                $courses->documents = $courses->documents()->where('status', 1)->count();
                $courses->pre_test = $courses->quiz()->where('type', 1)->where('status', 1)->count();

                if($courses->documents){
                    $navigator = "download";
                }elseif($courses->pre_test){
                    $navigator = "exam/pre-test";
                }else{
                    $navigator = "course";
                }

                $message = "ลงทะเบียนเข้าเรียนหลักสูตร ".$data_course->code." ".$data_course->title." เรียบร้อย";
                $status = "enroll";

                $enroll = Enroll::where('members_id', $_user->id)->where('courses_id', $input['cid'])->first();
                $eid = $enroll->id;
                $tid = $enroll->enroll_type_id;
                $type = $enroll->enroll_type;

                // $_site->logs('ลงทะเบียน', '{"alert_msg":"success"}', 200, $data_course->id, $_user->id, '{"courses_id":"'.$input['cid'].'", "enroll_type_id":"'.$input['tid'].'", "enroll_type":"'.$input['type'].'"}', $_user->groups_id, $_user->sub_groups_id, $tid, $type);
                $_site->logs('ลงทะเบียน', '{"alert_msg":"success"}', 200, $data_course->id, $_user->id, '{"courses_id":"'.$input['cid'].'", "enroll_type_id":"'.$input['tid'].'", "enroll_type":"'.$input['type'].'", "group_internal":'.$dataGroup->internal.', "group_is_connect_regis":'.$dataGroup->is_connect_regis.'}', $_user->groups_id, $_user->sub_groups_id, $tid, $type);


            }else{

                $dateNow = Carbon::now();
                $nowDateTimeFormat = $dateNow->toDateTimeString();
                $nowDateTimeFormatCustom = $dateNow->format('d-m-Y H:i:s');

                /* ===== START R2 (UPDATE ENROLL) ===== */
                $paramEnroll = array(
                    "courseid" => $data_course->id,
                    "userref" => $_user->ref_id,
                    // "userref" => 7000978, // Fix for test (skip bug).
                    "groupid" => $dataGroup->keyset,
                    "compCode" => $_user->company_code,
                    "status" => "C",
                    "enrollDateTime" => $nowDateTimeFormatCustom,
                );

                $results = $_security->encryptAndSignData(json_encode($paramEnroll, JSON_UNESCAPED_UNICODE));

                $oClient = new httpClient();

                try {
                    $response = $oClient->request('POST', config('constants._SET_URL.R2'), [
                        'json' => $results
                    ]);

                    // Callback
                    $data = new Enroll;
                    $data->groups_id = $_user->groups_id;
                    if($_user->sub_groups_id){
                        $data->sub_groups_id = $_user->sub_groups_id;
                    }
                    $data->members_id = $_user->id;
                    $data->courses_id = $input['cid'];
                    $data->enroll_type_id = $input['tid'];
                    $data->enroll_type = $input['type'];
                    $data->enroll_datetime = $nowDateTimeFormat;
                    $data->last_datetime = $nowDateTimeFormat;
                    $is_success = $data->save();

                    $courses = new Courses;
                    $courses = $courses->find($input['cid']);
                    $courses->documents = $courses->documents()->where('status', 1)->count();
                    $courses->pre_test = $courses->quiz()->where('type', 1)->where('status', 1)->count();

                    if($courses->documents){
                        $navigator = "download";
                    }elseif($courses->pre_test){
                        $navigator = "exam/pre-test";
                    }else{
                        $navigator = "course";
                    }

                    $message = "ลงทะเบียนเข้าเรียนหลักสูตร ".$data_course->code." ".$data_course->title." เรียบร้อย";
                    $status = "enroll";

                    $enroll = Enroll::where('members_id', $_user->id)->where('courses_id', $input['cid'])->first();
                    $eid = $enroll->id;
                    $tid = $enroll->enroll_type_id;
                    $type = $enroll->enroll_type;

                    //Logs
                    // $_site->logs('ลงทะเบียน', '{"alert_msg":"success"}', 200, $data_course->id, $_user->id, '{"courses_id":"'.$input['cid'].'", "enroll_type_id":"'.$input['tid'].'", "enroll_type":"'.$input['type'].'"}', $_user->groups_id, $_user->sub_groups_id, $tid, $type);
                    $_site->logs('ลงทะเบียน', '{"alert_msg":"success"}', 200, $data_course->id, $_user->id, '{"courses_id":"'.$input['cid'].'", "enroll_type_id":"'.$input['tid'].'", "enroll_type":"'.$input['type'].'", "group_internal":'.$dataGroup->internal.', "group_is_connect_regis":'.$dataGroup->is_connect_regis.'}', $_user->groups_id, $_user->sub_groups_id, $tid, $type);

                } catch(RequestException $e) {
                    if ($e->hasResponse()) {

                        $_site->logs('ลงทะเบียน', $e->getResponse()->getBody(), $e->getResponse()->getStatusCode(), $data_course->id, $_user->id, '', $_user->groups_id, $_user->sub_groups_id, '', '');
                        return response()->json(json_decode($e->getResponse()->getBody(), true), $e->getResponse()->getStatusCode());

                    } else {

                        return response()->json(["error_msg" => "Internal Server Error (R)"], 500);

                    }
                }
                /* ===== END R2 (UPDATE ENROLL) ===== */

            }

        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'status' => $status, 'eid' => $eid, 'navigator' => $navigator), 200);
    }

    public function enroll($id, _FunctionsController $oFunc){
        // $data = Enroll::find($id);
        /* Check Permision Enroll */
        $_user = session()->get('_user');
        $data = Enroll::where('id', $id)->where('members_id', $_user->id)->first();

        if (!$data) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }
        /* Check Permision Enroll */

        $data->pre_test = $data->enroll2quiz()->where('type', 1)->whereNotNull('score')->orderBy('id','desc')->get();
        $data->pre_chk = $data->enroll2quiz()->where('type', 1)->whereNotNull('score')->orderBy('id', 'desc')->first();
        if($data->pre_chk){
            $data->pre_chk->learning = true;
            // if($data->pre_chk->score){
            //     $data->pre_chk->learning = true;
            // }else{
            //     $data->pre_chk->learning = false;
            // }
        }

        $data->post_test = $data->enroll2quiz()->where('type', 4)->whereNotNull('score')->orderBy('id','desc')->get();
        $data->post_chk = $data->enroll2quiz()->where('type', 4)->whereNotNull('score')->orderBy('id', 'desc')->first();
        if($data->post_chk){
            $data->post_chk->learning = true;
            // if($data->post_chk->score){
            //     $data->post_chk->learning = true;
            // }else{
            //     $data->post_chk->learning = false;
            // }
        }

        $data->exam = $data->enroll2quiz()->where('type', 3)->whereNotNull('score')->orderBy('id','desc')->get();
        $data->exam_chk = $data->enroll2quiz()->where('type', 3)->whereNotNull('score')->orderBy('id', 'desc')->first();
        if($data->exam_chk){
            if($data->exam_chk->score){
                $data->exam_chk->progress = $data->exam_chk->score/$data->exam_chk->count;
            }
            $data->exam_chk->percentage = number_format($data->exam_chk->progress * 100);
            $data->exam_chk->quiz = Quiz::find($data->exam_chk->quiz_id);
            if($data->exam_chk->quiz->passing_score <= $data->exam_chk->percentage){
                $data->exam_chk->learning = true;
            }else{
                $data->exam_chk->learning = false;
            }
        }

        $data->topics = Topics::where('courses_id', $data->courses_id)->whereNull('parent')->where('status', 1)->orderBy('order','asc')->get();
        $data->duration2percentage = 0;
        for($i=0; $i<count($data->topics); $i++) {
            $data->topics[$i]->enroll2parent = Enroll2Topic::where('enroll_id', $data->id)->where('parent_id', $data->topics[$i]->id)->count();

            $data->topics[$i]->parent = Topics::where('parent', $data->topics[$i]->id)->where('status', 1)->orderBy('order','asc')->get();

            for($a=0; $a<count($data->topics[$i]->parent); $a++) {

                $data->topics[$i]->parent[$a]->enroll2topic = $data->topics[$i]->parent[$a]->enroll2topic()->where('enroll_id', $data->id)->first();
                $data->topics[$i]->parent[$a]->duration = (strtotime($data->topics[$i]->parent[$a]->end_time) - strtotime('TODAY')) - (strtotime($data->topics[$i]->parent[$a]->start_time) - strtotime('TODAY'));

                if($data->topics[$i]->parent[$a]->enroll2topic){
                    if($data->topics[$i]->parent[$a]->enroll2topic->status){
                        $data->topics[$i]->parent[$a]->duration_enroll = $data->topics[$i]->parent[$a]->duration;
                    }else{
                        $data->topics[$i]->parent[$a]->duration_enroll = $data->topics[$i]->parent[$a]->enroll2topic->duration;
                    }
                }else{
                    $data->topics[$i]->parent[$a]->duration_enroll = 0;
                }

                $data->topics[$i]->parent[$a]->progress = 0;
                $data->topics[$i]->parent[$a]->percentage = 0;

                // echo number_format(($data->topics[$i]->parent[$a]->duration_enroll/$data->topics[$i]->parent[$a]->duration) * 100);
                // exit;

                if ($data->topics[$i]->parent[$a]->duration_enroll != 0) {
                    $data->topics[$i]->parent[$a]->progress = $data->topics[$i]->parent[$a]->duration_enroll/$data->topics[$i]->parent[$a]->duration;
                    $data->topics[$i]->parent[$a]->percentage = number_format($data->topics[$i]->parent[$a]->progress * 100);
                }

                $data->duration2topic += $data->topics[$i]->parent[$a]->duration;
                $data->duration2enroll += $data->topics[$i]->parent[$a]->duration_enroll;

                if ($data->topics[$i]->parent[$a]->state == 'vod') {
                    $data->duration2progress = $data->duration2enroll/$data->duration2topic;
                    $data->duration2percentage = number_format($data->duration2progress * 100);
                }

                $data->topics[$i]->parent[$a]->quiz = $data->topics[$i]->parent[$a]->quiz()->where('status', 1)->first();

                if ($data->topics[$i]->parent[$a]->live_start_datetime && $data->topics[$i]->parent[$a]->live_end_datetime) {
                    $data->topics[$i]->parent[$a]->live_datetime = $oFunc->thai_date_fullmonth(strtotime($data->topics[$i]->parent[$a]->live_start_datetime)).' เวลา '.date('H:i', strtotime($data->topics[$i]->parent[$a]->live_start_datetime)).' - '.date('H:i', strtotime($data->topics[$i]->parent[$a]->live_end_datetime)).' น.';
                }

            }

        }

        if($data->courses->percentage <= $data->duration2percentage){
            $data->courses->learning = true;
        }else{
            $data->courses->learning = false;
        }

        return response()->json($data, 200);
    }

    public function quiz($enroll_id, $quiz_id){

        // $data = Enroll::find($id);
        /* Check Permision Enroll */
        $_user = session()->get('_user');
        $data = Enroll::where('id', $enroll_id)->where('members_id', $_user->id)->first();

        if (!$data) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }
        /* Check Permision Enroll */

        $data->data =  new Quiz();
        $data->data =  $data->data->find($quiz_id);
        if(count($data->data)){
            if($data->data->random_questions){
                $data->data->questions = $data->data->questions()->inRandomOrder()->where('status', 1)->limit($data->data->limit_questions)->get();
            }else{
                $data->data->questions = $data->data->questions()->where('status', 1)->orderBy('order', 'asc')->limit($data->data->limit_questions)->get();
            }
            $data->data->questions_count = 0;
            for($i=0; $i<count($data->data->questions); $i++) {
                if($data->data->random_answer){
                    $data->data->questions[$i]->answer = $data->data->questions[$i]->answer()->where('status', 1)->inRandomOrder()->get();
                }else{
                    $data->data->questions[$i]->answer = $data->data->questions[$i]->answer()->where('status', 1)->orderBy('order', 'asc')->get();
                }

                if ($data->data->questions[$i]->type == 1 || $data->data->questions[$i]->type == 2) {
                    $data->data->questions_count++;
                }
            }
        }

        $data->quiz = $data->enroll2quiz()->where('type', 2)->whereNotNull('score')->where('quiz_id', $quiz_id)->orderBy('id','desc')->get();
        $data->quiz_chk = $data->enroll2quiz()->where('type', 2)->whereNotNull('score')->where('quiz_id', $quiz_id)->orderBy('id', 'desc')->first();
        if($data->quiz_chk){
            if($data->quiz_chk->score){
                $data->quiz_chk->learning = true;
            }else{
                $data->quiz_chk->learning = false;
            }
        }

        $data->quiz2topic = Topics::where('courses_id', $data->courses_id)->where('quiz_id', $data->data->id)->first();


        return response()->json($data, 200);
    }

    public function enroll2quiz(Request $request, SiteController $_site){

        $input = $request->input();

        /* Check Permision Enroll */
        $_user = session()->get('_user');
        $dataCheck = Enroll::where('id', $input['eid'])->where('members_id', $_user->id)->first();

        if (!$dataCheck) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }
        /* Check Permision Enroll */

        $data_quiz = Quiz::find($input['qid']);
        if($data_quiz->take_new_exam){

            $count_enroll2quiz = Enroll2Quiz::where('enroll_id', $input['eid'])->where('quiz_id', $input['qid'])->whereNotNull('score')->count();

            if($count_enroll2quiz >= $data_quiz->take_new_exam){

                $enroll2quiz = Enroll2Quiz::where('enroll_id', $input['eid'])->where('quiz_id', $input['qid'])->orderBy('id', 'desc')->first();
                $is_success = false;
                $message = "ไม่สามารถทำแบบทดสอบได้ เนื่องจากคุณทำแบบทดสอบไปแล้ว ".$count_enroll2quiz." ครั้ง";
                $alert_msg = $message;
                $status = 401;

            }else{

                $data = new Enroll2Quiz;
                $data->enroll_id = $input['eid'];
                $data->quiz_id = $input['qid'];
                $data->type = $input['type'];
                $data->datetime = date('Y-m-d H:i:s');
                $is_success = $data->save();
                if ($is_success) {
                    $enroll2quiz = Enroll2Quiz::where('enroll_id', $input['eid'])->where('quiz_id', $input['qid'])->orderBy('id', 'desc')->first();
                    $message = "เริ่มทำแบบทดสอบ";
                    $alert_msg = "success";
                    $status = 200;
                } else {
                    $message = "ไม่สามารถทำแบบทดสอบได้";
                    $alert_msg = $message;
                    $status = 401;
                }

            }

        }else{

            $data = new Enroll2Quiz;
            $data->enroll_id = $input['eid'];
            $data->quiz_id = $input['qid'];
            $data->type = $input['type'];
            $data->datetime = date('Y-m-d H:i:s');
            $is_success = $data->save();
            if ($is_success) {
                $enroll2quiz = Enroll2Quiz::where('enroll_id', $input['eid'])->where('quiz_id', $input['qid'])->orderBy('id', 'desc')->first();
                $message = "เริ่มทำแบบทดสอบ";
                $alert_msg = "success";
                $status = 200;
            } else {
                $message = "ไม่สามารถทำแบบทดสอบได้";
                $alert_msg = $message;
                $status = 401;
            }

        }

        $_site->logs('ทำแบบทดสอบ', '{"alert_msg":"'.$alert_msg.'"}', $status, $dataCheck->courses_id, $_user->id, '{"enroll_id":"'.$input['eid'].'", "quiz_id":"'.$input['qid'].'", "type":"'.$input['type'].'"}', $_user->groups_id, $_user->sub_groups_id, '', '');
        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'enroll2quiz' => $enroll2quiz->id), 200);
    }

    public function questions2survey(Request $request, SiteController $_site){
        $input = $request->input();

        /* Check Permision Enroll */
        $_user = session()->get('_user');
        $dataCheck = Enroll::where('id', $input['eid'])->where('members_id', $_user->id)->first();

        if (!$dataCheck) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }
        /* Check Permision Enroll */

        $data = new Enroll2Quiz;
        $data->enroll_id = $input['eid'];
        $data->quiz_id = $input['qid'];
        $data->type = $input['type'];
        $data->datetime = date('Y-m-d H:i:s');
        $is_success = $data->save();
        if ($is_success) {
            $enroll2quiz = Enroll2Quiz::where('enroll_id', $input['eid'])->where('quiz_id', $input['qid'])->orderBy('id', 'desc')->first();
            foreach ($input as $key => $value) {
                if(($key != 'eid') and ($key != 'qid') and ($key != 'type')){
                    if(is_array($input[$key]))
                    {
                        foreach ($input[$key] as $key_arr => $value_arr) {
                            $data = new Questions2Answer;
                            $data->enroll2quiz_id = $enroll2quiz['id'];
                            $data->questions_id = $key;
                            $questions = Questions::find($key);
                            if($questions->type == 2){
                                $data->answer_id = $input[$key][$key_arr];
                                $data->answer_type = 2;
                            }
                            $data->datetime = date('Y-m-d H:i:s');
                            $data->save();
                        }

                    }else{
                        $data = new Questions2Answer;
                        $data->enroll2quiz_id = $enroll2quiz['id'];
                        $data->questions_id = $key;
                        $questions = Questions::find($key);
                        if($questions->type == 1){
                            $data->answer_id = $input[$key];
                            $data->answer_type = 1;
                        }
                        if($questions->type == 3){
                            $data->answer_type = 3;
                            $data->answer_text = $input[$key];
                        }
                        $data->datetime = date('Y-m-d H:i:s');
                        $data->save();

                    }
                }
            }

            $message = "ส่งแบบสอบถามเรียบร้อย";
            $alert_msg = "success";
            $status = 200;

        }else{

            $message = "เกิดข้อผิดพลาด ไม่สามารถส่งแบบสอบถามได้";
            $alert_msg = $message;
            $status = 401;
        }


        $_site->logs('ส่งแบบสอบถาม', '{"alert_msg":"'.$alert_msg.'"}', $status, $dataCheck->courses_id, $_user->id, '{"enroll_id":"'.$input['eid'].'", "quiz_id":"'.$input['qid'].'", "type":"'.$input['type'].'"}', $_user->groups_id, $_user->sub_groups_id, '', '');
        return response()->json(array('is_error' => false, 'message' => $message), 200);
    }

    public function questions2answer(Request $request, SiteController $_site){
        $input = $request->input();

        $enroll2quiz = Enroll2Quiz::find($input['enroll2quiz']);
        /* Check Permision Enroll */
        $_user = session()->get('_user');
        $dataCheck = Enroll::where('id', $enroll2quiz['enroll_id'])->where('members_id', $_user->id)->first();

        if (!$dataCheck) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }
        /* Check Permision Enroll */

        if($input['quiz2topic']){
            $quiz2topic = Enroll2Topic::where('enroll_id',$enroll2quiz->enroll_id)->where('topics_id', $input['quiz2topic'])->first();
            $quiz2topic->status = 1;
            $quiz2topic->datetime = date('Y-m-d H:i:s');
            $quiz2topic->save();
        }

        foreach ($input as $key => $value) {
            if(($key != 'enroll2quiz') and ($key != 'quiz2topic') and ($key != 'questionsCount')){
                if(is_array($input[$key]))
                {
                    foreach ($input[$key] as $key_arr => $value_arr) {
                        $data = new Questions2Answer;
                        $questions2answer_chk = $data->where('enroll2quiz_id', $input['enroll2quiz'])->where('questions_id', $key)->count();
                        if(!$questions2answer_chk){
                            $data->enroll2quiz_id = $input['enroll2quiz'];
                            $data->questions_id = $key;
                            $questions = Questions::find($key);
                            if($questions->type == 2){
                                $answer = Answer::find($input[$key][$key_arr]);
                                $data->answer_id = $input[$key][$key_arr];
                                $data->answer_type = 2;
                                $data->correct = $answer->correct?: null;
                            }
                            $data->datetime = date('Y-m-d H:i:s');
                            $data->save();
                        }
                    }

                    $count_answer2correct = Answer::where('questions_id', $key)->where('correct', 1)->count();
                    $correct_questions2answer = Questions2Answer::where('questions_id', $key)->where('enroll2quiz_id', $input['enroll2quiz'])->where('correct', 1)->count();

                    $data_quiz2score = new Quiz2Score;
                    $quiz2score_chk = $data_quiz2score->where('enroll2quiz_id', $input['enroll2quiz'])->where('questions_id', $key)->count();
                    if(!$quiz2score_chk){
                        $data_quiz2score->enroll2quiz_id = $input['enroll2quiz'];
                        $data_quiz2score->questions_id = $key;
                        if($count_answer2correct == $correct_questions2answer){
                            $data_quiz2score->correct = 1;
                        }
                        $data_quiz2score->datetime = date('Y-m-d H:i:s');
                        $data_quiz2score->save();
                    }

                }else{
                    $data = new Questions2Answer;
                    $questions2answer_chk = $data->where('enroll2quiz_id', $input['enroll2quiz'])->where('questions_id', $key)->count();
                    if(!$questions2answer_chk){
                        $data->enroll2quiz_id = $input['enroll2quiz'];
                        $data->questions_id = $key;
                        $questions = Questions::find($key);
                        if($questions->type == 1){
                            $answer = Answer::find($input[$key]);
                            $data->answer_id = $input[$key];
                            $data->answer_type = 1;
                            $data->correct = $answer->correct?: null;

                            $data_quiz2score = new Quiz2Score;
                            $data_quiz2score->enroll2quiz_id = $input['enroll2quiz'];
                            $data_quiz2score->questions_id = $key;
                            $data_quiz2score->correct = $answer->correct?: null;
                            $data_quiz2score->datetime = date('Y-m-d H:i:s');
                            $data_quiz2score->save();

                        }
                        if($questions->type == 3){
                            $data->answer_type = 3;
                            $data->answer_text = $input[$key];
                        }
                        $data->datetime = date('Y-m-d H:i:s');
                        $data->save();

                    }
                }
            }
        }


        $quiz2score = Quiz2Score::where('enroll2quiz_id', $input['enroll2quiz']);
        $quiz2score_score = $quiz2score->sum('correct');

        $enroll2quiz = Enroll2Quiz::find($input['enroll2quiz']);

        $quiz = new Quiz;
        $quiz = $quiz->find($enroll2quiz->quiz_id);

        // $questions_count = $quiz->limit_questions;
        $questions_count = $input['questionsCount'];
        $enroll2quiz = Enroll2Quiz::find($input['enroll2quiz']);
        $enroll2quiz->score = $quiz2score_score;
        $enroll2quiz->count = $questions_count;
        $enroll2quiz->save();

        $message = "ส่งคำตอบเรียบร้อย";
        $alert_msg = "success";
        $status = 200;

        $_site->logs('ส่งคำตอบ', '{"alert_msg":"'.$alert_msg.'"}', $status, $dataCheck->courses_id, $_user->id, '{"enroll2quiz":"'.$input['enroll2quiz'].'"}', $_user->groups_id, $_user->sub_groups_id, '', '');
        return response()->json(array('is_error' => false, 'message' => $message), 200);
    }

    public function questions2answer_single(Request $request, SiteController $_site){
        $input = $request->input();

        $enroll2quiz = Enroll2Quiz::find($input['enroll2quiz']);
        /* Check Permision Enroll */
        $_user = session()->get('_user');
        $dataCheck = Enroll::where('id', $enroll2quiz['enroll_id'])->where('members_id', $_user->id)->first();

        if (!$dataCheck) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }
        /* Check Permision Enroll */

        foreach ($input as $key => $value) {
            if(($key != 'enroll2quiz') and ($key != 'qid') and ($key != 'quiz2topic') and ($key != 'questionsCount')){
                if(is_array($input[$key]))
                {
                    foreach ($input[$key] as $key_arr => $value_arr) {
                        $data = new Questions2Answer;
                        $questions2answer_chk = $data->where('enroll2quiz_id', $input['enroll2quiz'])->where('questions_id', $key)->where('answer_id', $input[$key][$key_arr])->count();
                        if(!$questions2answer_chk){
                            $data->enroll2quiz_id = $input['enroll2quiz'];
                            $data->questions_id = $key;
                            $questions = Questions::find($key);
                            if($questions->type == 2){
                                $answer = Answer::find($input[$key][$key_arr]);
                                $data->answer_id = $input[$key][$key_arr];
                                $data->answer_type = 2;
                                $data->correct = $answer->correct?: null;
                            }
                            $data->datetime = date('Y-m-d H:i:s');
                            $data->save();
                        }
                    }

                    $count_answer2correct = Answer::where('questions_id', $key)->where('correct', 1)->count();
                    $correct_questions2answer = Questions2Answer::where('questions_id', $key)->where('enroll2quiz_id', $input['enroll2quiz'])->where('correct', 1)->count();

                    $data_quiz2score = new Quiz2Score;
                    $quiz2score_chk = $data_quiz2score->where('enroll2quiz_id', $input['enroll2quiz'])->where('questions_id', $key)->count();
                    if(!$quiz2score_chk){
                        $data_quiz2score->enroll2quiz_id = $input['enroll2quiz'];
                        $data_quiz2score->questions_id = $key;
                        if($count_answer2correct == $correct_questions2answer){
                            $data_quiz2score->correct = 1;
                        }
                        $data_quiz2score->datetime = date('Y-m-d H:i:s');
                        $data_quiz2score->save();
                    }

                }else{

                    $data = new Questions2Answer;
                    $questions2answer_chk = $data->where('enroll2quiz_id', $input['enroll2quiz'])->where('questions_id', $key)->count();
                    if(!$questions2answer_chk){
                        $data->enroll2quiz_id = $input['enroll2quiz'];
                        $data->questions_id = $key;
                        $questions = Questions::find($key);
                        if($questions->type == 1){
                            $answer = Answer::find($input[$key]);
                            $data->answer_id = $input[$key];
                            $data->answer_type = 1;
                            $data->correct = $answer->correct?: null;

                            $data_quiz2score = new Quiz2Score;
                            $data_quiz2score->enroll2quiz_id = $input['enroll2quiz'];
                            $data_quiz2score->questions_id = $key;
                            $data_quiz2score->correct = $answer->correct?: null;
                            $data_quiz2score->datetime = date('Y-m-d H:i:s');
                            $data_quiz2score->save();

                        }
                        if($questions->type == 3){
                            $data->answer_type = 3;
                            $data->answer_text = $input[$key];
                        }
                        $data->datetime = date('Y-m-d H:i:s');
                        $data->save();
                    }

                }
            }
        }


        $quiz2score = Quiz2Score::where('enroll2quiz_id', $input['enroll2quiz']);
        $quiz2score_score = $quiz2score->sum('correct');

        $enroll2quiz = Enroll2Quiz::find($input['enroll2quiz']);

        $quiz = new Quiz;
        $quiz = $quiz->find($enroll2quiz->quiz_id);

        // $questions_count = $quiz->limit_questions;
        $questions_count = $input['questionsCount'];
        $enroll2quiz = Enroll2Quiz::find($input['enroll2quiz']);
        $enroll2quiz->score = $quiz2score_score;
        $enroll2quiz->count = $questions_count;
        $enroll2quiz->save();

        $examQuestions = Questions::find($input['qid']);
        if($examQuestions->type != 3){
            $examQuestions->answer = $examQuestions->answer()->get();
        }else{
            $examQuestions->answer = [];
        }

        $message = "ส่งคำตอบเรียบร้อย";
        $alert_msg = "success";
        $status = 200;

        $_site->logs('ส่งคำตอบ', '{"alert_msg":"'.$alert_msg.'"}', $status, $dataCheck->courses_id, $_user->id, '{"enroll2quiz":"'.$input['enroll2quiz'].'", "questions_id":"'.$input['qid'].'"}', $_user->groups_id, $_user->sub_groups_id, '', '');
        return response()->json(array('is_error' => false, 'message' => $message, 'answer' => $examQuestions->answer), 200);
    }

    public function exam2score($id){

        $data = Enroll2Quiz::find($id);

        /* Check Permision Enroll */
        $_user = session()->get('_user');
        $dataCheck = Enroll::where('id', $data['enroll_id'])->where('members_id', $_user->id)->first();

        if (!$dataCheck) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }
        /* Check Permision Enroll */

        $data->enroll = $data->enroll()->first();
        $data->exam = $data->quiz()->first();
        $data->exam->questions2answer = Questions2Answer::where('enroll2quiz_id', $data->id)->get();
        for($i=0; $i<count($data->exam->questions2answer); $i++) {
            $data->exam->questions2answer[$i]->questions = $data->exam->questions2answer[$i]->questions()->first();
            $data->exam->questions2answer[$i]->questions->answer = $data->exam->questions2answer[$i]->questions->answer()->get();
            for($a=0; $a<count($data->exam->questions2answer[$i]->questions->answer); $a++) {
                $data->exam->questions2answer[$i]->questions->answer[$a]->questions2answer = $data->exam->questions2answer[$i]->questions->answer[$a]->questions2answer()->where('enroll2quiz_id', $data->id)->first();
            }
        }

        $percent = $data->score/$data->count;
        $data->score_percentage = number_format($percent * 100);

        $data->score_sum = $data->score."/".$data->count;
        $data->enroll2quiz = Enroll2Quiz::where('enroll_id', $data->enroll_id)->where('type', $data->type)->get();

        if($data->score_percentage >= $data->exam->passing_score){
            $data->score_text_header = "ผ่าน";
            $data->score_text_subject = "คะแนนของคุณ ".$data->score_sum." คะแนน";
            $data->score_ststus = true;
        }else{
            $data->score_text_header = "ไม่ผ่าน";
            $data->score_text_subject = "คะแนนของคุณ ".$data->score_sum." คะแนน";
            $data->score_ststus = false;
        }



        return response()->json($data, 200);

    }

    public function enroll2topic($id, SiteController $_site){
        /* Check Permision Enroll */
        $_user = session()->get('_user');
        $dataCheck = Enroll::where('id', $id)->where('members_id', $_user->id)->first();

        if (!$dataCheck) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }
        /* Check Permision Enroll */

        $topics = Topics::select('id')->where('courses_id', $dataCheck->courses_id)->where('state', '=', 'live')->get();
        // return response()->json($topics, 200);
        $null_enroll2topic = Enroll2Topic::where('enroll_id', $id)->whereNotIn('topics_id', $topics)->whereNull('status')->get();

        if(count($null_enroll2topic)){

            $data = Enroll2Topic::where('enroll_id', $id)->whereNotIn('topics_id', $topics)->whereNull('status')->orderBy('id', 'asc')->first();
            $data->log = Enroll2Topic::where('enroll_id', $id)->orderBy('id', 'desc')->get();
            $data->topics = Topics::find($data->topics_id);
            $data->topics->startTime = (strtotime($data->topics->start_time) - strtotime('TODAY')) * 1000;
            $data->topics->endTime = (strtotime($data->topics->end_time) - strtotime('TODAY')) * 1000;
            $data->topics->duration = $data->topics->endTime - $data->topics->startTime;
            $data->topics->totalTime = strtotime($data->topics->start_time) - strtotime('TODAY');

            if($data->topics->streaming_url){
                $data->topics->streaming_url = $data->topics->streaming_url;
            }else{
                $data->topics->courses = $data->topics->courses()->first();
                $data->topics->streaming_url = $data->topics->courses->streaming_url;
            }

            if ($data->topics->state == 'vod') {
                $data->topics->streaming_url .= '?wowzaplaystart='.$data->topics->startTime.'&wowzaplayduration='.$data->topics->duration;
            }

            $data->topics->slides = $data->topics->slides_times()->where('status', '=', '1')->has('slides')->orderBy('time', 'asc')->get();
            for($i=0; $i<count($data->topics->slides); $i++) {
                $data->topics->slides[$i]->slides = $data->topics->slides[$i]->slides()->first();
                $data->topics->slides[$i]->picture = $data->topics->slides[$i]->slides->picture;
                $data->topics->slides[$i]->time_convert = (strtotime($data->topics->slides[$i]->time) - strtotime('TODAY'));
            }

        }else{

            $enroll = Enroll::find($id);

            $not_null_enroll2topic = Enroll2Topic::where('enroll_id', $enroll->id)->whereNotNull('status')->get();
            if(count($not_null_enroll2topic)){

                $data = Enroll2Topic::where('enroll_id', $id)->whereNotNull('status')->orderBy('topics_id', 'desc')->first();

                $topics = Topics::find($data->topics_id);
                $next = Topics::where('courses_id', $enroll->courses_id)->where('order', '>', $topics->order)->where('status', 1)->whereNotNull('parent')->orderBy('order', 'asc')->first();

                if($next){
                    $enroll2topic = new Enroll2Topic;
                    $enroll2topic->enroll_id = $enroll->id;
                    $enroll2topic->topics_id = $next->id;
                    $enroll2topic->parent_id = $next->parent;
                    $enroll2topic->duration = '0';
                    $enroll2topic->datetime = date('Y-m-d H:i:s');
                    $is_success = $enroll2topic->save();
                    if ($is_success) {
                        $data = Enroll2Topic::where('enroll_id', $id)->orderBy('id', 'desc')->first();
                        $data->log = Enroll2Topic::where('enroll_id', $id)->orderBy('id', 'desc')->get();

                        $data->topics = Topics::find($data->topics_id);
                        $data->topics->startTime = (strtotime($data->topics->start_time) - strtotime('TODAY')) * 1000;
                        $data->topics->endTime = (strtotime($data->topics->end_time) - strtotime('TODAY')) * 1000;
                        $data->topics->duration = $data->topics->endTime - $data->topics->startTime;
                        $data->topics->totalTime = strtotime($data->topics->start_time) - strtotime('TODAY');

                        if($data->topics->streaming_url){
                            $data->topics->streaming_url = $data->topics->streaming_url;
                        }else{
                            $data->topics->courses = $data->topics->courses()->first();
                            $data->topics->streaming_url = $data->topics->courses->streaming_url;
                        }

                        if ($data->topics->state == 'vod') {
                            $data->topics->streaming_url .= '?wowzaplaystart='.$data->topics->startTime.'&wowzaplayduration='.$data->topics->duration;
                        }

                        // $data->topics->streaming_url = $data->topics->streaming_url.'?wowzaplaystart='.$data->topics->startTime.'&wowzaplayduration='.$data->topics->duration;
                        $data->topics->slides = $data->topics->slides_times()->where('status', '=', '1')->has('slides')->orderBy('time', 'asc')->get();
                        for($i=0; $i<count($data->topics->slides); $i++) {
                            $data->topics->slides[$i]->slides = $data->topics->slides[$i]->slides()->first();
                            $data->topics->slides[$i]->picture = $data->topics->slides[$i]->slides->picture;
                            $data->topics->slides[$i]->time_convert = (strtotime($data->topics->slides[$i]->time) - strtotime('TODAY'));
                        }
                    }
                }else{

                    $data = Enroll2Topic::where('enroll_id', $id)->orderBy('topics_id', 'asc')->first();
                    $data->log = Enroll2Topic::where('enroll_id', $id)->orderBy('topics_id', 'desc')->get();

                    $data->topics = Topics::find($data->topics_id);
                    $data->topics->startTime = (strtotime($data->topics->start_time) - strtotime('TODAY')) * 1000;
                    $data->topics->endTime = (strtotime($data->topics->end_time) - strtotime('TODAY')) * 1000;
                    $data->topics->duration = $data->topics->endTime - $data->topics->startTime;
                    $data->topics->totalTime = strtotime($data->topics->start_time) - strtotime('TODAY');

                    if($data->topics->streaming_url){
                        $data->topics->streaming_url = $data->topics->streaming_url;
                    }else{
                        $data->topics->courses = $data->topics->courses()->first();
                        $data->topics->streaming_url = $data->topics->courses->streaming_url;
                    }

                    if ($data->topics->state == 'vod') {
                        $data->topics->streaming_url .= '?wowzaplaystart='.$data->topics->startTime.'&wowzaplayduration='.$data->topics->duration;
                    }

                    // $data->topics->streaming_url = $data->topics->streaming_url.'?wowzaplaystart='.$data->topics->startTime.'&wowzaplayduration='.$data->topics->duration;
                    $data->topics->slides = $data->topics->slides_times()->where('status', '=', '1')->has('slides')->orderBy('time', 'asc')->get();
                    for($i=0; $i<count($data->topics->slides); $i++) {
                        $data->topics->slides[$i]->slides = $data->topics->slides[$i]->slides()->first();
                        $data->topics->slides[$i]->picture = $data->topics->slides[$i]->slides->picture;
                        $data->topics->slides[$i]->time_convert = (strtotime($data->topics->slides[$i]->time) - strtotime('TODAY'));
                    }

                }


            }else{

                $enroll->topic = Topics::where('courses_id', $enroll->courses_id)->where('status', 1)->whereNotNull('parent')->orderBy('order', 'asc')->first();

                $enroll2topic = new Enroll2Topic;
                $enroll2topic->enroll_id = $enroll->id;
                $enroll2topic->topics_id = $enroll->topic->id;
                $enroll2topic->parent_id = $enroll->topic->parent;
                $enroll2topic->duration = '0';
                $enroll2topic->datetime = date('Y-m-d H:i:s');
                $is_success = $enroll2topic->save();
                if ($is_success) {
                    $data = Enroll2Topic::where('enroll_id', $id)->orderBy('id', 'desc')->first();
                    $data->log = Enroll2Topic::where('enroll_id', $id)->orderBy('id', 'desc')->get();

                    $data->topics = Topics::find($data->topics_id);
                    $data->topics->startTime = (strtotime($data->topics->start_time) - strtotime('TODAY')) * 1000;
                    $data->topics->endTime = (strtotime($data->topics->end_time) - strtotime('TODAY')) * 1000;
                    $data->topics->duration = $data->topics->endTime - $data->topics->startTime;
                    $data->topics->totalTime = strtotime($data->topics->start_time) - strtotime('TODAY');

                    if($data->topics->streaming_url){
                        $data->topics->streaming_url = $data->topics->streaming_url;
                    }else{
                        $data->topics->courses = $data->topics->courses()->first();
                        $data->topics->streaming_url = $data->topics->courses->streaming_url;
                    }

                    if ($data->topics->state == 'vod') {
                        $data->topics->streaming_url .= '?wowzaplaystart='.$data->topics->startTime.'&wowzaplayduration='.$data->topics->duration;
                    }

                    // $data->topics->streaming_url = $data->topics->streaming_url.'?wowzaplaystart='.$data->topics->startTime.'&wowzaplayduration='.$data->topics->duration;
                    $data->topics->slides = $data->topics->slides_times()->where('status', '=', '1')->has('slides')->orderBy('time', 'asc')->get();
                    for($i=0; $i<count($data->topics->slides); $i++) {
                        $data->topics->slides[$i]->slides = $data->topics->slides[$i]->slides()->first();
                        $data->topics->slides[$i]->picture = $data->topics->slides[$i]->slides->picture;
                        $data->topics->slides[$i]->time_convert = (strtotime($data->topics->slides[$i]->time) - strtotime('TODAY'));
                    }
                }

            }

        }

        if ($data->topics->video) {
            $data->topics->subtitles_url = config('constants._BASE_API_URL')."site/videos/".$data->topics->video->id."/subtitles/file";
        } else {
            $data->topics->subtitles_url = null;
        }

        return response()->json($data, 200);
    }

    public function enroll2topic_duration(Request $request){

        $input = $request->input();

        $data = Enroll2Topic::find($input['id']);

        /* Check Permision Enroll */
        $_user = session()->get('_user');
        $dataCheck = Enroll::where('id', $data['enroll_id'])->where('members_id', $_user->id)->first();

        if (!$dataCheck) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }
        /* Check Permision Enroll */

        $data->duration = $input['duration'];
        $data->datetime = date('Y-m-d H:i:s');
        $is_success = $data->save();
        return response()->json($is_success, 200);
    }

    public function enroll2topic_live_duration(Request $request){

        $input = $request->input();

        $data = Enroll2TopicLive::find($input['id']);

        /* Check Permision Enroll */
        $_user = session()->get('_user');
        // $dataCheck = Enroll::where('members_id', $_user->id)->first();
        $dataCheck = Enroll::where('id', $data['enroll_id'])->where('members_id', $_user->id)->first();

        if (!$dataCheck) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }
        /* Check Permision Enroll */

        // $data->fill($input);
        $data->duration = $input['duration'];
        // $data->datetime = date('Y-m-d H:i:s');
        $is_success = $data->save();
        return response()->json($is_success, 200);
    }

    public function enroll2topic_status(Request $request){

        $input = $request->input();
        $data = Enroll2Topic::find($input['id']);

        /* Check Permision Enroll */
        $_user = session()->get('_user');
        $dataCheck = Enroll::where('id', $data['enroll_id'])->where('members_id', $_user->id)->first();

        if (!$dataCheck) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }
        /* Check Permision Enroll */

        $topics = Topics::find($data->topics_id);
        $topics->courses = $topics->courses()->first();

        if(!$topics->auto_quiz){
            $data->status = $input['status'];
            $data->datetime = date('Y-m-d H:i:s');
            $data->save();
        }else{
            // $data->duration = NULL;
            $data->duration = 0;
            $data->datetime = date('Y-m-d H:i:s');
            $data->save();
        }

        if($topics->quiz_id){
            $topics->quiz = $topics->quiz()->where('status', 1)->first();
            if($topics->auto_quiz){
                $message = [
                    "header" => "จบบทเรียน ".$topics->title,
                    "description" => "",
                    "btn1" => "แบบทดสอบระหว่างบทเรียน",
                    "btn2" => "ต้องการเรียนซ้ำ",
                    "qid" => $topics->quiz->id,
                    "auto_quiz" => true,
                ];
                $chk = "quiz";
            }else{
                $message = [
                    "header" => "จบบทเรียน ".$topics->title,
                    "description" => "",
                    "btn1" => "แบบทดสอบระหว่างบทเรียน",
                    "btn2" => "ต้องการเรียนซ้ำ",
                    "qid" => $topics->quiz->id,
                    "auto_quiz" => false,
                ];
                $chk = "quiz";
            }
        }else{

            $topics->courses = $topics->courses()->first();

            $data->topics = Topics::where('courses_id', $topics->courses->id)->whereNull('parent')->where('status', 1)->orderBy('order','asc')->get();
            $data->duration2percentage = 0;
            for($i=0; $i<count($data->topics); $i++) {
                $data->topics[$i]->parent = Topics::where('parent', $data->topics[$i]->id)->where('status', 1)->orderBy('order','asc')->get();
                for($a=0; $a<count($data->topics[$i]->parent); $a++) {

                    $data->topics[$i]->parent[$a]->enroll2topic = $data->topics[$i]->parent[$a]->enroll2topic()->where('enroll_id', $data->enroll_id)->first();
                    $data->topics[$i]->parent[$a]->duration = (strtotime($data->topics[$i]->parent[$a]->end_time) - strtotime('TODAY')) - (strtotime($data->topics[$i]->parent[$a]->start_time) - strtotime('TODAY'));

                    if($data->topics[$i]->parent[$a]->enroll2topic){
                        if($data->topics[$i]->parent[$a]->enroll2topic->status){
                            $data->topics[$i]->parent[$a]->duration_enroll = $data->topics[$i]->parent[$a]->duration;
                        }else{
                            $data->topics[$i]->parent[$a]->duration_enroll = $data->topics[$i]->parent[$a]->enroll2topic->duration;
                        }
                    }else{
                        $data->topics[$i]->parent[$a]->duration_enroll = 0;
                    }

                    $data->topics[$i]->parent[$a]->progress = 0;
                    $data->topics[$i]->parent[$a]->percentage = 0;

                    if ($data->topics[$i]->parent[$a]->duration_enroll != 0) {
                        $data->topics[$i]->parent[$a]->progress = $data->topics[$i]->parent[$a]->duration_enroll/$data->topics[$i]->parent[$a]->duration;
                        $data->topics[$i]->parent[$a]->percentage = number_format($data->topics[$i]->parent[$a]->progress * 100);
                    }

                    // $data->topics[$i]->parent[$a]->progress = $data->topics[$i]->parent[$a]->duration_enroll/$data->topics[$i]->parent[$a]->duration;
                    // $data->topics[$i]->parent[$a]->percentage = number_format($data->topics[$i]->parent[$a]->progress * 100);

                    $data->duration2topic += $data->topics[$i]->parent[$a]->duration;
                    $data->duration2enroll += $data->topics[$i]->parent[$a]->duration_enroll;

                    if ($data->topics[$i]->parent[$a]->state == 'vod') {
                        $data->duration2progress = $data->duration2enroll/$data->duration2topic;
                        $data->duration2percentage = number_format($data->duration2progress * 100);
                    }

                }

            }


            if(100 <= $data->duration2percentage){

                $topics->courses->post_test = $topics->courses->quiz()->where('type', 4)->where('status', 1)->count();
                $topics->courses->exam = $topics->courses->quiz()->where('type', 3)->where('status', 1)->count();

                if($topics->courses->post_test){

                    $message = [
                        "header" => "ท่านเรียนหลักสูตร ".$topics->courses->code." ".$topics->courses->title." แล้ว",
                        "description" => "",
                        "btn1" => "แบบทดสอบหลังเรียน (Post-Test)",
                        "btn2" => "ต้องการเรียนซ้ำ"

                    ];
                    $chk = "post-test";

                }else if($topics->courses->exam){

                    $message = [
                        "header" => "ท่านเรียนหลักสูตร ".$topics->courses->code." ".$topics->courses->title." แล้ว",
                        "description" => "",
                        "btn1" => "แบบทดสอบเพื่อวัดความรู้ (Examination)",
                        "btn2" => "ต้องการเรียนซ้ำ"

                    ];
                    $chk = "examination";

                }else{
                    $message = [
                        "header" => "ท่านเรียนหลักสูตร ".$topics->courses->code." ".$topics->courses->title." แล้ว",
                        "description" => "",
                        "btn1" => "ผลการเรียน",
                        "btn2" => "ต้องการเรียนซ้ำ"

                    ];
                    $chk = "success";
                }


            }else{
                $topics->next_chk = Enroll2Topic::where('enroll_id', $data->enroll_id)->whereNull('status')->orderBy('id', 'asc')->get();
                if(count($topics->next_chk)){
                    $message = [
                        "header" => "จบบทเรียน ".$topics->title,
                        "description" => "",
                        "btn1" => "ต้องการเรียนบทอื่นต่อ",
                        "btn2" => "ต้องการเรียนซ้ำ"
                    ];
                    $chk = "return";
                }else{

                    $topics->next = Topics::where('courses_id', $topics->courses_id)->where('order', '>', $topics->order)->where('status', 1)->whereNotNull('parent')->orderBy('order', 'asc')->first();
                    $message = [
                        "header" => "จบบทเรียน ".$topics->title,
                        "description" => "",
                        "btn1" => "ต้องการเรียนบทถัดไป",
                        "btn2" => "ต้องการเรียนซ้ำ"
                    ];
                    $chk = "next";

                }
            }

        }

        return response()->json(array('topics' => $topics, 'message' => $message, 'chk' => $chk), 200);
    }

    public function enroll2topic_stage(Request $request){
        $input = $request->input();

        /* Check Permision Enroll */
        $_user = session()->get('_user');
        $dataCheck = Enroll::where('id', $input['enroll'])->where('members_id', $_user->id)->first();

        if (!$dataCheck) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }
        /* Check Permision Enroll */

        $enroll2topic = Enroll2Topic::where('enroll_id', $input['enroll'])->where('topics_id', $input['topics'])->count();
        $topics = Topics::find($input['topics']);
        if(!$enroll2topic){
            $data = new Enroll2Topic;
            $data->enroll_id = $input['enroll'];
            $data->topics_id = $input['topics'];
            $data->parent_id = $topics->parent;
            $data->duration = '0';
            $data->datetime = date('Y-m-d H:i:s');
            $is_success = $data->save();
        }else{
            $is_success = false;
        }
        return response()->json(array('is_error' => !$is_success, 'id' => $input['topics']), 200);
    }

    public function enroll2topic_skip($id, $topics_id){

        // $enroll = Enroll::find($id);
        /* Check Permision Enroll */
        $_user = session()->get('_user');
        $enroll = Enroll::with('courses')->where('id', $id)->where('members_id', $_user->id)->first();

        if (!$enroll) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }
        /* Check Permision Enroll */

        $topics = Topics::where('id', $topics_id)->where('courses_id', $enroll->courses_id)->first();
        if($topics){

            if ($topics->state == 'vod') {
                $enroll2topic = Enroll2Topic::where('enroll_id', $id)->where('topics_id', $topics_id)->count();
                if(!$enroll2topic){

                    if ($enroll->courses->not_skip == 1) {
                        return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Topic", config('constants._errorMessage._404'))), 404);
                    }

                    $data = new Enroll2Topic;
                    $data->enroll_id = $id;
                    $data->topics_id = $topics->id;
                    $data->parent_id = $topics->parent;
                    $data->duration = '0';
                    $data->datetime = date('Y-m-d H:i:s');
                    $is_success = $data->save();
                    if ($is_success) {
                        $data = Enroll2Topic::where('enroll_id', $id)->where('topics_id', $topics_id)->first();
                        $data->log = Enroll2Topic::where('enroll_id', $id)->orderBy('id', 'desc')->get();

                        $data->topics = Topics::find($data->topics_id);
                        $data->topics->startTime = (strtotime($data->topics->start_time) - strtotime('TODAY')) * 1000;
                        $data->topics->endTime = (strtotime($data->topics->end_time) - strtotime('TODAY')) * 1000;
                        $data->topics->duration = $data->topics->endTime - $data->topics->startTime;
                        $data->topics->totalTime = strtotime($data->topics->start_time) - strtotime('TODAY');

                        if ($data->topics->streaming_url) {
                            $data->topics->streaming_url = $data->topics->streaming_url;
                        } else {
                            $data->topics->courses = $data->topics->courses()->first();
                            $data->topics->streaming_url = $data->topics->courses->streaming_url;
                        }

                        $bandwidths = new Bandwidths();

                        $bandwidths = $bandwidths->where('server_name', env('STREAMING_SERVER_CDN_DOMAIN'));
                        $bandwidths = $bandwidths->latest('id')->first();

                        if ($bandwidths->bandwidth_rx >= 2097152) {
                            $data->topics->streaming_url = str_replace(env('STREAMING_SERVER_CDN'), env('STREAMING_SERVER_CDN_EXTERNAL'), $data->topics->streaming_url);
                        }

                        if ($data->topics->state == 'vod') {

                            $data->topics->streaming_url .= '?wowzaplaystart='.$data->topics->startTime.'&wowzaplayduration='.$data->topics->duration;
                        }

                        $data->topics->slides = $data->topics->slides_times()->where('status', '=', '1')->has('slides')->orderBy('time', 'asc')->get();
                        for($i=0; $i<count($data->topics->slides); $i++) {
                            $data->topics->slides[$i]->slides = $data->topics->slides[$i]->slides()->first();
                            $data->topics->slides[$i]->picture = $data->topics->slides[$i]->slides->picture;
                            $data->topics->slides[$i]->time_convert = (strtotime($data->topics->slides[$i]->time) - strtotime('TODAY'));
                        }

                        if ($data->topics->video) {
                            $data->topics->subtitles_url = config('constants._BASE_API_URL')."site/videos/".$data->topics->video->id."/subtitles/file";
                        } else {
                            $data->topics->subtitles_url = null;
                        }

                        return response()->json($data, 200);
                    }

                }else{

                    $data = Enroll2Topic::where('enroll_id', $id)->where('topics_id', $topics_id)->first();
                    $data->log = Enroll2Topic::where('enroll_id', $id)->orderBy('id', 'desc')->get();

                    $data->topics = Topics::find($data->topics_id);
                    $data->topics->startTime = (strtotime($data->topics->start_time) - strtotime('TODAY')) * 1000;
                    $data->topics->endTime = (strtotime($data->topics->end_time) - strtotime('TODAY')) * 1000;
                    $data->topics->duration = $data->topics->endTime - $data->topics->startTime;
                    $data->topics->totalTime = strtotime($data->topics->start_time) - strtotime('TODAY');

                    if($data->topics->streaming_url){
                        $data->topics->streaming_url = $data->topics->streaming_url;
                    }else{
                        $data->topics->courses = $data->topics->courses()->first();
                        $data->topics->streaming_url = $data->topics->courses->streaming_url;
                    }

                    $bandwidths = new Bandwidths();

                    $bandwidths = $bandwidths->where('server_name', env('STREAMING_SERVER_CDN_DOMAIN'));
                    $bandwidths = $bandwidths->latest('id')->first();

                    if (isset($bandwidths) && $bandwidths->bandwidth_rx >= 2097152) {
                        $data->topics->streaming_url = str_replace(env('STREAMING_SERVER_CDN'), env('STREAMING_SERVER_CDN_EXTERNAL'), $data->topics->streaming_url);
                    }

                    if ($data->topics->state == 'vod') {

                        $data->topics->streaming_url .= '?wowzaplaystart='.$data->topics->startTime.'&wowzaplayduration='.$data->topics->duration;
                    }

                    // $data->topics->streaming_url = $data->topics->streaming_url.'?wowzaplaystart='.$data->topics->startTime.'&wowzaplayduration='.$data->topics->duration;
                    $data->topics->slides = $data->topics->slides_times()->where('status', '=', '1')->has('slides')->orderBy('time', 'asc')->get();
                    for($i=0; $i<count($data->topics->slides); $i++) {
                        $data->topics->slides[$i]->slides = $data->topics->slides[$i]->slides()->first();
                        $data->topics->slides[$i]->picture = $data->topics->slides[$i]->slides->picture;
                        $data->topics->slides[$i]->time_convert = (strtotime($data->topics->slides[$i]->time) - strtotime('TODAY'));
                    }

                    if ($data->topics->video) {
                        $data->topics->subtitles_url = config('constants._BASE_API_URL')."site/videos/".$data->topics->video->id."/subtitles/file";
                    } else {
                        $data->topics->subtitles_url = null;
                    }

                    return response()->json($data, 200);

                }
            } else if ($topics->state == 'live') {
                $data = new Enroll2TopicLive;
                $data->enroll_id = $id;
                $data->topics_id = $topics->id;
                $data->parent_id = $topics->parent;
                $data->duration = '0';
                $data->enter_datetime = date('Y-m-d H:i:s');
                $is_success = $data->save();
                if ($is_success) {
                    $data = Enroll2TopicLive::find($data->id);
                    $data->log = Enroll2TopicLive::where('enroll_id', $id)->orderBy('id', 'desc')->get();

                    $data->topics = Topics::find($data->topics_id);
                    $data->topics->startTime = (strtotime($data->topics->start_time) - strtotime('TODAY')) * 1000;
                    $data->topics->endTime = (strtotime($data->topics->end_time) - strtotime('TODAY')) * 1000;
                    $data->topics->duration = $data->topics->endTime - $data->topics->startTime;
                    $data->topics->totalTime = strtotime($data->topics->start_time) - strtotime('TODAY');

                    if ($data->topics->streaming_url) {
                        $data->topics->streaming_url = $data->topics->streaming_url;
                    } else {
                        $data->topics->courses = $data->topics->courses()->first();
                        $data->topics->streaming_url = $data->topics->courses->streaming_url;
                    }

                    $bandwidths = new Bandwidths();

                    $bandwidths = $bandwidths->where('server_name', env('STREAMING_SERVER_CDN_DOMAIN'));
                    $bandwidths = $bandwidths->latest('id')->first();

                    if (isset($bandwidths) && $bandwidths->bandwidth_rx >= 2097152) {
                        $data->topics->streaming_url = str_replace(env('STREAMING_SERVER_CDN'), env('STREAMING_SERVER_CDN_EXTERNAL'), $data->topics->streaming_url);
                    }

                    if ($data->topics->state == 'vod') {
                        $data->topics->streaming_url .= '?wowzaplaystart='.$data->topics->startTime.'&wowzaplayduration='.$data->topics->duration;
                        $data->topics->slides = $data->topics->slides_times()->where('status', '=', '1')->has('slides')->orderBy('time', 'asc')->get();
                        for($i=0; $i<count($data->topics->slides); $i++) {
                            $data->topics->slides[$i]->slides = $data->topics->slides[$i]->slides()->first();
                            $data->topics->slides[$i]->picture = $data->topics->slides[$i]->slides->picture;
                            $data->topics->slides[$i]->time_convert = (strtotime($data->topics->slides[$i]->time) - strtotime('TODAY'));
                        }
                    } else if ($data->topics->state == 'live') {
                        $slides = Slides::where('courses_id', $data->topics->courses_id)->orderBy('order', 'asc')->get();
                        $data->topics->slides = $slides;
                        for($i=0; $i<count($data->topics->slides); $i++) {
                            $data->topics->slides[$i]->time_convert = 0;
                        }
                    }

                    $data->topics->video = null;

                    return response()->json($data, 200);
                }
            }

        }

    }

    public function enroll2topic_live_skip($id, $topics_id){

        /* Check Permision Enroll */
        $_user = session()->get('_user');
        $enroll = Enroll::where('id', $id)->where('members_id', $_user->id)->first();

        if (!$enroll) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }
        /* Check Permision Enroll */

        $topics = Topics::where('id', $topics_id)->where('courses_id', $enroll->courses_id)->first();
        if($topics){
            $data = new Enroll2TopicLive;
            $data->enroll_id = $id;
            $data->topics_id = $topics->id;
            $data->parent_id = $topics->parent;
            $data->duration = '0';
            $data->enter_datetime = date('Y-m-d H:i:s');
            $is_success = $data->save();
            if ($is_success) {
                $data = Enroll2TopicLive::find($data->id);
                $data->log = Enroll2TopicLive::where('enroll_id', $id)->orderBy('id', 'desc')->get();

                $data->topics = Topics::find($data->topics_id);
                $data->topics->startTime = (strtotime($data->topics->start_time) - strtotime('TODAY')) * 1000;
                $data->topics->endTime = (strtotime($data->topics->end_time) - strtotime('TODAY')) * 1000;
                $data->topics->duration = $data->topics->endTime - $data->topics->startTime;
                $data->topics->totalTime = strtotime($data->topics->start_time) - strtotime('TODAY');

                if ($data->topics->streaming_url) {
                    $data->topics->streaming_url = $data->topics->streaming_url;
                } else {
                    $data->topics->courses = $data->topics->courses()->first();
                    $data->topics->streaming_url = $data->topics->courses->streaming_url;
                }

                if ($data->topics->state == 'vod') {
                    $data->topics->streaming_url .= '?wowzaplaystart='.$data->topics->startTime.'&wowzaplayduration='.$data->topics->duration;
                    $data->topics->slides = $data->topics->slides_times()->where('status', '=', '1')->has('slides')->orderBy('time', 'asc')->get();
                    for($i=0; $i<count($data->topics->slides); $i++) {
                        $data->topics->slides[$i]->slides = $data->topics->slides[$i]->slides()->first();
                        $data->topics->slides[$i]->picture = $data->topics->slides[$i]->slides->picture;
                        $data->topics->slides[$i]->time_convert = (strtotime($data->topics->slides[$i]->time) - strtotime('TODAY'));
                    }
                } else if ($data->topics->state == 'live') {
                    $slides = Slides::where('courses_id', $data->topics->courses_id)->orderBy('order', 'asc')->get();
                    $data->topics->slides = $slides;
                    for($i=0; $i<count($data->topics->slides); $i++) {
                        $data->topics->slides[$i]->time_convert = 0;
                    }
                }

                $data->topics->video = null;

                return response()->json($data, 200);
            }
        }
    }

    public function enroll2summary($id){
        // $data = Enroll::find($id);
        /* Check Permision Enroll */
        $_user = session()->get('_user');
        $data = Enroll::where('id', $id)->where('members_id', $_user->id)->first();

        if (!$data) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }
        /* Check Permision Enroll */

        $data->courses = Courses::find($data->courses_id);

        $data->topics = Topics::where('courses_id', $data->courses_id)->whereNull('parent')->where('status', 1)->orderBy('order','asc')->get();
        $data->duration2percentage = 0;
        for($i=0; $i<count($data->topics); $i++) {
            $data->topics[$i]->parent = Topics::where('parent', $data->topics[$i]->id)->where('status', 1)->orderBy('order','asc')->get();
            for($a=0; $a<count($data->topics[$i]->parent); $a++) {

                $data->topics[$i]->parent[$a]->enroll2topic = $data->topics[$i]->parent[$a]->enroll2topic()->where('enroll_id', $data->id)->first();
                $data->topics[$i]->parent[$a]->duration = (strtotime($data->topics[$i]->parent[$a]->end_time) - strtotime('TODAY')) - (strtotime($data->topics[$i]->parent[$a]->start_time) - strtotime('TODAY'));

                if($data->topics[$i]->parent[$a]->enroll2topic){
                    if($data->topics[$i]->parent[$a]->enroll2topic->status){
                        $data->topics[$i]->parent[$a]->duration_enroll = $data->topics[$i]->parent[$a]->duration;
                    }else{
                        $data->topics[$i]->parent[$a]->duration_enroll = $data->topics[$i]->parent[$a]->enroll2topic->duration;
                    }
                }else{
                    $data->topics[$i]->parent[$a]->duration_enroll = 0;
                }

                // $data->topics[$i]->parent[$a]->progress = $data->topics[$i]->parent[$a]->duration_enroll/$data->topics[$i]->parent[$a]->duration;
                // $data->topics[$i]->parent[$a]->percentage = number_format($data->topics[$i]->parent[$a]->progress * 100);

                $data->topics[$i]->parent[$a]->progress = 0;
                $data->topics[$i]->parent[$a]->percentage = 0;

                if ($data->topics[$i]->parent[$a]->duration_enroll != 0) {
                    $data->topics[$i]->parent[$a]->progress = $data->topics[$i]->parent[$a]->duration_enroll/$data->topics[$i]->parent[$a]->duration;
                    $data->topics[$i]->parent[$a]->percentage = number_format($data->topics[$i]->parent[$a]->progress * 100);
                }

                $data->duration2topic += $data->topics[$i]->parent[$a]->duration;
                $data->duration2enroll += $data->topics[$i]->parent[$a]->duration_enroll;

                if ($data->topics[$i]->parent[$a]->state == 'vod') {
                    $data->duration2progress = $data->duration2enroll/$data->duration2topic;
                    $data->duration2percentage = number_format($data->duration2progress * 100);
                }

                $data->topics[$i]->parent[$a]->quiz = $data->topics[$i]->parent[$a]->quiz()->where('status', 1)->first();
                $data->topics[$i]->parent[$a]->enroll2quiz = $data->enroll2quiz()->where('quiz_id', $data->topics[$i]->parent[$a]->quiz['id'])->where('type', 2)->whereNotNull('score')->orderBy('id', 'desc')->first();


            }

        }

        if($data->courses->percentage <= $data->duration2percentage){
            $data->courses->learning = true;
        }else{
            $data->courses->learning = false;
        }

        $data->pre_test = $data->enroll2quiz()->where('type', 1)->whereNotNull('score')->orderBy('id', 'desc')->first();
        if($data->pre_test){

            if($data->pre_test->score){
                $data->pre_test->progress = $data->pre_test->score/$data->pre_test->count;
            }
            $data->pre_test->percentage = number_format($data->pre_test->progress * 100);
            $data->pre_test->quiz = Quiz::find($data->pre_test->quiz_id);
            if($data->pre_test->quiz->passing_score <= $data->pre_test->percentage){
                $data->pre_test->learning = true;
            }else{
                $data->pre_test->learning = false;
            }
        }

        $data->post_test = $data->enroll2quiz()->where('type', 4)->whereNotNull('score')->orderBy('id', 'desc')->first();
        if($data->post_test){

            if($data->post_test->score){
                $data->post_test->progress = $data->post_test->score/$data->post_test->count;
            }
            $data->post_test->percentage = number_format($data->post_test->progress * 100);
            $data->post_test->quiz = Quiz::find($data->post_test->quiz_id);
            if($data->post_test->quiz->passing_score <= $data->post_test->percentage){
                $data->post_test->learning = true;
            }else{
                $data->post_test->learning = false;
            }
        }

        $countQuiz = $data->courses->quiz()->where('type', 3)->where('status', 1)->count();
        $data->exam = $data->enroll2quiz()->where('type', 3)->whereNotNull('score')->orderBy('id', 'desc')->first();
        if($data->exam){

            if($data->exam->score){
                $data->exam->progress = $data->exam->score/$data->exam->count;
            }
            $data->exam->percentage = number_format($data->exam->progress * 100);
            $data->exam->quiz = Quiz::find($data->exam->quiz_id);
            if($data->exam->quiz->passing_score <= $data->exam->percentage){
                $data->exam->learning = true;
            }else{
                $data->exam->learning = false;
            }
        }

        $data->survey = $data->enroll2quiz()->where('type', 5)->orderBy('id', 'desc')->first();
        if($data->survey){
            $data->survey = true;
        }else{
            $data->survey = false;
        }

        if($data->exam){
            if(($data->courses->learning == true) and ($data->exam->learning == true)){
                $data->certificate = true;
            }else{
                $data->certificate = false;
            }
        }else if($data->courses->learning == true && $countQuiz == 0){
            $data->certificate = true;
        }else{
            $data->certificate = false;
        }

        return response()->json($data, 200);
    }

    public function my2enroll_original(){

        ini_set('max_execution_time', 600);

        $_user = session()->get('_user');
        $data = Enroll::where('members_id', $_user->id)->get();
        for($i=0; $i<count($data); $i++) {
            $flagIgnore = false;
            $data[$i]->courses = $data[$i]->courses()->first();
            $data[$i]->courses->topics = $data[$i]->courses->topics()->where('status', 1)->get();
            $data[$i]->duration2percentage = 0;
            for($a=0; $a<count($data[$i]->courses->topics); $a++) {
                $data[$i]->courses->topics[$a]->parent = Topics::where('parent', $data[$i]->courses->topics[$a]->id)->where('status', 1)->orderBy('order','asc')->get();
                for($x=0; $x<count($data[$i]->courses->topics[$a]->parent); $x++) {
                    $data[$i]->courses->topics[$a]->parent[$x]->enroll2topic = $data[$i]->courses->topics[$a]->parent[$x]->enroll2topic()->where('enroll_id', $data[$i]->id)->first();
                    $data[$i]->courses->topics[$a]->parent[$x]->duration = (strtotime($data[$i]->courses->topics[$a]->parent[$x]->end_time) - strtotime('TODAY')) - (strtotime($data[$i]->courses->topics[$a]->parent[$x]->start_time) - strtotime('TODAY'));

                    if ($data[$i]->courses->topics[$a]->parent[$x]->start_time == $data[$i]->courses->topics[$a]->parent[$x]->end_time && $data[$i]->courses->topics[$a]->parent[$x]->state == 'vod') {
                        $flagIgnore = true;
                        break;
                    }

                    if($data[$i]->courses->topics[$a]->parent[$x]->enroll2topic){
                        if($data[$i]->courses->topics[$a]->parent[$x]->enroll2topic->status){
                            $data[$i]->courses->topics[$a]->parent[$x]->duration_enroll = $data[$i]->courses->topics[$a]->parent[$x]->duration;
                        }else{
                            $data[$i]->courses->topics[$a]->parent[$x]->duration_enroll = $data[$i]->courses->topics[$a]->parent[$x]->enroll2topic->duration;
                        }
                    }else{
                        $data[$i]->courses->topics[$a]->parent[$x]->duration_enroll = 0;
                    }

                    $$data[$i]->courses->topics[$a]->parent[$x]->progress = 0;
                    $data[$i]->courses->topics[$a]->parent[$x]->percentage = 0;

                    if ($data[$i]->courses->topics[$a]->parent[$x]->duration_enroll != 0) {
                        $data[$i]->courses->topics[$a]->parent[$x]->progress = $data[$i]->courses->topics[$a]->parent[$x]->duration_enroll/$data[$i]->courses->topics[$a]->parent[$x]->duration;
                        $data[$i]->courses->topics[$a]->parent[$x]->percentage = number_format($data[$i]->courses->topics[$a]->parent[$x]->progress * 100);
                    }

                    $data[$i]->duration2topic += $data[$i]->courses->topics[$a]->parent[$x]->duration;
                    $data[$i]->duration2enroll += $data[$i]->courses->topics[$a]->parent[$x]->duration_enroll;

                    if ($data[$i]->courses->topics[$a]->parent[$x]->state == 'vod') {
                        $data[$i]->duration2progress = $data[$i]->duration2enroll/$data[$i]->duration2topic;
                        $data[$i]->duration2percentage = number_format($data[$i]->duration2progress * 100);
                    }


                }

                if ($flagIgnore) {
                    break;
                }
            }

            if ($flagIgnore) {
                $data[$i] = null;
                continue;
            }

            if($data[$i]->courses->percentage <= $data[$i]->duration2percentage){
                $data[$i]->courses->learning = true;
            }else{
                $data[$i]->courses->learning = false;
            }

            $data[$i]->pre_test = $data[$i]->enroll2quiz()->where('type', 1)->whereNotNull('score')->orderBy('id', 'desc')->first();
            if($data[$i]->pre_test){
                if($data[$i]->pre_test->score){
                    $data[$i]->pre_test->progress = $data[$i]->pre_test->score/$data[$i]->pre_test->count;
                }
                $data[$i]->pre_test->percentage = number_format($data[$i]->pre_test->progress * 100);
                $data[$i]->pre_test->quiz = Quiz::find($data[$i]->pre_test->quiz_id);
                if($data[$i]->pre_test->quiz->passing_score <= $data[$i]->pre_test->percentage){
                    $data[$i]->pre_test->learning = true;
                }else{
                    $data[$i]->pre_test->learning = false;
                }
            }

            $data[$i]->post_test = $data[$i]->enroll2quiz()->where('type', 4)->whereNotNull('score')->orderBy('id', 'desc')->first();
            if($data[$i]->post_test){
                if($data[$i]->post_test->score){
                    $data[$i]->post_test->progress = $data[$i]->post_test->score/$data[$i]->post_test->count;
                }
                $data[$i]->post_test->percentage = number_format($data[$i]->post_test->progress * 100);
                $data[$i]->post_test->quiz = Quiz::find($data[$i]->post_test->quiz_id);
                if($data[$i]->post_test->quiz->passing_score <= $data[$i]->post_test->percentage){
                    $data[$i]->post_test->learning = true;
                }else{
                    $data[$i]->post_test->learning = false;
                }
            }

            $countQuiz = $data[$i]->courses->quiz()->where('type', 3)->where('status', 1)->count();
            $data[$i]->exam = $data[$i]->enroll2quiz()->where('type', 3)->whereNotNull('score')->orderBy('id', 'desc')->first();
            if($data[$i]->exam){
                if($data[$i]->exam->score){
                    $data[$i]->exam->progress = $data[$i]->exam->score/$data[$i]->exam->count;
                }
                $data[$i]->exam->percentage = number_format($data[$i]->exam->progress * 100);
                $data[$i]->exam->quiz = Quiz::find($data[$i]->exam->quiz_id);
                if($data[$i]->exam->quiz->passing_score <= $data[$i]->exam->percentage){
                    $data[$i]->exam->learning = true;
                }else{
                    $data[$i]->exam->learning = false;
                }
            }

            $data[$i]->survey = $data[$i]->enroll2quiz()->where('type', 5)->orderBy('id', 'desc')->first();
            if($data[$i]->survey){
                $data[$i]->survey = true;
            }else{
                $data[$i]->survey = false;
            }

            if($data[$i]->exam){
                if(($data[$i]->courses->learning == true) and ($data[$i]->exam->learning == true)){
                    $data[$i]->certificate = true;
                }else{
                    $data[$i]->certificate = false;
                }
            }else if($data[$i]->courses->learning == true && $countQuiz == 0){
                $data[$i]->certificate = true;
            }else{
                $data[$i]->certificate = false;
            }


//            if($data[$i]->exam){
//                if(($data[$i]->courses->learning == true) and ($data[$i]->exam->learning == true)){
//                    $data[$i]->certificate = true;
//                }else{
//                    $data[$i]->certificate = false;
//                }
//            }else{
//                $data[$i]->certificate = false;
//            }

        }

        $filteredData = [];
        for($i=0; $i<count($data); $i++) {
            if (!is_null($data[$i])) {
                $filteredData[] = $data[$i];
            }
        }

        return response()->json($filteredData, 200);
    }

    public function my2enroll_test(){

        // ini_set('max_execution_time', 600);

        // $_user = session()->get('_user');
        $data = Enroll::where('members_id', 32)->get();
        for($i=0; $i<count($data); $i++) {
        // for($i=0; $i < 1; $i++) {
            $flagIgnore = false;
            $data[$i]->courses = $data[$i]->courses()->select('id', 'code', 'title', 'thumbnail', 'download_certificate', 'start_datetime', 'end_datetime')->first();
            // $data[$i]->courses->topics = $data[$i]->courses->topics()->select('id')->where('status', 1)->get();
            $topics_data = $data[$i]->courses->topics()->select('id')->where('status', 1)->get();
            // for($a=0; $a<count($data[$i]->courses->topics); $a++) {
            $data[$i]->duration2percentage = 0;
            for($a=0; $a<count($topics_data); $a++) {
                $topics_data[$a]->parent = Topics::select('id', 'parent', 'start_time', 'end_time', 'status')->where('parent', $topics_data[$a]->id)->where('status', 1)->orderBy('order','asc')->get();
                for($x=0; $x<count($topics_data[$a]->parent); $x++) {
                    $topics_data[$a]->parent[$x]->enroll2topic = $topics_data[$a]->parent[$x]->enroll2topic()->select('id', 'enroll_id', 'duration', 'status')->where('enroll_id', $data[$i]->id)->first();
                    $topics_data[$a]->parent[$x]->duration = (strtotime($topics_data[$a]->parent[$x]->end_time) - strtotime('TODAY')) - (strtotime($topics_data[$a]->parent[$x]->start_time) - strtotime('TODAY'));

                    if ($topics_data[$a]->parent[$x]->start_time == $topics_data[$a]->parent[$x]->end_time && $topics_data[$a]->parent[$x]->state == 'vod') {
                        $flagIgnore = true;
                        break;
                    }

                    if($topics_data[$a]->parent[$x]->enroll2topic){
                        if($topics_data[$a]->parent[$x]->enroll2topic->status){
                            $topics_data[$a]->parent[$x]->duration_enroll = $topics_data[$a]->parent[$x]->duration;
                        }else{
                            $topics_data[$a]->parent[$x]->duration_enroll = $topics_data[$a]->parent[$x]->enroll2topic->duration;
                        }
                    }else{
                        $topics_data[$a]->parent[$x]->duration_enroll = 0;
                    }

                    $topics_data[$a]->parent[$x]->progress = 0;
                    $topics_data[$a]->parent[$x]->percentage = 0;

                    if ($topics_data[$a]->parent[$x]->duration_enroll != 0) {
                        $topics_data[$a]->parent[$x]->progress = $topics_data[$a]->parent[$x]->duration_enroll/$topics_data[$a]->parent[$x]->duration;
                        $topics_data[$a]->parent[$x]->percentage = number_format($topics_data[$a]->parent[$x]->progress * 100);
                    }

                    $data[$i]->duration2topic += $topics_data[$a]->parent[$x]->duration;
                    $data[$i]->duration2enroll += $topics_data[$a]->parent[$x]->duration_enroll;

                    if ($topics_data[$a]->parent[$x]->state == 'vod') {
                        $data[$i]->duration2progress = $data[$i]->duration2enroll/$data[$i]->duration2topic;
                        $data[$i]->duration2percentage = number_format($data[$i]->duration2progress * 100);
                    }


                }

                if ($flagIgnore) {
                    break;
                }
            }

            if ($flagIgnore) {
                $data[$i] = null;
                continue;
            }

            if($data[$i]->courses->percentage <= $data[$i]->duration2percentage){
                $data[$i]->courses->learning = true;
            }else{
                $data[$i]->courses->learning = false;
            }

            // $data[$i]->pre_test = $data[$i]->enroll2quiz()->where('type', 1)->whereNotNull('score')->orderBy('id', 'desc')->first();
            // if($data[$i]->pre_test){
            //     if($data[$i]->pre_test->score){
            //         $data[$i]->pre_test->progress = $data[$i]->pre_test->score/$data[$i]->pre_test->count;
            //     }
            //     $data[$i]->pre_test->percentage = number_format($data[$i]->pre_test->progress * 100);
            //     $data[$i]->pre_test->quiz = Quiz::find($data[$i]->pre_test->quiz_id);
            //     if($data[$i]->pre_test->quiz->passing_score <= $data[$i]->pre_test->percentage){
            //         $data[$i]->pre_test->learning = true;
            //     }else{
            //         $data[$i]->pre_test->learning = false;
            //     }
            // }

            // $data[$i]->post_test = $data[$i]->enroll2quiz()->where('type', 4)->whereNotNull('score')->orderBy('id', 'desc')->first();
            // if($data[$i]->post_test){
            //     if($data[$i]->post_test->score){
            //         $data[$i]->post_test->progress = $data[$i]->post_test->score/$data[$i]->post_test->count;
            //     }
            //     $data[$i]->post_test->percentage = number_format($data[$i]->post_test->progress * 100);
            //     $data[$i]->post_test->quiz = Quiz::find($data[$i]->post_test->quiz_id);
            //     if($data[$i]->post_test->quiz->passing_score <= $data[$i]->post_test->percentage){
            //         $data[$i]->post_test->learning = true;
            //     }else{
            //         $data[$i]->post_test->learning = false;
            //     }
            // }

            $countQuiz = $data[$i]->courses->quiz()->select('id', 'type', 'status')->where('type', 3)->where('status', 1)->count();
            $data[$i]->exam = $data[$i]->enroll2quiz()->select('id', 'type', 'score', 'count', 'quiz_id', 'datetime')->where('type', 3)->whereNotNull('score')->orderBy('id', 'desc')->first();
            if($data[$i]->exam){
                if($data[$i]->exam->score){
                    $data[$i]->exam->progress = $data[$i]->exam->score/$data[$i]->exam->count;
                }
                $data[$i]->exam->percentage = number_format($data[$i]->exam->progress * 100);
                $data[$i]->exam->quiz = Quiz::select('id', 'passing_score')->where('id', $data[$i]->exam->quiz_id)->first();
                if($data[$i]->exam->quiz->passing_score <= $data[$i]->exam->percentage){
                    $data[$i]->exam->learning = true;
                }else{
                    $data[$i]->exam->learning = false;
                }
            }

            // $data[$i]->survey = $data[$i]->enroll2quiz()->where('type', 5)->orderBy('id', 'desc')->first();
            // if($data[$i]->survey){
            //     $data[$i]->survey = true;
            // }else{
            //     $data[$i]->survey = false;
            // }

            if($data[$i]->exam){
                if(($data[$i]->courses->learning == true) and ($data[$i]->exam->learning == true)){
                    $data[$i]->certificate = true;
                }else{
                    $data[$i]->certificate = false;
                }
            }else if($data[$i]->courses->learning == true && $countQuiz == 0){
                $data[$i]->certificate = true;
            }else{
                $data[$i]->certificate = false;
            }


//            if($data[$i]->exam){
//                if(($data[$i]->courses->learning == true) and ($data[$i]->exam->learning == true)){
//                    $data[$i]->certificate = true;
//                }else{
//                    $data[$i]->certificate = false;
//                }
//            }else{
//                $data[$i]->certificate = false;
//            }


        }

        $filteredData = [];
        for($i=0; $i<count($data); $i++) {
            if (!is_null($data[$i])) {
                $filteredData[] = $data[$i];
            }
        }

        return response()->json($filteredData, 200);
    }

    public function my2enroll(Request $request, _FunctionsController $oFunc){

        // ini_set('max_execution_time', 600);

        $_user = session()->get('_user');
        $per_page = 10;
        $search = $request['search'];
        $data = Enroll::where('members_id', $_user->id)
                        ->whereHas('courses', function($query) use ($search) {
                            $query->where('courses.title', 'like', '%'.$search.'%')
                                ->orWhere('code', 'like', '%'.$search.'%')
                                ->orWhere('information', 'like', '%'.$search.'%')
                                ->orWhere('structure', 'like', '%'.$search.'%')
                                ->orWhereHas('instructors', function($query) use ($search) {
                                    $query->where('instructors.title', 'like', '%'.$search.'%');
                                });
                        })
                        ->paginate($per_page);
        for($i=0; $i<count($data); $i++) {
            $flagIgnore = false;
            $data[$i]->courses = $data[$i]->courses()->select('id', 'code', 'title', 'thumbnail', 'information', 'structure', 'download_certificate', 'start_datetime', 'end_datetime')->first();
            $topics_data = $data[$i]->courses->topics()->select('id')->where('status', 1)->get();
            $data[$i]->duration2percentage = 0;
            for($a=0; $a<count($topics_data); $a++) {
                $topics_data[$a]->parent = Topics::select('id', 'parent', 'start_time', 'end_time', 'state', 'status')->where('parent', $topics_data[$a]->id)->where('state', 'vod')->where('status', 1)->orderBy('order','asc')->get();
                for($x=0; $x<count($topics_data[$a]->parent); $x++) {
                    $topics_data[$a]->parent[$x]->enroll2topic = $topics_data[$a]->parent[$x]->enroll2topic()->select('id', 'enroll_id', 'duration', 'status')->where('enroll_id', $data[$i]->id)->first();
                    $topics_data[$a]->parent[$x]->duration = (strtotime($topics_data[$a]->parent[$x]->end_time) - strtotime('TODAY')) - (strtotime($topics_data[$a]->parent[$x]->start_time) - strtotime('TODAY'));

                    if ($topics_data[$a]->parent[$x]->start_time == $topics_data[$a]->parent[$x]->end_time && $topics_data[$a]->parent[$x]->state == 'vod') {
                        $flagIgnore = true;
                        break;
                    }

                    if($topics_data[$a]->parent[$x]->enroll2topic){
                        if($topics_data[$a]->parent[$x]->enroll2topic->status){
                            $topics_data[$a]->parent[$x]->duration_enroll = $topics_data[$a]->parent[$x]->duration;
                        }else{
                            $topics_data[$a]->parent[$x]->duration_enroll = $topics_data[$a]->parent[$x]->enroll2topic->duration;
                        }
                    }else{
                        $topics_data[$a]->parent[$x]->duration_enroll = 0;
                    }

                    $topics_data[$a]->parent[$x]->progress = 0;
                    $topics_data[$a]->parent[$x]->percentage = 0;

                    if ($topics_data[$a]->parent[$x]->duration_enroll != 0) {
                        $topics_data[$a]->parent[$x]->progress = $topics_data[$a]->parent[$x]->duration_enroll/$topics_data[$a]->parent[$x]->duration;
                        $topics_data[$a]->parent[$x]->percentage = number_format($topics_data[$a]->parent[$x]->progress * 100);
                    }

                    $data[$i]->duration2topic += $topics_data[$a]->parent[$x]->duration;
                    $data[$i]->duration2enroll += $topics_data[$a]->parent[$x]->duration_enroll;

                    if ($topics_data[$a]->parent[$x]->state == 'vod') {
                        $data[$i]->duration2progress = $data[$i]->duration2enroll/$data[$i]->duration2topic;
                        $data[$i]->duration2percentage = number_format($data[$i]->duration2progress * 100);
                    }


                }

                if ($flagIgnore) {
                    break;
                }
            }

            if ($flagIgnore) {
                $data[$i] = null;
                continue;
            }

            if($data[$i]->courses->percentage <= $data[$i]->duration2percentage){
                $data[$i]->courses->learning = true;
            }else{
                $data[$i]->courses->learning = false;
            }

            // $data[$i]->pre_test = $data[$i]->enroll2quiz()->where('type', 1)->whereNotNull('score')->orderBy('id', 'desc')->first();
            // if($data[$i]->pre_test){
            //     if($data[$i]->pre_test->score){
            //         $data[$i]->pre_test->progress = $data[$i]->pre_test->score/$data[$i]->pre_test->count;
            //     }
            //     $data[$i]->pre_test->percentage = number_format($data[$i]->pre_test->progress * 100);
            //     $data[$i]->pre_test->quiz = Quiz::find($data[$i]->pre_test->quiz_id);
            //     if($data[$i]->pre_test->quiz->passing_score <= $data[$i]->pre_test->percentage){
            //         $data[$i]->pre_test->learning = true;
            //     }else{
            //         $data[$i]->pre_test->learning = false;
            //     }
            // }

            // $data[$i]->post_test = $data[$i]->enroll2quiz()->where('type', 4)->whereNotNull('score')->orderBy('id', 'desc')->first();
            // if($data[$i]->post_test){
            //     if($data[$i]->post_test->score){
            //         $data[$i]->post_test->progress = $data[$i]->post_test->score/$data[$i]->post_test->count;
            //     }
            //     $data[$i]->post_test->percentage = number_format($data[$i]->post_test->progress * 100);
            //     $data[$i]->post_test->quiz = Quiz::find($data[$i]->post_test->quiz_id);
            //     if($data[$i]->post_test->quiz->passing_score <= $data[$i]->post_test->percentage){
            //         $data[$i]->post_test->learning = true;
            //     }else{
            //         $data[$i]->post_test->learning = false;
            //     }
            // }

            $countQuiz = $data[$i]->courses->quiz()->select('id', 'type', 'status')->where('type', 3)->where('status', 1)->count();
            $data[$i]->exam = $data[$i]->enroll2quiz()->select('id', 'type', 'score', 'count', 'quiz_id', 'datetime')->where('type', 3)->whereNotNull('score')->orderBy('id', 'desc')->first();
            if($data[$i]->exam){
                if($data[$i]->exam->score){
                    $data[$i]->exam->progress = $data[$i]->exam->score/$data[$i]->exam->count;
                }
                $data[$i]->exam->percentage = number_format($data[$i]->exam->progress * 100);
                $data[$i]->exam->quiz = Quiz::select('id', 'passing_score')->where('id', $data[$i]->exam->quiz_id)->first();
                if($data[$i]->exam->quiz->passing_score <= $data[$i]->exam->percentage){
                    $data[$i]->exam->learning = true;
                }else{
                    $data[$i]->exam->learning = false;
                }
            }

            // $data[$i]->survey = $data[$i]->enroll2quiz()->where('type', 5)->orderBy('id', 'desc')->first();
            // if($data[$i]->survey){
            //     $data[$i]->survey = true;
            // }else{
            //     $data[$i]->survey = false;
            // }

            if($data[$i]->exam){
                if(($data[$i]->courses->learning == true) and ($data[$i]->exam->learning == true)){
                    $data[$i]->certificate = true;
                }else{
                    $data[$i]->certificate = false;
                }
            }else if($data[$i]->courses->learning == true && $countQuiz == 0){
                $data[$i]->certificate = true;
            }else{
                $data[$i]->certificate = false;
            }


//            if($data[$i]->exam){
//                if(($data[$i]->courses->learning == true) and ($data[$i]->exam->learning == true)){
//                    $data[$i]->certificate = true;
//                }else{
//                    $data[$i]->certificate = false;
//                }
//            }else{
//                $data[$i]->certificate = false;
//            }

            $start_datetime = $oFunc->thai_date_short(strtotime($data[$i]->courses->start_datetime));
            // $end_datetime = $oFunc->thai_date_short(strtotime($data[$i]->courses->end_datetime));
            $last_datetime = $oFunc->thai_date_short(strtotime($data[$i]->last_datetime));
            $enroll_datetime = $oFunc->thai_date_short(strtotime($data[$i]->enroll_datetime));
            $certificate_datetime = isset($data[$i]->certificate_datetime) ? $oFunc->thai_date_short(strtotime($data[$i]->certificate_datetime)) : null;
            $exam_datetime = isset($data[$i]->exam->datetime) ? $oFunc->thai_date_short(strtotime($data[$i]->exam->datetime)) : null;

            if ($data[$i]->certificate) {
                $status = 'ผ่าน';
                $status_color = 'text-success';
                if($exam_datetime){
                    $status_datetime = "(".$exam_datetime.")";
                }else if($certificate_datetime){
                    $status_datetime = "(".$certificate_datetime.")";
                }else{
                    $status_datetime = "";
                }
            } else if ($data[$i]->duration2percentage == 0) {
                $status = 'ยืนยันการลงทะเบียน';
                $status_color = 'text-bold';
                $status_datetime = "(".$enroll_datetime.")";
            } else if ($data[$i]->duration2percentage > 0 && is_null($data[$i]->exam)) {
                $status = 'กำลังเรียน';
                $status_color = 'text-bold';
                $status_datetime = "(".$last_datetime.")";
            } else {
                $status = 'ไม่ผ่าน';
                $status_color = 'text-danger';
                $status_datetime = "(".$exam_datetime.")";
            }

            $statusData = array(
                'status' => $status,
                'status_color' => $status_color,
                'status_datetime' => $status_datetime,
                'last_datetime' => $last_datetime,
                'enroll_datetime' => $enroll_datetime,
            );

            $data[$i]->statusData = (object) $statusData;

        }

        return response()->json($data, 200);

    }

    public function checkCertificate($id, Request $request, _SecurityController $_security, _FunctionsController $oFunc){
        $dataSession = session()->get('_user');

        if (!isset($dataSession)) {
            return response()->json(array('message' => config('constants._errorMessage._401')), 401);
        }

        // return response()->json(['debug' => $request->has('lang')], 404);

        if ($dataSession->groups->multi_lang_certificate == 1) {
            if ($request->has('lang')) {
                $langCert = $request['lang'];
            } else {
                $langCert = $dataSession->is_foreign == 1 ? 'en' : 'th';
            }

            if (!$oFunc->hasCertLang($dataSession, $langCert)) {
                return response()->json(['message' => 'ไม่มีข้อมูลของท่านในภาษาที่ท่านต้องการดาว์โหลด'], 422);
            }
        } else {
            $langCert = 'th';
            // $langCert = $dataSession->is_foreign == 1 ? 'en' : 'th';
        }

        $data = Enroll::where('id', $id)->where('members_id', $dataSession['id'])->first();

        if (!$data) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Certificate", config('constants._errorMessage._404'))), 404);
        }

        $data->courses = Courses::find($data->courses_id);

        $data->topics = Topics::where('courses_id', $data->courses_id)->whereNull('parent')->where('status', 1)->orderBy('order','asc')->get();
        $data->duration2percentage = 0;
        for($i=0; $i<count($data->topics); $i++) {
            $data->topics[$i]->parent = Topics::where('parent', $data->topics[$i]->id)->where('status', 1)->orderBy('order','asc')->get();
            for($a=0; $a<count($data->topics[$i]->parent); $a++) {

                $data->topics[$i]->parent[$a]->enroll2topic = $data->topics[$i]->parent[$a]->enroll2topic()->where('enroll_id', $data->id)->first();
                $data->topics[$i]->parent[$a]->duration = (strtotime($data->topics[$i]->parent[$a]->end_time) - strtotime('TODAY')) - (strtotime($data->topics[$i]->parent[$a]->start_time) - strtotime('TODAY'));
                if($data->topics[$i]->parent[$a]->enroll2topic){
                    if($data->topics[$i]->parent[$a]->enroll2topic->status){
                        $data->topics[$i]->parent[$a]->duration_enroll = $data->topics[$i]->parent[$a]->duration;
                    }else{
                        $data->topics[$i]->parent[$a]->duration_enroll = $data->topics[$i]->parent[$a]->enroll2topic->duration;
                    }
                }else{
                    $data->topics[$i]->parent[$a]->duration_enroll = 0;
                }

                $data->topics[$i]->parent[$a]->progress = 0;
                $data->topics[$i]->parent[$a]->percentage = 0;

                if ($data->topics[$i]->parent[$a]->duration_enroll != 0) {
                    $data->topics[$i]->parent[$a]->progress = $data->topics[$i]->parent[$a]->duration_enroll/$data->topics[$i]->parent[$a]->duration;
                    $data->topics[$i]->parent[$a]->percentage = number_format($data->topics[$i]->parent[$a]->progress * 100);
                }

                    // $data->topics[$i]->parent[$a]->progress = $data->topics[$i]->parent[$a]->duration_enroll/$data->topics[$i]->parent[$a]->duration;
                    // $data->topics[$i]->parent[$a]->percentage = number_format($data->topics[$i]->parent[$a]->progress * 100);

                    $data->duration2topic += $data->topics[$i]->parent[$a]->duration;
                    $data->duration2enroll += $data->topics[$i]->parent[$a]->duration_enroll;

                    if ($data->topics[$i]->parent[$a]->state == 'vod') {
                        $data->duration2progress = $data->duration2enroll/$data->duration2topic;
                        $data->duration2percentage = number_format($data->duration2progress * 100);
                    }

            }

        }

        if($data->courses->percentage <= $data->duration2percentage){
            $data->courses->learning = true;
        }else{
            $data->courses->learning = false;
        }

        $countQuiz = $data->courses->quiz()->where('type', 3)->where('status', 1)->count();
        $data->exam = $data->enroll2quiz()->where('type', 3)->whereNotNull('score')->orderBy('id', 'desc')->first();
        if($data->exam){
            if($data->exam->score){
                $data->exam->progress = $data->exam->score/$data->exam->count;
            }
            $data->exam->percentage = number_format($data->exam->progress * 100);
            $data->exam->quiz = Quiz::find($data->exam->quiz_id);
            if($data->exam->quiz->passing_score <= $data->exam->percentage){
                $data->exam->learning = true;
            }else{
                $data->exam->learning = false;
            }
        }

        $countSurvey = $data->courses->quiz()->where('type', 5)->count();
        $data->survey = $data->enroll2quiz()->where('type', 5)->orderBy('id', 'desc')->first();
        if($data->survey || $countSurvey == 0){
            $data->survey = true;
        }else{
            $data->survey = false;
        }

        if($data->exam){
            if(($data->courses->learning == true) and ($data->exam->learning == true) and ($data->survey)){
                $data->certificate = true;

                if (!isset($data->certificate_reference_number)) {
                    $dataGroup = Groups::find($dataSession->groups_id);
                    if ($dataGroup->is_connect_regis == 1 && false) {
                        /* ===== START R2 (UPDATE ENROLL) ===== */
                        $dataGroup = Groups::find($dataSession['groups_id']);

                        $paramEnroll = array(
                            "courseid" => $data->courses_id,
                            // "courseid" => 4, // Fix for test (skip bug).
                            "userref" => $dataSession->ref_id,
                            // "userref" => 7000978, // Fix for test (skip bug).
                            "groupid" => $dataGroup->keyset,
                            "compCode" => $dataSession->company_code,
                            "status" => "P",
                            "enrollDateTime" => Carbon::parse($data->enroll_datetime)->format('d-m-Y H:i:s'),
                        );

                        $results = $_security->encryptAndSignData(json_encode($paramEnroll, JSON_UNESCAPED_UNICODE));

                        $oClient = new httpClient();

                        try {
                            $responseEnroll = $oClient->request('POST', config('constants._SET_URL.R2'), [
                                'json' => $results
                            ]);

                            // Callback
                            $respData = json_decode($responseEnroll->getBody(), true);

                            if (isset($respData['certificate_reference_number'])) {
                                Enroll::find($data->id)->update([
                                    'certificate_reference_number' => $respData['certificate_reference_number'],
                                    'certificate_datetime' => date('Y-m-d H:i:s')
                                ]);
                            }

                        } catch(RequestException $e) {
                            if ($e->hasResponse()) {
                                return response()->json(json_decode($e->getResponse()->getBody(), true), $e->getResponse()->getStatusCode());
                            }

                            return response()->json(['error_msg' => "Internal Server Error (R)"], 500);
                        }
                        /* ===== END R2 (UPDATE ENROLL) ===== */
                    } else {
                        $data->certificate_datetime = date('Y-m-d H:i:s');
                        if ($data->enroll_type == 1 || $data->enroll_type == 2) {
                            $sessionNo = str_pad($data->enroll_type_id, 4, "0", STR_PAD_LEFT);
                        } else {
                            $sessionNo = "0001";
                        }

                        $sessionNo .= date('Y') + 543;

                        $data->certificate_reference_number = config('constants.CERTIFICATE_NUMBER_PREFIX').strtoupper($data->courses->code).$sessionNo.$data->members_id;

                        Enroll::find($data->id)->update([
                            'certificate_reference_number' => $data->certificate_reference_number,
                            'certificate_datetime' => $data->certificate_datetime
                        ]);
                    }
                }

            }else{
                $data->certificate = false;
            }
        }else if(($data->courses->learning == true) and ($countQuiz == 0) and ($data->survey)){
            $data->certificate = true;

            if (!isset($data->certificate_reference_number)) {
                $dataGroup = Groups::find($dataSession->groups_id);
                if ($dataGroup->is_connect_regis == 1 && false) {
                    /* ===== START R2 (UPDATE ENROLL) ===== */
                    $dataGroup = Groups::find($dataSession['groups_id']);

                    $paramEnroll = array(
                        "courseid" => $data->courses_id,
                        // "courseid" => 4, // Fix for test (skip bug).
                        "userref" => $dataSession->ref_id,
                        // "userref" => 7000978, // Fix for test (skip bug).
                        "groupid" => $dataGroup->keyset,
                        "compCode" => $dataSession->company_code,
                        "status" => "P",
                        "enrollDateTime" => Carbon::parse($data->enroll_datetime)->format('d-m-Y H:i:s'),
                    );

                    $results = $_security->encryptAndSignData(json_encode($paramEnroll, JSON_UNESCAPED_UNICODE));

                    $oClient = new httpClient();

                    try {
                        $responseEnroll = $oClient->request('POST', config('constants._SET_URL.R2'), [
                            'json' => $results
                        ]);

                        // Callback
                        $respData = json_decode($responseEnroll->getBody(), true);

                        if (isset($respData['certificate_reference_number'])) {
                            Enroll::find($data->id)->update([
                                'certificate_reference_number' => $respData['certificate_reference_number'],
                                'certificate_datetime' => date('Y-m-d H:i:s')
                            ]);
                        }

                    } catch(RequestException $e) {
                        if ($e->hasResponse()) {
                            return response()->json(json_decode($e->getResponse()->getBody(), true), $e->getResponse()->getStatusCode());
                        }

                        return response()->json(['error_msg' => "Internal Server Error (R)."], 500);
                    }
                    /* ===== END R2 (UPDATE ENROLL) ===== */
                } else {
                    $data->certificate_datetime = date('Y-m-d H:i:s');
                    if ($data->enroll_type == 1 || $data->enroll_type == 2) {
                        $sessionNo = str_pad($data->enroll_type_id, 4, "0", STR_PAD_LEFT);
                    } else {
                        $sessionNo = "0001";
                    }

                    $sessionNo .= date('Y') + 543;

                    $data->certificate_reference_number = config('constants.CERTIFICATE_NUMBER_PREFIX').strtoupper($data->courses->code).$sessionNo.$data->members_id;

                    Enroll::find($data->id)->update([
                        'certificate_reference_number' => $data->certificate_reference_number,
                        'certificate_datetime' => $data->certificate_datetime
                    ]);
                }
            }
        }else{
            $data->certificate = false;
        }

        $data->certificate_url = config('constants._BASE_SITE_API_URL').'enroll/'.$data->id.'/certificate/'.$langCert;

        return response()->json($data, 200);
    }

    public function downloadCertificate($id, $lang = null, Request $request, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site){
        if ($request->server('HTTP_REFERER') !== null) {
            $referer = $request->server('HTTP_REFERER');
        } else {
            $referer = config('constants.URL.HOME');
        }

        $dataSession = session()->get('_user');

        if (!isset($dataSession)) {
            return redirect($referer);
        }

        if ($dataSession->groups->multi_lang_certificate == 1) {
            if (!$oFunc->hasCertLang($dataSession, $lang)) {
                return redirect($referer);
            }
        } else {
            $lang = 'th';
            // $lang = $dataSession->is_foreign == 1 ? 'en' : 'th';
        }

        $data = Enroll::where('id', $id)->where('members_id', $dataSession['id'])->first();

        if (!$data) {
            return redirect($referer);
        }

        $data->member = Members::find($data->members_id);
        $data->courses = Courses::find($data->courses_id);
        $data->certificates = Certificates::find($data->courses->certificates_id);
        if ($data->certificates) {
            $certificateTemplate = 'certificate-course';
        } else {
            $certificateTemplate = 'certificate';
        }

        $data->topics = Topics::where('courses_id', $data->courses_id)->whereNull('parent')->where('status', 1)->orderBy('order','asc')->get();
        $data->duration2percentage = 0;
        for($i=0; $i<count($data->topics); $i++) {
            $data->topics[$i]->parent = Topics::where('parent', $data->topics[$i]->id)->where('status', 1)->orderBy('order','asc')->get();
            for($a=0; $a<count($data->topics[$i]->parent); $a++) {

                $data->topics[$i]->parent[$a]->enroll2topic = $data->topics[$i]->parent[$a]->enroll2topic()->where('enroll_id', $data->id)->first();
                $data->topics[$i]->parent[$a]->duration = (strtotime($data->topics[$i]->parent[$a]->end_time) - strtotime('TODAY')) - (strtotime($data->topics[$i]->parent[$a]->start_time) - strtotime('TODAY'));

                if($data->topics[$i]->parent[$a]->enroll2topic){
                    if($data->topics[$i]->parent[$a]->enroll2topic->status){
                        $data->topics[$i]->parent[$a]->duration_enroll = $data->topics[$i]->parent[$a]->duration;
                    }else{
                        $data->topics[$i]->parent[$a]->duration_enroll = $data->topics[$i]->parent[$a]->enroll2topic->duration;
                    }
                }else{
                    $data->topics[$i]->parent[$a]->duration_enroll = 0;
                }

                $data->topics[$i]->parent[$a]->progress = 0;
                $data->topics[$i]->parent[$a]->percentage = 0;

                if ($data->topics[$i]->parent[$a]->duration_enroll != 0) {
                    $data->topics[$i]->parent[$a]->progress = $data->topics[$i]->parent[$a]->duration_enroll/$data->topics[$i]->parent[$a]->duration;
                    $data->topics[$i]->parent[$a]->percentage = number_format($data->topics[$i]->parent[$a]->progress * 100);
                }

                    // $data->topics[$i]->parent[$a]->progress = $data->topics[$i]->parent[$a]->duration_enroll/$data->topics[$i]->parent[$a]->duration;
                    // $data->topics[$i]->parent[$a]->percentage = number_format($data->topics[$i]->parent[$a]->progress * 100);

                    $data->duration2topic += $data->topics[$i]->parent[$a]->duration;
                    $data->duration2enroll += $data->topics[$i]->parent[$a]->duration_enroll;

                    if ($data->topics[$i]->parent[$a]->state == 'vod') {
                        $data->duration2progress = $data->duration2enroll/$data->duration2topic;
                        $data->duration2percentage = number_format($data->duration2progress * 100);
                    }

            }

        }

        if($data->courses->percentage <= $data->duration2percentage){
            $data->courses->learning = true;
        }else{
            $data->courses->learning = false;
        }

        $countQuiz = $data->courses->quiz()->where('type', 3)->where('status', 1)->count();
        $data->exam = $data->enroll2quiz()->where('type', 3)->whereNotNull('score')->orderBy('id', 'desc')->first();
        if($data->exam){
            if($data->exam->score){
                $data->exam->progress = $data->exam->score/$data->exam->count;
            }
            $data->exam->percentage = number_format($data->exam->progress * 100);
            $data->exam->quiz = Quiz::find($data->exam->quiz_id);
            if($data->exam->quiz->passing_score <= $data->exam->percentage){
                $data->exam->learning = true;
            }else{
                $data->exam->learning = false;
            }
        }

        $countSurvey = $data->courses->quiz()->where('type', 5)->count();
        $data->survey = $data->enroll2quiz()->where('type', 5)->orderBy('id', 'desc')->first();
        if ($data->survey || $countSurvey == 0) {
            $data->survey = true;
        } else {
            $data->survey = false;
        }

        if ($data->exam) {
            if (($data->courses->learning == true) and ($data->exam->learning == true) and ($data->survey)) {
                $data->certificate = true;

                if (!isset($data->certificate_reference_number)) {
                    return redirect($referer);
                }

                $data->certificate_datetime_th = $oFunc->thai_date_fullmonth(strtotime($data->certificate_datetime));
                if ($data->exam) {
                    $data->datetime_full_format = $lang == "en" ? Carbon::parse($data->certificate_datetime)->format('j F Y') : $oFunc->thai_date_fullmonth(strtotime($data->exam->datetime));
                } else {
                    $data->datetime_full_format = $lang == "en" ? Carbon::parse($data->certificate_datetime)->format('j F Y') : $oFunc->thai_date_fullmonth(strtotime($data->certificate_datetime));
                }

                // Gen Cert. PDF
                $pdf = PDF::setOptions([
                    'defaultFont' => 'thsarabunnew',
                    'isRemoteEnabled' => true,
                ]);

                $pdf->loadView($certificateTemplate, ['data' => $data, 'lang' => $lang]);

                $alert_msg = "success";
                $status = 200;
                $_site->logs('certificate', '{"alert_msg":"'.$alert_msg.'"}', $status, $data->courses_id, $dataSession['id'], '{"enroll":"'.$data->id.'", "courses code":"'.$data->courses->code.'", "certificate reference number":"'.$data->certificate_reference_number.'"}', $dataSession['groups_id'], $dataSession['sub_groups_id'], '', '');

                return $pdf->setPaper('a3', 'landscape')->download('Certificate-'.$data->courses->code.'-'.strtoupper($lang).'.pdf');

            }else{
                $data->certificate = false;

                $alert_msg = "เกิดข้อผิดพลาด ไม่สามารถ download certificate ได้";
                $status = 401;
                $_site->logs('certificate', '{"alert_msg":"'.$alert_msg.'"}', $status, $data->courses_id, $dataSession['id'], '{"enroll":"'.$data->id.'", "courses code":"'.$data->courses->code.'"}', $dataSession['groups_id'], $dataSession['sub_groups_id'], '', '');

                return redirect($referer);
            }
        } else if (($data->courses->learning == true) and ($countQuiz == 0) and ($data->survey)) {
            $data->certificate = true;

            if (!isset($data->certificate_reference_number)) {
                return redirect($referer);
            }

            $data->certificate_datetime_th = $oFunc->thai_date_fullmonth(strtotime($data->certificate_datetime));
            if ($data->exam) {
                $data->datetime_full_format = $lang == "en" ? Carbon::parse($data->exam->datetime)->format('j F Y') : $oFunc->thai_date_fullmonth(strtotime($data->exam->datetime));
            } else {
                $data->datetime_full_format = $lang == "en" ? Carbon::parse($data->certificate_datetime)->format('j F Y') : $oFunc->thai_date_fullmonth(strtotime($data->certificate_datetime));
            }

            // Gen Cert. PDF
            $pdf = PDF::setOptions([
                'defaultFont' => 'thsarabunnew',
                'isRemoteEnabled' => true,
            ]);

            $pdf->loadView($certificateTemplate, ['data' => $data, 'lang' => $lang]);

            $alert_msg = "success";
            $status = 200;
            $_site->logs('certificate', '{"alert_msg":"'.$alert_msg.'"}', $status, $data->courses_id, $dataSession['id'], '{"enroll":"'.$data->id.'", "courses code":"'.$data->courses->code.'", "certificate reference number":"'.$data->certificate_reference_number.'"}', $dataSession['groups_id'], $dataSession['sub_groups_id'], '', '');

            return $pdf->setPaper('a3', 'landscape')->download('Certificate-'.$data->courses->code.'-'.strtoupper($lang).'.pdf');
        } else {
            $data->certificate = false;

            $alert_msg = "เกิดข้อผิดพลาด ไม่สามารถ download certificate ได้";
            $status = 401;
            $_site->logs('certificate', '{"alert_msg":"'.$alert_msg.'"}', $status, $data->courses_id, $dataSession['id'], '{"enroll":"'.$data->id.'", "courses code":"'.$data->courses->code.'"}', $dataSession['groups_id'], $dataSession['sub_groups_id'], '', '');

            return redirect($referer);
        }

    }

    public function testGeoIP(Request $request)
    {
        ////Logs////
        $currentIP = $request->has('ip') ? $request->ip : '';
        $dataGeoIP = GeoIP::getLocation($currentIP);

        $results = [
            "ip"    => $currentIP,
            "GeoIp" => $dataGeoIP->toArray()
        ];

        return response()->json($results, 200);

    }

    public function getLicenseTypes($id)
    {
        $data = LicenseTypes::where('id', $id)->where('status', 1)->first();

        if (!$data) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "License Types", config('constants._errorMessage._404'))), 404);
        }

        return response()->json($data->toArray(), 200);
    }

    public function getLicenseTypesList(Request $request)
    {
        $options['per_page'] = $request->input('per_page', 30);
        $options['order_by'] = $request->input('order_by', 'order');
        $options['order_direction'] = $request->input('order_direction', 'asc');

        $options['isCheckExpire'] = $request->input('isCheckExpire', true);

        $data = new LicenseTypes;

        if($options['isCheckExpire'] == true) {
            $data = $data->where('expire_datetime', '>', Carbon::now()->toDateTimeString());
        }

        $data = $data->where('status', 1)->orderBy($options['order_by'], $options['order_direction'])->paginate($options['per_page']);

        if (!$data) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "License Types", config('constants._errorMessage._404'))), 404);
        }

        return response()->json($data->toArray(), 200);
    }

    public function forgetSessionActionLogin(Request $request)
    {
        $_user = session()->get('_user');
        $_action_login = session()->get('_action_login');

        if ($_action_login && $_user->filter_courses_status == 0) {
            if ($request->has('filter_courses_status')) {
                $dataMember = Members::find($_user->id);
                $dataMember->update(['filter_courses_status' => (int)$request->filter_courses_status]);
            }

            $request->session()->put('_action_login', false);
        }

        return response()->json([], 200);
    }

    public function filterCourses(Request $request, SiteController $_site)
    {
        $_user = session()->get('_user');
        $input = $request->all();

        $dataQuestionnairePack = QuestionnairePacks::find($input['qpid']);
        if (!$dataQuestionnairePack) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Questionnaire Pack", config('constants._errorMessage._404'))), 404);
        }

        $data = new FilterCourses;
        $data->members_id = $_user->id;
        $data->questionnaire_packs_title = $dataQuestionnairePack->title;
        $data->questionnaire_packs_description = $dataQuestionnairePack->description;
        $data->questionnaire_packs_force_datetime = $dataQuestionnairePack->force_datetime;
        $data->datetime = date('Y-m-d H:i:s');
        $is_success = $data->save();

        if ($is_success) {
            $answers = array_flatten(array_pluck($input['questions'], 'answer'));

            foreach ($answers as $answer) {
                $dataAnswer = QuestionnaireChoices::find($answer);
                FilterCoursesDetail::insert([
                    "filter_courses_id"         => $data->id,
                    "question"                  => $dataAnswer->questionnaires->question,
                    "question_type"             => $dataAnswer->questionnaires->type,
                    "question_known"            => $dataAnswer->questionnaires->question_known,
                    "question_condition_type"   => $dataAnswer->questionnaires->condition_type,
                    "answer"                    => $dataAnswer->answer,
                    "answer_known"              => $dataAnswer->answer_known,
                    "answer_condition_list"     => $dataAnswer->condition_list,
                    "answer_condition_fix_list" => $dataAnswer->condition_fix_list
                ]);
            }

            // ================================================================================== //
            // ==================================== Inprogress ================================== //
            // ================================================================================== //
            $conditionLevels = [];
            $conditionPrefixCodes = [];
            $conditionFixCodes = [];

            foreach ($data->filter_courses_detail as $filter_courses_detail) {
                $conditionListArr = explode(",", $filter_courses_detail->answer_condition_list);
                $conditionListArr = array_where($conditionListArr, function ($value, $key) { return $value != ""; });

                $conditionFixListArr = explode(",", $filter_courses_detail->answer_condition_fix_list);
                $conditionFixListArr = array_where($conditionFixListArr, function ($value, $key) { return $value != ""; });

                switch ($filter_courses_detail->question_condition_type) {
                    case 'level':
                        $conditionListArr = array_map(function($n) {
                            switch (strtoupper($n)) {
                                case 'L1': $levelTxt = 'Beginner'; break;
                                case 'L2': $levelTxt = 'Intermediate'; break;
                                case 'L3': $levelTxt = 'Advance'; break;
                                default: $levelTxt = $n; break;
                            }

                            return $levelTxt;
                        }, $conditionListArr);

                        $conditionLevels = array_merge($conditionLevels, $conditionListArr);
                        break;

                    case 'code': $conditionPrefixCodes = array_merge($conditionPrefixCodes, $conditionListArr); break;
                    default: break;
                }


                if ($conditionFixListArr) {
                    $conditionFixCodes = array_merge($conditionFixCodes, $conditionFixListArr);
                }
            }

            $_user->groups = $_user->groups()->first();
            if(!empty($_user->level_groups->toArray())){
                $dataCourses = $_user->groups->courses();
                $dataCourses = $dataCourses->where('level_public', 1);

                $dataCoursesClassroomsTargets = $dataCoursesClassroomsTargets->where(function($query) use ($conditionLevels, $conditionPrefixCodes, $conditionFixCodes) {
                    $query->whereIn('level', array_unique($conditionLevels));

                    $query->where(function($query2) use ($conditionPrefixCodes) {
                        foreach ($conditionPrefixCodes as $conditionPrefixCode) {
                            $query2->orWhere('code', 'like', $conditionPrefixCode.'%');
                        }
                    });

                    $query->orWhereIn('code', array_unique($conditionFixCodes));
                });

                $dataCourses = $dataCourses->where('status', '1')->get();
                $data->courses()->syncWithoutDetaching(array_pluck($dataCourses->toArray(), "id"));

                $dataMembers = new Members;
                $dataMembers = $dataMembers->find($_user->id);
                $classrooms_target = $dataMembers->classrooms()->whereDate('start_datetime', '<=', date('Y-m-d H:i:s'))->whereDate('end_datetime', '>=', date('Y-m-d H:i:s'))->get();
                for($i=0; $i<count($classrooms_target); $i++) {
                    $dataCoursesClassroomsTargets = $classrooms_target[$i]->courses();
                    if($groups->use_sub_groups_single){
                        $dataCoursesClassroomsTargets = $dataCoursesClassroomsTargets->whereHas('classrooms.sub_groups', function($query) use ($dataMembers) {
                            $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                        });
                    }

                    $dataCoursesClassroomsTargets = $dataCoursesClassroomsTargets->where(function($query) use ($conditionLevels, $conditionPrefixCodes, $conditionFixCodes) {
                        $query->whereIn('level', array_unique($conditionLevels));

                        $query->where(function($query2) use ($conditionPrefixCodes) {
                            foreach ($conditionPrefixCodes as $conditionPrefixCode) {
                                $query2->orWhere('code', 'like', $conditionPrefixCode.'%');
                            }
                        });

                        $query->orWhereIn('code', array_unique($conditionFixCodes));
                    });

                    $classrooms_target[$i]->courses = $dataCoursesClassroomsTargets->get();
                    $data->courses()->syncWithoutDetaching(array_pluck($classrooms_target[$i]->courses->toArray(), "id"));
                }

                $dataMembers = new Members;
                $dataMembers = $dataMembers->find($_user->id);
                $classrooms_level_group = $dataMembers->level_groups()->get();
                for($l=0; $l<count($classrooms_level_group); $l++) {
                    $classrooms_level_group[$l]->classroom = $classrooms_level_group[$l]->classrooms()->whereDate('start_datetime', '<=', date('Y-m-d H:i:s'))->whereDate('end_datetime', '>=', date('Y-m-d H:i:s'))->get();
                    for($i=0; $i<count($classrooms_level_group[$l]->classroom); $i++) {
                        $dataCoursesClassroomsLevelGroups = $classrooms_level_group[$l]->classroom[$i]->courses();

                        if($groups->use_sub_groups_single){
                            $dataCoursesClassroomsLevelGroups = $dataCoursesClassroomsLevelGroups->whereHas('classrooms.sub_groups', function($query) use ($dataMembers) {
                                $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                            });
                        }

                        $dataCoursesClassroomsLevelGroups = $dataCoursesClassroomsLevelGroups->where(function($query) use ($conditionLevels, $conditionPrefixCodes, $conditionFixCodes) {
                            $query->whereIn('level', array_unique($conditionLevels));

                            $query->where(function($query2) use ($conditionPrefixCodes) {
                                foreach ($conditionPrefixCodes as $conditionPrefixCode) {
                                    $query2->orWhere('code', 'like', $conditionPrefixCode.'%');
                                }
                            });

                            $query->orWhereIn('code', array_unique($conditionFixCodes));
                        });

                        $classrooms_level_group[$l]->classroom[$i]->courses = $dataCoursesClassroomsLevelGroups->get();
                        $data->courses()->syncWithoutDetaching(array_pluck($classrooms_level_group[$l]->classroom[$i]->courses->toArray(), "id"));
                    }
                }

                $dataMembers = new Members;
                $dataMembers = $dataMembers->find($_user->id);

                $dataCoursesTargets = $dataMembers->courses();

                if($groups->use_sub_groups_single){
                    $dataCoursesTargets = $dataCoursesTargets->whereHas('sub_groups', function($query) use ($dataMembers) {
                        $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                    });
                }

                $dataCoursesTargets = $dataCoursesTargets->where(function($query) use ($conditionLevels, $conditionPrefixCodes, $conditionFixCodes) {
                    $query->whereIn('level', array_unique($conditionLevels));

                    $query->where(function($query2) use ($conditionPrefixCodes) {
                        foreach ($conditionPrefixCodes as $conditionPrefixCode) {
                            $query2->orWhere('code', 'like', $conditionPrefixCode.'%');
                        }
                    });

                    $query->orWhereIn('code', array_unique($conditionFixCodes));
                });

                $courses_target = $dataCoursesTargets->get();
                $data->courses()->syncWithoutDetaching(array_pluck($courses_target->toArray(), "id"));

                $dataMembers = new Members;
                $dataMembers = $dataMembers->find($_user->id);
                $courses_level_groups = $dataMembers->level_groups()->get();
                for($l=0; $l<count($courses_level_groups); $l++) {
                    $dataCoursesLevelGroups = $courses_level_groups[$l]->courses();

                    if($groups->use_sub_groups_single){
                        $dataCoursesLevelGroups = $dataCoursesLevelGroups->whereHas('sub_groups', function($query) use ($dataMembers) {
                            $query->where('sub_groups.id', $dataMembers->sub_groups_id);
                        });
                    }

                    $dataCoursesLevelGroups = $dataCoursesLevelGroups->where(function($query) use ($conditionLevels, $conditionPrefixCodes, $conditionFixCodes) {
                        $query->whereIn('level', array_unique($conditionLevels));

                        $query->where(function($query2) use ($conditionPrefixCodes) {
                            foreach ($conditionPrefixCodes as $conditionPrefixCode) {
                                $query2->orWhere('code', 'like', $conditionPrefixCode.'%');
                            }
                        });

                        $query->orWhereIn('code', array_unique($conditionFixCodes));
                    });

                    $courses_level_groups[$l]->courses = $dataCoursesLevelGroups->get();
                    $data->courses()->syncWithoutDetaching(array_pluck($courses_level_groups[$l]->courses->toArray(), "id"));
                }

            }else{

                $dataCourses = $_user->groups->courses();
                $dataCourses = $dataCourses->where('level_public', 1);

                $dataCourses = $dataCourses->where(function($query) use ($conditionLevels, $conditionPrefixCodes, $conditionFixCodes) {
                    $query->whereIn('level', array_unique($conditionLevels));

                    $query->where(function($query2) use ($conditionPrefixCodes) {
                        foreach ($conditionPrefixCodes as $conditionPrefixCode) {
                            $query2->orWhere('code', 'like', $conditionPrefixCode.'%');
                        }
                    });

                    $query->orWhereIn('code', array_unique($conditionFixCodes));
                });

                $dataCourses = $dataCourses->where('status', '1')->orderBy("order", "asc")->get();
                $data->courses()->sync(array_pluck($dataCourses->toArray(), "id"));
            }


            // ================================================================================== //
            // ==================================== Inprogress ================================== //
            // ================================================================================== //

            $dataMember = Members::find($_user->id);
            $dataMember->update(['filter_courses_status' => 2]);

            if (session()->get('_action_login')) {
                $request->session()->put('_action_login', false);
            }

            $message = "ค้นหาหลักสูตรที่เหมาะสมสำเร็จ";
            $alert_msg = "success";
            $status = 200;
        }else{
            $message = "เกิดข้อผิดพลาด ไม่สามารถค้นหาหลักสูตรที่เหมาะสมได้";
            $alert_msg = $message;
            $status = 500;
        }

        $_site->logs('filter-courses', '{"alert_msg":"'.$alert_msg.'"}', $status, null, $_user->id, '{"questionnaire_packs_id":"'.$input['qpid'].'", "questionnaires":"'.json_encode($input['questions']).'"}', $_user->groups_id, $_user->sub_groups_id, '', '');
        return response()->json(array('is_error' => $status == 200 ? false : true, 'message' => $message), $status);
    }

    public function enrollByCourse(Request $request, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site, $courses_id) {
        if (!$oFunc->checkSession()) {
            return response()->json(array('message' => config('constants._errorMessage._401')), 401);
        }

        $_user = session()->get('_user');
        // $input = $request->input();
        $data = Enroll::where('members_id', $_user->id)->where('courses_id', $courses_id)->first();

        return response()->json($data, 200);
    }

    public function createOrders(Request $request, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site)
    {
        if (!$oFunc->checkSession()) {
            return response()->json(array('message' => config('constants._errorMessage._401')), 401);
        }

        $_user = session()->get('_user');
        $input = $request->all();

        $validator = Validator::make($input, [
            'courses_id' => 'required|numeric',
            'type_tax_invoice' => 'required|in:personal,corporate',
            // 'methods_id' => 'required|numeric',
            // 'currency' => 'required|max:3',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $dataCourse = Courses::find($input['courses_id']);
        if (!$dataCourse) {
            $message = "ไม่พบหลักสูตรดังกล่าว";
            $_site->logs('orders', '{"alert_msg":"'.$message.'"}', 404, '', $_user['id'], json_encode($request->all(), JSON_UNESCAPED_UNICODE), $_user->groups_id, $_user->sub_groups_id, '', '');
            return response()->json(array('is_error' => true, 'message' => $message), 404);
        }

        $_2c2p_merchant_id = env('2C2P_'.strtoupper($_user->groups->key).'_MERCHANT_ID');
        $_2c2p_secret_key = env('2C2P_'.strtoupper($_user->groups->key).'_SECRET_KEY');
        if (empty($_2c2p_merchant_id) || empty($_2c2p_secret_key)) {
            $message = "เกิดข้อผิดพลาดในการชำระเงิน";
            $_site->logs('orders', '{"alert_msg":"'.$message.' (Error Merchant)"}', 500, '', $_user['id'], json_encode($request->all(), JSON_UNESCAPED_UNICODE), $_user->groups_id, $_user->sub_groups_id, '', '');
            return response()->json(array('is_error' => true, 'message' => $message), 500);
        }

        $dataGeoIP = GeoIP::getLocation();
        $agent = new Agent();

        $data = new Orders;
        $data->fill($input);
        $data->groups_id = $_user->groups_id;
        $data->methods_id = 1;
        $data->members_id = $_user->id;
        $data->courses_code = $dataCourse->code;
        $data->courses_title = $dataCourse->title;
        $data->courses_price = $dataCourse->price;
        $data->currency = 'THB';

        if ($input['type_tax_invoice'] == "personal") {
            $data->inv_name = $_user->inv_personal_first_name." ".$_user->inv_personal_last_name;
            $data->inv_tax_id = $_user->inv_personal_tax_id;
            $data->inv_email = $_user->inv_personal_email;
            $data->inv_tel = $_user->inv_personal_tel;
            $data->inv_address = $_user->inv_personal_address;
            $data->inv_zip_code = $_user->inv_personal_zip_code;
        } else {
            $data->inv_name = $_user->inv_corporate_name;
            $data->inv_branch = $_user->inv_corporate_branch;
            $data->inv_branch_no = $_user->inv_corporate_branch_no;
            $data->inv_tax_id = $_user->inv_corporate_tax_id;
            $data->inv_email = $_user->inv_corporate_email;
            $data->inv_tel = $_user->inv_corporate_tel;
            $data->inv_address = $_user->inv_corporate_address;
            $data->inv_zip_code = $_user->inv_corporate_zip_code;
        }

        do {
            $token = str_random(32);
            $dataOrders = Orders::where('token', '=', $token)->first();
        } while ($token == $dataOrders['token']);

        $data->token = $token;

        $data->ip = ($dataGeoIP['ip'] != "Unknown") ? $dataGeoIP['ip'] : $_SERVER['REMOTE_ADDR'];
        $data->user_agent = $_SERVER['HTTP_USER_AGENT'];
        $data->isoCode = $dataGeoIP['iso_code'];
        $data->country = $dataGeoIP['country'];
        $data->city = $dataGeoIP['city'];
        $data->timezone = $dataGeoIP['timezone'];
        $data->continent = $dataGeoIP['continent'];
        $data->device = $agent->device();
        $data->platform = $agent->platform();
        $data->platform_version = $agent->version($data->platform);
        $data->browser = $agent->browser();
        $data->browser_version = $agent->version($data->browser);

        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $is_success = $data->save();

        if ($is_success) {
            $message = "The orders has been created.";
            $_site->logs('orders', '{"alert_msg":"success"}', 200, '', $_user['id'], json_encode($data->toArray(), JSON_UNESCAPED_UNICODE), $_user->groups_id, $_user->sub_groups_id, '', '');
        } else {
            $message = "Failed to create the orders.";
            $_site->logs('orders', '{"alert_msg":"'.$message.'"}', 500, '', $_user['id'], json_encode($request->all(), JSON_UNESCAPED_UNICODE), $_user->groups_id, $_user->sub_groups_id, '', '');
        }

        $start_date = date("Ymd", strtotime($dataCourse->start_datetime));
        $end_date = date("Ymd", strtotime($dataCourse->end_datetime));
        $arrAddress = $oFunc->str_split_unicode($data->inv_address, 150);
        $arrCourseTitle = $oFunc->str_split_unicode($dataCourse->title, 150);

        $user_defined_1 = $data->inv_name."|".$data->inv_email;
        $user_defined_2 = $arrAddress[0];
        $user_defined_3 = !empty($arrAddress[1]) ? $arrAddress[1] : '' ;
        $user_defined_3 .= "|".$data->inv_zip_code."|".$data->inv_tax_id;
        $user_defined_4 = $arrCourseTitle[0];

        if ($input['type_tax_invoice'] == "personal") {
            $user_defined_3 .= "|00000|".$start_date."|".$end_date;
        } else {
            if ($data->inv_branch == 0) {
                $user_defined_3 .= "|00000";
            } else {
                $user_defined_3 .= "|".$data->inv_branch_no;
            }

            $user_defined_3 .= "|".$start_date."|".$end_date;
        }

        $user_defined_1 = str_replace(',', '&#44;', $user_defined_1);
        $user_defined_2 = str_replace(',', '&#44;', $user_defined_2);
        $user_defined_3 = str_replace(',', '&#44;', $user_defined_3);
        $user_defined_4 = str_replace(',', '&#44;', $user_defined_4);

        $payment_request = [
            "merchant_id" => env('2C2P_'.strtoupper($_user->groups->key).'_MERCHANT_ID'),
            "version" => env('2C2P_VERSION'),
            "result_url_1" => env('2C2P_RESULT_URL_1'),
            "payment_description" => $data->courses_code." - ".$data->courses_title,
            "order_id" => $data->id,
            "amount" => str_pad($data->courses_price."00", 12, "0", STR_PAD_LEFT),
            "customer_email" => $data->inv_email,
            "user_defined_1" => $user_defined_1,
            "user_defined_2" => $user_defined_2,
            "user_defined_3" => $user_defined_3,
            "user_defined_4" => $user_defined_4,
        ];

        $params = $payment_request['version'].$payment_request['merchant_id'].$payment_request['payment_description'].$payment_request['order_id'].$payment_request['amount'].$payment_request['customer_email'].$payment_request['user_defined_1'].$payment_request['user_defined_2'].$payment_request['user_defined_3'].$payment_request['user_defined_4'].$payment_request['result_url_1'];
        $payment_request['hash_value'] = hash_hmac('sha1',$params, env('2C2P_'.strtoupper($_user->groups->key).'_SECRET_KEY'),false);

        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'createdId' => $data->id, "payment_url" => env('2C2P_PAYMENT_URL'), 'payment_request' => $payment_request), 200);
    }

    public function my2orders()
    {

        $_user = session()->get('_user');
        $data = Orders::where('members_id', $_user->id)->get();

        for($i=0; $i<count($data); $i++) {
            $data[$i]->courses;
            // $data[$i]->payments = $data[$i]->payments()->where('approve_status', 1)->orderBy('approve_datetime', 'DESC')->first();
            $data[$i]->payments = $data[$i]->payments()->first();
        }

        return response()->json($data, 200);
    }

    public function debugEnroll(Request $request)
    {
        // $data = Enroll::whereNotNull('certificate_reference_number')->whereDoesntHave('enroll2quiz', function ($query) {
        //     $query->where('type', 3);
        // })->limit(5)->get();

        // $dataQuiz = Quiz::where('type', 3)->whereNotNull('passing_score')->get();
        // $coursesIds = array_pluck($dataQuiz, 'courses_id');

        // $data = Enroll::whereIn('courses_id', $coursesIds)->whereNotNull('certificate_reference_number')->whereBetween('last_datetime', [Carbon::now()->subMonth(6)->toDateTimeString(), Carbon::now()->subMonth(5)->toDateTimeString()])->whereDoesntHave('enroll2quiz', function ($query) {
        //     $query->where('type', 3);
        // // })->offset(1)->limit(5)->get();
        // })->get();

        // $arrEnrollIds = array_unique([6064, 7445, 8336, 8626, 8627, 8721, 9742, 9785, 9907, 9973, 9992, 10662, 10680, 10705, 10710, 10711, 10740, 10747, 11146, 11418, 11907, 12020, 12022, 12024, 12025, 12035, 12036, 12037, 12062, 12121, 12169, 12208, 12212, 12214, 12217, 12228, 12236, 12239, 12240, 12246, 12256, 12262, 12263, 12270, 12271, 12272, 12275, 12276, 12279, 12280, 12281, 12296, 12301, 12306, 12308, 12311, 12313, 12335, 12336, 12337, 12338, 12339, 12342, 12347, 12348, 12349, 12357, 12360, 12363, 12364, 12365, 12367, 12368, 12369, 12375, 12376, 12378, 12381, 12383, 12387, 12390, 12392, 12394, 12395, 12396, 12397, 12400, 12405, 12417, 12422, 12423, 12424, 12430, 12432, 12436, 12437, 12440, 12442, 12445, 12446, 12449, 12452, 12455, 12458, 12460, 12461, 12463, 12464, 12466, 12467, 12468, 12479, 12480, 12482, 12483, 12484, 12487, 12488, 12506, 12509, 12530, 12534, 12535, 12536, 12539, 12540, 12543, 12544, 12545, 12546, 12548, 12550, 12552, 12557, 12594, 12601, 12635, 12653, 12661, 12664, 12669, 12670, 12684, 12709, 12711, 12716, 12743, 12744, 12762, 12765, 12790, 12791, 12815, 12816, 12857, 12878, 12906, 12910, 12920, 12943, 12949, 12981, 12982, 12984, 13004, 13005, 13012, 13013, 13015, 13016, 13017, 13045, 13061, 13066, 13069, 13072, 13076, 13123, 13136, 13140, 13141, 13179, 13181, 13190, 13197, 13232, 13233, 13234, 13299, 13300, 13301, 13302, 13362, 13363, 13365, 13370, 13371, 13372, 13410, 13411, 13414, 13415, 13418, 13422, 13423, 13424, 13425, 13435, 13450, 13455, 13474, 13476, 13477, 13482, 13484, 13489, 13502, 13506, 13508, 13509, 13511, 13513, 13514, 13555, 13588, 13589, 13599, 13601, 13620, 13621, 13622, 13625, 13639, 13640, 13645, 13693, 13694, 13831, 14068, 14073, 14088, 14091, 14093, 14095, 14096, 14100, 14102, 14104, 14140, 14142, 14144, 14170, 14171, 14172, 14173, 14175, 14224, 14259, 14310, 14323, 14421, 6745, 8629, 8630, 9043, 9393, 11985, 12114, 12435, 12465, 12471, 12510, 12885, 12951, 12960, 14039, 14081, 14260, 14452, 14508, 14509, 14526, 14560, 14588, 14642, 14646, 14648, 14654, 14682, 14736, 14737, 14738, 14739, 14741, 14746, 14750, 14754, 14756, 14758, 14759, 14822, 14823, 14825, 14827, 14828, 14829, 14830, 14833, 14834, 14838, 14843, 14849, 14851, 14853, 14870, 14906, 14908, 14912, 14999, 15000, 15031, 15032, 15042, 15044, 15045, 15079, 15080, 15081, 15082, 15083, 15084, 15085, 15086, 15087, 15088, 15089, 15090, 15091, 15092, 15104, 15109, 15113, 15114, 15262, 15263, 15277, 15278, 15333, 15375, 15554, 15567, 15568, 15584, 15586, 15587, 15845, 15859, 15873, 16125, 16259, 16309, 16386, 16449, 16469, 16493, 16578, 16801, 16947, 16964, 16965, 16972, 17016, 17175, 17195, 17211]);

        // Enroll::whereIn('id', $arrEnrollIds)->update(['certificate_reference_number' => null]);
        // Enroll::where('id', 17211)->update(['certificate_reference_number' => null, 'certificate_datetime' => null]);

        // return response()->json(["debug_data" => array_pluck($data, 'id')], 200);
        // return response()->json(["debug_data" => ['count' => count($arrEnrollIds), 'data' => $arrEnrollIds]], 200);
    }

    public function discussion_send(Request $request, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site)
    {

        if (!$oFunc->checkSession()) {
            return response()->json(array('message' => config('constants._errorMessage._401')), 401);
        }

        $_user = session()->get('_user');

        $enroll = new Enroll;
        $enroll = $enroll->find($request['enroll']);
        $enroll->groups = $enroll->groups()->first();

        $data = new Discussions;
        $data->enroll_id = $enroll['id'];
        $data->groups_id = $enroll['groups_id'];
        $data->courses_id = $enroll['courses_id'];
        $data->topics_id = $request['topics_id'];
        $data->members_id = $enroll['members_id'];
        $data->topic = $request['topic'];
        $data->description = $request['description'];
        $data->file = $request['file'];
        $data->type = 0;

        if ($enroll->courses->is_filter == 0) {
            $data->is_public = 1;
        }

        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $is_success = $data->save();

        if ($is_success) {
            $userData = $data->toArray();
            $message = "ตั้งหัวเรื่องสำเร็จ" ;

            $dataTopic = Topics::find($data->topics_id);

            if ($dataTopic->state == 'vod') {
                $dataCourse = $data->courses;

                $toEmails = [];

                for ($i=0; $i < count($dataCourse->groups); $i++) {
                    $toEmails[] = Admins::whereHas('admins_groups', function($query) use ($dataCourse, $i) {
                        $query->whereIn('admins_groups_id', array_pluck($dataCourse->groups[$i]->admins_groups, 'id'));
                    })->whereNull('super_users')->where('status', 1)->orWhere(function($subQuery) use ($dataCourse) {
                        $subQuery->where('id', $dataCourse->admins_id)->where('status', 1);
                    })->get();
                }

                $toEmails = array_flatten($toEmails);
                $toEmails = array_values(array_unique(array_pluck($toEmails, 'email')));

                $dataMail = array(
                    'dataDiscussion' => $data,
                    'dataCourse'     => $dataCourse,
                    'dataTopic'      => $dataTopic,
                    'dataMember'     => $data->members,
                );

                Mail::send('create-discussion-mail', $dataMail, function($mail) use ($dataMail) {
                    // $mail->to($toEmails);
                    $mail->to("nawee.ku.dootvmedia@gmail.com");
                    $mail->subject('แจ้งการตั้งหัวเรื่องสนทนา หลักสูตร '.$dataMail['dataCourse']['code']." - ".$dataMail['dataCourse']['title']." โดย คุณ".$dataMail['dataDiscussion']['members']['first_name']." ".$dataMail['dataDiscussion']['members']['last_name']);
                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                    $mail->bcc(config('constants.EMAIL.BCC'), config('constants.EMAIL.BCC'));
                    // $mail->bcc('nawee.ku.dootvmedia@gmail.com', 'Nawee Kunrod');
                });
            }
        } else {
            $message = "เกิดข้อผิดพลาด ไม่สามารถตั้งหัวเรื่องได้กรุณาลองอีกครั้ง";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'groupsKey' => $enroll->groups['key'], 'coursesID' => $enroll['courses_id']), 200);
    }

    public function discussion_reply(Request $request, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site)
    {

        if (!$oFunc->checkSession()) {
            return response()->json(array('message' => config('constants._errorMessage._401')), 401);
        }

        $_user = session()->get('_user');

        $enroll = new Enroll;
        $enroll = $enroll->find($request['enroll']);
        $enroll->groups = $enroll->groups()->first();

        $data = new Discussions;
        $data->parent_id = $request['parent_id'];
        $data->mention_id = $request['mention_id'];
        $data->enroll_id = $enroll['id'];
        $data->groups_id = $enroll['groups_id'];
        $data->courses_id = $enroll['courses_id'];
        $data->topics_id = $request['topics_id'];
        $data->members_id = $enroll['members_id'];
        $data->description = $oFunc->makeLinks($request['description']);
        $data->file = $request['file'];
        $data->type = 0;
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $is_success = $data->save();

        if ($is_success) {
            $userData = $data->toArray();
            $message = "แสดงความคิดเห็นสำเร็จ" ;
        } else {
            $message = "เกิดข้อผิดพลาด ไม่สามารถแสดงความคิดเห็นได้กรุณาลองอีกครั้ง";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'groupsKey' => $enroll->groups['key'], 'coursesID' => $enroll['courses_id'], 'discussion_id' => $data->id), 200);
    }

    public function discussion_instructors_reply(Request $request, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site)
    {
        $_user_instructors = session()->get('_user_instructors');

        if (!$_user_instructors) {
            return response()->json(array('message' => config('constants._errorMessage._401')), 401);
        }

        $dataDiscussion = Discussions::find($request['parent_id']);

        $data = new Discussions;
        $data->parent_id = $request['parent_id'];
        $data->mention_id = $request['mention_id'];
        $data->groups_id = $_user_instructors['groups_id'];
        $data->courses_id = $dataDiscussion['courses_id'];
        $data->instructors_id = $_user_instructors['id'];
        $data->description = $oFunc->makeLinks($request['description']);
        $data->file = $request['file'];
        $data->type = 2;
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $is_success = $data->save();

        if ($is_success) {
            $userData = $data->toArray();
            $message = "แสดงความคิดเห็นสำเร็จ" ;
        } else {
            $message = "เกิดข้อผิดพลาด ไม่สามารถแสดงความคิดเห็นได้กรุณาลองอีกครั้ง";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'groupsKey' => $_user_instructors->groups->key, 'coursesID' => $dataDiscussion['courses_id'], 'discussion_id' => $data->id), 200);
    }

    public function discussion_list($groupsKey, $id, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site)
    {

        if (!$oFunc->checkSession()) {
            return response()->json(array('message' => config('constants._errorMessage._401')), 401);
        }

        $_user = session()->get('_user');

        $groups = new Groups;
        $groups = $groups->where('key', $groupsKey)->first();

        $data = new Discussions;
        // $data = $data->whereNull('parent_id')->where('courses_id', $id)->where('groups_id', $groups['id'])->where('members_id', $_user['id'])->where('status', 1);
        $data = $data->where(function ($query) use ($id, $groups, $_user) {
            $query->whereNull('parent_id')->where('courses_id', $id)->where('groups_id', $groups['id'])->where('members_id', $_user['id'])->where('status', 1);
        });
        $data = $data->orWhere(function ($query) use ($id, $groups) {
            $query->whereNull('parent_id')->where('courses_id', $id)->where('groups_id', $groups['id'])->where('is_reject', '!=', 1)->where('is_public', 1)->where('status', 1);
        });
        $data = $data->orderBy('order', 'desc')->get();
        // $data = $data->whereNull('parent_id')->where('courses_id', $id)->where('status', 1)->where('members_id', $_user['id'])->orWhere('is_public', 1)->orderBy('order', 'asc')->get();
        for($i=0; $i<count($data); $i++) {
            $data[$i]->members = $data[$i]->members()->select('first_name', 'last_name')->first();

            if ($data[$i]->create_by) {
                $admins = Admins::find($data[$i]->create_by);
                $data[$i]->create_by = $admins->username;
            }

            if ($data[$i]->modify_by) {
                $admins = Admins::find($data[$i]->modify_by);
                $data[$i]->modify_by = $admins->username;
            }

            $data[$i]->replies = Discussions::where('parent_id', $data[$i]->id)->where('is_reject', '!=', 1)->where('status', 1)->get();
            $data[$i]->count_reply = count($data[$i]->replies);
            for($j=0; $j<count($data[$i]->replies); $j++) {
                if ($data[$i]->replies[$j]->create_by) {
                    $admins = Admins::find($data[$i]->replies[$j]->create_by);
                    $data[$i]->replies[$j]->create_by = $admins->username;
                }

                if ($data[$i]->replies[$j]->modify_by) {
                    $admins = Admins::find($data[$i]->replies[$j]->modify_by);
                    $data[$i]->replies[$j]->modify_by = $admins->username;
                }

                $data[$i]->replies[$j]->replies = Discussions::where('parent_id', $data[$i]->replies[$j]->id)->where('is_reject', '!=', 1)->where('status', 1)->get();
                $data[$i]->count_reply += count($data[$i]->replies[$j]->replies);
            }
            $data[$i]->view = number_format($data[$i]->view);
        }

        return response()->json($data, 200);
    }

    public function discussion_instructors_list($groupsKey, $id, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site)
    {

        // if (!$oFunc->checkSession()) {
        //     return response()->json(array('message' => config('constants._errorMessage._401')), 401);
        // }

        $_user_instructors = session()->get('_user_instructors');

        $groups = new Groups;
        $groups = $groups->where('key', $groupsKey)->first();

        $data = new Discussions;
        $data = $data->with('instructors')->whereNull('parent_id')->where('courses_id', $id)->where('groups_id', $groups['id'])->where('is_sent_instructor', 1)->where('is_reject', '!=', 1)->where('status', 1);
        $data = $data->orderBy('order', 'desc')->get();
        for($i=0; $i<count($data); $i++) {
            $data[$i]->is_instructors_read = $data[$i]->instructors_read()->where('instructor_id', $_user_instructors['id'])->first() ? true : false;

            $data[$i]->members = $data[$i]->members()->select('first_name', 'last_name')->first();

            if ($data[$i]->create_by) {
                $admins = Admins::find($data[$i]->create_by);
                $data[$i]->create_by = $admins->username;
            }

            if ($data[$i]->modify_by) {
                $admins = Admins::find($data[$i]->modify_by);
                $data[$i]->modify_by = $admins->username;
            }

            $data[$i]->replies = Discussions::with('instructors')->where('parent_id', $data[$i]->id)->where('is_reject', '!=', 1)->where('status', 1)->get();
            $data[$i]->count_reply = count($data[$i]->replies);
            for($j=0; $j<count($data[$i]->replies); $j++) {
                $data[$i]->replies[$j]->is_instructors_read = $data[$i]->replies[$j]->instructors_read()->where('instructor_id', $_user_instructors['id'])->first() ? true : false;

                if ($data[$i]->replies[$j]->create_by) {
                    $admins = Admins::find($data[$i]->replies[$j]->create_by);
                    $data[$i]->replies[$j]->create_by = $admins->username;
                }

                if ($data[$i]->replies[$j]->modify_by) {
                    $admins = Admins::find($data[$i]->replies[$j]->modify_by);
                    $data[$i]->replies[$j]->modify_by = $admins->username;
                }

                $data[$i]->replies[$j]->replies = Discussions::with('instructors')->where('parent_id', $data[$i]->replies[$j]->id)->where('is_reject', '!=', 1)->where('status', 1)->get();
                $data[$i]->count_reply += count($data[$i]->replies[$j]->replies);
                for($k=0; $k<count($data[$i]->replies[$j]->replies); $k++) {
                    $data[$i]->replies[$j]->replies[$k]->is_instructors_read = $data[$i]->replies[$j]->replies[$k]->instructors_read()->where('instructor_id', $_user_instructors['id'])->first() ? true : false;
                }
            }
            $data[$i]->view = number_format($data[$i]->view);
        }

        return response()->json($data, 200);
    }

    public function discussion_detail($id, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site)
    {

        if (!$oFunc->checkSession() && !$oFunc->checkInstructorSession()) {
            return response()->json(array('message' => config('constants._errorMessage._401')), 401);
        }

        $_user = session()->get('_user');

        $data = new Discussions;
        $data = $data->with('instructors')->where(function($query) use ($id, $_user) {
            $query->where('id', $id)->where('is_reject', '!=', 1)->where('status', 1);
        })->orWhere(function($query) use ($id, $_user) {
            $query->where('id', $id)->where('members_id', $_user['id'])->where('status', 1);
        })->first();

        if (!$data) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Discussion", config('constants._errorMessage._404'))), 404);
        }


        $data->members = $data->members()->select('first_name', 'last_name')->first();
        $data->replies = Discussions::with('instructors')->where('parent_id', $data->id)->where('is_reject', '!=', 1)->where('status', 1)->get();

        if ($data->create_by) {
            $admins = Admins::find($data->create_by);
            $data->create_by = $admins->username;
        }

        if ($data->modify_by) {
            $admins = Admins::find($data->modify_by);
            $data->modify_by = $admins->username;
        }

        for($i=0; $i<count($data->replies); $i++) {
            $data->replies[$i]->members = $data->replies[$i]->members()->select('first_name', 'last_name')->first();

            if ($data->replies[$i]->create_by) {
                $admins = Admins::find($data->replies[$i]->create_by);
                $data->replies[$i]->create_by = $admins->username;
            }

            if ($data->replies[$i]->modify_by) {
                $admins = Admins::find($data->replies[$i]->modify_by);
                $data->replies[$i]->modify_by = $admins->username;
            }

            $data->replies[$i]->replies = Discussions::with('instructors')->where('parent_id', $data->replies[$i]->id)->where('is_reject', '!=', 1)->where('status', 1)->get();
            for($j=0; $j<count($data->replies[$i]->replies); $j++) {
                $data->replies[$i]->replies[$j]->members = $data->replies[$i]->replies[$j]->members()->select('first_name', 'last_name')->first();

                if ($data->replies[$i]->replies[$j]->create_by) {
                    $admins = Admins::find($data->replies[$i]->replies[$j]->create_by);
                    $data->replies[$i]->replies[$j]->create_by = $admins->username;
                }

                if ($data->replies[$i]->replies[$j]->modify_by) {
                    $admins = Admins::find($data->replies[$i]->replies[$j]->modify_by);
                    $data->replies[$i]->replies[$j]->modify_by = $admins->username;
                }
            }
        }


        return response()->json($data, 200);
    }

    public function discussion_instructors_detail($id, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site)
    {

        if (!$oFunc->checkSession() && !$oFunc->checkInstructorSession()) {
            return response()->json(array('message' => config('constants._errorMessage._401')), 401);
        }

        $_user_instructors = session()->get('_user_instructors');

        $data = new Discussions;
        $data = $data->with('instructors')->where('id', $id)->where('is_reject', '!=', 1)->where('status', 1)->first();

        $data->is_instructors_read = $data->instructors_read()->where('instructor_id', $_user_instructors['id'])->first() ? true : false;
        $data->members = $data->members()->select('first_name', 'last_name')->first();
        $data->replies = Discussions::with('instructors')->where('parent_id', $data->id)->where('is_reject', '!=', 1)->where('status', 1)->get();

        if ($data->create_by) {
            $admins = Admins::find($data->create_by);
            $data->create_by = $admins->username;
        }

        if ($data->modify_by) {
            $admins = Admins::find($data->modify_by);
            $data->modify_by = $admins->username;
        }

        for($i=0; $i<count($data->replies); $i++) {
            $data->replies[$i]->is_instructors_read = $data->replies[$i]->instructors_read()->where('instructor_id', $_user_instructors['id'])->first() ? true : false;
            $data->replies[$i]->members = $data->replies[$i]->members()->select('first_name', 'last_name')->first();

            if ($data->replies[$i]->create_by) {
                $admins = Admins::find($data->replies[$i]->create_by);
                $data->replies[$i]->create_by = $admins->username;
            }

            if ($data->replies[$i]->modify_by) {
                $admins = Admins::find($data->replies[$i]->modify_by);
                $data->replies[$i]->modify_by = $admins->username;
            }

            $data->replies[$i]->replies = Discussions::with('instructors')->where('parent_id', $data->replies[$i]->id)->where('is_reject', '!=', 1)->where('status', 1)->get();
            for($j=0; $j<count($data->replies[$i]->replies); $j++) {
                $data->replies[$i]->replies[$j]->is_instructors_read = $data->replies[$i]->replies[$j]->instructors_read()->where('instructor_id', $_user_instructors['id'])->first() ? true : false;
                $data->replies[$i]->replies[$j]->members = $data->replies[$i]->replies[$j]->members()->select('first_name', 'last_name')->first();

                if ($data->replies[$i]->replies[$j]->create_by) {
                    $admins = Admins::find($data->replies[$i]->replies[$j]->create_by);
                    $data->replies[$i]->replies[$j]->create_by = $admins->username;
                }

                if ($data->replies[$i]->replies[$j]->modify_by) {
                    $admins = Admins::find($data->replies[$i]->replies[$j]->modify_by);
                    $data->replies[$i]->replies[$j]->modify_by = $admins->username;
                }
            }
        }


        return response()->json($data, 200);
    }

    public function discussion_update_view($id, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site)
    {

        if (!$oFunc->checkSession() && !$oFunc->checkInstructorSession()) {
            return response()->json(array('message' => config('constants._errorMessage._401')), 401);
        }

        $data = new Discussions;
        $data = $data->where('id', $id)->where('status', 1)->increment('view');

        return response()->json($data, 200);
    }

    public function discussion_update_like($id, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site)
    {

        if (!$oFunc->checkSession()) {
            return response()->json(array('message' => config('constants._errorMessage._401')), 401);
        }

        $data = new Discussions;
        $data = $data->where('id', $id)->where('status', 1)->increment('count_like');

        return response()->json($data, 200);
    }

    public function discussion_update_dislike($id, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site)
    {

        if (!$oFunc->checkSession()) {
            return response()->json(array('message' => config('constants._errorMessage._401')), 401);
        }

        $data = new Discussions;
        $data = $data->where('id', $id)->where('status', 1)->increment('count_dislike');

        return response()->json($data, 200);
    }

    public function discussion_instructors_read($id, Request $request)
    {
        //
        $_user_instructors = session()->get('_user_instructors');

        $dataDiscussion = Discussions::find($id);

        if (!$dataDiscussion) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Discussion", config('constants._errorMessage._404'))), 404);
        }

        $dataIntructor = Instructors::find($_user_instructors['id']);

        $arrDiscussions[] = $dataDiscussion->id;

        $dataReplies = Discussions::where('parent_id', $dataDiscussion->id)->get();
        $arrDiscussions = array_merge($arrDiscussions, array_pluck($dataReplies, 'id'));

        for ($i=0; $i < count($dataReplies); $i++) {

            $dataSubReplies = Discussions::where('parent_id', $dataReplies[$i]->id)->get();
            $arrDiscussions = array_merge($arrDiscussions, array_pluck($dataSubReplies, 'id'));
        }

        $arrDiscussions = array_unique($arrDiscussions);
        sort($arrDiscussions);

        $dataIntructor->discussions_read()->syncWithoutDetaching($arrDiscussions);

        $is_success = true;
        $message = "The discussion has been readed.";

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function instructors_login($groups_key, $courses_id, Request $request, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $groups = new Groups;
        $groups = $groups->where('key', $groups_key)->first();

        $instructors = Instructors::where('code', $request['code'])->where('groups_id', $groups['id'])->first();
        if(!$instructors) {
            return response()->json(['message' => 'ไม่พบรหัสผ่านในระบบ'], 403);
        }

        $course = Courses::where('id', $courses_id)->whereHas('groups', function ($query) use ($groups) {
            $query->where('groups_id', $groups['id']);
        })->whereHas('instructors', function ($query) use ($instructors) {
            $query->where('instructors_id', $instructors['id']);
        })->first();

        if (!$course) {
            return response()->json(['message' => 'ไม่พบรหัสผ่านในระบบ'], 403);
        }

        $request->session()->put('_user_instructors', $instructors);

        return response()->json(['message' => 'เข้าสู่ระบบเรียบร้อย'], 200);
    }

    public function instructors_logout(Request $request, _SecurityController $_security)
    {
        $_user_instructors = session()->get('_user_instructors');

        if (!isset($_user_instructors)) {
            // return redirect()->back();
            return redirect(config('constants.URL.HOME'));
        }

        $request->session()->forget('_user_instructors');

        return response()->json('You have been successfully logged out.', 200);
    }

    public function getSessionUserInstructor(Request $request)
    {
        $_user_instructors = session()->get('_user_instructors');

        if (!$_user_instructors) {
            return response()->json(["message" => "Unauthorized Access."], 401);
        }

        $dataIntructor = Instructors::find($_user_instructors['id']);

        return response()->json(array("data" => $dataIntructor, "debug_time" => time()), 200);
    }

    public function getSlideActive($id)
    {
        $data = Slides::where('courses_id', '=', $id)->where('slide_active', '=', 1)->first();

        return response()->json($data, 200);
    }

    public function getStream($id, Request $request)
    {
        $data = Topics::find($id);

        if ($data->state == 'vod') {
            if ($data->streaming_status == 1) {
                $message = 'On Demand Now';
                $status = 'vod';
            } else if ($data->streaming_status == 0) {
                if ($data->vod_format == 'end_live') {
                    $message = 'สิ้นสุดการถ่ายทอดสด<br>ขอบคุณที่รับชม';
                } else {
                    $message = 'ขอบคุณที่รับชม<br>ติดตามชมย้อนหลังได้<br>เร็วๆ นี้';

                }

                $status = 'stop';
            }

        } else {
            if ($data->streaming_status == 1) {
                if ($data->streaming_pause == 1) {
                    $message = "กรุณารอสักครู่...<br>การถ่ายทอดสดจะเริ่มเร็วๆ นี้";
                    $status = 'pause';
                } else {
                    $bandwidths = new Bandwidths();

                    if ($request->has('server_name')) {
                        $bandwidths = $bandwidths->where('server_name', $request->input('server_name'));
                    }

                    $bandwidths = $bandwidths->where('server_name', env('STREAMING_SERVER_CDN_DOMAIN'));
                    $bandwidths = $bandwidths->latest('id')->first();

                    if (isset($bandwidths) && $bandwidths->bandwidth_rx >= 2097152) {
                        $data->streaming_url = str_replace(env('STREAMING_SERVER_CDN'), env('STREAMING_SERVER_CDN_EXTERNAL'), $data->streaming_url);
                    }

                    $message = 'Live Now';
                    $status = 'stream';
                }
            } else if ($data->streaming_status == 0) {
                $now = new DateTime();
                $bangkokTZ = new DateTimeZone("Asia/Bangkok");
                $now->setTimezone($bangkokTZ);
                $datetime_stamp_live = new DateTime($data->live_start_datetime);
                $diff = strtotime($datetime_stamp_live->format("Y-m-d H:i:s")) - strtotime($now->format("Y-m-d H:i:s"));
                if ($diff < 1) {
                    $message = 'กรุณารอสักครู่...<br>การถ่ายทอดสดจะเริ่มเร็วๆ นี้';
                    $status = 'coming';
                } else {
                    $message = 'Countdown...';
                    $status = 'countdown';
                }
            }
        }

        $stream = array(
            'status' => $status,
            'streaming_url' => $data->streaming_url,
            'streaming_status' => $data->streaming_status,
            'messages_status' => $data->messages,
            'sync_slides' => $data->sync_slides
        );

        return response()->json(array('message' => $message, 'streamData' => $stream, 'view' => $data->view), 200);
    }

    public function getSubtitlesFile($id)
    {
        //
        $data = Videos::find($id);
        $data->subtitles = $data->subtitles()->orderBy('from_time')->get();

        // return response()->json($data->toArray(), 200);

        // $content = "WEBVTT"."\n\n";
        $content = "";
        $numberOrder = 1;

        for($i=0; $i<count($data->subtitles); $i++) {
            if (isset($data->subtitles[$i]->from_time) && $data->subtitles[$i]->from_time != "" && isset($data->subtitles[$i]->to_time) && $data->subtitles[$i]->to_time != "" && isset($data->subtitles[$i]->message) && $data->subtitles[$i]->message != "") {
                $content .= $numberOrder."\n";
                $content .= $data->subtitles[$i]->from_time.",000 --> ".$data->subtitles[$i]->to_time.",000"."\n";
                $content .= $data->subtitles[$i]->message."\n\n";
                $numberOrder++;
            }
        }

        $fileName = "subtitles.srt";
        $headers = ['Content-type'=>'text/srt', 'Content-Disposition'=>sprintf('; filename="%s"', $fileName)];
        // $headers = ['Content-type'=>'text/srt', 'Content-Disposition'=>sprintf('attachment; filename="%s"', $fileName)];
        return response()->make($content, 200, $headers);


    }

    public function member2live(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'courses_id' => 'required|numeric',
            'topics_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $_user = session()->get('_user');
        $member = Members::find($_user->id);

        if ($member->id != $_user->id) {
            return response()->json(array('message' => 'unauthorized', 'status_code' => 401), 401);
        }

        // $topic = Topics::find($request['topics_id']);

        // $member2live = Member2Live::where('members_id', $member->id)->first();

        $agent = new Agent();
        if (!$agent->isRobot()) {
            $data = new Member2Live;
            // $input = $request->all();
            // $input = $request->json()->all();
            // $data->fill($input);
            $data->groups_id = $_user->groups_id;
            $data->sub_groups_id = $_user->sub_groups_id;
            $data->courses_id = $request['courses_id'];
            $data->topics_id = $request['topics_id'];
            $data->members_id = $member->id;

            $dataGeoIP = GeoIP::getLocation();

            $data->user_agent = $_SERVER['HTTP_USER_AGENT'];
            $data->ip = ($dataGeoIP['ip'] != "Unknown") ? $dataGeoIP['ip'] : $request->ip();
            $data->isoCode = $dataGeoIP['iso_code'];
            $data->country = $dataGeoIP['country'];
            $data->city = $dataGeoIP['city'];
            $data->state = $dataGeoIP['state_name'];
            $data->timezone = $dataGeoIP['timezone'];
            $data->continent = $dataGeoIP['continent'];
            $data->device = $agent->device();
            $data->platform = $agent->platform();
            $data->platform_version = $agent->version($data->platform);
            $data->browser = $agent->browser();
            $data->browser_version = $agent->version($data->browser);
            $data->datetime = date('Y-m-d H:i:s');
            $data->md5_uip = md5($data->ip.$data->user_agent);

            $is_success = $data->save();

            if ($is_success) {
                $message = 'Recorded successfully.';
            }

            // if ($topic->uip_live != null) {
            //     $topic->increment('uip_live');
            // } else {
            //     $topic->uip_live = 1;
            //     $topic->save();
            // }
        }
        // else {
        //     $message = 'Updated live view.';
        // }

        // if ($topic->hit_live != null) {
        //     $topic->increment('hit_live');
        // } else {
        //     $topic->hit_live = 1;
        //     $topic->save();
        // }

        // if ($topic->state == "vod") {
        //     $topic->increment('hit');
        // }

        return response()->json(array('message' => $message), 200);
    }

    public function enrollViews(Request $request){

        $input = $request->input();

        if ($request->has('id')) {
            $data = Views::find($input['id']);
        } else if ($request->has('enroll_id')) {
            $data = new Views;
            $data->enroll_id = $input['enroll_id'];
            $data->start_datetime = date('Y-m-d H:i:s');
        } else {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }

        /* Check Permision Enroll */
        $_user = session()->get('_user');
        $dataCheck = Enroll::where('id', $data->enroll_id)->where('members_id', $_user->id)->first();

        if (!$dataCheck) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "Enroll", config('constants._errorMessage._404'))), 404);
        }
        /* Check Permision Enroll */

        $data->topics_id = $input['topics_id'];
        $data->state = $input['state'];
        $data->end_datetime = date('Y-m-d H:i:s');
        $is_success = $data->save();

        return response()->json(['is_success' => $is_success, 'id' => $data->id], 200);
    }
}
