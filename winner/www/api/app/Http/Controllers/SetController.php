<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response;
use Jenssegers\Agent\Agent;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use GuzzleHttp\Client as httpClient;
use GuzzleHttp\Exception\GuzzleException;

use App\Models\Categories;
use App\Models\Courses;
use App\Models\Groups;
use App\Models\Members;
use App\Models\MembersPreApproved;
use App\Models\Enroll;
use App\Models\Topics;
use App\Models\Quiz;
use App\Models\SubGroups;
use App\Models\Certificates;

use Torann\GeoIP\Facades\GeoIP as GeoIP;
use Carbon\Carbon;
use File;
use Auth;
use PDF;
use Validator;


class SetController extends Controller
{
    private function syncMember($ref_id, $data, $dataGroup, $isLogin = true)
    {
        $dataGeoIP = GeoIP::getLocation();
        $agent = new Agent();
        $dataMember = Members::where('ref_id', $ref_id)->where('groups_id', $dataGroup->id)->first();

        if (!$dataMember) {
            $dataMember = new Members;
            $dataMember->fill($data['user_profile']);
            $dataMember->groups_id = $dataGroup->id;
            $dataMember->company_code = isset($data['company_code']) ? $data['company_code'] : null;
            $dataMember->ip = ($dataGeoIP['ip'] != "Unknown") ? $dataGeoIP['ip'] : $_SERVER['REMOTE_ADDR'];
            // $dataMember->my_session_id = session()->getId();
            $dataMember->country = $dataGeoIP['country'];
            $dataMember->city = $dataGeoIP['city'];
            $dataMember->device = $agent->device();
            $dataMember->platform = $agent->platform();
            $dataMember->platform_version = $agent->version($dataMember->platform);
            $dataMember->approved_type = 3;
            $dataMember->approved_datetime = date('Y-m-d H:i:s');
            $dataMember->active = 1;
            $dataMember->active_remark = 1;
            $dataMember->status = 1;
            $dataMember->created_type = 3;
            $dataMember->create_datetime = date('Y-m-d H:i:s');
            $dataMember->modify_datetime = date('Y-m-d H:i:s');

            if ($isLogin) {
                $dataMember->last_login = date('Y-m-d H:i:s');
            }

            $is_success = $dataMember->save();
        } else {
            $dataMember->fill($data['user_profile']);
            $dataMember->ip = ($dataGeoIP['ip'] != "Unknown") ? $dataGeoIP['ip'] : $_SERVER['REMOTE_ADDR'];
            // $dataMember->my_session_id = session()->getId();
            $dataMember->country = $dataGeoIP['country'];
            $dataMember->city = $dataGeoIP['city'];
            $dataMember->device = $agent->device();
            $dataMember->platform = $agent->platform();
            $dataMember->platform_version = $agent->version($dataMember->platform);
            $dataMember->active = 1;
            $dataMember->active_remark = 1;
            $dataMember->status = 1;
            $dataMember->modify_datetime = date('Y-m-d H:i:s');

            if ($isLogin) {
                $dataMember->last_login = date('Y-m-d H:i:s');
            }

            $is_success = $dataMember->save();
        }

        return array("is_success" => $is_success, "dataMember" => $dataMember);
    }

