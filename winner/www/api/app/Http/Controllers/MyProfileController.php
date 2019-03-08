<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\PasswordHistories;

use Auth;
use Hash;
use Validator;
use Mail;

class MyProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(_RolesController $oRole, $id)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "admins")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = new Admins;
        $data = $data->find($id);
        $data->level_group = $data->admin2level_group()->get();
        return response()->json($data->toArray(), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, Request $request, _RolesController $oRole)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id, _RolesController $oRole)
    {
        //
    }

    public function showSelf(_RolesController $oRole)
    {
        $authSession = Auth::user();

        $data = new Admins;
        $data = $data->find($authSession->id);
        // $data->level_group = $data->admin2level_group()->get();
        return response()->json($data->toArray(), 200);
    }

    public function updateSelf(Request $request, _RolesController $oRole)
    {
        $authSession = Auth::user();
        $input = $request->json()->all();

        if ($authSession->super_users) {
            $validator = Validator::make($input, [
                // 'password' => 'required|between:8,50|case_diff|numbers|letters',
                'mobile' => 'required|max:255',
            ]);
        } else if (!$oRole->isSuper()) {
            $validator = Validator::make($input, [
                // 'username' => 'required|unique:admins,username,'.$authSession->id.'|between:6,30',
                // 'password' => 'required|between:8,50|case_diff|numbers|letters',
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'email' => 'required|email|max:255',
                'mobile' => 'required|max:255',
            ]);
        } else {
            $validator = Validator::make($input, [
                // 'username' => 'required|unique:admins,username,'.$authSession->id.'|between:6,30',
                // 'password' => 'required|between:8,50|case_diff|numbers|letters',
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'email' => 'required|email|max:255',
                'mobile' => 'required|max:255',
                'admins_groups_id' => 'required|numeric',
                'active' => 'required|max:1'
            ]);
        }

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = new Admins;
        $data = $data->find($authSession->id);

        // $isReActive = false;
        // if ($data->active != 1 && $input['active'] == 1) {
        //     $data->active_remark = 3;
        //     $data->last_login = null;
        //     $data->incorrect_password = 0;
        //     unset($input['incorrect_password']);
        //     $isReActive = true;
        // }

        if ($authSession->super_users) {
            $data->fill(array_only($input, ['mobile', 'phone']));
        } else if (!$oRole->isSuper()) {
            $data->fill(array_only($input, ['first_name', 'last_name', 'email', 'mobile', 'phone', 'address', 'avatar']));
        } else {
            $data->fill(array_except($input, ['username', 'password']));
        }

        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();

        // if (isset($input['admin2level_group'])) {
        //     $admin2level_group = $input['admin2level_group'];
        //     $data->admin2level_group()->sync($admin2level_group);
        // }

        if ($is_success) {
            $message = "Your profile has been updated.";

            // $authSession->update($data->toArray());

            // if ($isReActive) {
            //     /* BEGIN E-MAIL FUNCTION */
            //     // Notify Mail (Member's reactivated)
            //     $url = config('constants._BASE_BACKEND_URL')."login.html";
            //     $dataMail = array(
            //         'dataAdmin'=>$data,
            //         'dataGroups'=> $data->groups,
            //         'url' => $url
            //     );
            //     Mail::send('superusers-reactivated-mail', $dataMail, function($mail) use ($dataMail) {
            //         $mail->to($dataMail['dataAdmin']['email'], $dataMail['dataAdmin']['first_name']." ".$dataMail['dataAdmin']['last_name'])->subject('แจ้งการเปิดใช้งานสมาชิก '.$dataMail['dataGroups']['subject']);
            //         $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
            //     });
            //     /* END E-MAIL FUNCTION */
            // }
        } else {
            $message = "Failed to update your profile.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function changeAccess(Request $request, _RolesController $oRole, AdminsController $_admin)
    {
        $authSession = Auth::user();
        $input = $request->json()->all();

        if ($authSession->super_users) {
            $input = array_only($input, ['old_password', 'new_password', 'confirm_new_password']);
            $validator = Validator::make($input, [
                'old_password' => 'required|max:255',
                'new_password' => 'required|between:8,50|case_diff|numbers|letters',
                'confirm_new_password' => 'required|same:new_password',
            ]);
        } else if (!$oRole->isSuper()) {
            $input = array_only($input, ['username', 'old_password', 'new_password', 'confirm_new_password']);
            $validator = Validator::make($input, [
                'username' => 'required|unique:admins,username,'.$authSession->id.'|between:6,30',
                'old_password' => 'required|max:255',
                'new_password' => 'required|between:8,50|case_diff|numbers|letters',
                'confirm_new_password' => 'required|same:new_password',
            ]);
        } else {
            $input = array_only($input, ['username', 'old_password', 'new_password', 'confirm_new_password']);
            $validator = Validator::make($input, [
                'username' => 'required|unique:admins,username,'.$authSession->id.'|between:6,30',
                'old_password' => 'required|max:255',
                'new_password' => 'required|between:8,50|case_diff|numbers|letters',
                'confirm_new_password' => 'required|same:new_password',
            ]);
        }

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Admins::find($authSession->id);

        if ($data->password != $input['old_password']) {
            $status = 422;
            $message = "รหัสผ่านเดิมไม่ถูกต้อง";
            $alert_msg = $message;

            $_admin->logs('change-password', '{"alert_msg":"'.$alert_msg.'"}', $status, $authSession->id, json_encode($input, JSON_UNESCAPED_UNICODE), $authSession->admins_groups_id, $authSession->groups_id, $authSession->sub_groups_id);
            return response()->json(array('is_error' => true, 'message' => $message), $status);
        }

        $dataPwdExist = PasswordHistories::where('admin_id', $authSession->id)->whereNull('create_by')->orderBy('id', 'desc')->limit(5)->get()->first(function ($pwd) use ($input) {
            return $pwd->password == $input['new_password'];
        });

        if ($dataPwdExist) {
            $status = 422;
            $message = "รหัสผ่านใหม่ดังกล่าวเคยถูกใช้ไปแล้ว";
            $alert_msg = $message;

            $_admin->logs('change-password', '{"alert_msg":"'.$alert_msg.'"}', $status, $authSession->id, json_encode($input, JSON_UNESCAPED_UNICODE), $authSession->admins_groups_id, $authSession->groups_id, $authSession->sub_groups_id);
            return response()->json(array('is_error' => true, 'message' => $message), $status);
        }

        if (isset($input['username'])) {
            $data->username = $input['username'];
        }

        $data->password = $input['new_password'];
        $data->encode_password = Hash::make($input['new_password']);
        $data->last_changed_password = date('Y-m-d H:i:s');
        $is_success = $data->save();

        if ($is_success) {
            $dataOldPwd = PasswordHistories::where('active', 1)->update(['active' => 0]);

            $dataPwdHistory = new PasswordHistories;
            $dataPwdHistory->admin_id = $data->id;
            $dataPwdHistory->password = $data->password;
            $dataPwdHistory->create_datetime = date('Y-m-d H:i:s');
            $dataPwdHistory->modify_datetime = date('Y-m-d H:i:s');
            $dataPwdHistory->save();

            // $dataMail = array(
            //     'dataMember'=>$data,
                // 'dataGroup'=>$authSession->admins_groups,
            //     'new_password'=>$input['new_password']
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
            $message = "เปลี่ยนข้อมูลการเข้าระบบเรียบร้อย";
            $alert_msg = "success";
        } else {
            $status = 401;
            $message = "เกิดข้อผิดพลาด ไม่สามารถเปลี่ยนข้อมูลได้";
            $alert_msg = $message;
        }

        $_admin->logs('change-password', '{"alert_msg":"'.$alert_msg.'"}', $status, $data->id, json_encode($input, JSON_UNESCAPED_UNICODE), $data->admins_groups_id, $data->groups_id, $data->sub_groups_id);
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);

    }

}
