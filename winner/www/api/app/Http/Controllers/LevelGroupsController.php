<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\Groups;
use App\Models\LevelGroups;
use App\Models\Members;
use App\Models\MembersPreApproved;
use App\Models\SubGroups;

use Auth;
use Hash;
use Mail;

class LevelGroupsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request, _RolesController $oRole)
    {
        //
        $data = new LevelGroups;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($request->has('search')){
            $data = $data->where(function ($query) use ($request) {
                $query->where('level_groups.title', 'like', '%'.$request['search'].'%');
            });
        }

        if ($request->has('sub_groups_id')) {
            $data = $data->where('sub_groups_id', $request['sub_groups_id']);
        }

        if ($authSession->super_users) {
            $data = $data->where('admins_id', $authSession->id);
        } else if (!$oRole->isSuper()) {
            if ($request->has('groups_id')) {
                $groupsQuery = array_where($authSessionGroups->toArray(), function ($authSessionGroup) use ($request) {
                    return $authSessionGroup['id'] == $request['groups_id'];
                });
            } else {
                $groupsQuery = $authSessionGroups;
            }
            $data = $data->with('groups');
            $data = $data->whereHas('groups', function($query) use ($groupsQuery) {
                $query->whereIn('groups_id', array_pluck($groupsQuery, 'id'));
            });
        } else {
            if ($request->has('groups_id')) {
                $data = $data->where('groups_id', $request['groups_id']);
            }
        }

        $data = $data->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
            $data[$i]->admins = $data[$i]->admins()->first();
            $data[$i]->sub_groups = $data[$i]->sub_groups()->first();
            $data[$i]->members = $data[$i]->members()->count();
        }

        return response()->json($data, 200);
    }

    public function access_groups(Request $request, _RolesController $oRole)
    {
        //
        $per_page = $request->input('per_page', 10);
        $order_by = $request->input('order_by', 'order');
        $order_direction = $request->input('order_direction', 'asc');

        $data = new Admins;
        $authSession = Auth::user();
        if($authSession->super_users){
            $data = $data->find($authSession->id);
        }
        $data = $data->admin2level_group();

        if($request->has('search')){
            $data = $data->where(function ($query) use ($request) {
                $query->where('level_groups.title', 'like', '%'.$request['search'].'%');
            });
        }

        if ($request->has('sub_groups_id')) {
            $data = $data->where('sub_groups_id', $request['sub_groups_id']);
        }

        $data = $data->orderBy($order_by, $order_direction)->paginate($per_page);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
            $data[$i]->admins = $data[$i]->admins()->first();
            $data[$i]->sub_groups = $data[$i]->sub_groups()->first();
            $data[$i]->members = $data[$i]->members()->count();
        }

        return response()->json($data, 200);
    }

    public function waiting_groups(Request $request, _RolesController $oRole)
    {
        //
        $per_page = $request->input('per_page', 10);
        $order_by = $request->input('order_by', 'order');
        $order_direction = $request->input('order_direction', 'asc');

        $data = new LevelGroups;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($request->has('search')){
            $data = $data->where(function ($query) use ($request) {
                $query->where('level_groups.title', 'like', '%'.$request['search'].'%');
            });
        }

        if ($request->has('sub_groups_id')) {
            $data = $data->where('sub_groups_id', $request['sub_groups_id']);
        }

        if ($authSession->super_users) {
            $data = $data->where('admins_id', $authSession->id);
        } else if (!$oRole->isSuper()) {
            if ($request->has('groups_id')) {
                $groupsQuery = array_where($authSessionGroups->toArray(), function ($authSessionGroup) use ($request) {
                    return $authSessionGroup['id'] == $request['groups_id'];
                });
            } else {
                $groupsQuery = $authSessionGroups;
            }
            $data = $data->with('groups');
            $data = $data->whereHas('groups', function($query) use ($groupsQuery) {
                $query->whereIn('groups_id', array_pluck($groupsQuery, 'id'));
            });
        } else {
            if ($request->has('groups_id')) {
                $data = $data->where('groups_id', $request['groups_id']);
            }
        }

        $data = $data->where('approve', 0);
        $data = $data->orderBy($order_by,$order_direction)->paginate($per_page);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
            $data[$i]->admins = $data[$i]->admins()->first();
            $data[$i]->sub_groups = $data[$i]->sub_groups()->first();
            $data[$i]->members = $data[$i]->members()->count();
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
            'groups_id' => 'required|numeric',
            'sub_groups_id' => 'required|numeric',
            'title' => 'required|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $authSession = Auth::user();

        $data = new LevelGroups;
        $data_chk = $data->where('admins_id', $authSession->id)->count();

        if ($authSession->super_users) {
           if($data_chk == $authSession->limit_groups){
                $is_success = false;
                $message = "ไม่สามารถสร้าง units เพิ่มได้เนื่องจากเกินจำนวนที่กำหนด";
                return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
           }
        }

        $input = $request->json()->all();
        $data->fill($input);
        $data->admins_id = $authSession->id;
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if ($is_success) {
            $message = "The units has been created.";

            $toEmails = Admins::whereHas('admins_groups', function($query) use ($data) {
                $query->whereIn('admins_groups_id', array_pluck($data->groups->admins_groups, 'id'));
            })->whereNull('super_users')->get();

            $toEmails = array_values(array_unique(array_pluck($toEmails, 'email')));

            /* BEGIN E-MAIL FUNCTION */
            // Notify Mail (New Unit)
            $url_login = config('constants._BASE_BACKEND_URL')."login.html";
            $dataMail = array(
                'dataAdmin'=>$authSession,
                'dataGroup'=> $data->groups,
                'dataSubGroup' => $data->sub_groups,
                'dataLevelGroup' => $data,
                'url_login' => $url_login
            );
            Mail::send('levelgroups-create-mail', $dataMail, function($mail) use ($dataMail, $toEmails) {
                $mail->to($toEmails);
                $mail->subject('แจ้งการสร้าง'.$dataMail['dataGroup']['meaning_of_level_groups_id'].' '.$dataMail['dataGroup']['subject']);
                $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
            });
            /* END E-MAIL FUNCTION */
        } else {
            $message = "Failed to create the units.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'createdId' => $data->id), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id, _RolesController $oRole)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "level_groups")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = new LevelGroups;
        $data = $data->find($id);

        // $data->members = $data->members()->where('members.sub_groups_id', $data->sub_groups_id)->whereNotNull('approved_type')->orderBy('approved_datetime', 'DESC')->get();
        // $data->members_count = $data->members->count();
        // for($i=0; $i<count($data->members); $i++) {
        //     $data->members[$i]->num = $data->members_count - $i;
        //     $data->members[$i]->approved_admin = Admins::select('first_name')->find($data->members[$i]->approved_by);
        //     $data->members[$i]->created_admin = Admins::select('first_name')->find($data->members[$i]->created_by);
        // }

        // $data->members_pre_approved = $data->members_pre_approved()->where('members_pre_approved.sub_groups_id', $data->sub_groups_id)->orderBy('id', 'DESC')->get();
        // $data->members_pre_approved_count = $data->members_pre_approved->count();
        // for($i=0; $i<count($data->members_pre_approved); $i++) {
        //     $data->members_pre_approved[$i]->num = $data->members_pre_approved_count - $i;
        //     $data->members_pre_approved[$i]->created_admin = Admins::select('first_name')->find($data->members_pre_approved[$i]->created_by);
        // }

        // $data->members_not_approved = $data->members()->where('members.sub_groups_id', $data->sub_groups_id)->whereNull('approved_type')->orderBy('create_datetime', 'DESC')->get();
        // $data->members_not_approved_count = $data->members_not_approved->count();
        // for($i=0; $i<count($data->members_not_approved); $i++) {
        //     $data->members_not_approved[$i]->num = $data->members_not_approved_count - $i;
        //     $data->members_not_approved[$i]->rejected_admin = Admins::select('first_name')->find($data->members_not_approved[$i]->rejected_by);
        //     $data->members_not_approved[$i]->created_admin = Admins::select('first_name')->find($data->members_not_approved[$i]->created_by);
        // }

        $data->groups = $data->groups()->first();
        $data->sub_groups = $data->sub_groups()->with('groups')->first();

        switch ($data->sub_groups->groups->field_approval) {
            case 'email'         : $data->sub_groups->groups->field_approval_text = "อีเมล์"; break;
            case 'full_name'     : $data->sub_groups->groups->field_approval_text = "ชื่อ - นามสกุล"; break;
            case 'id_card'       : $data->sub_groups->groups->field_approval_text = "เลขบัตรประจำตัวประชาชน"; break;
            case 'license_id'    : $data->sub_groups->groups->field_approval_text = "เลขที่ใบอนุญาต"; break;
            case 'occupation_id' : $data->sub_groups->groups->field_approval_text = $data->sub_groups->groups->meaning_of_occupation_id; break;
        }

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
        if (!$oRole->haveAccess($id, "level_groups")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
            'sub_groups_id' => 'required|numeric',
            'title' => 'required|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = LevelGroups::find($id);
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if ($is_success) {
            $message = "The super users units has been updated.";
        } else {
            $message = "Failed to update the units.";
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
        if (!$oRole->haveAccess($id, "level_groups")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = LevelGroups::find($id);
        $is_success = $data->delete();
        if ($is_success) {
            $message = "The units has been deleted.";
        } else {
            $message = "Failed to delete the units.";
        }
        return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = LevelGroups::find($input[$i]['id']);
            $data[$i]->fill($input[$i]);
            $data[$i]->save();
        }
    }

    public function detachMembers($id, Request $request)
    {
        //
        $data = LevelGroups::find($id);

        if ($data) {
            $input = $request->json()->all();
            $membersIDs = array_pluck($input['members'], 'id');
            $is_success = $data->members()->detach($membersIDs);
        } else {
            $is_success = false;
        }

        if ($is_success) {
            $message = "The members has been detached from this unit.";
        } else {
            $message = "Failed to detach the members from this unit.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function getMembers(Request $request, _RolesController $oRole, $id)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "level_groups")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $per_page = $request->input('per_page', 999);
        $order_by = $request->input('order_by', 'approved_datetime');
        $order_direction = $request->input('order_direction', 'DESC');

        $data = LevelGroups::find($id);

        if (!$data) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "'Unit'", config('constants._errorMessage._404'))), 404);
        }

        $dataMembers = $data->members()->where('members.sub_groups_id', $data->sub_groups_id)->whereNotNull('approved_type');

        if($request->has('search')){
            $dataMembers = $dataMembers->where(function ($query) use ($request) {
                $query->where('members.email', 'like', '%'.$request['search'].'%')
                      ->orWhere('members.first_name', 'like', '%'.$request['search'].'%')
                      ->orWhere('members.first_name_en', 'like', '%'.$request['search'].'%')
                      ->orWhere('members.last_name', 'like', '%'.$request['search'].'%')
                      ->orWhere('members.last_name_en', 'like', '%'.$request['search'].'%');
            });
        }

        $dataMembers = $dataMembers->orderBy($order_by, $order_direction)->paginate($per_page);

        for($i=0; $i<count($dataMembers); $i++) {
            $dataMembers[$i]->num = $dataMembers->lastItem() - $i;
            $dataMembers[$i]->approved_admin = Admins::select('first_name')->find($dataMembers[$i]->approved_by);
            $dataMembers[$i]->created_admin = Admins::select('first_name')->find($dataMembers[$i]->created_by);
        }

        return $dataMembers;
    }

    public function getMembersPreApproved(Request $request, _RolesController $oRole, $id)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "level_groups")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $per_page = $request->input('per_page', 999);
        $order_by = $request->input('order_by', 'id');
        $order_direction = $request->input('order_direction', 'DESC');

        $data = LevelGroups::find($id);

        if (!$data) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "'Unit'", config('constants._errorMessage._404'))), 404);
        }

        $dataMembersPreApproved = $data->members_pre_approved()->where('members_pre_approved.sub_groups_id', $data->sub_groups_id);

        if($request->has('search')){
            $dataMembersPreApproved = $dataMembersPreApproved->where(function ($query) use ($request) {
                $query->where('members_pre_approved.email', 'like', '%'.$request['search'].'%')
                      ->orWhere('members_pre_approved.first_name', 'like', '%'.$request['search'].'%')
                      ->orWhere('members_pre_approved.first_name_en', 'like', '%'.$request['search'].'%')
                      ->orWhere('members_pre_approved.last_name', 'like', '%'.$request['search'].'%')
                      ->orWhere('members_pre_approved.last_name_en', 'like', '%'.$request['search'].'%');
            });
        }

        $dataMembersPreApproved = $dataMembersPreApproved->orderBy($order_by, $order_direction)->paginate($per_page);

        for($i=0; $i<count($dataMembersPreApproved); $i++) {
            $dataMembersPreApproved[$i]->num = $dataMembersPreApproved->lastItem() - $i;
            $dataMembersPreApproved[$i]->created_admin = Admins::select('first_name')->find($dataMembersPreApproved[$i]->created_by);
        }

        return $dataMembersPreApproved;
    }

    public function getMembersNotApproved(Request $request, _RolesController $oRole, $id)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "level_groups")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $per_page = $request->input('per_page', 999);
        $order_by = $request->input('order_by', 'create_datetime');
        $order_direction = $request->input('order_direction', 'DESC');

        $data = LevelGroups::find($id);

        if (!$data) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "'Unit'", config('constants._errorMessage._404'))), 404);
        }

        $dataMembers = $data->members()->where('members.sub_groups_id', $data->sub_groups_id)->whereNull('approved_type');

        if($request->has('search')){
            $dataMembers = $dataMembers->where(function ($query) use ($request) {
                $query->where('members.email', 'like', '%'.$request['search'].'%')
                      ->orWhere('members.first_name', 'like', '%'.$request['search'].'%')
                      ->orWhere('members.first_name_en', 'like', '%'.$request['search'].'%')
                      ->orWhere('members.last_name', 'like', '%'.$request['search'].'%')
                      ->orWhere('members.last_name_en', 'like', '%'.$request['search'].'%');
            });
        }

        $dataMembers = $dataMembers->orderBy($order_by, $order_direction)->paginate($per_page);

        for($i=0; $i<count($dataMembers); $i++) {
            $dataMembers[$i]->num = $dataMembers->lastItem() - $i;
            $dataMembers[$i]->rejected_admin = Admins::select('first_name')->find($dataMembers[$i]->rejected_by);
            $dataMembers[$i]->created_admin = Admins::select('first_name')->find($dataMembers[$i]->created_by);
        }

        return $dataMembers;
    }

    public function sub_groups(Request $request)
    {
        //
        $admins = new Admins;
        $admins = $admins->find($request['admins_id']);

        $data = new LevelGroups;
        $data = $data->whereHas('sub_groups', function($query) use ($admins) {
                $query->where('sub_groups_id', $admins->sub_groups_id)->where('admins_id', '!=', $admins->id);
        });
        $data = $data->orderBy('order','desc')->get();
        for($i=0; $i<count($data); $i++) {
            $data[$i]->admins = $data[$i]->admins()->first();
            $data[$i]->sub_groups = $data[$i]->sub_groups()->first();
            $data[$i]->title = $data[$i]->title." - ".$data[$i]->sub_groups->title;
        }

        return response()->json($data, 200);
    }

    public function all()
    {
        //
        $data_owner = new LevelGroups;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($authSession->super_users){
            $data_owner = $data_owner->where('admins_id', $authSession->id);
        }else{
            $data_owner = $data_owner->with('groups');
            $data_owner = $data_owner->whereHas('groups', function($query) use ($authSessionGroups) {
                $a=0;
                foreach($authSessionGroups as $authSessionGroups){
                    $a++;
                    if($a == 1){
                        $query->where('groups_id', $authSessionGroups->id);
                    }else{
                        $query->orWhere('groups_id', $authSessionGroups->id);
                    }
                }
            });
        }
        $data_owner = $data_owner->orderBy('order','asc')->get();
        for($i=0; $i<count($data_owner); $i++) {
            $data_owner[$i]->sub_groups = $data_owner[$i]->sub_groups()->first();
            $data_owner[$i]->title = $data_owner[$i]->title." - ".$data_owner[$i]->sub_groups->title;
        }

        $data_access = new Admins;
        if($authSession->super_users){
            $data_access = $data_access->find($authSession->id);
        }
        $data_access = $data_access->admin2level_group();
        $data_access = $data_access->orderBy('order','asc');
        $data_access = $data_access->get();
        for($i=0; $i<count($data_access); $i++) {
            $data_access[$i]->sub_groups = $data_access[$i]->sub_groups()->first();
            $data_access[$i]->title = $data_access[$i]->title." - ".$data_access[$i]->sub_groups->title;
        }

        return response()->json(array('owner' => $data_owner, 'access' => $data_access), 200);
    }

    public function allBySubGroups(Request $request)
    {
        //
        $input = $request->all();
        // return response()->json($input['sub_groups'], 500);

        // return response()->json(['owner' => [], 'access' => []], 200);

        $data_owner = new LevelGroups;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($authSession->super_users){
            $data_owner = $data_owner->where('admins_id', $authSession->id);
        }else{
            $data_owner = $data_owner->with('groups');
            $data_owner = $data_owner->whereHas('groups', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }

        $data_owner = $data_owner->whereHas('sub_groups', function($query) use ($input) {
            $query->whereIn('sub_groups_id', $input['sub_groups']);
        });

        $data_owner = $data_owner->orderBy('order','asc')->get();
        for($i=0; $i<count($data_owner); $i++) {
            $data_owner[$i]->sub_groups = $data_owner[$i]->sub_groups()->first();
            $data_owner[$i]->title = $data_owner[$i]->title." - ".$data_owner[$i]->sub_groups->title;
        }

        $data_access = new Admins;
        if($authSession->super_users){
            $data_access = $data_access->find($authSession->id);
        }
        $data_access = $data_access->admin2level_group();

        $data_access = $data_access->whereHas('sub_groups', function($query) use ($input) {
            $query->whereIn('sub_groups_id', $input['sub_groups']);
        });

        $data_access = $data_access->orderBy('order','asc');
        $data_access = $data_access->get();
        for($i=0; $i<count($data_access); $i++) {
            $data_access[$i]->sub_groups = $data_access[$i]->sub_groups()->first();
            $data_access[$i]->title = $data_access[$i]->title." - ".$data_access[$i]->sub_groups->title;
        }

        return response()->json(array('owner' => $data_owner, 'access' => $data_access), 200);
    }

    public function sort(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'type' => 'in:moveAfter,moveBefore',
            'positionEntityId' => 'numeric',
            'order' => 'numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $entity = LevelGroups::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = LevelGroups::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = LevelGroups::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = LevelGroups::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = LevelGroups::where('order', '>', $request['order'])->min('id');
                    LevelGroups::find($next)->decrement('order');
                    $entity->moveBefore(LevelGroups::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The groups has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

    private function updateMembersSSO($dataGroups, $dataLevelGroups, $dataMembers, $dataFromExcel, $active, $activeRemark, $approved_type = null)
    {
        $authSession = Auth::user();

        $dataMembers->sub_groups_id = $dataFromExcel[9];

        $dataMembers->active = $active;
        $dataMembers->active_remark = $activeRemark;

        switch ($approved_type) {
            case 1:
                $dataMembers->approved_type = $approved_type;
                $dataMembers->approved_field = $dataGroups->field_approval;
                $dataMembers->approved_by = $authSession->id;
                $dataMembers->approved_datetime = date('Y-m-d H:i:s');
                break;

            case 2:
                $dataMembers->approved_type = $approved_type;
                $dataMembers->approved_by = $authSession->id;
                $dataMembers->approved_datetime = date('Y-m-d H:i:s');
                break;

            case 3:
                $dataMembers->approved_type = $approved_type;
                $dataMembers->approved_datetime = date('Y-m-d H:i:s');
                break;

            default:
                # code...
                break;
        }

        $dataMembers->modify_datetime = date('Y-m-d H:i:s');
        $dataMembers->modify_by = $authSession->id;
        $dataMembers->save();
        $dataMembers->sub_groupsList()->syncWithoutDetaching([$dataFromExcel[9] => ['active' => 1]]);
        $dataMembers->level_groups()->syncWithoutDetaching([$dataFromExcel[11]]);

        return $dataMembers;
    }

    private function updateMembers($dataGroups, $dataLevelGroups, $dataMembers, $dataFromExcel, $active, $activeRemark, $approved_type = null, $allowUpdateEmail = true)
    {
        $authSession = Auth::user();

        $dataMembers->name_title = $dataFromExcel[0];
        $dataMembers->gender = $dataFromExcel[1];
        $dataMembers->first_name = $dataFromExcel[2];
        $dataMembers->last_name = $dataFromExcel[3];

        if ($allowUpdateEmail) {
            $dataMembers->email = $dataFromExcel[4];
        }

        // $dataMembers->password = $dataFromExcel[5];
        // $dataMembers->encrypt_password = Hash::make($dataFromExcel[5]);
        $dataMembers->id_card = $dataFromExcel[6];
        $dataMembers->birth_date = $dataFromExcel[7];
        $dataMembers->mobile_number = $dataFromExcel[8];
        $dataMembers->sub_groups_id = $dataFromExcel[9];
        $dataMembers->occupation_id = $dataFromExcel[10];
        // $dataMembers->level_groups_id = $dataLevelGroups->id;

        switch ($dataGroups->id) {
            case 1:
                // $dataMembers->education_level_id = $dataFromExcel[12];
                break;

            case 2:
                $dataMembers->position_id = $dataFromExcel[12];
                $dataMembers->role = $dataFromExcel[13];
                break;

            case 3:
                $dataMembers->license_id = $dataFromExcel[12];
                $dataMembers->position_id = $dataFromExcel[13];
                $dataMembers->education_level_id = $dataFromExcel[14];
                break;

            case 4:
                $dataMembers->education_level_id = $dataFromExcel[12];
                break;

            case 5:
                $dataMembers->position_id = $dataFromExcel[12];
                $dataMembers->table_number = $dataFromExcel[13];
                $dataMembers->chief_name = $dataFromExcel[14];
                break;

            default:
                // $dataMembers->education_level_id = $dataFromExcel[12];
                break;
        }

        $dataMembers->active = $active;
        $dataMembers->active_remark = $activeRemark;

        switch ($approved_type) {
            case 1:
                $dataMembers->approved_type = $approved_type;
                $dataMembers->approved_field = $dataGroups->field_approval;
                $dataMembers->approved_by = $authSession->id;
                $dataMembers->approved_datetime = date('Y-m-d H:i:s');
                break;

            case 2:
                $dataMembers->approved_type = $approved_type;
                $dataMembers->approved_by = $authSession->id;
                $dataMembers->approved_datetime = date('Y-m-d H:i:s');
                break;

            case 3:
                $dataMembers->approved_type = $approved_type;
                $dataMembers->approved_datetime = date('Y-m-d H:i:s');
                break;

            default:
                # code...
                break;
        }

        $dataMembers->modify_datetime = date('Y-m-d H:i:s');
        $dataMembers->modify_by = $authSession->id;
        $dataMembers->save();
        // $dataMembers->sub_groupsList()->syncWithoutDetaching([$dataFromExcel[9] => ['active' => 1]]);
        $dataMembers->level_groups()->syncWithoutDetaching([$dataFromExcel[11]]);

        return $dataMembers;
    }

    private function updateMembersPreApproved($dataGroups, $dataLevelGroups, $dataMembersPreApproved, $dataFromExcel)
    {
        $dataMembersPreApproved->name_title = $dataFromExcel[0];
        $dataMembersPreApproved->gender = $dataFromExcel[1];
        $dataMembersPreApproved->first_name = $dataFromExcel[2];
        $dataMembersPreApproved->last_name = $dataFromExcel[3];
        $dataMembersPreApproved->email = $dataFromExcel[4];
        // $dataMembersPreApproved->password = $dataFromExcel[5];
        // $dataMembersPreApproved->encrypt_password = Hash::make($dataFromExcel[5]);
        $dataMembersPreApproved->id_card = $dataFromExcel[6];
        $dataMembersPreApproved->birth_date = $dataFromExcel[7];
        $dataMembersPreApproved->mobile_number = $dataFromExcel[8];
        $dataMembersPreApproved->sub_groups_id = $dataFromExcel[9];
        $dataMembersPreApproved->occupation_id = $dataFromExcel[10];
        // $dataMembersPreApproved->level_groups_id = $dataLevelGroups->id;

        switch ($dataGroups->id) {
            case 1:
                // $dataMembersPreApproved->education_level_id = $dataFromExcel[12];
                break;

            case 2:
                $dataMembersPreApproved->position_id = $dataFromExcel[12];
                $dataMembersPreApproved->role = $dataFromExcel[13];
                break;

            case 3:
                $dataMembersPreApproved->license_id = $dataFromExcel[12];
                $dataMembersPreApproved->position_id = $dataFromExcel[13];
                $dataMembersPreApproved->education_level_id = $dataFromExcel[14];
                break;

            case 4:
                $dataMembersPreApproved->education_level_id = $dataFromExcel[12];
                break;

            case 5:
                $dataMembersPreApproved->position_id = $dataFromExcel[12];
                $dataMembersPreApproved->table_number = $dataFromExcel[13];
                $dataMembersPreApproved->chief_name = $dataFromExcel[14];
                break;

            default:
                // $dataMembersPreApproved->education_level_id = $dataFromExcel[12];
                break;
        }

        $dataMembersPreApproved->modify_datetime = date('Y-m-d H:i:s');
        $dataMembersPreApproved->modify_by = Auth::user()->id;
        $dataMembersPreApproved->save();
        $dataMembersPreApproved->level_groups()->syncWithoutDetaching([$dataFromExcel[11]]);

        return $dataMembersPreApproved;
    }

    private function handleRejected($dataGroups, $dataReject = [], $dataFromExcel, $rowRejected, $rowCurrent, $errorMessage)
    {
        $dataReject[$rowRejected]['row'] = $rowCurrent;
        $dataReject[$rowRejected]['name_title'] = isset($dataFromExcel[0]) ? $dataFromExcel[0] : null;
        $dataReject[$rowRejected]['gender'] = isset($dataFromExcel[1]) ? $dataFromExcel[1] : null;
        $dataReject[$rowRejected]['first_name'] = isset($dataFromExcel[2]) ? $dataFromExcel[2] : null;
        $dataReject[$rowRejected]['last_name'] = isset($dataFromExcel[3]) ? $dataFromExcel[3] : null;
        $dataReject[$rowRejected]['email'] = isset($dataFromExcel[4]) ? $dataFromExcel[4] : null;
        $dataReject[$rowRejected]['password'] = isset($dataFromExcel[5]) ? $dataFromExcel[5] : null;
        $dataReject[$rowRejected]['id_card'] = isset($dataFromExcel[6]) ? $dataFromExcel[6] : null;
        $dataReject[$rowRejected]['birth_date'] = isset($dataFromExcel[7]) ? $dataFromExcel[7] : null;
        $dataReject[$rowRejected]['mobile_number'] = isset($dataFromExcel[8]) ? $dataFromExcel[8] : null;
        $dataReject[$rowRejected]['sub_groups_id'] = isset($dataFromExcel[9]) ? $dataFromExcel[9] : null;
        $dataReject[$rowRejected]['occupation_id'] = isset($dataFromExcel[10]) ? $dataFromExcel[10] : null;
        $dataReject[$rowRejected]['level_groups_id'] = isset($dataFromExcel[11]) ? $dataFromExcel[11] : null;

        switch ($dataGroups->id) {
            case 1:
                // $dataReject[$rowRejected]['education_level_id'] = isset($dataFromExcel[12]) ? $dataFromExcel[12] : null;
                break;

            case 2:
                $dataReject[$rowRejected]['position_id'] = isset($dataFromExcel[12]) ? $dataFromExcel[12] : null;
                $dataReject[$rowRejected]['role'] = isset($dataFromExcel[13]) ? $dataFromExcel[13] : null;
                break;

            case 3:
                $dataReject[$rowRejected]['license_id'] = isset($dataFromExcel[12]) ? $dataFromExcel[12] : null;
                $dataReject[$rowRejected]['position_id'] = isset($dataFromExcel[13]) ? $dataFromExcel[13] : null;
                $dataReject[$rowRejected]['education_level_id'] = isset($dataFromExcel[14]) ? $dataFromExcel[14] : null;
                break;

            case 4:
                $dataReject[$rowRejected]['education_level_id'] = isset($dataFromExcel[12]) ? $dataFromExcel[12] : null;
                break;

            case 5:
                $dataReject[$rowRejected]['position_id'] = isset($dataFromExcel[12]) ? $dataFromExcel[12] : null;
                $dataReject[$rowRejected]['table_number'] = isset($dataFromExcel[13]) ? $dataFromExcel[13] : null;
                $dataReject[$rowRejected]['chief_name'] = isset($dataFromExcel[14]) ? $dataFromExcel[14] : null;
                break;

            default:
                // $dataReject[$rowRejected]['education_level_id'] = isset($dataFromExcel[12]) ? $dataFromExcel[12] : null;
                break;
        }

        $dataReject[$rowRejected]['message'] = $errorMessage;

        return $dataReject;
    }

    private function handleCommonRejected($dataReject = [], $dataFromExcel, $rowRejected, $rowCurrent, $errorMessage)
    {
        $dataReject[$rowRejected]['row'] = $rowCurrent;
        $dataReject[$rowRejected]['name_title'] = isset($dataFromExcel[0]) ? $dataFromExcel[0] : null;
        $dataReject[$rowRejected]['gender'] = isset($dataFromExcel[1]) ? $dataFromExcel[1] : null;
        $dataReject[$rowRejected]['first_name'] = isset($dataFromExcel[2]) ? $dataFromExcel[2] : null;
        $dataReject[$rowRejected]['last_name'] = isset($dataFromExcel[3]) ? $dataFromExcel[3] : null;
        $dataReject[$rowRejected]['email'] = isset($dataFromExcel[4]) ? $dataFromExcel[4] : null;
        $dataReject[$rowRejected]['password'] = isset($dataFromExcel[5]) ? $dataFromExcel[5] : null;
        $dataReject[$rowRejected]['id_card'] = isset($dataFromExcel[6]) ? $dataFromExcel[6] : null;
        $dataReject[$rowRejected]['birth_date'] = isset($dataFromExcel[7]) ? $dataFromExcel[7] : null;
        $dataReject[$rowRejected]['mobile_number'] = isset($dataFromExcel[8]) ? $dataFromExcel[8] : null;
        $dataReject[$rowRejected]['sub_groups_id'] = isset($dataFromExcel[9]) ? $dataFromExcel[9] : null;
        $dataReject[$rowRejected]['occupation_id'] = isset($dataFromExcel[10]) ? $dataFromExcel[10] : null;
        $dataReject[$rowRejected]['level_groups_id'] = isset($dataFromExcel[11]) ? $dataFromExcel[11] : null;

        return $dataReject;
    }

    private function handleValidations($dataGroups, $dataLevelGroups, $dataFromExcel, $isUploadMembers)
    {
        $oFunc = new _FunctionsController;
        $oRole = new _RolesController;
        $authSession = Auth::user();
        $errorMessage =  null;

        $dataAdminsGroups = $authSession->admins_groups()->with(['groups' => function ($query) use ($dataGroups) {
            $query->where('groups.id', $dataGroups->id);
        }])->first();

        // if ($isUploadMembers) {
        //     $validator = Validator::make(['password' => $dataFromExcel[5]], [
        //         'password' => 'between:8,255|case_diff|numbers|letters|symbols|not_contain_credentials:'.implode(",", [$dataFromExcel[2], $dataFromExcel[3], $dataFromExcel[4]]),
        //     ]);
        // }

        switch ($dataGroups->id) {
            case 1:
                if (count($dataFromExcel) != 12) {
                    $errorMessage = "Column must be length 12 not ".count($dataFromExcel).".";
                }
                break;

            case 2:
                if (count($dataFromExcel) != 14) {
                    $errorMessage = "Column must be length 14 not ".count($dataFromExcel).".";
                } else if (empty($dataFromExcel[12]) && $isUploadMembers) {
                    $errorMessage = "The column position id has no value.";
                } else if (empty($dataFromExcel[13]) && $isUploadMembers) {
                    $errorMessage = "The column role has no value.";
                }
                break;

            case 3:
                if (count($dataFromExcel) != 15) {
                    $errorMessage = "Column must be length 15 not ".count($dataFromExcel).".";
                } else if (empty($dataFromExcel[12]) && $dataGroups->field_approval == "license_id") {
                    $errorMessage = "The column license id has no value.";
                } else if (empty($dataFromExcel[13]) && $isUploadMembers) {
                    $errorMessage = "The column position id has no value.";
                } else if (empty($dataFromExcel[14]) && $isUploadMembers) {
                    $errorMessage = "The column education level id has no value.";
                }
                break;

            case 4:
                if (count($dataFromExcel) != 13) {
                    $errorMessage = "Column must be length 13 not ".count($dataFromExcel).".";
                } else if (empty($dataFromExcel[12]) && $isUploadMembers) {
                    $errorMessage = "The column education level id has no value.";
                }
                break;

            case 5:
                if (count($dataFromExcel) != 15) {
                    $errorMessage = "Column must be length 15 not ".count($dataFromExcel).".";
                } else if (empty($dataFromExcel[12]) && $isUploadMembers) {
                    $errorMessage = "The column position id has no value.";
                } else if (empty($dataFromExcel[13]) && $isUploadMembers) {
                    $errorMessage = "The column table number has no value.";
                } else if (empty($dataFromExcel[14]) && $isUploadMembers) {
                    $errorMessage = "The column chief name has no value.";
                }
                break;

            default:
                if (count($dataFromExcel) != 12) {
                    $errorMessage = "Column must be length 12 not ".count($dataFromExcel).".";
                }
                break;
        }

        if (is_null($errorMessage)) {
            if (empty($dataFromExcel[0]) && $isUploadMembers) {
                $errorMessage = "The column prefix name has no value.";
            } else if ((empty($dataFromExcel[1]) || !in_array($dataFromExcel[1], ['M','m','F','f'])) && $isUploadMembers) {
                $errorMessage = "The column gender is invalid format. (Only M and F)";
            } else if (empty($dataFromExcel[2]) && $dataGroups->field_approval == "full_name") {
                $errorMessage = "The column first name has no value.";
            } else if (empty($dataFromExcel[3]) && $dataGroups->field_approval == "full_name") {
                $errorMessage = "The column last name has no value.";
            } else if (filter_var($dataFromExcel[4], FILTER_VALIDATE_EMAIL) === false && $isUploadMembers) {
                $errorMessage = "The column e-mail is invalid format.";
            }/* else if (!empty($dataFromExcel[5]) && $isUploadMembers && $validator->errors()->has('password')) {
                $errorMessage = $validator->errors()->get('password');
            }*/ else if (empty($dataFromExcel[6]) && $dataGroups->field_approval == "id_card") {
                $errorMessage = "The column id card has no value.";
            } else if (!empty($dataFromExcel[6]) && !$oFunc->checkIDCard($dataFromExcel[6])) {
                $errorMessage = "The column id card is invalid format.";
            } else if (!$oFunc->validateDate($dataFromExcel[7]) && $isUploadMembers) {
                $errorMessage = "The column birth date is invalid format. (yyyy-mm-dd)";
            } else if (empty($dataFromExcel[8]) && $isUploadMembers) {
                $errorMessage = "The column mobile number has no value.";
            } else if (empty($dataFromExcel[9])) {
                $errorMessage = "The subgroup id was not found.";
            } else if (empty($dataFromExcel[10]) && $dataGroups->field_approval == "occupation_id") {
                $errorMessage = "The column occupation id has no value.";
            } else if (empty($dataFromExcel[11])) {
                $errorMessage = "The column unit id has no value.";
            } else if ($dataLevelGroups->id != $dataFromExcel[11]) {
                $errorMessage = "Invalid unit.";
            } else {
                $dataSubGroup = $dataGroups->sub_groups()->find($dataFromExcel[9]);

                if ($dataSubGroup && $isUploadMembers) {
                    $dataDomainExist = $dataSubGroup->domains()->where('domains.title', explode('@', $dataFromExcel[4])[1])->first();
                } else {
                    $dataDomainExist = false;
                }

                if ((!$dataSubGroup || ($authSession->sub_groups_id != $dataSubGroup->id) && count($dataAdminsGroups->groups) == 0)) {
                    $errorMessage = "The subgroup id was not found.";
                } else if ($dataSubGroup->id != $dataLevelGroups->sub_groups_id) {
                    $errorMessage = "The unit of not matched in sub group.";
                } else if ($dataSubGroup->restriction_mode == "allow" && !$dataDomainExist && $isUploadMembers) {
                    $errorMessage = "The email domain is not allowed.";
                } else if ($dataSubGroup->restriction_mode == "deny" && $dataDomainExist && $isUploadMembers) {
                    $errorMessage = "The email domain is denied.";
                } else {
                    $dataLevelGroupsExist = LevelGroups::where('id', $dataFromExcel[11])->where('admins_id', $authSession->id)->first();
                    $dataLevelGroupsHasPerm = $authSession->admin2level_group()->where('level_groups_id', $dataFromExcel[11])->first();
                    if (!$dataLevelGroupsHasPerm && !$dataLevelGroupsExist && count($dataAdminsGroups->groups) == 0) {
                        $errorMessage = "The unit id was not found.";
                    }
                }
            }
        }

        return [
            "errorMessage" => $errorMessage,
            "dataSubGroup" => isset($dataSubGroup) ? $dataSubGroup : null,
            "dataLevelGroupsExist" => isset($dataLevelGroupsExist) ? $dataLevelGroupsExist : null,
            "dataLevelGroupsHasPerm" => isset($dataLevelGroupsHasPerm) ? $dataLevelGroupsHasPerm : null
        ];
    }

    private function createMembersPreApproved($dataGroups, $dataLevelGroups, $dataFromExcel)
    {
        $dataMembersPreApproved = new MembersPreApproved;
        $dataMembersPreApproved->groups_id = $dataGroups->id;
        $dataMembersPreApproved->name_title = $dataFromExcel[0];
        $dataMembersPreApproved->gender = $dataFromExcel[1];
        $dataMembersPreApproved->first_name = $dataFromExcel[2];
        $dataMembersPreApproved->last_name = $dataFromExcel[3];
        $dataMembersPreApproved->email = $dataFromExcel[4];
        // $dataMembersPreApproved->password = $dataFromExcel[5];
        // $dataMembersPreApproved->encrypt_password = Hash::make($dataFromExcel[5]);
        $dataMembersPreApproved->id_card = $dataFromExcel[6];
        $dataMembersPreApproved->birth_date = $dataFromExcel[7];
        $dataMembersPreApproved->mobile_number = $dataFromExcel[8];
        $dataMembersPreApproved->sub_groups_id = $dataFromExcel[9];
        $dataMembersPreApproved->occupation_id = $dataFromExcel[10];

        switch ($dataGroups->id) {
            case 1:
                // $dataMembersPreApproved->education_level_id = $dataFromExcel[12];
                break;

            case 2:
                $dataMembersPreApproved->position_id = $dataFromExcel[12];
                $dataMembersPreApproved->role = $dataFromExcel[13];
                break;

            case 3:
                $dataMembersPreApproved->license_id = $dataFromExcel[12];
                $dataMembersPreApproved->position_id = $dataFromExcel[13];
                $dataMembersPreApproved->education_level_id = $dataFromExcel[14];
                break;

            case 4:
                $dataMembersPreApproved->education_level_id = $dataFromExcel[12];
                break;

            case 5:
                $dataMembersPreApproved->position_id = $dataFromExcel[12];
                $dataMembersPreApproved->table_number = $dataFromExcel[13];
                $dataMembersPreApproved->chief_name = $dataFromExcel[14];
                break;

            default:
                // $dataMembersPreApproved->education_level_id = $dataFromExcel[12];
                break;
        }

        $dataMembersPreApproved->create_datetime = date('Y-m-d H:i:s');
        $dataMembersPreApproved->created_by = Auth::user()->id;
        $dataMembersPreApproved->modify_datetime = date('Y-m-d H:i:s');
        $dataMembersPreApproved->modify_by = Auth::user()->id;
        $dataMembersPreApproved->status = 1;
        $dataMembersPreApproved->save();
        $dataMembersPreApproved->level_groups()->sync([$dataFromExcel[11]]);

        return $dataMembersPreApproved;
    }

    private function createMembers($dataGroups, $dataLevelGroups, $dataFromExcel)
    {
        $oFunc = new _FunctionsController;
        $dataMembers = new Members;
        $dataMembers->groups_id = $dataGroups->id;
        $dataMembers->name_title = $dataFromExcel[0];
        $dataMembers->gender = $dataFromExcel[1];
        $dataMembers->first_name = $dataFromExcel[2];
        $dataMembers->last_name = $dataFromExcel[3];
        $dataMembers->email = $dataFromExcel[4];

        if (empty($dataFromExcel[5])) {
            $dataMembers->password = $oFunc->generateSecurePassword();
        } else {
            $dataMembers->password = $dataFromExcel[5];
        }

        $dataMembers->encrypt_password = Hash::make($dataMembers->password);
        $dataMembers->id_card = $dataFromExcel[6];
        $dataMembers->birth_date = $dataFromExcel[7];
        $dataMembers->mobile_number = $dataFromExcel[8];
        $dataMembers->sub_groups_id = $dataFromExcel[9];
        $dataMembers->occupation_id = $dataFromExcel[10];

        switch ($dataGroups->id) {
            case 1:
                // $dataMembers->education_level_id = $dataFromExcel[12];
                break;

            case 2:
                $dataMembers->position_id = $dataFromExcel[12];
                $dataMembers->role = $dataFromExcel[13];
                break;

            case 3:
                $dataMembers->license_id = $dataFromExcel[12];
                $dataMembers->position_id = $dataFromExcel[13];
                $dataMembers->education_level_id = $dataFromExcel[14];
                break;

            case 4:
                $dataMembers->education_level_id = $dataFromExcel[12];
                break;

            case 5:
                $dataMembers->position_id = $dataFromExcel[12];
                $dataMembers->table_number = $dataFromExcel[13];
                $dataMembers->chief_name = $dataFromExcel[14];
                break;

            default:
                // $dataMembers->education_level_id = $dataFromExcel[12];
                break;
        }

        $dataMembers->approved_type = 1;
        $dataMembers->approved_field = $dataGroups->field_approval;
        $dataMembers->approved_datetime = date('Y-m-d H:i:s');
        $dataMembers->approved_by = Auth::user()->id;
        $dataMembers->active = 1;
        $dataMembers->create_datetime = date('Y-m-d H:i:s');
        $dataMembers->created_type = 2;
        $dataMembers->created_by = Auth::user()->id;
        $dataMembers->modify_datetime = date('Y-m-d H:i:s');
        $dataMembers->modify_by = Auth::user()->id;
        $dataMembers->status = 1;
        $dataMembers->save();
        $dataMembers->sub_groupsList()->sync([$dataFromExcel[9] => ['active' => 1]]);
        $dataMembers->level_groups()->sync([$dataFromExcel[11]]);

        /* BEGIN E-MAIL FUNCTION */
        // Notify Mail (New Members)
        $url_login = config('constants._BASE_URL').$dataGroups->key."/login";
        $dataMail = array(
            'dataMembers'=>$dataMembers,
            'dataGroups'=> $dataGroups,
            'dataSubGroups' => $dataMembers->sub_groups()->first(),
            'dataLevelGroups' => $dataMembers->level_groups()->first(),
            'url_login' => $url_login
        );
        Mail::send('levelgroups-newmembers-mail', $dataMail, function($mail) use ($dataMail) {
            if ($dataMail['dataMembers']['is_foreign'] != 1) {
                $receiverName = $dataMail['dataMembers']['first_name']." ".$dataMail['dataMembers']['last_name'];
            } else {
                $receiverName = $dataMail['dataMembers']['first_name_en']." ".$dataMail['dataMembers']['last_name_en'];
            }
            $mail->to($dataMail['dataMembers']['email'], $receiverName)->subject('แจ้งยืนยันการเป็นสมาชิก '.$dataMail['dataGroups']['subject']);
            $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
        });
        /* END E-MAIL FUNCTION */

        return $dataMembers;
    }

    private function readMembersPreApprovedExcel($fileExcel, $countHeader, $dataLevelGroups, $dataGroups)
    {
        $oFunc = new _FunctionsController;
        $authSession = Auth::user();
        $dataInsert = array();
        $dataInserted = array();
        $dataUpdated = array();
        $dataReject = array();
        $resourceFile = fopen($fileExcel, "r");

        $countHeader = (int)$countHeader;
        if (count($countHeader) > 0) {
            for ($i=0; $i < $countHeader; $i++) {
                $arrHeader[] = fgetcsv($resourceFile);
            }
        }

        $row = 1;
        $rowInserted = 0;
        $rowRejected = 0;

        while (($arrDetail = fgetcsv($resourceFile)) !== false) {
            $dataFormValidations = $this->handleValidations($dataGroups, $dataLevelGroups, $arrDetail, false);

            if (!is_null($dataFormValidations['errorMessage'])) {
                if ($dataGroups) {
                    $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, $dataFormValidations['errorMessage']);
                } else {
                    $dataReject = $this->handleCommonRejected($dataReject, $arrDetail, $rowRejected, $row, $dataFormValidations['errorMessage']);
                }
                $rowRejected++;
            } else {

                if ($dataGroups->id == 3) {
                    if ($dataGroups->field_approval == "full_name") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('first_name', $arrDetail[2])->where('last_name', $arrDetail[3])->first();
                    } else if ($dataGroups->field_approval == "id_card") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[6])->first();
                    } else if ($dataGroups->field_approval == "occupation_id") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[10])->first();
                    } else if ($dataGroups->field_approval == "license_id") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[12])->first();
                    } else {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('email', $arrDetail[4])->first();
                    }

                    if ($dataExist) {
                        if ($dataExist->sub_groups_id == $arrDetail[9]) {
                            if ($dataExist->active == 1) {
                                if (empty($dataExist->last_login)) {
                                    $dataUpdate = $this->updateMembers($dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 0);
                                    $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated."];
                                } else {
                                    if (!$dataExist->level_groups()->where('level_groups.id', $arrDetail[11])->first()) {
                                        $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                        $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "Added member to unit."];
                                    } else {
                                        $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, "Duplicated Member.");
                                        $rowRejected++;
                                    }
                                }
                            } else {
                                $dataUpdate = $this->updateMembers($dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 3, 1);
                                $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "Activated member."];
                                /* BEGIN E-MAIL FUNCTION */
                                // Notify Mail (Member's reactivated)
                                $url = config('constants._BASE_URL').$dataGroups->key."/login";
                                $dataMail = array(
                                    'dataMembers'=>$dataExist,
                                    'dataGroups'=> $dataGroups,
                                    'dataSubGroups' => $dataExist->sub_groups()->first(),
                                    'dataLevelGroups' => $dataLevelGroups,
                                    'url' => $url
                                );
                                Mail::send('levelgroups-reactivated-mail', $dataMail, function($mail) use ($dataMail) {
                                    if ($dataMail['dataMembers']['is_foreign'] != 1) {
                                        $receiverName = $dataMail['dataMembers']['first_name']." ".$dataMail['dataMembers']['last_name'];
                                    } else {
                                        $receiverName = $dataMail['dataMembers']['first_name_en']." ".$dataMail['dataMembers']['last_name_en'];
                                    }
                                    $mail->to($dataMail['dataMembers']['email'], $receiverName)->subject('แจ้งการเปิดใช้งานสมาชิก '.$dataMail['dataGroups']['subject']);
                                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                });
                                /* END E-MAIL FUNCTION */
                            }
                        } else {
                            $countNewEmailExist = Members::where("email", $arrDetail[4])->where('groups_id', $dataGroups->id)->where('id', '!=', $dataExist->id)->count();
                            if ($countNewEmailExist > 0) {
                                $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, "Duplicated new email.");
                                $rowRejected++;
                            } else {
                                if (empty($dataExist->last_login)) {
                                    $dataUpdate = $this->updateMembers($dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 2, null, false);
                                    $dataMember = $dataUpdate;
                                } else {
                                    $dataExist->active = 1;
                                    $dataExist->active_remark = 2;
                                    $dataExist->save();
                                    $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                    $dataMember = $dataExist;
                                }

                                $dataMember->sub_groupsList()->syncWithoutDetaching([$arrDetail[9] => ['active' => 2, 'email' => $arrDetail[4]]]);
                                $dataUpdated[] = ["row" => $row] + $dataMember->toArray() + ["message" => "This member has changing of subgroup."];

                                /* BEGIN E-MAIL FUNCTION */
                                // Notify Mail (Member's Group Changed)
                                $url = config('constants._BASE_URL').$dataGroups->key."/login";
                                $dataMail = array(
                                    'dataMember'=>$dataMember,
                                    'dataGroups'=> $dataGroups,
                                    'dataOldSubGroups' => $dataMember->sub_groups()->first(),
                                    'dataNewSubGroups' => $dataMember->sub_groupsList()->where('active', 2)->orderBy('id', 'desc')->first(),
                                    'url' => $url,
                                    'newEmail' => $arrDetail[4]
                                );
                                Mail::send('levelgroups-change-subgroups-alert-mail', $dataMail, function($mail) use ($dataMail) {
                                    if ($dataMail['dataMember']['is_foreign'] != 1) {
                                        $receiverName = $dataMail['dataMember']['first_name']." ".$dataMail['dataMember']['last_name'];
                                    } else {
                                        $receiverName = $dataMail['dataMember']['first_name_en']." ".$dataMail['dataMember']['last_name_en'];
                                    }
                                    $mail->to([$dataMail['dataMember']['email'], $dataMail['newEmail']], $receiverName)->subject('แจ้งเปลี่ยนสถานะการเป็นสมาชิกจาก '.$dataMail['dataOldSubGroups']['title'].' เป็น '.$dataMail['dataNewSubGroups']['title']);
                                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                });
                                /* END E-MAIL FUNCTION */
                            }

                        }
                    } else {
                        if ($dataGroups->field_approval == "full_name") {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('first_name', $arrDetail[2])->where('last_name', $arrDetail[3])->first();
                        } else if ($dataGroups->field_approval == "id_card") {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[6])->first();
                        } else if ($dataGroups->field_approval == "occupation_id") {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[10])->first();
                        } else if ($dataGroups->field_approval == "license_id") {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[12])->first();
                        } else {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('email', $arrDetail[4])->first();
                        }

                        if ($dataPreApprovedExist) {
                            $dataUpdate = $this->updateMembersPreApproved($dataGroups, $dataLevelGroups, $dataPreApprovedExist, $arrDetail);
                            if ($dataUpdate) {
                                $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated."];
                            }
                        } else {
                            $dataCreate = $this->createMembersPreApproved($dataGroups, $dataLevelGroups, $arrDetail);
                            if ($dataCreate) {
                                $dataInserted[] = ["row" => $row] + $dataCreate->toArray();
                            }
                        }
                    }
                } else {
                    if ($dataGroups->field_approval == "full_name") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('first_name', $arrDetail[2])->where('last_name', $arrDetail[3])->first();
                    } else if ($dataGroups->field_approval == "id_card") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[6])->first();
                    } else if ($dataGroups->field_approval == "occupation_id") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('sub_groups_id', $arrDetail[9])->where($dataGroups->field_approval, $arrDetail[10])->first();
                    } else {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('email', $arrDetail[4])->first();
                    }

                    if ($dataExist) {
                        if ($dataExist->sub_groups_id == $arrDetail[9]) {
                            if ($dataExist->active == 1) {
                                if (empty($dataExist->last_login)) {
                                    $dataUpdate = $this->updateMembers($dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 0);
                                    $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated."];
                                } else {
                                    if (!$dataExist->level_groups()->where('level_groups.id', $arrDetail[11])->first()) {
                                        $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                        $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "Added member to unit."];
                                    } else {
                                        $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, "Duplicated Member.");
                                        $rowRejected++;
                                    }
                                }
                            } else {
                                $dataUpdate = $this->updateMembers($dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 3, 1);
                                $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "Activated member."];
                                /* BEGIN E-MAIL FUNCTION */
                                // Notify Mail (Member's reactivated)
                                $url = config('constants._BASE_URL').$dataGroups->key."/login";
                                $dataMail = array(
                                    'dataMembers'=>$dataExist,
                                    'dataGroups'=> $dataGroups,
                                    'dataSubGroups' => $dataExist->sub_groups()->first(),
                                    'dataLevelGroups' => $dataLevelGroups,
                                    'url' => $url
                                );
                                Mail::send('levelgroups-reactivated-mail', $dataMail, function($mail) use ($dataMail) {
                                    if ($dataMail['dataMembers']['is_foreign'] != 1) {
                                        $receiverName = $dataMail['dataMembers']['first_name']." ".$dataMail['dataMembers']['last_name'];
                                    } else {
                                        $receiverName = $dataMail['dataMembers']['first_name_en']." ".$dataMail['dataMembers']['last_name_en'];
                                    }
                                    $mail->to($dataMail['dataMembers']['email'], $receiverName)->subject('แจ้งการเปิดใช้งานสมาชิก '.$dataMail['dataGroups']['subject']);
                                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                });
                                /* END E-MAIL FUNCTION */
                            }
                        } else if (is_null($dataExist->sub_groups_id)) {
                            if ($dataGroups->internal == 1) {
                                if (empty($dataExist->last_login)) {
                                    $dataUpdate = $this->updateMembers($dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 0);
                                    $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated."];
                                } else {
                                    $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, "Duplicated Member.");
                                    $rowRejected++;
                                }
                            } else {
                                $dataUpdate = $this->updateMembersSSO($dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 1);
                                $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated."];
                            }
                        } else {
                            $countNewEmailExist = Members::where("email", $arrDetail[4])->where('groups_id', $dataGroups->id)->where('id', '!=', $dataExist->id)->count();
                            if ($countNewEmailExist > 0) {
                                $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, "Duplicated new email.");
                                $rowRejected++;
                            } else {
                                if (empty($dataExist->last_login)) {
                                    $dataUpdate = $this->updateMembers($dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 2, null, false);
                                    $dataMember = $dataUpdate;
                                } else {
                                    $dataExist->active = 1;
                                    $dataExist->active_remark = 2;
                                    $dataExist->save();
                                    $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                    $dataMember = $dataExist;
                                }

                                if ($dataGroups->internal == 1) {
                                    $dataMember->sub_groupsList()->syncWithoutDetaching([$arrDetail[9] => ['active' => 2, 'email' => $arrDetail[4]]]);
                                    $dataUpdated[] = ["row" => $row] + $dataMember->toArray() + ["message" => "This member has changing of subgroup."];

                                    /* BEGIN E-MAIL FUNCTION */
                                    // Notify Mail (Member's Group Changed)
                                    $url = config('constants._BASE_URL').$dataGroups->key."/login";
                                    $dataMail = array(
                                        'dataMember'=>$dataMember,
                                        'dataGroups'=> $dataGroups,
                                        'dataOldSubGroups' => $dataMember->sub_groups()->first(),
                                        'dataNewSubGroups' => $dataMember->sub_groupsList()->where('active', 2)->orderBy('id', 'desc')->first(),
                                        'url' => $url,
                                        'newEmail' => $arrDetail[4]
                                    );
                                    Mail::send('levelgroups-change-subgroups-alert-mail', $dataMail, function($mail) use ($dataMail) {
                                        if ($dataMail['dataMember']['is_foreign'] != 1) {
                                            $receiverName = $dataMail['dataMember']['first_name']." ".$dataMail['dataMember']['last_name'];
                                        } else {
                                            $receiverName = $dataMail['dataMember']['first_name_en']." ".$dataMail['dataMember']['last_name_en'];
                                        }
                                        $mail->to([$dataMail['dataMember']['email'], $dataMail['newEmail']], $receiverName)->subject('แจ้งเปลี่ยนสถานะการเป็นสมาชิกจาก '.$dataMail['dataOldSubGroups']['title'].' เป็น '.$dataMail['dataNewSubGroups']['title']);
                                        $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                    });
                                    /* END E-MAIL FUNCTION */
                                } else {
                                    $dataMember->sub_groupsList()->where('active', 1)->update(['active' => 3]);
                                    $dataMember->sub_groupsList()->where('active', 2)->update(['active' => 4]);
                                    $dataMember->sub_groupsList()->syncWithoutDetaching([$arrDetail[9] => ['active' => 1]]);
                                    $dataUpdated[] = ["row" => $row] + $dataMember->toArray() + ["message" => "This member changed subgroup."];
                                }
                            }
                        }
                    } else {
                        if ($dataGroups->field_approval == "full_name") {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('first_name', $arrDetail[2])->where('last_name', $arrDetail[3])->first();
                        } else if ($dataGroups->field_approval == "id_card") {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[6])->first();
                        } else if ($dataGroups->field_approval == "occupation_id") {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('sub_groups_id', $arrDetail[9])->where($dataGroups->field_approval, $arrDetail[10])->first();
                        } else {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('email', $arrDetail[4])->first();
                        }

                        if ($dataPreApprovedExist) {
                            $dataUpdate = $this->updateMembersPreApproved($dataGroups, $dataLevelGroups, $dataPreApprovedExist, $arrDetail);
                            if ($dataUpdate) {
                                $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated."];
                            }
                        } else {
                            $dataCreate = $this->createMembersPreApproved($dataGroups, $dataLevelGroups, $arrDetail);
                            if ($dataCreate) {
                                $dataInserted[] = ["row" => $row] + $dataCreate->toArray();
                            }
                        }
                    }
                }
            }

            $row++;
            // if ( $row == 100 ) break;
        }

        fclose($resourceFile);

        return ["dataInsert" => $dataInsert, "dataInserted" => $dataInserted, "dataReject" => $dataReject, "dataUpdated" => $dataUpdated];
    }

    private function readMembersExcel($fileExcel, $countHeader, $dataLevelGroups, $dataGroups)
    {
        $oFunc = new _FunctionsController;
        $authSession = Auth::user();
        $dataInsert = array();
        $dataInserted = array();
        $dataUpdated = array();
        $dataReject = array();
        $resourceFile = fopen($fileExcel, "r");

        $countHeader = (int)$countHeader;
        if (count($countHeader) > 0) {
            for ($i=0; $i < $countHeader; $i++) {
                $arrHeader[] = fgetcsv($resourceFile);
            }
        }

        $row = 1;
        $rowInserted = 0;
        $rowRejected = 0;

        while (($arrDetail = fgetcsv($resourceFile)) !== false) {
            $dataFormValidations = $this->handleValidations($dataGroups, $dataLevelGroups, $arrDetail, true);

            if (!is_null($dataFormValidations['errorMessage'])) {
                if ($dataGroups) {
                    $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, $dataFormValidations['errorMessage']);
                } else {
                    $dataReject = $this->handleCommonRejected($dataReject, $arrDetail, $rowRejected, $row, $dataFormValidations['errorMessage']);
                }
                $rowRejected++;
            } else {

                if ($dataGroups->id == 3) {
                    if ($dataGroups->field_approval == "full_name") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('first_name', $arrDetail[2])->where('last_name', $arrDetail[3])->first();
                    } else if ($dataGroups->field_approval == "id_card") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[6])->first();
                    } else if ($dataGroups->field_approval == "occupation_id") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[10])->first();
                    } else if ($dataGroups->field_approval == "license_id") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[12])->first();
                    } else {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('email', $arrDetail[4])->first();
                    }

                    if ($dataExist) {
                        if ($dataExist->sub_groups_id == $arrDetail[9]) {
                            if ($dataExist->active == 1) {
                                if (empty($dataExist->last_login)) {
                                    $dataUpdate = $this->updateMembers($dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 0);
                                    $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated."];
                                } else {
                                    if (!$dataExist->level_groups()->where('level_groups.id', $arrDetail[11])->first()) {
                                        $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                        $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "Added member to unit."];
                                    } else {
                                        $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, "Duplicated Member.");
                                        $rowRejected++;
                                    }
                                }
                            } else {
                                $dataUpdate = $this->updateMembers($dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 3, 1);
                                $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "Activated member."];
                                /* BEGIN E-MAIL FUNCTION */
                                // Notify Mail (Member's reactivated)
                                $url = config('constants._BASE_URL').$dataGroups->key."/login";
                                $dataMail = array(
                                    'dataMembers'=>$dataExist,
                                    'dataGroups'=> $dataGroups,
                                    'dataSubGroups' => $dataExist->sub_groups()->first(),
                                    'dataLevelGroups' => $dataLevelGroups,
                                    'url' => $url
                                );
                                Mail::send('levelgroups-reactivated-mail', $dataMail, function($mail) use ($dataMail) {
                                    if ($dataMail['dataMembers']['is_foreign'] != 1) {
                                        $receiverName = $dataMail['dataMembers']['first_name']." ".$dataMail['dataMembers']['last_name'];
                                    } else {
                                        $receiverName = $dataMail['dataMembers']['first_name_en']." ".$dataMail['dataMembers']['last_name_en'];
                                    }
                                    $mail->to($dataMail['dataMembers']['email'], $receiverName)->subject('แจ้งการเปิดใช้งานสมาชิก '.$dataMail['dataGroups']['subject']);
                                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                });
                                /* END E-MAIL FUNCTION */
                            }
                        } else {
                            $countNewEmailExist = Members::where("email", $arrDetail[4])->where('groups_id', $dataGroups->id)->where('id', '!=', $dataExist->id)->count();
                            if ($countNewEmailExist > 0) {
                                $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, "Duplicated new email.");
                                $rowRejected++;
                            } else {
                                if (empty($dataExist->last_login)) {
                                    $dataUpdate = $this->updateMembers($dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 2, null, false);
                                    $dataMember = $dataUpdate;
                                } else {
                                    $dataExist->active = 1;
                                    $dataExist->active_remark = 2;
                                    $dataExist->save();
                                    $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                    $dataMember = $dataExist;
                                }

                                $dataMember->sub_groupsList()->syncWithoutDetaching([$arrDetail[9] => ['active' => 2, 'email' => $arrDetail[4]]]);
                                $dataUpdated[] = ["row" => $row] + $dataMember->toArray() + ["message" => "This member has changing of subgroup."];

                                /* BEGIN E-MAIL FUNCTION */
                                // Notify Mail (Member's Group Changed)
                                $url = config('constants._BASE_URL').$dataGroups->key."/login";
                                $dataMail = array(
                                    'dataMember'=>$dataMember,
                                    'dataGroups'=> $dataGroups,
                                    'dataOldSubGroups' => $dataMember->sub_groups()->first(),
                                    'dataNewSubGroups' => $dataMember->sub_groupsList()->where('active', 2)->orderBy('id', 'desc')->first(),
                                    'url' => $url,
                                    'newEmail' => $arrDetail[4]
                                );
                                Mail::send('levelgroups-change-subgroups-alert-mail', $dataMail, function($mail) use ($dataMail) {
                                    if ($dataMail['dataMember']['is_foreign'] != 1) {
                                        $receiverName = $dataMail['dataMember']['first_name']." ".$dataMail['dataMember']['last_name'];
                                    } else {
                                        $receiverName = $dataMail['dataMember']['first_name_en']." ".$dataMail['dataMember']['last_name_en'];
                                    }
                                    $mail->to([$dataMail['dataMember']['email'], $dataMail['newEmail']], $receiverName)->subject('แจ้งเปลี่ยนสถานะการเป็นสมาชิกจาก '.$dataMail['dataOldSubGroups']['title'].' เป็น '.$dataMail['dataNewSubGroups']['title']);
                                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                });
                                /* END E-MAIL FUNCTION */
                            }
                        }
                    } else {
                        // Add New Member
                        $dataCreate = $this->createMembers($dataGroups, $dataLevelGroups, $arrDetail);
                        if ($dataCreate) {
                            $dataInserted[] = ["row" => $row] + $dataCreate->toArray();

                            // Remove Pre-Approved
                            if ($dataGroups->field_approval == "full_name") {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('first_name', $arrDetail[2])->where('last_name', $arrDetail[3])->first();
                            } else if ($dataGroups->field_approval == "id_card") {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[6])->first();
                            } else if ($dataGroups->field_approval == "occupation_id") {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[10])->first();
                            } else if ($dataGroups->field_approval == "license_id") {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[12])->first();
                            } else {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('email', $arrDetail[4])->first();
                            }

                            if ($dataPreApprovedExist) {
                                if ($dataPreApprovedExist->courses) {
                                    $dataCreate->courses()->syncWithoutDetaching(array_pluck($dataPreApprovedExist->courses, 'id'));
                                    $dataPreApprovedExist->courses()->detach();
                                }

                                if ($dataPreApprovedExist->classrooms) {
                                    $dataCreate->classrooms()->syncWithoutDetaching(array_pluck($dataPreApprovedExist->classrooms, 'id'));
                                    $dataPreApprovedExist->classrooms()->detach();
                                }

                                $dataPreApprovedExist->delete();
                                $dataPreApprovedExist->level_groups()->detach();
                            }
                        }
                    }
                } else {
                    if ($dataGroups->field_approval == "full_name") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('first_name', $arrDetail[2])->where('last_name', $arrDetail[3])->first();
                    } else if ($dataGroups->field_approval == "id_card") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[6])->first();
                    } else if ($dataGroups->field_approval == "occupation_id") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('sub_groups_id', $arrDetail[9])->where($dataGroups->field_approval, $arrDetail[10])->first();
                    } else {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('email', $arrDetail[4])->first();
                    }

                    if ($dataExist) {
                        if ($dataExist->sub_groups_id == $arrDetail[9]) {
                            if ($dataExist->active == 1) {
                                if (empty($dataExist->last_login)) {
                                    $dataUpdate = $this->updateMembers($dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 0);
                                    $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated."];
                                } else {
                                    if (!$dataExist->level_groups()->where('level_groups.id', $arrDetail[11])->first()) {
                                        $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                        $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "Added member to unit."];
                                    } else {
                                        $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, "Duplicated Member.");
                                        $rowRejected++;
                                    }
                                }
                            } else {
                                $dataUpdate = $this->updateMembers($dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 3, 1);
                                $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "Activated member."];
                                /* BEGIN E-MAIL FUNCTION */
                                // Notify Mail (Member's reactivated)
                                $url = config('constants._BASE_URL').$dataGroups->key."/login";
                                $dataMail = array(
                                    'dataMembers'=>$dataExist,
                                    'dataGroups'=> $dataGroups,
                                    'dataSubGroups' => $dataExist->sub_groups()->first(),
                                    'dataLevelGroups' => $dataLevelGroups,
                                    'url' => $url
                                );
                                Mail::send('levelgroups-reactivated-mail', $dataMail, function($mail) use ($dataMail) {
                                    if ($dataMail['dataMembers']['is_foreign'] != 1) {
                                        $receiverName = $dataMail['dataMembers']['first_name']." ".$dataMail['dataMembers']['last_name'];
                                    } else {
                                        $receiverName = $dataMail['dataMembers']['first_name_en']." ".$dataMail['dataMembers']['last_name_en'];
                                    }
                                    $mail->to($dataMail['dataMembers']['email'], $receiverName)->subject('แจ้งการเปิดใช้งานสมาชิก '.$dataMail['dataGroups']['subject']);
                                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                });
                                /* END E-MAIL FUNCTION */
                            }
                        } else {
                            $countNewEmailExist = Members::where("email", $arrDetail[4])->where('groups_id', $dataGroups->id)->where('id', '!=', $dataExist->id)->count();
                            if ($countNewEmailExist > 0) {
                                $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, "Duplicated new email.");
                                $rowRejected++;
                            } else {
                                if (empty($dataExist->last_login)) {
                                    $dataUpdate = $this->updateMembers($dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 2, null, false);
                                    $dataMember = $dataUpdate;
                                } else {
                                    $dataExist->active = 1;
                                    $dataExist->active_remark = 2;
                                    $dataExist->save();
                                    $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                    $dataMember = $dataExist;
                                }

                                $dataMember->sub_groupsList()->syncWithoutDetaching([$arrDetail[9] => ['active' => 2, 'email' => $arrDetail[4]]]);
                                $dataUpdated[] = ["row" => $row] + $dataMember->toArray() + ["message" => "This member has changing of subgroup."];

                                /* BEGIN E-MAIL FUNCTION */
                                // Notify Mail (Member's Group Changed)
                                $url = config('constants._BASE_URL').$dataGroups->key."/login";
                                $dataMail = array(
                                    'dataMember'=>$dataMember,
                                    'dataGroups'=> $dataGroups,
                                    'dataOldSubGroups' => $dataMember->sub_groups()->first(),
                                    'dataNewSubGroups' => $dataMember->sub_groupsList()->where('active', 2)->orderBy('id', 'desc')->first(),
                                    'url' => $url,
                                    'newEmail' => $arrDetail[4]
                                );
                                Mail::send('levelgroups-change-subgroups-alert-mail', $dataMail, function($mail) use ($dataMail) {
                                    if ($dataMail['dataMember']['is_foreign'] != 1) {
                                        $receiverName = $dataMail['dataMember']['first_name']." ".$dataMail['dataMember']['last_name'];
                                    } else {
                                        $receiverName = $dataMail['dataMember']['first_name_en']." ".$dataMail['dataMember']['last_name_en'];
                                    }
                                    $mail->to([$dataMail['dataMember']['email'], $dataMail['newEmail']], $receiverName)->subject('แจ้งเปลี่ยนสถานะการเป็นสมาชิกจาก '.$dataMail['dataOldSubGroups']['title'].' เป็น '.$dataMail['dataNewSubGroups']['title']);
                                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                });
                                /* END E-MAIL FUNCTION */
                            }

                        }
                    } else {
                        // Add New Member
                        $dataCreate = $this->createMembers($dataGroups, $dataLevelGroups, $arrDetail);
                        if ($dataCreate) {
                            $dataInserted[] = ["row" => $row] + $dataCreate->toArray();

                            // Remove Pre-Approved
                            if ($dataGroups->field_approval == "full_name") {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('first_name', $arrDetail[2])->where('last_name', $arrDetail[3])->first();
                            } else if ($dataGroups->field_approval == "id_card") {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[6])->first();
                            } else if ($dataGroups->field_approval == "occupation_id") {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('sub_groups_id', $arrDetail[9])->where($dataGroups->field_approval, $arrDetail[10])->first();
                            } else {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('email', $arrDetail[4])->first();
                            }

                            if ($dataPreApprovedExist) {
                                if ($dataPreApprovedExist->courses) {
                                    $dataCreate->courses()->syncWithoutDetaching(array_pluck($dataPreApprovedExist->courses, 'id'));
                                    $dataPreApprovedExist->courses()->detach();
                                }

                                if ($dataPreApprovedExist->classrooms) {
                                    $dataCreate->classrooms()->syncWithoutDetaching(array_pluck($dataPreApprovedExist->classrooms, 'id'));
                                    $dataPreApprovedExist->classrooms()->detach();
                                }

                                $dataPreApprovedExist->delete();
                                $dataPreApprovedExist->level_groups()->detach();
                            }
                        }

                    }
                }
            }

            $row++;
            // if ( $row == 100 ) break;
        }

        fclose($resourceFile);

        return ["dataInsert" => $dataInsert, "dataInserted" => $dataInserted, "dataUpdated" => $dataUpdated, "dataReject" => $dataReject];
    }

    private function checkPermission($case)
    {
        $authSession = Auth::user();

        switch (strtolower($case)) {
            case 'upload':
                return $authSession->upload_status == 1;
                break;

            default:
                return 'failed';
                break;
        }

        return false;

    }

    public function importPreApprovedMembers(Request $request, _FunctionsController $oFunc, $id)
    {
        ini_set('max_execution_time', 300);

        if ($this->checkPermission('upload') === false) {
            return response()->json(array('message' => config('constants._errorMessage._403')), 404);
        }

        $dataLevelGroups = LevelGroups::find($id);

        if (!$dataLevelGroups) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "'Level Groups'", config('constants._errorMessage._404'))), 404);
        }

        if (!$request->hasFile('file')) {
            $excelExtension = "";
        } else {
            $fileExcel = $request->file('file');
            $excelExtension = strtolower($fileExcel->getClientOriginalExtension());
        }

        $validator = Validator::make(
            [
                'file'   => $excelExtension,
            ],
            [
                'file'   => 'required|in:csv',
            ],
            [
                'file.in' => 'The :attribute must be one of the following types: .csv',
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $fileConverted = $oFunc->convertFileUTF8($fileExcel);
        if ($fileConverted['file'] === false) {
            if (!empty($fileConverted['encoding'])) {
                $message = "The encoding '".$fileConverted['encoding']."' is not supported.";
            } else {
                $message = "This file encoding not supported.";
            }

            return response()->json(['message' => $message], 422);
        }

        $dataGroups = $dataLevelGroups->sub_groups()->with('groups')->first()->groups;

        $dataExcel = $this->readMembersPreApprovedExcel($fileExcel, 1, $dataLevelGroups, $dataGroups);

        $is_success = true;

        if (!empty($dataExcel['dataInserted']) && !empty($dataExcel['dataUpdated'])) {
            $message = "Successfully imported ".count($dataExcel['dataInserted'])." row(s) and updated ".count($dataExcel['dataUpdated'])." row(s)";
        } else if (!empty($dataExcel['dataInserted'])) {
            $message = "Successfully imported ".count($dataExcel['dataInserted'])." row(s).";
        } else if (!empty($dataExcel['dataUpdated'])) {
            $message = "Successfully updated ".count($dataExcel['dataUpdated'])." row(s).";
        } else {
            $is_success = false;
            $message = "Nothing members pre-approved was imported or updated.";
        }

        if (!$is_success) {
            $message = isset($message) ? $message : "Import failed.";
            return response()->json(array('is_error' => !$is_success, 'message' => $message, 'uploaded_members' => $dataExcel['dataInserted'], 'rejected_members' => $dataExcel['dataReject'], 'updated_members' => $dataExcel['dataUpdated']), 500);
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'uploaded_members' => $dataExcel['dataInserted'], 'rejected_members' => $dataExcel['dataReject'], 'updated_members' => $dataExcel['dataUpdated']), 200);

    }

    public function importMembers(Request $request, _FunctionsController $oFunc, $id)
    {
        ini_set('max_execution_time', 300);

        if ($this->checkPermission('upload') === false) {
            return response()->json(array('message' => config('constants._errorMessage._403')), 404);
        }

        $dataLevelGroups = LevelGroups::find($id);

        if (!$dataLevelGroups) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "'Level Groups'", config('constants._errorMessage._404'))), 404);
        }

        if (!$request->hasFile('file')) {
            $excelExtension = "";
        } else {
            $fileExcel = $request->file('file');
            $excelExtension = strtolower($fileExcel->getClientOriginalExtension());
        }

        $validator = Validator::make(
            [
                'file'   => $excelExtension,
            ],
            [
                'file'   => 'required|in:csv',
            ],
            [
                'file.in' => 'The :attribute must be one of the following types: .csv',
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $fileConverted = $oFunc->convertFileUTF8($fileExcel);
        if ($fileConverted['file'] === false) {
            if (!empty($fileConverted['encoding'])) {
                $message = "The encoding '".$fileConverted['encoding']."' is not supported.";
            } else {
                $message = "This file encoding not supported.";
            }

            return response()->json(['message' => $message], 422);
        }

        $dataGroups = $dataLevelGroups->sub_groups()->with('groups')->first()->groups;

        $dataExcel = $this->readMembersExcel($fileExcel, 1, $dataLevelGroups, $dataGroups);

        $is_success = true;

        if (!empty($dataExcel['dataInserted']) && !empty($dataExcel['dataUpdated'])) {
            $message = "Successfully imported ".count($dataExcel['dataInserted'])." row(s) and updated ".count($dataExcel['dataUpdated'])." row(s)";
        } else if (!empty($dataExcel['dataInserted'])) {
            $message = "Successfully imported ".count($dataExcel['dataInserted'])." row(s).";
        } else if (!empty($dataExcel['dataUpdated'])) {
            $message = "Successfully updated ".count($dataExcel['dataUpdated'])." row(s).";
        } else {
            $is_success = false;
            $message = "Nothing members was imported or updated.";
        }

        if (!$is_success) {
            $message = isset($message) ? $message : "Import failed.";
            return response()->json(array('is_error' => !$is_success, 'message' => $message, 'uploaded_members' => $dataExcel['dataInserted'], 'rejected_members' => $dataExcel['dataReject'], 'updated_members' => $dataExcel['dataUpdated']), 500);
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'uploaded_members' => $dataExcel['dataInserted'], 'rejected_members' => $dataExcel['dataReject'], 'updated_members' => $dataExcel['dataUpdated']), 200);

    }

    public function checkPreApproved($id, Request $request)
    {
        //
        $data = LevelGroups::find($id);

        if (!$data) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "'Level Groups'", config('constants._errorMessage._404'))), 404);
        }

        $dataGroups = $data->sub_groups()->with('groups')->first()->groups;

        $data->members_not_approved = $data->members()->whereNull('approved_type')->orderBy('create_datetime', 'DESC')->get();
        for($i=0; $i<count($data->members_not_approved); $i++) {
            $dataMatched = null;

            if ($dataGroups->field_approval == "full_name") {
                $dataMatched = $data->members_pre_approved()->where('first_name', $data->members_not_approved[$i]->first_name)->where('last_name', $data->members_not_approved[$i]->last_name)->first();
            } else if ($dataGroups->field_approval == "occupation_id") {
                $dataMatched = $data->members_pre_approved()->where('occupation_id', $data->members_not_approved[$i]->occupation_id)->first();
            }  else if ($dataGroups->field_approval == "license_id") {
                $dataMatched = $data->members_pre_approved()->where('license_id', $data->members_not_approved[$i]->license_id)->first();
            } else {
                $dataMatched = $data->members_pre_approved()->where('id_card', $data->members_not_approved[$i]->id_card)->first();
            }

            if ($dataMatched) {
                $data->members_not_approved[$i]->approved_type = 1;
                $data->members_not_approved[$i]->approved_field = $dataGroups->field_approval;
                $data->members_not_approved[$i]->approved_datetime = date('Y-m-d H:i:s');
                $data->members_not_approved[$i]->modify_datetime = date('Y-m-d H:i:s');
                $is_success = $data->members_not_approved[$i]->save();
            }
        }
        // $input = $request->json()->all();
        // $data->fill($input);
        // $data->ip = $_SERVER['REMOTE_ADDR'];
        // $data->encrypt_password = Hash::make($input['password']);
        // $data->modify_datetime = date('Y-m-d H:i:s');
        // $is_success = $data->save();
        // if ($data) {
        //     $data->approved_type = 2;
        //     $data->approved_by = Auth::user()->id;
        //     $data->approved_datetime = date('Y-m-d H:i:s');
        //     $data->modify_datetime = date('Y-m-d H:i:s');
        //     $data->modify_by = Auth::user()->id;
        //     $is_success = $data->save();
        // } else {
        //     $is_success = false;
        // }

        // dd($is_success);

        // if ($is_success) {
        //     $message = "The members has been updated.";
        // } else {
        //     $message = "Failed to update the members.";
        // }
        $message = "The members has been checked pre-approved.";
        return response()->json(array('is_error' => false, 'message' => $message), 200);
    }

}