    private function checkSingleSignOn($request, $_security, $group_key)
    {
        // if ($request->server('HTTP_REFERER') !== null) {
        //     $referer = $request->server('HTTP_REFERER');
        // } else {
        //     $referer = config('constants.URL.HOME');
        // }

        if ($request->has('forceLogin') && $request->forceLogin == true) {
            $tempUser = session()->get('temp_user');
            $requestData = $tempUser;
        } else {
            $requestData = $request->all();
        }

        $referer = config('constants.URL.HOME');
        $site = isset($requestData['site']) ? '?site='.$requestData['site'] : '';
        $referer .= $site;

        $resp = array(
            "isSuccess" => true
        );

        $dataGroup = Groups::where('key', $group_key)->first();

        if (!$dataGroup || $dataGroup->internal == 1) {
            // dd('group');
            // return redirect($referer);

            $dataReturn = array(
                'message' => 'The '.$group_key.' group not found.'
            );

            return Response::json($dataReturn, 401, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        }

        $results = $_security->verifyData($requestData['data'], $requestData['signature'], env('PATH_'.strtoupper($dataGroup->key).'_PUBLIC_KEY'));

        if (!$results['isSuccess']) {
            $results = $_security->verifyData($requestData['data'], $requestData['signature'], env('PATH_PUBLIC_KEY'));
            if (!$results['isSuccess']) {
                // dd($results);
                // return redirect($referer);
                return Response::json($results, 401, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
            }
        }

        $validator = Validator::make($results['data'], [
            'user_profile.ref_id' => 'required'
        ],[
            'user_profile.ref_id.required' => 'The user_profile.ref_id field is required.'
        ]);

        if ($validator->fails()) {
            // dd($validator->errors()->toArray());
            // return redirect($referer);
            $errors = [];
            foreach ($validator->errors()->toArray() as $key => $value) {
                array_set($errors, $key, $value);
            }

            $errorParams = [
                'message' => config('constants._errorMessage._422'),
                'invalid_params' => $errors,
            ];

            // return Response::json($errorParams, 401, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        } else {
            // $dataMember = Members::where('ref_id', $results['data']['user_profile']['ref_id'])->where('groups_id', $dataGroup->id)->first();
            // if ($dataMember) {
            //     $_user = session()->get('_user');

            //     if ($dataMember->my_session_id && (!$request->has('forceLogin') || $request->forceLogin === false)) {
            //         if (is_null($_user) || (!is_null($_user) && $_user['my_session_id'] != $dataMember->my_session_id)) {
            //             $dataMySessionId = unserialize(session()->getHandler()->read($dataMember->my_session_id));
            //             if (!empty($dataMySessionId['_user_session']) && (is_object($dataMySessionId['_user_session']) || is_array($dataMySessionId['_user_session']))) {
            //                 $request->session()->put('temp_user', $request->all());
            //                 $pageSessionRedirect = str_replace("{GROUP_KEY}", $dataGroup->key, config('constants.URL_GROUP.SESSION_EXISTS'));

            //                 return redirect($pageSessionRedirect.$site.($site == '' ? '?' : '&')."redirectPage=".urlencode(url()->current()));
            //             }
            //         }
            //     }
            // }
        }

        $resp['referer'] = $referer;
        $resp['dataGroup'] = $dataGroup;
        $resp['results'] = $results;

        return $resp;
    }

    private function createMembers($dataGroup, $dataLevelGroup = null, $data)
    {
        $dataGeoIP = GeoIP::getLocation();
        $agent = new Agent();

        $dataProfile = $data['user_profile'];
        $dataMember = new Members;
        $dataMember->fill($dataProfile);
        $dataMember->groups_id = $dataGroup->id;
        $dataMember->company_code = isset($data['company_code']) ? $data['company_code'] : null;
        $dataMember->ip = ($dataGeoIP['ip'] != "Unknown") ? $dataGeoIP['ip'] : $_SERVER['REMOTE_ADDR'];
        // $dataMember->my_session_id = session()->getId();
        $dataMember->country = $dataGeoIP['country'];
        $dataMember->city = $dataGeoIP['city'];
        $dataMember->device = $agent->device();
        $dataMember->platform = $agent->platform();
        $dataMember->platform_version = $agent->version($dataMember->platform);
        $dataMember->approved_type = 3;
        $dataMember->approved_datetime = date('Y-m-d H:i:s');
        $dataMember->active = 1;
        $dataMember->active_remark = 1;
        $dataMember->created_type = 3;
        $dataMember->create_datetime = date('Y-m-d H:i:s');
        $dataMember->modify_datetime = date('Y-m-d H:i:s');
        $dataMember->last_login = date('Y-m-d H:i:s');
        $is_success = $dataMember->save();
        $dataMember->sub_groupsList()->sync([$dataProfile['sub_groups_id'] => ['active' => 1]]);
        // $dataMember->level_groups()->sync([$dataProfile['level_groups_id']]);

        return $dataMember;
    }

    private function updateMembers($dataGroup, $dataLevelGroup = null, $dataMember, $data, $active, $activeRemark, $approved_type = null)
    {
        $dataGeoIP = GeoIP::getLocation();
        $agent = new Agent();

        $dataProfile = $data['user_profile'];
        $dataMember->fill($dataProfile);
        // $dataMember->name_title = "นางสาว";
        // $dataMember->name_title_en = "Ms.";
        $dataMember->company_code = isset($data['company_code']) ? $data['company_code'] : null;
        $dataMember->ip = ($dataGeoIP['ip'] != "Unknown") ? $dataGeoIP['ip'] : $_SERVER['REMOTE_ADDR'];
        $dataMember->country = $dataGeoIP['country'];
        $dataMember->city = $dataGeoIP['city'];
        $dataMember->device = $agent->device();
        $dataMember->platform = $agent->platform();
        $dataMember->platform_version = $agent->version($dataMember->platform);

        $dataMember->active = $active;
        $dataMember->active_remark = $activeRemark;

        switch ($approved_type) {
            case 1:
                $dataMember->approved_type = $approved_type;
                $dataMember->approved_field = $dataGroup->field_approval;
                $dataMember->approved_datetime = date('Y-m-d H:i:s');
                break;

            case 2:
                $dataMember->approved_type = $approved_type;
                $dataMember->approved_datetime = date('Y-m-d H:i:s');
                break;

            case 3:
                $dataMember->approved_type = $approved_type;
                $dataMember->approved_datetime = date('Y-m-d H:i:s');
                break;

            default:
                # code...
                break;
        }

        $dataMember->last_login = date('Y-m-d H:i:s');
        $dataMember->modify_datetime = date('Y-m-d H:i:s');
        $dataMember->save();
        // // $dataMember->sub_groupsList()->syncWithoutDetaching([$dataProfile['sub_groups_id'] => ['active' => 1]]);
        // $dataMember->level_groups()->syncWithoutDetaching([$dataProfile['level_groups_id']]);

        return $dataMember;
    }

    private function handleValidations($dataGroup, $dataLevelGroups = null, $dataProfile)
    {
        $oFunc = new _FunctionsController;
        $errorMessage =  null;

        $strictMode = false;

        switch ($dataGroup->id) {
            case 1:
                // Do Something.
                break;

            case 2:
                if (empty($dataProfile['position_id']) && $strictMode) {
                    $errorMessage = "The column position id has no value.";
                } else if (empty($dataProfile['role']) && $strictMode) {
                    $errorMessage = "The column role has no value.";
                }
                break;

            case 3:
                if (empty($dataProfile['license_id']) && $dataGroup->field_approval == "license_id") {
                    $errorMessage = "The column license id has no value.";
                } else if (empty($dataProfile['position_id'])) {
                    $errorMessage = "The column position id has no value.";
                } else if (empty($dataProfile['education_level_id'])) {
                    $errorMessage = "The column education level id has no value.";
                }
                break;

            case 4:
                if (empty($dataProfile['education_level_id']) && $strictMode) {
                    $errorMessage = "The column education level id has no value.";
                }
                break;

            case 5:
                if (empty($dataProfile['position_id']) && $strictMode) {
                    $errorMessage = "The column position id has no value.";
                } else if (empty($dataProfile['table_number']) && $strictMode) {
                    $errorMessage = "The column table number has no value.";
                } else if (empty($dataProfile['chief_name']) && $strictMode) {
                    $errorMessage = "The column chief name has no value.";
                }
                break;

            default:
                // Do Something.
                break;
        }

        if (is_null($errorMessage)) {
            if (empty($dataProfile['name_title']) && $strictMode) {
                $errorMessage = "The column prefix name has no value.";
            } else if ((empty($dataProfile['gender']) || !in_array($dataProfile['gender'], ['M','m','F','f'])) && $strictMode) {
                $errorMessage = "The column gender is invalid format. (Only M and F)";
            } else if (empty($dataProfile['first_name']) && $dataGroup->field_approval == "full_name") {
                $errorMessage = "The column first name has no value.";
            } else if (empty($dataProfile['last_name']) && $dataGroup->field_approval == "full_name") {
                $errorMessage = "The column last name has no value.";
            } else if (filter_var($dataProfile['email'], FILTER_VALIDATE_EMAIL) === false) {
                $errorMessage = "The column e-mail is invalid format.";
            } else if (empty($dataProfile['id_card']) && $dataGroup->field_approval == "id_card") {
                $errorMessage = "The column id card has no value.";
            } else if (!empty($dataProfile['id_card']) && !$oFunc->checkIDCard($dataProfile['id_card'])) {
                $errorMessage = "The column id card is invalid format.";
            } else if (empty($dataProfile['birth_date']) && $strictMode) {
                $errorMessage = "The column birth date is invalid format. (yyyy-mm-dd)";
            } else if (!empty($dataProfile['birth_date']) && !$oFunc->validateDate($dataProfile['birth_date']) && $strictMode) {
                $errorMessage = "The column birth date is invalid format. (yyyy-mm-dd)";
            } else if (empty($dataProfile['mobile_number']) && $strictMode) {
                $errorMessage = "The column mobile number has no value.";
            } else if (empty($dataProfile['occupation_id']) && $dataGroup->field_approval == "occupation_id") {
                $errorMessage = "The column occupation id has no value.";
            } else if (!empty($dataProfile['sub_groups_id'])) {
                $dataSubGroup = $dataGroup->sub_groups()->find($dataProfile['sub_groups_id']);

                if ($dataSubGroup) {
                    $dataDomainExist = $dataSubGroup->domains()->where('domains.title', explode('@', $dataProfile['email'])[1])->first();
                } else {
                    $dataDomainExist = false;
                }

                /*if ((!$dataSubGroup || ($authSession->sub_groups_id != $dataSubGroup->id) && count($dataAdminsGroups->groups) == 0)) {
                    $errorMessage = "The subgroup id was not found.";
                } else if ($dataSubGroup->id != $dataLevelGroups->sub_groups_id) {
                    $errorMessage = "The unit of not matched in sub group.";
                } else */if ($dataSubGroup->restriction_mode == "allow" && !$dataDomainExist) {
                    $errorMessage = "The email domain is not allowed.";
                } else if ($dataSubGroup->restriction_mode == "deny" && $dataDomainExist) {
                    $errorMessage = "The email domain is denied.";
                }
            }
        }

        return [
            "errorMessage" => $errorMessage,
            "dataSubGroup" => isset($dataSubGroup) ? $dataSubGroup : null,
            "dataLevelGroupsExist" => isset($dataLevelGroupsExist) ? $dataLevelGroupsExist : null,
            "dataLevelGroupsHasPerm" => isset($dataLevelGroupsHasPerm) ? $dataLevelGroupsHasPerm : null
        ];

        // if ($dataGroup->id == 3) {

        //     if (empty($dataProfile['first_name']) && $dataGroup->field_approval == "full_name") {
        //         $errorMessage = "The column first name has no value.";
        //     } else if (empty($dataProfile['last_name']) && $dataGroup->field_approval == "full_name") {
        //         $errorMessage = "The column last name has no value.";
        //     } else if (filter_var($dataProfile['email'], FILTER_VALIDATE_EMAIL) === false) {
        //         $errorMessage = "The coulumn e-mail is invalid format.";
        //     } else if (empty($dataProfile['id_card']) && $dataGroup->field_approval == "id_card") {
        //         $errorMessage = "The coulumn id card is invalid format.";
        //     } else if (!empty($dataProfile['id_card']) && !$oFunc->checkIDCard($dataProfile['id_card'])) {
        //         $errorMessage = "The coulumn id card has no value.";
        //     } else if (empty($dataProfile['occupation_id']) && $dataGroup->field_approval == "occupation_id") {
        //         $errorMessage = "The coulumn occupation id has no value.";
        //     } else if (empty($dataProfile['license_id']) && $dataGroup->field_approval == "license_id") {
        //         $errorMessage = "The coulumn license id has no value.";
        //     } else if (!empty($dataProfile['sub_groups_id'])) {
        //         $dataSubGroup = $dataGroup->sub_groups()->find($dataProfile['sub_groups_id']);

        //         if ($dataSubGroup) {
        //             $dataDomainExist = $dataSubGroup->domains()->where('domains.title', explode('@', $dataProfile['email'])[1])->first();
        //         } else {
        //             $dataDomainExist = false;
        //         }

        //         if (!$dataSubGroup) {
        //             $errorMessage = "The subgroup id was not found.";
        //         } else if ($dataSubGroup->restriction_mode == "allow" && !$dataDomainExist && $isUploadMembers) {
        //             $errorMessage = "The email domain is not allowed.";
        //         } else if ($dataSubGroup->restriction_mode == "deny" && $dataDomainExist && $isUploadMembers) {
        //             $errorMessage = "The email domain is denied.";
        //         }
        //     }

        // } else {

        //     if (empty($dataProfile['first_name']) && $dataGroup->field_approval == "full_name") {
        //         $errorMessage = "The column first name has no value.";
        //     } else if (empty($dataProfile['last_name']) && $dataGroup->field_approval == "full_name") {
        //         $errorMessage = "The column last name has no value.";
        //     } else if (filter_var($dataProfile['email'], FILTER_VALIDATE_EMAIL) === false) {
        //         $errorMessage = "The coulumn e-mail is invalid format.";
        //     } else if (empty($dataProfile['id_card']) && $dataGroup->field_approval == "id_card") {
        //         $errorMessage = "The coulumn id card has no value.";
        //     } else if (!empty($dataProfile['id_card']) && !$oFunc->checkIDCard($dataProfile['id_card'])) {
        //         $errorMessage = "The coulumn id card has no value.";
        //     } else if (empty($dataProfile['occupation_id']) && $dataGroup->field_approval == "occupation_id") {
        //         $errorMessage = "The coulumn occupation id has no value.";
        //     } else if (!empty($dataProfile['sub_groups_id'])) {
        //         $dataSubGroup = $dataGroup->sub_groups()->find($dataProfile['sub_groups_id']);

        //         if ($dataSubGroup) {
        //             $dataDomainExist = $dataSubGroup->domains()->where('domains.title', explode('@', $dataProfile['email'])[1])->first();
        //         } else {
        //             $dataDomainExist = false;
        //         }

        //         if (!$dataSubGroup) {
        //             $errorMessage = "The subgroup id was not found.";
        //         } else if ($dataSubGroup->restriction_mode == "allow" && !$dataDomainExist && $isUploadMembers) {
        //             $errorMessage = "The email domain is not allowed.";
        //         } else if ($dataSubGroup->restriction_mode == "deny" && $dataDomainExist && $isUploadMembers) {
        //             $errorMessage = "The email domain is denied.";
        //         }
        //     }

        // }

        // return [
        //     "errorMessage" => $errorMessage,
        //     "dataSubGroup" => isset($dataSubGroup) ? $dataSubGroup : null,
        //     "dataLevelGroupsExist" => isset($dataLevelGroupsExist) ? $dataLevelGroupsExist : null,
        //     "dataLevelGroupsHasPerm" => isset($dataLevelGroupsHasPerm) ? $dataLevelGroupsHasPerm : null
        // ];
    }

    public function checkPreApproved($dataGroup, $dataProfile, $dataMembers)
    {
        if ($dataGroup->field_approval == "full_name") {
            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroup->id)->where('first_name', $dataProfile['first_name'])->where('last_name', $dataProfile['last_name'])->first();
        } else if ($dataGroup->field_approval == "id_card") {
            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroup->id)->where($dataGroup->field_approval, $dataProfile['id_card'])->first();
        } else if ($dataGroup->field_approval == "occupation_id") {
            if ($dataGroup->id == 4) {
                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroup->id)->where('sub_groups_id', $dataProfile['sub_groups_id'])->where($dataGroup->field_approval, $dataProfile['occupation_id'])->first();
            } else {
                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroup->id)->where($dataGroup->field_approval, $dataProfile['occupation_id'])->first();
            }
        } else if ($dataGroup->field_approval == "license_id" && ($dataGroup->id == 3)) {
            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroup->id)->where($dataGroup->field_approval, $dataProfile['license_id'])->first();
        } else {
            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroup->id)->where('email', $dataProfile['email'])->first();
        }

        if ($dataPreApprovedExist) {
            if ($dataPreApprovedExist->courses) {
                $dataMembers->courses()->syncWithoutDetaching(array_pluck($dataPreApprovedExist->courses, 'id'));
                $dataPreApprovedExist->courses()->detach();
            }

            if ($dataPreApprovedExist->classrooms) {
                $dataMembers->classrooms()->syncWithoutDetaching(array_pluck($dataPreApprovedExist->classrooms, 'id'));
                $dataPreApprovedExist->classrooms()->detach();
            }

            $dataMembers->sub_groupsList()->syncWithoutDetaching([$dataPreApprovedExist->sub_groups_id => ['active' => 1]]);

            if (is_null($dataMembers->sub_groups_id)) {
                $dataMembers->sub_groups_id = $dataPreApprovedExist->sub_groups_id;
                $dataMembers->save();
            }

            $dataMembers->level_groups()->syncWithoutDetaching(array_pluck($dataPreApprovedExist->level_groups, 'id'));
            $dataPreApprovedExist->delete();
            $dataPreApprovedExist->level_groups()->detach();
        }

        return $dataMembers;
    }

    public function testDecrypt(_SecurityController $_security)
    {
        // $cipherText = "8OjQFvxLvebi\/mfKpjDDTuRg0gYvD+aHziMLH0K2cok=";
        $cipherText = "WyKmX\/U9ROBgkHdD5POc9g==";

        // $cipherText = "vKt+RM0/LxSjItuBoGmkVdu3RFqUsd0HzON0v/MKk2KIQI56esjvJUVCve8zh/UbaDcxhjVFeLXB4+6RWnoc0Q==";
        $cipherText = "AxSIdiMOD/NJ+yh3k2O2mjW8O/StjSFbZ9hvLgn9q/+Cat0sbzWi+pZ4xEtPkEDY";
        // $cipherText = ("vKt+RM0/LxSjItuBoGmkVUF2EAgD2U8OIXFOiFKw8awBPmjihKqMtOVFZW1zQfjw");
        // $cipherText = base64_encode("FK2m6TEg/9WBtEt4CoxEUtX37mEUWhfmTEKSKJv9oRBUqWmZYMiRgGKqls5qZEKaYVpQV+vN4COXQ8JBKZVUg68kEqnyfsXRLCXhmSaB34+xqAR2S1T/aLXeXVWV51ELVC3vAz+7ouFyvyZ1MP3flajQzIArpPXVKsqD6r0E+zKxStwAwwqCXcPI27OssCU553o9T+x7Bx1fVgWhSljyDrsu9vyHN3gCF7mwGWYNtSsNG4c38tmlGbJTgtiHrGrFcsG0/Nkh2LX6iUzfTyB67Q0r+5k1kfI5jSXuLnr7FI3ex2s6pwo0PzYhsHZwkdAM6e9s/sUSX8rpLJK1gOl+t8zxsQ++WiaDIhIXXlvKddsbkmcoFdMWV1l+wjDnj1qYKRFeenklWoC9T8JNXr6qW13D0GKImMwYJST6IFMNoS0gs1zqsA5/j+jKpP8Ssa2Pr0wh0rcqkwiv4LBpHZgrwH9xa1r4/AAuT4+qkVnwcl/K8wIpYr2rzRhC6eCEdZ4+rYm5atrYwUaAHXtjDNNtidhe/GBfsxaW7u3b+ADzW8a75k9nInRph3K35wAvQwsnttjV5kKmdpTCUbq7S4KOQ8ckR+lkX3a/oEDNdhz2LoeZdyP+jmJukizP1XakQQeTbeffnH4eFYPNXZiRJre9/716tcA40NSLTE6++O77Vvu+kKRo30USMHGwgQXZftFUjNzKRAhhS+MdjGLJa9bFsJIPYKielLxjq/danB1FzMgSoE5KVscld6PfbwbbG3ePypZ67vLAfIQ2qnIQnZdkOvA2dHaBQp9fx+xM2SxAYBmnUl8Tc8DERrEYMlSQ2HKNnmjO+ZRycAYHWf5wq2kvO+Y+bnq5KrzHaHk+r8Q8OAQ49gS97YsKYWtr40iWG21wgkQ5laqn+m217yZ6XmNTkQ6R5oktIWsjS5lvM1UPGeU1vIRlqURNYUo/pwfFaLEPfz67ZUVsdGxTDnAhIDmpGYi/qLbQu8lzV78rzFlAUrygaSnfPip8rSJK6Qq11x6zTcXCXorDMDjW9/594pfysKhaSywTfjGIVSfx3koW52ts1u3SSXCfQH9UFmytCfT54dWSswDvn7fQXj1x1YxxgTt6VWBZZhjIPBU1M3XX95t2Wn57N3EITDDA/2/TnvPRyWiSH5gbU+goBQXRToJITtxijnAnptFujyzz1+rFL13Q6Kl+k7HHcQKDoUx/5ElHtG5OJ4zb7o2WkaH+bgc1GuZJca6D3zFv18T544oxci9fm3C5Zsyf01H2/YCbKdrk0sVTalqOTGkpwSuMYQvOdxSUG/bZkg/N2LT0Lktz/PmZ8ZOPDmaJvVYwz7X7vjHf1Dlh9kB9ot+w0rVPtmqbyY82op89oMs6YXUqJxe5zCLcakQac5b6WdbPnsVob3lSYtHpU5VMrB6bn43WPTNCLZQXLU2eGtStUYRfwxMum9Xu3imYa15nYSIp7CtExpNK619QoLMjiyrBDfTKB2D1vFHIldMtxhU8L7cbTtLjr8LEmAbY0iZsfFhkWyVZKMCi+FfgvuFK+RJPlxUUnX3gpEfyAShpjkO54hZf/Gu3zgevA2tyICAPeztM2cvCUV3+P9iB1zZPbBOvc8wuzhgREZ0orx/sTEc5Gn+jdFwV5rsRv6mcjD71crQQsjCvj6eU6mBMDhyWGHAKHYfjxoRTsRusKWBvph+zSsx1bx3lbAROUyLIzhlriEb1vNhdSaGg+6Stxm0c69urukd08tZE5Qf/Z1Ngi6BTKFLMO5v8ZyXEISMxDfc7v//gvSSqMY05249HzSSOth7X7hLNV/uZlvjp3O4dEun2oUWr+b0fb//p+wtJVwqnxP+l39WJq+nps7ss2I+MJjr1skxIx42n7gZJo23Vk1nnSpORNj9suHM=");
        // $cipherText = "FK2m6TEg/9WBtEt4CoxEUtX37mEUWhfmTEKSKJv9oRBUqWmZYMiRgGKqls5qZEKaYVpQV+vN4COXQ8JBKZVUg68kEqnyfsXRLCXhmSaB34+xqAR2S1T/aLXeXVWV51ELVC3vAz+7ouFyvyZ1MP3flajQzIArpPXVKsqD6r0E+zKxStwAwwqCXcPI27OssCU553o9T+x7Bx1fVgWhSljyDrsu9vyHN3gCF7mwGWYNtSsNG4c38tmlGbJTgtiHrGrFcsG0/Nkh2LX6iUzfTyB67Q0r+5k1kfI5jSXuLnr7FI3ex2s6pwo0PzYhsHZwkdAM6e9s/sUSX8rpLJK1gOl+t8zxsQ++WiaDIhIXXlvKddsbkmcoFdMWV1l+wjDnj1qYKRFeenklWoC9T8JNXr6qW13D0GKImMwYJST6IFMNoS0gs1zqsA5/j+jKpP8Ssa2Pr0wh0rcqkwiv4LBpHZgrwH9xa1r4/AAuT4+qkVnwcl/K8wIpYr2rzRhC6eCEdZ4+rYm5atrYwUaAHXtjDNNtidhe/GBfsxaW7u3b+ADzW8a75k9nInRph3K35wAvQwsnttjV5kKmdpTCUbq7S4KOQ8ckR+lkX3a/oEDNdhz2LoeZdyP+jmJukizP1XakQQeTbeffnH4eFYPNXZiRJre9/716tcA40NSLTE6++O77Vvu+kKRo30USMHGwgQXZftFUjNzKRAhhS+MdjGLJa9bFsJIPYKielLxjq/danB1FzMgSoE5KVscld6PfbwbbG3ePypZ67vLAfIQ2qnIQnZdkOvA2dHaBQp9fx+xM2SxAYBmnUl8Tc8DERrEYMlSQ2HKNnmjO+ZRycAYHWf5wq2kvO+Y+bnq5KrzHaHk+r8Q8OAQ49gS97YsKYWtr40iWG21wgkQ5laqn+m217yZ6XmNTkQ6R5oktIWsjS5lvM1UPGeU1vIRlqURNYUo/pwfFaLEPfz67ZUVsdGxTDnAhIDmpGYi/qLbQu8lzV78rzFlAUrygaSnfPip8rSJK6Qq11x6zTcXCXorDMDjW9/594pfysKhaSywTfjGIVSfx3koW52ts1u3SSXCfQH9UFmytCfT54dWSswDvn7fQXj1x1YxxgTt6VWBZZhjIPBU1M3XX95t2Wn57N3EITDDA/2/TnvPRyWiSH5gbU+goBQXRToJITtxijnAnptFujyzz1+rFL13Q6Kl+k7HHcQKDoUx/5ElHtG5OJ4zb7o2WkaH+bgc1GuZJca6D3zFv18T544oxci9fm3C5Zsyf01H2/YCbKdrk0sVTalqOTGkpwSuMYQvOdxSUG/bZkg/N2LT0Lktz/PmZ8ZOPDmaJvVYwz7X7vjHf1Dlh9kB9ot+w0rVPtmqbyY82op89oMs6YXUqJxe5zCLcakQac5b6WdbPnsVob3lSYtHpU5VMrB6bn43WPTNCLZQXLU2eGtStUYRfwxMum9Xu3imYa15nYSIp7CtExpNK619QoLMjiyrBDfTKB2D1vFHIldMtxhU8L7cbTtLjr8LEmAbY0iZsfFhkWyVZKMCi+FfgvuFK+RJPlxUUnX3gpEfyAShpjkO54hZf/Gu3zgevA2tyICAPeztM2cvCUV3+P9iB1zZPbBOvc8wuzhgREZ0orx/sTEc5Gn+jdFwV5rsRv6mcjD71crQQsjCvj6eU6mBMDhyWGHAKHYfjxoRTsRusKWBvph+zSsx1bx3lbAROUyLIzhlriEb1vNhdSaGg+6Stxm0c69urukd08tZE5Qf/Z1Ngi6BTKFLMO5v8ZyXEISMxDfc7v//gvSSqMY05249HzSSOth7X7hLNV/uZlvjp3O4dEun2oUWr+b0fb//p+wtJVwqnxP+l39WJq+nps7ss2I+MJjr1skxIx42n7gZJo23Vk1nnSpORNj9suHM";
        // $cipherText = "VDjbmzMRUEsdna1jC9Mzpq7BH9o9SDAa952HZR239BK4+xJlGSfvhbxXphSmsIAh2kMvg4J2RZ03grwT4NMX5Xe6pyxqp6xD43I3TqbgGSDzOLTg6X8RC/ZQgleizOZijdNgu7NkeYihrgsaUlB4Y5Yd2BXfymuha3+34nfbnFkuaCqOKA+bSGKqILaP0UneTg5Hk5LVDwBvdoJJN9h5aRotehNcaBFfDVuoLQ3B7OnZlu+xy0UZVm0kPevpTxPBBk9ffqYK8xRCd/qfdTi795MxMapIZJIkvInVVN8wcFJro7RDZIb0XQjbT015iLFY9DCAQcycUI3I3tO5B4pLnSWBbrfyZfJbEIz/VliO+wo/uoVEoAcibMU2Nmdl3Hoqf95/JGP6n8jcUpKREm+Tn36W86XseSmiP18TMMSYdcP8UirQrR0SdiHIGMC5k6ZxvtDwyEEdZfAGjhCuIwLEdwXpF83YOrP5yBpAXUVECS5bNdT9HaE8d4Tc67ucJOkgECd9/ARcrSt9czDchm9pQmNTfAXMq3hlzoYOpKQJwUOdsWQq4yjzkIQtFVbubKSJOwcQW9Q9EhRZOhqcO5QtNfdelwv4dK5f81huabcPCV7XHX1j9crXPjFZ2dYpEFNDa1eAEYwEJvg9h5yrRK+eCPeNgnbaCYrzhOO2c/FOLb89sMtjggIGskfbcLGKD43aGYnrLZp28SaWjhxVHRHO3eaFLMua7rKm+wPU1F5afHkI+wxvB2FfOqeFiSPQQ2RqBXHbvcfKZ5ewhJCoYVSdrMPw61FDn31xcab/ntdWnMs0SfSSUkDpJJEqneFPAy2KixbVe6RnZp+Dd8notFOmRsLBCxkiS8BbQjnhotx1tgIuTx+isEPm6hmMN6x4d6I4yIuW1hhHt17Zk/Zuwa6SYTIlueewevaGJY2aolQZ8OuhY+/r67m55/uY/HFzJ+FjxA5zkudNWcKwXKyYLgAthAg/HCw/xoC9RZE9KoZhOXM0iGWy46DfI65lJFjRQXTVIitp9fymNwyC05Bv0JplDnTyWEOra2bWffAYkIY2aMH/EzG5sV2ofZSTYZe66uYsunKKdbZxMFQ9YETREw3MajmDBEohNzjErzpyJkHf3cC11GoFM7t1q1kg1Oliuw9980odoHvXiAfW2XT0i3v5udI/X08P0+x1Gd1fUOYIzMjAgVW2b+mKhpdC5duW5GTZRX3fyr1lokAdfVlKoSvfK6/9jWbLAmYi+0WCNxe8XuNo140SNdw4noJrkpweKec3xUA+wVTMe1B+WfLftEdxAi/Vfn0aKBxUmmjzJ3+u2hSFJEakf5GkZusKMCEM6odjlLbrZsiABDeSDgEWCBPrnBi472kdwWiGbymmENWVjq4uB2DIvjfxBMu16dYRa/pjIObphKvHxTGucK4Adoj8tbXfPKX11k5yIODyCdu/Obs6LDvf3+HvcXbbJhp6h48oLrPgYI8SpQ2ZeuAixLoHvzRrjwv4Rpuw2Vv0KoezPMaEO3XlNQuxjZYdjcsxINthJ+nCvpygQg3breeNTfF3CNqXGwpKilJ0MuFEKsTSAXXe2lE7TlXWhDwMivAbf/BGiSZKvizFqIRexiZ9ghc6tOkVLLnpHHbcxP206ONXubxm5fa2w9s5irLrppanLz+qbTBZi3yNWdZNgwSMHB24SXuq7hqjPdR7Aqaq3NnCOoS2Ub7dLjdo3B+qGJ5EyCXAJLe8ubseClIMvcz9f2x0jfaiYNSSbpNmUzkauhti8/37gd3nvWFuXcfbBwtx3UdA0JuBcs7vF3tM4fMNx6P5MZYYRsBdoyLmKqrVkeKhfHKhvbI+G6LMrFC5kXScX+iZI/clD78TiXXsAknfWx00IZyhaf3VBxT7d1PpyPcwRXA=";
        // $publicKey = env('PATH_PUBLIC_KEY');
        $publicKey = env('PATH_SET_WEB_PUBLIC_KEY');

        $publicKeysXml = File::get($publicKey);

        $result = $_security->aesDecryptString($cipherText, $_security->getAesKeys($publicKeysXml));

        return response(array($result), 200);
        // return response($result, 200);
    }

    public function testEncrypt(_SecurityController $_security)
    {
        $plaintext = "Tes Encrypted";

        $privateKey = env('PATH_PRIVATE_KEY');
        $privateKeysXml = File::get($privateKey);

        $result = $_security->aesEncryptString($plaintext, $_security->getAesKeys($privateKeysXml));

        return response(array($result), 200);
        // return response($result, 200);
    }

    public function generateKeys(_SecurityController $_security)
    {
        $isGenerated = $_security->generateKeyFiles();

        if (!$isGenerated) {
            return response()->json(["isGenerated" => $isGenerated], 500);
        }

        return response()->json(["isGenerated" => $isGenerated], 200);
    }

    public function singleSignOn(Request $request, _SecurityController $_security)
    {
        $results = $_security->verifyData($request->data, $request->signature, env('PATH_SET_WEB_PUBLIC_KEY'));

        if ($request->server('HTTP_REFERER') !== null) {
            $referer = $request->server('HTTP_REFERER');
        } else {
            $referer = config('constants.URL.HOME');
        }

        if (!$results['isSuccess']) {
            return redirect($referer);
        }

        $dataGroup = Groups::where('key', $results['data']['group_id'])->first();

        if (!$dataGroup) {
            return redirect($referer);
        }

        if (isset($results['data']['user_profile'])) {
            $dataMember = Members::where('ref_id', $results['data']['user_profile']['ref_id'])->first();
            $dataGeoIP = GeoIP::getLocation();
            $agent = new Agent();

            if (!$dataMember) {
                $dataMember = new Members;
                $dataMember->fill($results['data']['user_profile']);
                $dataMember->groups_id = $dataGroup->id;
                $dataMember->company_code = isset($results['data']['company_code']) ? $results['data']['company_code'] : null;
                $dataMember->ip = ($dataGeoIP['ip'] != "Unknown") ? $dataGeoIP['ip'] : $_SERVER['REMOTE_ADDR'];
                // $dataMember->my_session_id = session()->getId();
                $dataMember->country = $dataGeoIP['country'];
                $dataMember->city = $dataGeoIP['city'];
                $dataMember->device = $agent->device();
                $dataMember->platform = $agent->platform();
                $dataMember->platform_version = $agent->version($dataMember->platform);
                $dataMember->create_datetime = date('Y-m-d H:i:s');
                $dataMember->modify_datetime = date('Y-m-d H:i:s');
                $dataMember->last_login = date('Y-m-d H:i:s');
                $is_success = $dataMember->save();
            } else {
                $dataMember->fill($results['data']['user_profile']);
                $dataMember->ip = ($dataGeoIP['ip'] != "Unknown") ? $dataGeoIP['ip'] : $_SERVER['REMOTE_ADDR'];
                // $dataMember->my_session_id = session()->getId();
                $dataMember->country = $dataGeoIP['country'];
                $dataMember->city = $dataGeoIP['city'];
                $dataMember->device = $agent->device();
                $dataMember->platform = $agent->platform();
                $dataMember->platform_version = $agent->version($dataMember->platform);
                $dataMember->modify_datetime = date('Y-m-d H:i:s');
                $dataMember->last_login = date('Y-m-d H:i:s');
                $is_success = $dataMember->save();
            }

            if (!$is_success) {
                return redirect($referer);
            } else {
                $request->session()->regenerate();
                $request->session()->put('_user', $dataMember);
                $this->swappingSession($dataMember);
            }
        }

        $action = strtoupper($results['data']['action']);
        if (config('constants.URL.'.$action)) {

            if ($dataGroup->id != 1) {
                return redirect(config('constants.URL.'.$action).$dataGroup->key);
            }

            return redirect(config('constants.URL.'.$action));
        }

        return redirect(config('constants.URL.HOME'));
        // return redirect("https://www.google.co.th/");
    }

    public function logout(Request $request, _SecurityController $_security)
    {
        $_user = session()->get('_user');

        if (!isset($_user)) {
            // return redirect()->back();
            return redirect(config('constants.URL.HOME'));
        }

        $request->session()->forget('_user');
        $request->session()->forget('_user_session');

        $dataMember = Members::find($_user['id']);
        $dataMember->my_session_id = null;
        $dataMember->last_logout = date('Y-m-d H:i:s');
        $dataMember->save();

        $dataGroup = Groups::find($_user['groups_id']);
        $paramsEncrypted = null;

        if ($dataGroup->internal != 1) {
            $agent = new Agent();

            if ($agent->isMobile()) {
                $txtChannel = 'mobile';
            } else if ($agent->isTablet()) {
                $txtChannel = 'tablet';
            } else {
                $txtChannel = 'web';
            }

            $param = array(
                "userref" => $_user['ref_id'],
                "groupid" => $dataGroup->key,
                "txtChannel" => $txtChannel,
                "sessionId" => $_user['session_id']
            );

            $results = $_security->encryptAndSignData(json_encode($param));
            $paramsEncrypted = array("data" => $results['data'], "signature" => $results['signature'], "system" => "elearning");
        }

        // return response()->json(["param" => $param, "results" => array("data" => $results['data'], "signature" => $results['signature'])], 200);
        // return response()->json(array("data" => $results['data'], "signature" => $results['signature'], "system" => "elearning"), 200);

        $urlLogoutGroup = config('constants._SET_URL.GROUPS.'.$dataGroup->key.'.S2');

        return response()->json(array(
            "signoutUrl" => $urlLogoutGroup ? $urlLogoutGroup : config('constants._SET_URL.S2'),
            "params" => $paramsEncrypted
        ), 200);

        // $oClient = new httpClient();

        // try {
        //     $_response = $oClient->request('POST', config('constants._SET_URL.S2'), [
        //         'json' => array("data" => $results['data'], "signature" => $results['signature'], "system" => "elearning"),
        //         'allow_redirects' => [
        //             'strict' => true,
        //             'referer' => true,
        //         ]
        //     ]);

        //     // return response()->json(array("data" => $results['data'], "signature" => $results['signature'], "system" => "elearning"), 200);
        //     echo $_response->getBody();
        // } catch(RequestException $e) {
        //     // if ($e->hasResponse()) {
        //     //     return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
        //     // }
        //     return redirect()->back();
        // }
    }

    private function manageMember($request, $referer, $data, $dataGroup)
    {
        $dataProfile = $data['user_profile'];
        $dataFormValidations = $this->handleValidations($dataGroup, null, $dataProfile);

        if (!is_null($dataFormValidations['errorMessage'])) {
            return redirect($referer);
            return response()->json([
                'message' => $dataFormValidations['errorMessage']], 422,
                ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE
            );
        }

        if ($dataFormValidations['dataSubGroup']) {
            // dd('Has Sub Group.');
            if ($dataGroup->field_approval == "full_name") {
                $dataExist = Members::where('groups_id', $dataGroup->id)->where('first_name', $dataProfile['first_name'])->where('last_name', $dataProfile['last_name'])->first();
            } else if ($dataGroup->field_approval == "id_card") {
                $dataExist = Members::where('groups_id', $dataGroup->id)->where($dataGroup->field_approval, $dataProfile['id_card'])->first();
            } else if ($dataGroup->field_approval == "occupation_id") {
                if ($dataGroup->id == 4) {
                    $dataExist = Members::where('groups_id', $dataGroup->id)->where('sub_groups_id', $dataProfile['sub_groups_id'])->where($dataGroup->field_approval, $dataProfile['occupation_id'])->first();
                } else {
                    $dataExist = Members::where('groups_id', $dataGroup->id)->where($dataGroup->field_approval, $dataProfile['occupation_id'])->first();
                }
            } else if ($dataGroup->field_approval == "license_id" && ($dataGroup->id == 3)) {
                $dataExist = Members::where('groups_id', $dataGroup->id)->where($dataGroup->field_approval, $dataProfile['license_id'])->first();
            } else {
                $dataExist = Members::where('groups_id', $dataGroup->id)->where('email', $dataProfile['email'])->first();
            }

            if ($dataExist) {
                if ($dataExist->sub_groups_id == $dataProfile['sub_groups_id']) {
                    if ($dataExist->active == 1) {
                        // $dataReject = $this->handleRejected($dataGroup, $dataReject, $dataProfile, $rowRejected, $row, "Duplicated Member.");
                        // $rowRejected++;
                        $dataUpdate = $this->updateMembers($dataGroup, null, $dataExist, $data, 1, 1);
                    } else {
                        $dataUpdate = $this->updateMembers($dataGroup, null, $dataExist, $data, 1, 1);
                        // $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "Activated member."];

                        /* BEGIN E-MAIL FUNCTION */
                        // Notify Mail (Member's reactivated)
                        /* END E-MAIL FUNCTION */
                    }

                    $is_success = $dataUpdate;
                } else {
                    $dataUpdate = $this->updateMembers($dataGroup, null, $dataExist, $data, 1, 1);
                    $is_success = $dataUpdate;

                    $dataExist->sub_groupsList()->where('active', 1)->update(['active' => 3]);
                    $dataExist->sub_groupsList()->where('active', 2)->update(['active' => 4]);
                    $dataExist->sub_groupsList()->syncWithoutDetaching([$dataProfile['sub_groups_id'] => ['active' => 1]]);
                    // $dataExist->level_groups()->syncWithoutDetaching([$dataProfile['leve_groups_id']]);
                    // $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "This member has changing of subgroup."];

                    /* BEGIN E-MAIL FUNCTION */
                    // Notify Mail (Member's Group Changed)
                    /* END E-MAIL FUNCTION */
                }

                $dataMember = $this->checkPreApproved($dataGroup, $dataProfile, $dataUpdate);

                // $dataMember = $dataUpdate;
            } else {
                // Add New Member
                $dataCreate = $this->createMembers($dataGroup, null, $data);
                if ($dataCreate) {
                    // $dataInserted[] = ["row" => $row] + $dataCreate->toArray();

                    $dataMember = $this->checkPreApproved($dataGroup, $dataProfile, $dataCreate);
                } else {
                    $dataMember = $dataCreate;
                }

                $is_success = $dataMember;
            }
        } else {
            // dd('Hasn\'t Sub Group.');
            $syncResults = $this->syncMember($dataProfile['ref_id'], $data, $dataGroup);
            $dataMember = $this->checkPreApproved($dataGroup, $dataProfile, $syncResults['dataMember']);

            // $dataMember = $syncResults['dataMember'];
            $is_success = $syncResults['is_success'];
        }

        if (!$is_success) {
            // return redirect($referer);
            return response()->json([
                'message' => config('constants._errorMessage._500')], 500,
                ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE
            );
        } else {
            $request->session()->regenerate();
            $request->session()->put('_user', $dataMember);
            $request->session()->put('_user_session', $data);
            $request->session()->put('_action_login', true);
            $this->swappingSession($dataMember);
            return true;
        }
    }

    public function debugHttps(Request $request) {
        // Request::setTrustedProxies(array('110.170.25.51'));
        // Request::setTrustedProxies([$request->getClientIp()]);
        return response()->json([
            $request->isSecure(),
            $request->url(),
            url()->current(),
            route('certificates-preview', ['filename' => 'xxx']),
            $request->getClientIp(),
            time(),
        ], 500);
    }

    public function singleSignOnTest(Request $request, _SecurityController $_security, $group_key = 'SETGroup')
    {
        $_dataFromSSO = $this->checkSingleSignOn($request, $_security, $group_key);

        if (!is_array($_dataFromSSO)) {
            return $_dataFromSSO;
        }

        $referer = $_dataFromSSO['referer'];
        $dataGroup = $_dataFromSSO['dataGroup'];
        $results = $_dataFromSSO['results'];

        if (isset($results['data']['user_profile'])) {
            $resultsManage = $this->manageMember($request, $referer, $results['data'], $dataGroup);

            if ($resultsManage !== true) {
                return $resultsManage;
            }
        }

        $actionRedirect = config('constants.URL.HOME');
        if (isset($results['data']['action']) && $results['data']['action'] != "INFO") {
            $action = strtoupper($results['data']['action']);
            $urlAction = config('constants.URL_GROUP.'.$action);
            if ($urlAction) {
                $actionRedirect = str_replace("{GROUP_KEY}", $dataGroup->key, $urlAction);
            }
        }

        $actionRedirect .= isset($request->site) ? '?site='.$request->site : '';

        return redirect($actionRedirect);
    }

    public function groupsCoursesLists(Request $request, _SecurityController $_security, _FunctionsController $oFunc, $group_key)
    {
        $per_page = $request->input('per_page', 10);
        $order_by = $request->input('order_by', 'id');
        $order_direction = $request->input('order_direction', 'DESC');

        $dataGroup = Groups::where('key', $group_key)->first();

        if (!$dataGroup) {

            $dataReturn = array(
                'message' => 'The '.$group_key.' group not found.'
            );

            return response()->json($dataReturn, 404, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        }

        $now = Carbon::now()->toDateTimeString();

        $data = $dataGroup->courses()->select('courses.id', 'courses.code', 'courses.title', 'courses.thumbnail', 'courses.price', 'courses.latest', 'courses.recommended', 'courses.free')
                ->where('courses.start_datetime', '<=', $now)
                ->where('courses.end_datetime', '>', $now)
                ->where('courses.status', 1)->get();
                // ->get();

        for ($i=0; $i < count($data); $i++) {
            // unset($data[$i]->modify_by);
            // unset($data[$i]->infomation);
            // unset($data[$i]->objective);
            // unset($data[$i]->suitable);
            // unset($data[$i]->level);
            // unset($data[$i]->Introductory);
            // unset($data[$i]->getting_certificate);
            // unset($data[$i]->getting_certificate_url);
            // unset($data[$i]->more_details);

            if (isset($data[$i]->thumbnail) && $data[$i]->thumbnail != "") {
                $data[$i]->thumbnail = config('constants._BASE_FILE_URL.COURSES_THUMBNAIL').$data[$i]->thumbnail;
            }

            $data[$i]->categories = $data[$i]->categories()->select('categories.id','categories.title', 'categories.hex_color')->get();
            $data[$i]->categories = $oFunc->clearPivot($data[$i]->categories->toArray());

            $data[$i]->latest = $data[$i]->latest == 1 ? true : false;
            $data[$i]->recommended = $data[$i]->recommended == 1 ? true : false;
            $data[$i]->free = $data[$i]->free == 1 ? true : false;

            unset($data[$i]->pivot);
        }

        // return response()->json($data, 200);

        // $results = $_security->encryptAndSignData($data->toJson());
        // return response()->json(array("data" => $results['data'], "signature" => $results['signature']), 200);
        // return response()->json($data, 200);
        return response()->json($data, 200, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    public function groupsCoursesInfo(Request $request, _SecurityController $_security, $group_key = 'SETGroup', $course_id)
    {
        $_dataFromSSO = $this->checkSingleSignOn($request, $_security, $group_key);

        if (!is_array($_dataFromSSO)) {
            return $_dataFromSSO;
        }

        $referer = $_dataFromSSO['referer'];
        $dataGroup = $_dataFromSSO['dataGroup'];
        $results = $_dataFromSSO['results'];

        if (isset($results['data']['user_profile'])) {
            $resultsManage = $this->manageMember($request, $referer, $results['data'], $dataGroup);

            if ($resultsManage !== true) {
                return $resultsManage;
            }
        }

        $dataCourse = $dataGroup->courses()->find($course_id);

        if ($dataCourse) {
            $searchArr = ["{GROUP_KEY}", "{COURSE_ID}"];
            $replaceArr = [$dataGroup->key, $course_id];

            $actionRedirect = str_replace($searchArr, $replaceArr, config('constants.URL_GROUP.INFO'));

            if (isset($results['data']['action'])) {
                $action = strtoupper($results['data']['action']);
                $urlAction = config('constants.URL_GROUP.'.$action);
                if ($urlAction) {
                    $actionRedirect = str_replace($searchArr, $replaceArr, $urlAction);
                }
            }
        } else {
            $actionRedirect = config('constants.URL.HOME');
        }

        $actionRedirect .= isset($request->site) ? '?site='.$request->site : '';
        return redirect($actionRedirect);
    }

    public function downloadCertificate($group_key = 'SETGroup', $course_id, $lang = null, Request $request, _SecurityController $_security, _FunctionsController $oFunc)
    {
        $_dataFromSSO = $this->checkSingleSignOn($request, $_security, $group_key);

        if (!is_array($_dataFromSSO)) {
            return $_dataFromSSO;
        }

        $referer = $_dataFromSSO['referer'];
        $dataGroup = $_dataFromSSO['dataGroup'];
        $results = $_dataFromSSO['results'];

        if (isset($results['data']['user_profile'])) {
            $resultsManage = $this->manageMember($request, $referer, $results['data'], $dataGroup);

            if ($resultsManage !== true) {
                return $resultsManage;
            }
        }

        if ($request->server('HTTP_REFERER') !== null) {
            $referer = $request->server('HTTP_REFERER');
        } else {
            $referer = config('constants.URL.HOME');
        }

        $dataSession = session()->get('_user');

        if (!isset($dataSession) || $dataSession['groups_id'] != $dataGroup->id) {
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

        $data = Enroll::where('courses_id', $course_id)->where('members_id', $dataSession['id'])->first();

        if (!$data) {
            // return response()->json(["message" => "ไม่พบใบประกาศนียบัตรที่ดังกล่าว"], 404, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
            return redirect(str_replace("{GROUP_KEY}", $dataGroup->key, config('constants.URL_GROUP.MY_COURSES')));
        }

        $data->member = Members::find($data->members_id);
        $data->courses = Courses::find($data->courses_id);
        $data->certificates = Certificates::find($data->courses->certificates_id);
        if ($data->certificates) {
            $certificateTemplate = 'certificate-course';
        } else {
            $certificateTemplate = 'certificate';
        }

        if (!$data->courses || $data->courses->download_certificate != 1) {
            // return response()->json(["message" => "ไม่พบใบประกาศนียบัตรที่ดังกล่าว"], 404, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
            return redirect(str_replace("{GROUP_KEY}", $dataGroup->key, config('constants.URL_GROUP.MY_COURSES')));
        }

        $data->topics = Topics::where('courses_id', $data->courses_id)->whereNull('parent')->orderBy('order','asc')->get();
        for($i=0; $i<count($data->topics); $i++) {
            $data->topics[$i]->parent = Topics::where('parent', $data->topics[$i]->id)->orderBy('order','asc')->get();
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

                    $data->topics[$i]->parent[$a]->progress = $data->topics[$i]->parent[$a]->duration_enroll/$data->topics[$i]->parent[$a]->duration;
                    $data->topics[$i]->parent[$a]->percentage = number_format($data->topics[$i]->parent[$a]->progress * 100);

                    $data->duration2topic += $data->topics[$i]->parent[$a]->duration;
                    $data->duration2enroll += $data->topics[$i]->parent[$a]->duration_enroll;

                    $data->duration2progress = $data->duration2enroll/$data->duration2topic;
                    $data->duration2percentage = number_format($data->duration2progress * 100);

            }

        }

        if($data->courses->percentage <= $data->duration2percentage){
            $data->courses->learning = true;
        }else{
            $data->courses->learning = false;
        }

        $data->exam = $data->enroll2quiz()->where('type', 3)->orderBy('score', 'desc')->first();
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
        $data->survey = $data->enroll2quiz()->where('type', 5)->orderBy('score', 'desc')->first();
        if($data->survey || $countSurvey == 0){
            $data->survey = true;
        }else{
            $data->survey = false;
        }

        if($data->exam){
            if(($data->courses->learning == true) and ($data->exam->learning == true) and ($data->survey)){
                $data->certificate = true;

                if (!isset($data->certificate_reference_number)) {
                    // return redirect($referer);
                    $dataCerRef = $this->getCerRef($dataSession, $dataGroup, $data);
                    if (!$dataCerRef['isSuccess']) {
                        return response()->json($dataCerRef['errorInfo'], $dataCerRef['statusCode']);
                    }

                    $data = $dataCerRef['dataEnroll'];
                }

                $data->certificate_datetime_th = $oFunc->thai_date_fullmonth(strtotime($data->certificate_datetime));
                if($data->exam){
                    $data->datetime_full_format = $lang == "en" ? Carbon::parse($data->exam->datetime)->format('j F Y') : $oFunc->thai_date_fullmonth(strtotime($data->exam->datetime));
                }else{
                    $data->datetime_full_format = $lang == "en" ? Carbon::parse($data->certificate_datetime)->format('j F Y') : $oFunc->thai_date_fullmonth(strtotime($data->certificate_datetime));
                }

                // print_r($data);

                $pdf = PDF::setOptions([
                    'defaultFont' => 'thsarabunnew',
                    'isRemoteEnabled' => true,
                ]);

                // return response()->view('certificate', ['data' => $data]);

                $pdf->loadView($certificateTemplate, ['data' => $data, 'lang' => $lang]);
                return $pdf->setPaper('a3', 'landscape')->download('Certificate-'.$data->courses->code.'-'.strtoupper($lang).'.pdf');

            } else {
                $data->certificate = false;

                // return response()->json(["message" => "ไม่พบใบประกาศนียบัตรที่ดังกล่าว เนื่องจากคุณยังไม่ผ่านเกณฑ์ตามหลักสูตร หรือยังไม่ได้ทำแบบสำรวจ"], 404, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);

                $searchArr = ["{GROUP_KEY}", "{COURSE_ID}"];
                $replaceArr = [$dataGroup->key, $course_id];
                return redirect(str_replace($searchArr, $replaceArr, config('constants.URL_GROUP.MY_COURSES_WITH_CER'))."/".$lang);

                // return redirect($referer);
            }
        } else if (($data->courses->learning == true) && ($data->survey)) {
            $data->certificate = true;

            if (!isset($data->certificate_reference_number)) {
                // return redirect($referer);
                $dataCerRef = $this->getCerRef($dataSession, $dataGroup, $data);
                if (!$dataCerRef['isSuccess']) {
                    return response()->json($dataCerRef['errorInfo'], $dataCerRef['statusCode']);
                }

                $data = $dataCerRef['dataEnroll'];
            }

            $data->certificate_datetime_th = $oFunc->thai_date_fullmonth(strtotime($data->certificate_datetime));
            if($data->exam){
                $data->datetime_full_format = $lang == "en" ? Carbon::parse($data->exam->datetime)->format('j F Y') : $oFunc->thai_date_fullmonth(strtotime($data->exam->datetime));
            }else{
                $data->datetime_full_format = $lang == "en" ? Carbon::parse($data->certificate_datetime)->format('j F Y') : $oFunc->thai_date_fullmonth(strtotime($data->certificate_datetime));
            }

            // print_r($data);

            $pdf = PDF::setOptions([
                'defaultFont' => 'thsarabunnew',
                'isRemoteEnabled' => true,
            ]);

            // return response()->view('certificate', ['data' => $data]);

            $pdf->loadView($certificateTemplate, ['data' => $data, 'lang' => $lang]);
            return $pdf->setPaper('a3', 'landscape')->download('Certificate-'.$data->courses->code.'-'.strtoupper($lang).'.pdf');
        } else {
            $data->certificate = false;

            // return response()->json(["message" => "ไม่พบใบประกาศนียบัตรที่ดังกล่าว เนื่องจากคุณยังไม่ได้ทำแบบทดสอบ"], 404, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);

            $searchArr = ["{GROUP_KEY}", "{COURSE_ID}"];
            $replaceArr = [$dataGroup->key, $course_id];
            return redirect(str_replace($searchArr, $replaceArr, config('constants.URL_GROUP.MY_COURSES_WITH_CER'))."/".$lang);

            // return redirect($referer);
        }

    }

    public function dummyData(Request $request, _SecurityController $_security)
    {
        $results = $_security->encryptAndSignData(json_encode($request->all(), JSON_UNESCAPED_UNICODE));
        return response()->json(array("data" => $results['data'], "signature" => $results['signature']), 200);
    }

    public function debug(Request $request, _SecurityController $_security)
    {
        // dd(Auth::guard('admin')->user());
        dd(Auth::guard()->user());
    }


    public function getSessionUser(Request $request)
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

        // $_user->groups = $_user->groups;
        // $_user->sub_groups = $_user->sub_groups;
        // $_user->level_groups = $_user->level_groups;
        // $_user->action_login = session()->get('_action_login');

        // $request->session()->regenerate();
        $request->session()->put('_user', $dataMember);
        $this->swappingSession($dataMember);

        $dataMember->groups = $dataMember->groups;
        $dataMember->sub_groups = $dataMember->sub_groups;
        $dataMember->level_groups = $dataMember->level_groups;
        $dataMember->action_login = session()->get('_action_login');

        return response()->json(array("data" => $dataMember, "debug_time" => time()), 200);
    }

    public function getTempSession(Request $request)
    {
        $tmpUser = session()->get('temp_user');
        return response()->json(['data' => $tmpUser], 200);
    }

    public function forgetTempSession(Request $request)
    {
        session()->forget('temp_user');
        return response()->json([], 200);
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

    private function getCerRef($dataSession, $dataGroup, $dataEnroll)
    {
        $_security = new _SecurityController;
        $oFunc = new _FunctionsController;
        $dataReturn = ["isSuccess" => true];

        if ($dataGroup->is_connect_regis == 1) {
            /* ===== START R2 (UPDATE ENROLL) ===== */
            // $dataGroup = Groups::find($dataSession['groups_id']);

            $paramEnroll = array(
                "courseid" => $dataEnroll->courses_id,
                // "courseid" => 4, // Fix for test (skip bug).
                "userref" => $dataSession->ref_id,
                // "userref" => 7000978, // Fix for test (skip bug).
                "groupid" => $dataGroup->keyset,
                "compCode" => $dataSession->company_code,
                "status" => "P",
            );

            $results = $_security->encryptAndSignData(json_encode($paramEnroll, JSON_UNESCAPED_UNICODE));

            $oClient = new httpClient();

            try {
                $responseEnroll = $oClient->request('POST', config('constants._SET_URL.R2'), [
                    'json' => $results,
                ]);

                // Callback
                $respData = json_decode($responseEnroll->getBody(), true);

                if (isset($respData['certificate_reference_number'])) {
                    $dataEnroll->certificate_datetime = date('Y-m-d H:i:s');
                    $dataEnroll->certificate_reference_number = $respData['certificate_reference_number'];
                    Enroll::find($dataEnroll->id)->update([
                        'certificate_reference_number' => $dataEnroll->certificate_reference_number,
                        'certificate_datetime' => $dataEnroll->certificate_datetime
                    ]);
                }

            } catch(RequestException $e) {
                if ($e->hasResponse()) {
                    return Response::json(json_decode($e->getResponse()->getBody(), true), $e->getResponse()->getStatusCode());
                }

                $dataReturn["isSuccess"] = false;
                $dataReturn["errorInfo"] = $e->hasResponse() ? json_decode($e->getResponse()->getBody(), true) : ["error_msg" => config('constants._errorMessage._500')] ;
                $dataReturn["statusCode"] = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 500 ;
            }
            /* ===== END R2 (UPDATE ENROLL) ===== */
        } else {
            $dataEnroll->certificate_datetime = date('Y-m-d H:i:s');
            if ($dataEnroll->enroll_type == 1 || $dataEnroll->enroll_type == 2) {
                $sessionNo = str_pad($dataEnroll->enroll_type_id, 4, "0", STR_PAD_LEFT);
            } else {
                $sessionNo = "0001";
            }

            $sessionNo .= date('Y') + 543;

            $dataEnroll->certificate_reference_number = "SET".strtoupper($dataEnroll->courses->code).$sessionNo.$dataEnroll->members_id;

            Enroll::find($dataEnroll->id)->update([
                'certificate_reference_number' => $dataEnroll->certificate_reference_number,
                'certificate_datetime' => $dataEnroll->certificate_datetime
            ]);
        }

        $dataReturn["dataEnroll"] = $dataEnroll;
        return $dataReturn;
    }

    public function testThaiLanguage(Request $request, _SecurityController $_security)
    {
        // return response($request->all());
        return response()->json($request->all(), 200, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], 200);
    }

    public function enroll(Request $request, _SecurityController $_security)
    {
        /* ===== START R2 (UPDATE ENROLL) ===== */
        $paramEnroll = array(
            "courseid" => $request->courseid,
            "userref" => $request->userref,
            "groupid" => $request->groupid,
            "compCode" => $request->compCode,
            "status" => $request->status
        );

        $results = $_security->encryptAndSignData(json_encode($paramEnroll, JSON_UNESCAPED_UNICODE));

        $oClient = new httpClient();

        try {
            $response = $oClient->request('POST', config('constants._SET_URL.R2'), [
                'json' => $results
            ]);

            return response()->json(['is_success' => true, 'data' => $request->json()->all()], 200);

        } catch(RequestException $e) {
            if ($e->hasResponse()) {

                return response()->json(json_decode($e->getResponse()->getBody(), true), $e->getResponse()->getStatusCode());

            } else {

                return response()->json(["error_msg" => "Internal Server Error (R)"], 500);

            }
        }
        /* ===== END R2 (UPDATE ENROLL) ===== */
    }

    public function enrollList(Request $request, _SecurityController $_security)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 3600);

        // $from_datetime = date("Y-m-d H:i:s", strtotime($fromDate." ".$fromTime));
        // $to_datetime = date("Y-m-d H:i:s", strtotime($toDate." ".$toTime));

        // $data = Enroll::whereBetween('enroll_datetime', array($from_datetime, $to_datetime))->get();
        $data = Enroll::whereHas('members')->whereHas('groups', function ($query) {
            $query->where('is_connect_regis', 1);
        })->with([
            'groups' => function($query) {
                $query->select('id', 'keyset');
            },
            'members' => function($query) {
                $query->select('id', 'ref_id', 'company_code');
            }
        // ])->orderBy('id', 'asc')->get();
        ])->orderBy('id', 'asc');

        $dataEnrolls = [];
        $i = 0;

        $data->chunk(1000, function($enrolls) use (&$dataEnrolls, &$i) {
            foreach ($enrolls as $enroll) {
                $dataEnrolls[$i] = [
                    // "id" => $data[$i]->id,
                    "courseid" => $enroll->courses_id,
                    "userref" => $enroll->members->ref_id,
                    "groupid" => $enroll->groups->keyset,
                    "compCode" => $enroll->members->company_code,
                    "status" => is_null($enroll->certificate_reference_number) ? "C" : "P"
                ];

                $i++;
            }
        });

        return response()->json($dataEnrolls, 200);

        for ($i=0; $i < count($data); $i++) {
            $enrolls[$i] = [
                // "id" => $data[$i]->id,
                "courseid" => $data[$i]->courses_id,
                "userref" => $data[$i]->members->ref_id,
                "groupid" => $data[$i]->groups->keyset,
                "compCode" => $data[$i]->members->company_code,
                "status" => is_null($data[$i]->certificate_reference_number) ? "C" : "P"
            ];
        }

        return response()->json($enrolls, 200);
    }


}
