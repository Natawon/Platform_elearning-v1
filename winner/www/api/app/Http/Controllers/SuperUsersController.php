<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\SuperUsersConf;

use Auth;
use Hash;
use Validator;
use Mail;

class SuperUsersController extends Controller
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
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if ($request->has('search')) {
            $data = $data->where(function ($query) use ($request) {
                $query->where('username', 'like', '%'.$request['search'].'%')
                      ->orWhere('first_name', 'like', '%'.$request['search'].'%')
                      ->orWhere('last_name', 'like', '%'.$request['search'].'%')
                      ->orWhere('email', 'like', '%'.$request['search'].'%');
            });
        }

        if ($request->has('groups_id')) {
            $groupsQuery = array_where($authSessionGroups->toArray(), function ($authSessionGroup) use ($request) {
                return $authSessionGroup['id'] == $request['groups_id'];
            });
        } else {
            $groupsQuery = $authSessionGroups;
        }

        if ($request->has('sub_groups_id')) {
            $data = $data->where('sub_groups_id', $request['sub_groups_id']);
        }

        $data = $data->where('super_users', 1);
        $data = $data->with('groups');
        $data = $data->whereHas('groups', function($query) use ($groupsQuery) {
            $query->whereIn('groups_id', array_pluck($groupsQuery, 'id'));
        });

        $data = $data->orderBy($request['order_by'],$request['order_direction']);
        $data = $data->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $data[$i]->sub_groups = $data[$i]->sub_groups()->first();

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

        $validator = Validator::make($input, [
            // 'password' => 'required|between:8,255|case_diff|numbers|letters|symbols|not_contain_credentials:'.$input['username'],
            'username' => 'required|between:6,30',
            'password' => 'required|between:8,50|case_diff|numbers|letters',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'mobile' => 'required|max:255',
            'limit_groups' => 'required|max:11',
            'admins_groups_id' => 'required|numeric',
            'groups_id' => 'required|numeric',
            'sub_groups_id' => 'required|numeric',
            'active' => 'required|max:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = new Admins;
        $data->fill($input);
        $data->super_users = 1;
        $data->encode_password = Hash::make($input['password']);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if (isset($input['admin2level_group'])) {
            $admin2level_group = $input['admin2level_group'];
            $data->admin2level_group()->sync($admin2level_group);
        }

        if ($is_success) {
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
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "admins")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $input = $request->json()->all();

        $validator = Validator::make($input, [
            // 'password' => 'required|between:8,255|case_diff|numbers|letters|symbols|not_contain_credentials:'.$input['username'],
            'username' => 'required|between:6,30',
            'password' => 'required|between:8,50|case_diff|numbers|letters',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'mobile' => 'required|max:255',
            'limit_groups' => 'required|max:11',
            'admins_groups_id' => 'required|numeric',
            'groups_id' => 'required|numeric',
            'sub_groups_id' => 'required|numeric',
            'active' => 'required|max:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = new Admins;
        $data = $data->find($id);

        $isReActive = false;
        if ($data->active != 1 && $input['active'] == 1) {
            $data->active_remark = 3;
            $data->last_login = null;
            $data->incorrect_password = 0;
            unset($input['incorrect_password']);
            $isReActive = true;
        }

        $data->fill($input);
        $data->encode_password = Hash::make($input['password']);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if (isset($input['admin2level_group'])) {
            $admin2level_group = $input['admin2level_group'];
            $data->admin2level_group()->sync($admin2level_group);
        }

        if ($is_success) {
            $message = "The super users has been updated.";

            if ($isReActive) {
                /* BEGIN E-MAIL FUNCTION */
                // Notify Mail (Member's reactivated)
                $url = config('constants._BASE_BACKEND_URL')."login.html";
                $dataMail = array(
                    'dataAdmin'=>$data,
                    'dataGroups'=> $data->groups,
                    'url' => $url
                );
                Mail::send('superusers-reactivated-mail', $dataMail, function($mail) use ($dataMail) {
                    $mail->to($dataMail['dataAdmin']['email'], $dataMail['dataAdmin']['first_name']." ".$dataMail['dataAdmin']['last_name'])->subject('แจ้งการเปิดใช้งานสมาชิก '.$dataMail['dataGroups']['subject']);
                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                });
                /* END E-MAIL FUNCTION */
            }
        } else {
            $message = "Failed to update the super users.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id, _RolesController $oRole)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "admins")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = new Admins;
        $data = $data->find($id);
        $is_success = $data->delete();
        if ($is_success) {
            $message = "The super user has been deleted.";
        } else {
            $message = "Failed to delete the super user.";
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
            $message = "The super user has been updated.";
        } else {
            $message = "Failed to update the super user.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

}
