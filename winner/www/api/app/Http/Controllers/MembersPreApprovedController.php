<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\MembersPreApproved;
use App\Models\Members;
use App\Models\Admins;
use App\Models\Groups;

use Hash;
use Input;
use Validator;
use Auth;

class MembersPreApprovedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $data = new MembersPreApproved;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();
        $authSessionLevelGroups = $authSession->level_groups()->get();

        $search = $request['search'];
        $from_date = $request['from_date'];
        $to_date = $request['to_date'];

        if($authSession->super_users){

            //Access
            $access = new MembersPreApproved;
            $access = $access->whereHas('level_groups', function($query) use ($authSessionLevelGroups){
                $query->whereIn('level_groups_id', array_pluck($authSessionLevelGroups, 'id'));
            });

            if($search){
                $access = $access->where(function ($query) use ($search) {
                    $query->where('email', 'like', '%'.$search.'%')
                          ->orWhere('first_name', 'like', '%'.$search.'%')
                          ->orWhere('first_name_en', 'like', '%'.$search.'%')
                          ->orWhere('last_name', 'like', '%'.$search.'%')
                          ->orWhere('last_name_en', 'like', '%'.$search.'%');
                });
            }

            if($from_date && $to_date){
                $from_date = date("Y-m-d", strtotime($from_date));
                $to_date = date("Y-m-d", strtotime($to_date."+1 day"));
                $access = $access->whereBetween('members_pre_approved.create_datetime',array($from_date,$to_date));
            }
            //End Access

            //Owner
            $data = $data->whereHas('level_groups', function($query) use ($authSession, $authSessionGroups) {
                $query->where('admins_id', $authSession->id);
                $query->where('groups_id', $authSession->groups_id);
            });

            if($search){
                $data = $data->where(function ($query) use ($search) {
                    $query->where('email', 'like', '%'.$search.'%')
                          ->orWhere('first_name', 'like', '%'.$search.'%')
                          ->orWhere('first_name_en', 'like', '%'.$search.'%')
                          ->orWhere('last_name', 'like', '%'.$search.'%')
                          ->orWhere('last_name_en', 'like', '%'.$search.'%');
                });
            }

            if($from_date && $to_date){
                $from_date = date("Y-m-d", strtotime($from_date));
                $to_date = date("Y-m-d", strtotime($to_date."+1 day"));
                $data = $data->whereBetween('members_pre_approved.create_datetime',array($from_date,$to_date));
            }
            //End Owner

            $page = $request->input('page', 1);;
            $per_page = $request->input('per_page', 10);
            $order_by = $request->input('order_by', 'id');
            $order_direction = $request->input('order_direction', 'DESC');

            $data = $data->orderBy($order_by,$order_direction);
            $data = $data->union($access)->get();
            for($i=0; $i<count($data); $i++) {
                $data[$i]->groups = $data[$i]->groups()->first();
                $data[$i]->sub_groups = $data[$i]->sub_groups()->first();
                $data[$i]->level_groups = $data[$i]->level_groups()->get();
                $data[$i]->approved_admin = Admins::select('first_name')->find($data[$i]->approved_by);
            }

            $offSet = ($page * $per_page) - $per_page;
            $itemsForCurrentPage = array_slice($data->toArray(), $offSet, $per_page, true);
            $data = new \Illuminate\Pagination\LengthAwarePaginator($itemsForCurrentPage, count($data), $per_page, $page);



            // if ($request->has('approved_type')) {
            //     if ($request['approved_type'] == "waiting") {
            //         $data = $data->whereNull('approved_type')->where('reject_status', 0);
            //     } else if ($request['approved_type'] == "rejected") {
            //         $data = $data->where('reject_status', 1);
            //     } else {
            //         $data = $data->where('approved_type', $request['approved_type']);
            //     }
            // }

            // if ($request->has('active')) {
            //     $data = $data->where('active', $request['active']);
            // }



        }else{

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

            if($search){
                $data = $data->where(function ($query) use ($search) {
                    $query->where('email', 'like', '%'.$search.'%')
                          ->orWhere('first_name', 'like', '%'.$search.'%')
                          ->orWhere('first_name_en', 'like', '%'.$search.'%')
                          ->orWhere('last_name', 'like', '%'.$search.'%')
                          ->orWhere('last_name_en', 'like', '%'.$search.'%');
                });
            }

            if($from_date && $to_date){
                $from_date = date("Y-m-d", strtotime($from_date));
                $to_date = date("Y-m-d", strtotime($to_date."+1 day"));
                $data = $data->whereBetween('members_pre_approved.create_datetime',array($from_date,$to_date));
            }


            $order_by = $request->input('order_by', 'id');
            $order_direction = $request->input('order_direction', 'DESC');
            $data = $data->orderBy($order_by,$order_direction)->paginate($request['per_page']);;
            for($i=0; $i<count($data); $i++) {
                $data[$i]->groups = $data[$i]->groups()->first();
                $data[$i]->sub_groups = $data[$i]->sub_groups()->first();
                $data[$i]->level_groups = $data[$i]->level_groups()->get();
                $data[$i]->approved_admin = Admins::select('first_name')->find($data[$i]->approved_by);
            }


            // if ($request->has('approved_type')) {
            //     if ($request['approved_type'] == "waiting") {
            //         $data = $data->whereNull('approved_type')->where('reject_status', 0);
            //     } else if ($request['approved_type'] == "rejected") {
            //         $data = $data->where('reject_status', 1);
            //     } else {
            //         $data = $data->where('approved_type', $request['approved_type']);
            //     }
            // }

            // if ($request->has('active')) {
            //     $data = $data->where('active', $request['active']);
            // }


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
        $error = array();
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
        ]);

        if (empty($input['groups_id']) || !is_numeric($input['groups_id'])) {
            $error['groups_id'][] = "The groups id field is required.";
        } else {
            $dataGroup = Groups::find($input['groups_id']);
            if (!$dataGroup) {
                $error['groups_id'][] = "The groups id field is required.";
            } else {
                if ($dataGroup->field_approval == 'full_name' && (empty($input['first_name']) || empty($input['last_name']))) {
                    $error['full_name'][] = "The full name field is required in the groups using for field approval.";
                } else if ($dataGroup->field_approval == 'id_card') {
                    if (empty($input['id_card'])) {
                        $error['id_card'][] = "The id card field is required in the groups using for field approval.";
                    } else if ((empty($input['is_foreign']) || $input['is_foreign'] != 1) && $oFunc->checkIDCard($input['id_card']) == false) {
                        $error['id_card'][] = "The id card field format is invalid.";
                    } else if (Members::where('id_card', $input['id_card'])->where('groups_id', $input['groups_id'])->first()) {
                        $error['id_card'][] = "The id card field has already been taken.";
                    } else if (MembersPreApproved::where('id_card', $input['id_card'])->where('groups_id', $input['groups_id'])->first()) {
                        $error['id_card'][] = "The id card field has already been taken.";
                    }
                } else if ($dataGroup->field_approval == 'occupation_id' && empty($input['occupation_id'])) {
                    $error['occupation_id'][] = "The occupation id field is required in the groups using for field approval.";
                } else if ($dataGroup->field_approval == 'license_id' && empty($input['license_id'])) {
                    $error['license_id'][] = "The license id field is required in the groups using for field approval.";
                } else if ($dataGroup->field_approval == 'email' && empty($input['email'])) {
                    $error['email'][] = "The email field is required in the groups using for field approval.";
                }
            }
        }

        if ($validator->fails() || !empty($error)) {
            $data = array_merge($validator->errors()->toArray(), $error);
            return response()->json($data, 422);
        }

        $data = new MembersPreApproved;
        // $input = $request->json()->all();
        $data->fill($input);

        $data->ip = $_SERVER['REMOTE_ADDR'];
        if (!empty($input['password'])) {
            $data->encrypt_password = Hash::make($input['password']);
        }
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->last_login = date('Y-m-d H:i:s');
        $is_success = $data->save();

        if ($is_success) {
            $message = "The members pre-approved has been created.";
        } else {
            $message = "Failed to create the members pre-approved.";
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
        if (!$oRole->haveAccess($id, "members_pre_approved")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = MembersPreApproved::find($id);
        $data->approved_admin = Admins::select('first_name')->find($data->approved_by);
        $data->rejected_admin = Admins::select('first_name')->find($data->rejected_by);
        $data->level_groups = $data->level_groups()->get();
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
    public function update($id, Request $request, _FunctionsController $oFunc, _RolesController $oRole)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "members_pre_approved")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $error = array();
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
        ]);

        if (empty($input['groups_id']) || !is_numeric($input['groups_id'])) {
            $error['groups_id'][] = "The groups id field is required.";
        } else {
            $dataGroup = Groups::find($input['groups_id']);
            if (!$dataGroup) {
                $error['groups_id'][] = "The groups id field is required.";
            } else {
                if ($dataGroup->field_approval == 'full_name') {
                    if (empty($input['first_name']) || empty($input['last_name'])) {
                        $error['first_name'][] = "The full name field is required in the groups using for field approval.";
                        $error['last_name'][] = "The full name field is required in the groups using for field approval.";
                    } else if (MembersPreApproved::where('first_name', $input['first_name'])->where('last_name', $input['last_name'])->where('id', '!=', $input['id'])->where('groups_id', $input['groups_id'])->first()) {
                        $error['first_name'][] = "The full name field has already been taken.";
                        $error['last_name'][] = "The full name field has already been taken.";
                    }
                } else if ($dataGroup->field_approval == 'id_card') {
                    if (empty($input['id_card'])) {
                        $error['id_card'][] = "The id card field is required in the groups using for field approval.";
                    } else if ((empty($input['is_foreign']) || $input['is_foreign'] != 1) && $oFunc->checkIDCard($input['id_card']) == false) {
                        $error['id_card'][] = "The id card field format is invalid.";
                    } else if (Members::where('id_card', $input['id_card'])->where('groups_id', $input['groups_id'])->first()) {
                        $error['id_card'][] = "The id card field has already been taken.";
                    } else if (MembersPreApproved::where('id_card', $input['id_card'])->where('id', '!=', $input['id'])->where('groups_id', $input['groups_id'])->first()) {
                        $error['id_card'][] = "The id card field has already been taken.";
                    }
                } else if ($dataGroup->field_approval == 'occupation_id') {
                    if (empty($input['occupation_id'])) {
                        $error['occupation_id'][] = "The occupation id field is required in the groups using for field approval.";
                    } else if (MembersPreApproved::where('occupation_id', $input['occupation_id'])->where('id', '!=', $input['id'])->where('groups_id', $input['groups_id'])->first()) {
                        $error['occupation_id'][] = "The occupation id field has already been taken.";
                    }
                } else if ($dataGroup->field_approval == 'license_id') {
                    if (empty($input['license_id'])) {
                        $error['license_id'][] = "The license id field is required in the groups using for field approval.";
                    } else if (MembersPreApproved::where('license_id', $input['license_id'])->where('id', '!=', $input['id'])->where('groups_id', $input['groups_id'])->first()) {
                        $error['license_id'][] = "The license id field has already been taken.";
                    }
                } else if ($dataGroup->field_approval == 'email') {
                    if (empty($input['email'])) {
                        $error['email'][] = "The email field is required in the groups using for field approval.";
                    } else if (MembersPreApproved::where('email', $input['email'])->where('id', '!=', $input['id'])->where('groups_id', $input['groups_id'])->first()) {
                        $error['email'][] = "The email field has already been taken.";
                    }
                }
            }
        }

        if ($validator->fails() || !empty($error)) {
            $data = array_merge($validator->errors()->toArray(), $error);
            return response()->json($data, 422);
        }

        $data = MembersPreApproved::find($id);
        // $input = $request->json()->all();
        $data->fill($input);
        $data->ip = $_SERVER['REMOTE_ADDR'];

        if (!empty($input['password'])) {
            $data->encrypt_password = Hash::make($input['password']);
        }

        $data->modify_datetime = date('Y-m-d H:i:s');
        $is_success = $data->save();

        if (isset($input['member2level_group'])) {
            $member2level_group = $input['member2level_group'];
            $data->level_groups()->sync($member2level_group);
        }

        if ($is_success) {
            $message = "The members pre-approved has been updated.";
        } else {
            $message = "Failed to update the members pre-approved.";
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
        if (!$oRole->haveAccess($id, "members_pre_approved")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = MembersPreApproved::find($id);
        $data->delete();
        $data->level_groups()->detach();
        $data->courses()->detach();
        $data->classrooms()->detach();
        $is_success = $data;

        if ($is_success) {
            $message = "The members pre-approved has been deleted.";
        } else {
            $message = "Failed to delete the members pre-approved.";
        }
        return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }

    public function approve($id, Request $request)
    {
        //
        $data = MembersPreApproved::find($id);

        if ($data) {
            $data->approved_type = 2;
            $data->approved_by = Auth::user()->id;
            $data->approved_datetime = date('Y-m-d H:i:s');

            $data->reject_status = 0;
            $data->rejected_by = null;
            $data->rejected_datetime = null;

            $data->modify_datetime = date('Y-m-d H:i:s');
            $data->modify_by = Auth::user()->id;
            $is_success = $data->save();
        } else {
            $is_success = false;
        }

        if ($is_success) {
            $message = "The members pre-approved has been approved.";
        } else {
            $message = "Failed to approve the members pre-approved.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function reject($id, Request $request)
    {
        //
        $data = MembersPreApproved::find($id);

        if ($data) {
            $data->approved_type = null;
            $data->approved_by = null;
            $data->approved_datetime = null;

            $data->reject_status = 1;
            $data->rejected_by = Auth::user()->id;
            $data->rejected_datetime = date('Y-m-d H:i:s');

            $data->modify_datetime = date('Y-m-d H:i:s');
            $data->modify_by = Auth::user()->id;
            $is_success = $data->save();
        } else {
            $is_success = false;
        }

        if ($is_success) {
            $message = "The members pre-approved has been rejected.";
        } else {
            $message = "Failed to reject the members pre-approved.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function downloadExampleFileUpload($groups_key) {
        $dataGroup = Groups::where('key', $groups_key)->first();

        if (!$dataGroup) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "'Group'", config('constants._errorMessage._404'))), 404);
        }

        return response()->download(storage_path('app/members/example-files-upload/').$dataGroup->key.'-pre-approved.csv', 'example-file-'.str_replace('set', '', strtolower($dataGroup->key)).'-pre-approved.csv');
    }


}
