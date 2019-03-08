<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\AdminsLogs;
use App\Models\PasswordHistories;

use Auth;
use Hash;
use Validator;
use Mail;
use Jenssegers\Agent\Agent;
use Torann\GeoIP\Facades\GeoIP as GeoIP;

class AdminsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $data = new Admins;

        if ($request->has('search')) {
            $data = $data->where(function ($query) use ($request) {
                $query->where('username', 'like', '%'.$request['search'].'%')
                      ->orWhere('first_name', 'like', '%'.$request['search'].'%')
                      ->orWhere('last_name', 'like', '%'.$request['search'].'%')
                      ->orWhere('email', 'like', '%'.$request['search'].'%');
            });
        }

        $data = $data->whereNull('super_users');
        $data = $data->orderBy($request['order_by'],$request['order_direction']);
        $data = $data->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $data[$i]->admins_groups = $data[$i]->admins_groups()->first();
        }
        return response()->json($data, 200);
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
        $input = $request->json()->all();

        $validator = Validator::make($request->json()->all(), [
            // 'password' => 'required|between:8,255|case_diff|numbers|letters|symbols|not_contain_credentials:'.$input['username'],
            'username' => 'required|between:6,30',
            'password' => 'required|between:8,50|case_diff|numbers|letters',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'mobile' => 'required|max:255',
            'admins_groups_id' => 'required|numeric',
            'active' => 'required|max:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = new Admins;
        // $input = $request->json()->all();
        $data->fill($input);
        $data->encode_password = Hash::make($input['password']);
        $data->last_changed_password = date('Y-m-d H:i:s');
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->create_by = Auth::user()->id;
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();
        if ($is_success) {
            $dataPwdHistory = new PasswordHistories;
            $dataPwdHistory->admin_id = $data->id;
            $dataPwdHistory->password = $data->password;
            $dataPwdHistory->create_datetime = date('Y-m-d H:i:s');
            $dataPwdHistory->modify_datetime = date('Y-m-d H:i:s');
            $dataPwdHistory->save();

            $message = "The admins has been created.";
        } else {
            $message = "Failed to create the admins.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'createdId' => $data->id), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
        $data = Admins::find($id);
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
    public function update($id, Request $request)
    {
        //
        $input = $request->json()->all();

        $validator = Validator::make($request->json()->all(), [
            // 'password' => 'required|between:8,255|case_diff|numbers|letters|symbols|not_contain_credentials:'.$input['username'],
            'username' => 'required|between:6,30',
            'password' => 'required|between:8,50|case_diff|numbers|letters',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'mobile' => 'required|max:255',
            'admins_groups_id' => 'required|numeric',
            'active' => 'required|max:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Admins::find($id);
        // $input = $request->json()->all();

        $isReActive = false;
        if ($data->active != 1 && $input['active'] == 1) {
            $data->active_remark = 3;
            // $data->last_login = null;
            $data->incorrect_password = 0;
            unset($input['incorrect_password']);
            $isReActive = true;
        }

        if ($data->password != $input['password']) {
            $data->last_changed_password = date('Y-m-d H:i:s');
        }

        $data->fill($input);
        $data->encode_password = Hash::make($input['password']);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if ($is_success) {
            $message = "The admins has been updated.";

            if ($isReActive) {
                /* BEGIN E-MAIL FUNCTION */
                // Notify Mail (Member's reactivated)
                $url = config('constants._BASE_BACKEND_URL')."login.html";
                $dataMail = array(
                    'dataAdmin'=>$data,
                    'url' => $url
                );
                Mail::send('admins-reactivated-mail', $dataMail, function($mail) use ($dataMail) {
                    $mail->to($dataMail['dataAdmin']['email'], $dataMail['dataAdmin']['first_name']." ".$dataMail['dataAdmin']['last_name'])->subject('แจ้งการเปิดใช้งานสมาชิก');
                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                });
                /* END E-MAIL FUNCTION */
            }
        } else {
            $message = "Failed to update the admins.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
        $data = Admins::find($id);
        $is_success = $data->delete();
        if ($is_success) {
            $message = "The admins has been deleted.";
        } else {
            $message = "Failed to delete the admins.";
        }
        return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }

    public function updateStatus($id, Request $request)
    {
        //
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'status' => 'required|max:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Admins::find($id);
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The admins has been updated.";
        } else {
            $message = "Failed to update the admins.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function targets()
    {
        $data = new Admins();
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($authSession->super_users){

            $data = $data->where('id', $authSession->id)->get();
            for($i=0; $i<count($data); $i++) {
                $data[$i]->sub_groups = $data[$i]->sub_groups()->first();
                $data[$i]->level_groups = $data[$i]->level_groups()->where('approve', 1)->orderBy('order','asc')->get();
                for($l=0; $l<count($data[$i]->level_groups); $l++) {
                    $data[$i]->level_groups[$l]->members = $data[$i]->level_groups[$l]->members()->get();
                    for($m=0; $m<count($data[$i]->level_groups[$l]->members); $m++) {
                        $data[$i]->level_groups[$l]->members[$m]->title = $data[$i]->level_groups[$l]->members[$m]->first_name." ".$data[$i]->level_groups[$l]->members[$m]->last_name." (".$data[$i]->level_groups[$l]->title." / ".$data[$i]->sub_groups->title.")";
                    }
                }
                $data[$i]->access_level_groups = $data[$i]->admin2level_group()->where('approve', 1)->orderBy('order','asc')->get();
                for($l=0; $l<count($data[$i]->access_level_groups); $l++) {
                    $data[$i]->access_level_groups[$l]->members = $data[$i]->access_level_groups[$l]->members()->get();
                    for($m=0; $m<count($data[$i]->access_level_groups[$l]->members); $m++) {
                        $data[$i]->access_level_groups[$l]->members[$m]->title = $data[$i]->access_level_groups[$l]->members[$m]->first_name." ".$data[$i]->access_level_groups[$l]->members[$m]->last_name." (".$data[$i]->access_level_groups[$l]->title." / ".$data[$i]->sub_groups->title.")";
                    }
                }
            }

        }else{

            $data = $data->with('admins_groups')->whereHas('admins_groups', function($query) use ($authSessionGroups) {
                $auth = 0;
                foreach($authSessionGroups as $authSessionGroups){
                    $auth++;
                    if($auth==1){
                        $query->where('groups_id', $authSessionGroups->id);
                    }else{
                        $query->orWhere('groups_id', $authSessionGroups->id);
                    }
                }
            })->get();
            for($i=0; $i<count($data); $i++) {
                $data[$i]->sub_groups = $data[$i]->sub_groups()->first();
                $data[$i]->level_groups = $data[$i]->level_groups()->where('approve', 1)->orderBy('order','asc')->get();
                for($l=0; $l<count($data[$i]->level_groups); $l++) {
                    $data[$i]->level_groups[$l]->members = $data[$i]->level_groups[$l]->members()->get();
                    for($m=0; $m<count($data[$i]->level_groups[$l]->members); $m++) {
                        $data[$i]->level_groups[$l]->members[$m]->title = $data[$i]->level_groups[$l]->members[$m]->first_name." ".$data[$i]->level_groups[$l]->members[$m]->last_name." (".$data[$i]->level_groups[$l]->title." / ".$data[$i]->sub_groups->title.")";
                    }
                }
            }

        }

        return response()->json($data, 200);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'change_password' => 'required|between:8,255|case_diff|numbers|letters',
            'confirm_change_password' => 'required|same:change_password',
        ]);

        if ($validator->fails()) {
            // return response()->json($validator->messages(), 422);
            $errors = $validator->errors();
            if (!empty($errors->first('confirm_change_password'))) {
                return response()->json(['is_error' => true, 'message' => 'ยืนยันรหัสผ่านไม่ถูกต้อง'], 422);
            } else {
                return response()->json(['is_error' => true, 'message' => $errors->first('change_password')], 422);
                // return response()->json(['is_error' => true, 'message' => 'รหัาผ่านไม่ถูกต้องตามเงื่อนไขความปลอดภัย'], 422);
            }
        }

        $dataSession = Auth::user();

        $dataPwdExist = PasswordHistories::where('admin_id', $dataSession->id)->whereNull('create_by')->orderBy('id', 'desc')->limit(5)->get()->first(function ($pwd) use ($request) {
            return $pwd->password == $request['change_password'];
        });

        if ($dataPwdExist) {
            $status = 422;
            $message = "รหัสผ่านดังกล่าวเคยถูกใช้ไปแล้ว";
            $alert_msg = $message;

            $this->logs('change-password', '{"alert_msg":"'.$alert_msg.'"}', $status, $dataSession->id, json_encode($request->all(), JSON_UNESCAPED_UNICODE), $dataSession->admins_groups_id, $dataSession->groups_id, $dataSession->sub_groups_id);
            return response()->json(array('is_error' => true, 'message' => $message), $status);
        }

        $data = new Admins;
        $data = $data->find($dataSession->id);
        $data->password = $request['change_password'];
        $data->encode_password = Hash::make($request['change_password']);
        $data->last_changed_password = date('Y-m-d H:i:s');
        $data->active_remark = 1;
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
                // 'dataGroup'=>$dataSession->admins_groups,
            //     'change_password'=>$request['change_password']
            // );
            // Mail::send('change-password-mail', $dataMail, function($mail) use ($dataMail) {
            //     $mail->to($dataMail['dataMember']['email'], $dataMail['dataMember']['first_name']." ".$dataMail['dataMember']['last_name'])->subject('แจ้งการเปลี่ยนรหัสผ่าน '.$dataMail['dataGroup']['subject']);
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

        $this->logs('change-password', '{"alert_msg":"'.$alert_msg.'"}', $status, $data->id, json_encode($request->all(), JSON_UNESCAPED_UNICODE), $data->admins_groups_id, $data->groups_id, $data->sub_groups_id);
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);

    }

    public function logs($type, $return, $status, $admins_id, $data, $admins_groups_id, $groups_id, $sub_groups_id)
    {
        ////Logs////
        $dataGeoIP = GeoIP::getLocation();

        $agent = new Agent();
        $logs = new AdminsLogs;
        if($admins_groups_id){ $logs->admins_groups_id = $admins_groups_id; }
        if($groups_id){ $logs->groups_id = $groups_id; }
        if($sub_groups_id){ $logs->sub_groups_id = $sub_groups_id; }
        if($admins_id){ $logs->admins_id = $admins_id; }
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

}
