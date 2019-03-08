<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Members;
use App\Models\Admins;
use App\Models\Groups;
use App\Models\SubGroups;
use App\Models\LevelGroups;
use App\Models\PasswordHistories;
use App\Models\Enroll;

use Hash;
use Input;

use Auth;
use Mail;

class MembersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    public function index(Request $request)
    {
        //
        $data = new Members;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();
        $authSessionLevelGroups = $authSession->admin2level_group()->get();

        $search = $request['search'];
        $from_date = $request['from_date'];
        $to_date = $request['to_date'];

        if($authSession->super_users){

            //Access
            $access = new Members;
            $access = $access->where('sub_groups_id', $authSession->sub_groups_id);
            $access = $access->whereHas('level_groups', function($query) use ($authSessionLevelGroups) {
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

            if ($request->has('approved_type')) {
                if ($request['approved_type'] == "waiting") {
                    $access = $access->whereNull('approved_type')->where('reject_status', 0);
                } else if ($request['approved_type'] == "rejected") {
                    $access = $access->where('reject_status', 1);
                } else {
                    $access = $access->where('approved_type', $request['approved_type']);
                }
            }

            if ($request->has('active')) {
                $access = $access->where('active', $request['active']);
            }

            if($from_date && $to_date){
                $from_date = date("Y-m-d", strtotime($from_date));
                $to_date = date("Y-m-d", strtotime($to_date."+1 day"));
                $access = $access->whereBetween('members.create_datetime',array($from_date,$to_date));
            }
            //End Access

            //Owner
            $data = $data->where('sub_groups_id', $authSession->sub_groups_id);
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

            if ($request->has('approved_type')) {
                if ($request['approved_type'] == "waiting") {
                    $data = $data->whereNull('approved_type')->where('reject_status', 0);
                } else if ($request['approved_type'] == "rejected") {
                    $data = $data->where('reject_status', 1);
                } else {
                    $data = $data->where('approved_type', $request['approved_type']);
                }
            }

            if ($request->has('active')) {
                $data = $data->where('active', $request['active']);
            }

            if($from_date && $to_date){
                $from_date = date("Y-m-d", strtotime($from_date));
                $to_date = date("Y-m-d", strtotime($to_date."+1 day"));
                $data = $data->whereBetween('members.create_datetime',array($from_date,$to_date));
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

            if ($request->has('approved_type')) {
                if ($request['approved_type'] == "waiting") {
                    $data = $data->whereNull('approved_type')->where('reject_status', 0);
                } else if ($request['approved_type'] == "rejected") {
                    $data = $data->where('reject_status', 1);
                } else {
                    $data = $data->where('approved_type', $request['approved_type']);
                }
            }

            if ($request->has('active')) {
                $data = $data->where('active', $request['active']);
            }

            if($from_date && $to_date){
                $from_date = date("Y-m-d", strtotime($from_date));
                $to_date = date("Y-m-d", strtotime($to_date."+1 day"));
                $data = $data->whereBetween('members.create_datetime',array($from_date,$to_date));
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
    public function store(Request $request, _FunctionsController $oFunc)
    {
        //
        $error = array();
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
            'company_code' => 'required_if:groups_id,2|max:255',
            // 'ref_id' => 'required|max:255',
            'gender' => 'required|in:F,M',
            'name_title' => 'required_unless:is_foreign,1|max:255',
            'name_title_en' => 'required_if:is_foreign,1|max:255',
            'first_name' => 'required_unless:is_foreign,1|max:255',
            'first_name_en' => 'required_if:is_foreign,1|max:255',
            'last_name' => 'required_unless:is_foreign,1|max:255',
            'last_name_en' => 'required_if:is_foreign,1|max:255',
            'email' => 'required|email|max:255',
            'password' => 'between:8,255|case_diff|numbers|letters',
            // 'id_card' => 'unique:members,id_card|max:255',
            'nationality' => 'required_if:groups_id,3|required_if:groups_id,4|max:255',
            'mobile_number' => 'required|max:255',
            'position_id' => 'required_if:groups_id,2|required_if:groups_id,3|required_if:groups_id,5|max:255',
            // 'department' => 'required_if:groups_id,2|required_if:groups_id,3|required_if:groups_id,5|max:255',
            'role' => 'required_if:groups_id,2|max:255',
            // 'license_type_id' => 'required_if:groups_id,3|max:255',
            // 'license_id' => 'required_if:groups_id,3|max:255',
            'education_level_id' => 'required_if:groups_id,3|required_if:groups_id,4|max:255',
            // 'faculty_id' => 'required|max:255',
            'occupation_id' => 'required_if:groups_id,3|required_if:groups_id,4|required_if:groups_id,5|max:255',
            // 'field_study_id' => 'required|max:255',
            // 'education_degree_id' => 'required|max:255',
            'table_number' => 'required_if:groups_id,5|max:255',
            'chief_name' => 'required_if:groups_id,5|max:255',
            // 'expire' => 'required|max:255',
            'active' => 'required|max:1',
            // 'sub_groups_id' => 'required|numeric',
            // 'member2level_group' => 'required|max:255',
            'inv_personal_first_name' => 'max:255',
            'inv_personal_last_name' => 'max:255',
            'inv_personal_tax_id' => 'max:13',
            'inv_personal_email' => 'email|max:128',
            'inv_personal_tel' => 'max:10',
            'inv_personal_address' => 'max:220',
            'inv_personal_zip_code' => 'max:5',
            'inv_corporate_name' => 'max:70',
            'inv_corporate_branch' => 'max:1',
            'inv_corporate_branch_no' => 'required_if:inv_corporate_branch,1|max:5',
            'inv_corporate_tax_id' => 'max:13',
            'inv_corporate_email' => 'email|max:128',
            'inv_corporate_tel' => 'max:10',
            'inv_corporate_address' => 'max:220',
            'inv_corporate_zip_code' => 'max:5',
        ],[
            'name_title.required_unless' => 'The :attribute field is required.',
            'first_name.required_unless' => 'The :attribute field is required.',
            'last_name.required_unless' => 'The :attribute field is required.',
            'name_title_en.required_if' => 'The :attribute field is required.',
            'first_name_en.required_if' => 'The :attribute field is required.',
            'last_name_en.required_if' => 'The :attribute field is required.',
            'nationality.required_if' => 'The :attribute field is required in this group.',
            'position_id.required_if' => 'The :attribute field is required in this group.',
            // 'department.required_if' => 'The :attribute field is required in this group.',
            'role.required_if' => 'The :attribute field is required in this group.',
            // 'license_type_id.required_if' => 'The :attribute field is required in this group.',
            // 'license_id.required_if' => 'The :attribute field is required.',
            'education_level_id.required_if' => 'The :attribute field is required in this group.',
            'occupation_id.required_if' => 'The :attribute field is required in this group.',
            'table_number.required_if' => 'The :attribute field is required in this group.',
            'chief_name.required_if' => 'The :attribute field is required in this group.',
        ]);

        if (empty($input['groups_id']) || !is_numeric($input['groups_id'])) {
            $error['groups_id'][] = "The groups id field is required.";
        } else {
            $dataGroup = Groups::find($input['groups_id']);
            if (!$dataGroup) {
                $error['groups_id'][] = "The groups id field is required.";
            } else {
                if ($dataGroup->internal == 0 && empty($input['ref_id'])) {
                    $error['ref_id'][] = "The ref id field is required in single sign-on group.";
                }
                if ($dataGroup->multi_lang_certificate == 1) {
                    if (empty($input['name_title'])) {
                        $error['name_title'][] = "The name title en field is required in group with multiple language certificate.";
                    }
                    if (empty($input['first_name'])) {
                        $error['first_name'][] = "The first name field is required in group with multiple language certificate.";
                    }
                    if (empty($input['last_name'])) {
                        $error['last_name'][] = "The last name field is required in group with multiple language certificate.";
                    }
                    if (empty($input['name_title_en'])) {
                        $error['name_title_en'][] = "The name title en field is required in group with multiple language certificate.";
                    }
                    if (empty($input['first_name_en'])) {
                        $error['first_name_en'][] = "The first name field is required in group with multiple language certificate.";
                    }
                    if (empty($input['last_name_en'])) {
                        $error['last_name_en'][] = "The last name field is required in group with multiple language certificate.";
                    }
                }
                if ($dataGroup->internal == 1) {
                    if (empty($input['password'])) {
                        $error['password'][] = "The password field is required in the internal group.";
                    }

                    if (empty($input['sub_groups_id'])) {
                        $error['sub_groups_id'][] = "The sub groups id field is required in the internal group.";
                    }

                    if (empty($input['member2level_group'])) {
                        $error['member2level_group'][] = "The member level group field is required in the internal group.";
                    }
                }
                if ($dataGroup->field_approval == 'id_card') {
                    if (empty($input['id_card'])) {
                        $error['id_card'][] = "The id card field is required in the groups using for field approval.";
                    } else if ((empty($input['is_foreign']) || $input['is_foreign'] != 1) && $oFunc->checkIDCard($input['id_card']) == false) {
                        $error['id_card'][] = "The id card field format is invalid.";
                    } else if (Members::where('id_card', $input['id_card'])->where('groups_id', $input['groups_id'])->first()) {
                        $error['id_card'][] = "The id card field has already been taken.";
                    }
                }
            }
        }

        if ($validator->fails() || !empty($error)) {
            $data = array_merge($validator->errors()->toArray(), $error);
            return response()->json($data, 422);
        }

        $data = new Members;
        // $input = $request->json()->all();
        $data->fill($input);

        $data->ip = $_SERVER['REMOTE_ADDR'];

        if (!empty($input['password'])) {
            $data->encrypt_password = Hash::make($input['password']);
        }

        $data->create_datetime = date('Y-m-d H:i:s');
        $data->created_by = Auth::user()->id;
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $data->last_login = date('Y-m-d H:i:s');
        $data->last_changed_password = date('Y-m-d H:i:s');
        $is_success = $data->save();

        if (!empty($input['sub_groups_id'])) {
            $data->sub_groupsList()->sync([$input['sub_groups_id'] => ['active' => 1]]);
        }

        if (isset($input['member2level_group']) && is_array($input['member2level_group'])) {
            $data->level_groups()->sync($input['member2level_group']);
        }

        if ($is_success) {
            $dataPwdHistory = new PasswordHistories;
            $dataPwdHistory->member_id = $data->id;
            $dataPwdHistory->password = $data->password;
            $dataPwdHistory->create_datetime = date('Y-m-d H:i:s');
            $dataPwdHistory->create_by = Auth::user()->id;
            $dataPwdHistory->modify_datetime = date('Y-m-d H:i:s');
            $dataPwdHistory->modify_by = Auth::user()->id;
            $dataPwdHistory->save();

            $message = "The members has been created.";
        } else {
            $message = "Failed to create the members.";
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
        if (!$oRole->haveAccess($id, "members")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();
        $authSessionLevelGroups = $authSession->admin2level_group()->get();

        $data = Members::find($id);
        $data->approved_admin = Admins::select('first_name')->find($data->approved_by);
        $data->rejected_admin = Admins::select('first_name')->find($data->rejected_by);

        if ($authSession->super_users) {
            $data->level_groups = $data->level_groups()->where(function($query) use ($authSession) {
                $query->where('admins_id', $authSession->id)->where('level_groups.groups_id', $authSession->groups_id);
            })->orWhere(function($query) use ($authSessionLevelGroups, $data) {
                $query->where('members_id', $data->id)->whereIn('level_groups_id', array_pluck($authSessionLevelGroups, 'id'));
            })->get();
            // $data->owner_level_groups = $data->level_groups()->where('admins_id', $authSession->id)->where('level_groups.groups_id', $authSession->groups_id);
            // $data->access_level_groups = $data->level_groups()->whereIn('level_groups_id', array_pluck($authSessionLevelGroups, 'id'));
            // return response()->json($data->level_groups, 500);
            // $data->level_groups = $data->owner_level_groups->union($data->access_level_groups)->get();
        } else if (!$oRole->isSuper()) {
            $data->level_groups = $data->level_groups()->where('groups_id', array_pluck($authSessionGroups, 'id'))->get();
        } else {
            $data->level_groups = $data->level_groups()->get();
        }

        $data->groups = $data->groups()->first();

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
        if (!$oRole->haveAccess($id, "members")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();
        $authSessionLevelGroups = $authSession->admin2level_group()->get();

        $error = array();
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
            'company_code' => 'required_if:groups_id,2|max:255',
            // 'ref_id' => 'required|max:255',
            // 'gender' => 'required|in:F,M',
            // 'name_title' => 'required_unless:is_foreign,1|max:255',
            // 'name_title_en' => 'required_if:is_foreign,1|max:255',
            'first_name' => 'required_unless:is_foreign,1|max:255',
            'first_name_en' => 'required_if:is_foreign,1|max:255',
            'last_name' => 'required_unless:is_foreign,1|max:255',
            'last_name_en' => 'required_if:is_foreign,1|max:255',
            'email' => 'required|email|max:255',
            'password' => 'between:8,255|case_diff|numbers|letters',
            // 'id_card' => 'unique:members,id_card|max:255',
            'nationality' => 'required_if:groups_id,3|required_if:groups_id,4|max:255',
            // 'mobile_number' => 'required|max:255',
            'mobile_number' => 'max:15',
            'position_id' => 'required_if:groups_id,2|required_if:groups_id,3|required_if:groups_id,5|max:255',
            // 'department' => 'required_if:groups_id,2|required_if:groups_id,3|required_if:groups_id,5|max:255',
            'role' => 'required_if:groups_id,2|max:255',
            // 'license_type_id' => 'required_if:groups_id,3|max:255',
            // 'license_id' => 'required_if:groups_id,3|max:255',
            'education_level_id' => 'required_if:groups_id,3|required_if:groups_id,4|max:255',
            // 'faculty_id' => 'required|max:255',
            'occupation_id' => 'required_if:groups_id,3|required_if:groups_id,4|required_if:groups_id,5|max:255',
            // 'field_study_id' => 'required|max:255',
            // 'education_degree_id' => 'required|max:255',
            'table_number' => 'required_if:groups_id,5|max:255',
            'chief_name' => 'required_if:groups_id,5|max:255',
            // 'expire' => 'required|max:255',
            'active' => 'required|max:1',
            // 'sub_groups_id' => 'required|numeric',
            // 'member2level_group' => 'required|max:255',
            'inv_personal_first_name' => 'max:255',
            'inv_personal_last_name' => 'max:255',
            'inv_personal_tax_id' => 'max:13',
            'inv_personal_email' => 'email|max:128',
            'inv_personal_tel' => 'max:10',
            'inv_personal_address' => 'max:220',
            'inv_personal_zip_code' => 'max:5',
            'inv_corporate_name' => 'max:70',
            'inv_corporate_branch' => 'max:1',
            'inv_corporate_branch_no' => 'required_if:inv_corporate_branch,1|max:5',
            'inv_corporate_tax_id' => 'max:13',
            'inv_corporate_email' => 'email|max:128',
            'inv_corporate_tel' => 'max:10',
            'inv_corporate_address' => 'max:220',
            'inv_corporate_zip_code' => 'max:5',
        ],[
            'name_title.required_unless' => 'The :attribute field is required.',
            'first_name.required_unless' => 'The :attribute field is required.',
            'last_name.required_unless' => 'The :attribute field is required.',
            'name_title_en.required_if' => 'The :attribute field is required.',
            'first_name_en.required_if' => 'The :attribute field is required.',
            'last_name_en.required_if' => 'The :attribute field is required.',
            'nationality.required_if' => 'The :attribute field is required in this group.',
            'position_id.required_if' => 'The :attribute field is required in this group.',
            // 'department.required_if' => 'The :attribute field is required in this group.',
            'role.required_if' => 'The :attribute field is required in this group.',
            // 'license_type_id.required_if' => 'The :attribute field is required in this group.',
            // 'license_id.required_if' => 'The :attribute field is required.',
            'education_level_id.required_if' => 'The :attribute field is required in this group.',
            'occupation_id.required_if' => 'The :attribute field is required in this group.',
            'table_number.required_if' => 'The :attribute field is required in this group.',
            'chief_name.required_if' => 'The :attribute field is required in this group.',
        ]);

        if (empty($input['groups_id']) || !is_numeric($input['groups_id'])) {
            $error['groups_id'][] = "The groups id field is required.";
        } else {
            $dataGroup = Groups::find($input['groups_id']);
            if (!$dataGroup) {
                $error['groups_id'][] = "The groups id field is required.";
            } else {
                if ($dataGroup->internal == 0 && empty($input['ref_id'])) {
                    $error['ref_id'][] = "The ref id field is required in single sign-on group.";
                }
                if ($dataGroup->multi_lang_certificate == 1) {
                    if (empty($input['name_title'])) {
                        $error['name_title'][] = "The name title en field is required in group with multiple language certificate.";
                    }
                    if (empty($input['first_name'])) {
                        $error['first_name'][] = "The first name field is required in group with multiple language certificate.";
                    }
                    if (empty($input['last_name'])) {
                        $error['last_name'][] = "The last name field is required in group with multiple language certificate.";
                    }
                    if (empty($input['name_title_en'])) {
                        $error['name_title_en'][] = "The name title en field is required in group with multiple language certificate.";
                    }
                    if (empty($input['first_name_en'])) {
                        $error['first_name_en'][] = "The first name field is required in group with multiple language certificate.";
                    }
                    if (empty($input['last_name_en'])) {
                        $error['last_name_en'][] = "The last name field is required in group with multiple language certificate.";
                    }
                } else {
                    if ($dataGroup->internal == 1) {
                        if (empty($input['name_title']) && $input['is_foreign'] != 1) {
                            $error['name_title'][] = "The name title en field is required.";
                        }
                        if (empty($input['name_title_en']) && $input['is_foreign'] == 1) {
                            $error['name_title_en'][] = "The name title en field is required.";
                        }
                    }
                }
                if ($dataGroup->internal == 1) {
                    if (empty($input['password'])) {
                        $error['password'][] = "The password field is required in the internal group.";
                    }

                    if (empty($input['sub_groups_id'])) {
                        $error['sub_groups_id'][] = "The sub groups id field is required in the internal group.";
                    }

                    if (empty($input['member2level_group'])) {
                        $error['member2level_group'][] = "The member level group field is required in the internal group.";
                    }

                    if (empty($input['gender']) || !in_array($input['gender'], ['M', 'F'])) {
                        $error['gender'][] = "The gender field is required ('M' or 'F' only).";
                    }

                    if (empty($input['mobile_number'])) {
                        $error['mobile_number'][] = "The mobile number field is required.";
                    }
                }
                if ($dataGroup->field_approval == 'id_card') {
                    if (empty($input['id_card'])) {
                        $error['id_card'][] = "The id card field is required in the groups using for field approval.";
                    } else if ((empty($input['is_foreign']) || $input['is_foreign'] != 1) && $oFunc->checkIDCard($input['id_card']) == false) {
                        $error['id_card'][] = "The id card field format is invalid.";
                    } else if (Members::where('id_card', $input['id_card'])->where('id', '!=', $input['id'])->where('groups_id', $input['groups_id'])->first()) {
                        $error['id_card'][] = "The id card field has already been taken.";
                    }
                }
            }
        }

        if ($validator->fails() || !empty($error)) {
            $data = array_merge($validator->errors()->toArray(), $error);
            return response()->json($data, 422);
        }

        $data = Members::find($id);
        // $input = $request->json()->all();

        $isReActive = false;
        if ($data->active != 1 && $input['active'] == 1) {
            if ($data->reject_status == 1) {
                $data->approved_type = 2;
                $data->approved_by = Auth::user()->id;
                $data->approved_datetime = date('Y-m-d H:i:s');

                $data->reject_status = 0;
                $data->rejected_by = null;
                $data->rejected_datetime = null;
            }

            $data->active_remark = 3;
            // $data->last_login = null;
            $data->incorrect_password = 0;
            unset($input['incorrect_password']);
            $isReActive = true;
        }

        if (!empty($input['password'])) {
            $isChangePassword = $data->password != $input['password'];
            if ($isChangePassword) {
                $data->last_changed_password = date('Y-m-d H:i:s');
            }
        } else {
            $isChangePassword = false;
        }

        $data->fill($input);
        $data->ip = $_SERVER['REMOTE_ADDR'];
        if (!empty($input['password'])) {
            $data->encrypt_password = Hash::make($input['password']);
        }
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if (!empty($input['sub_groups_id'])) {
            $data->sub_groupsList()->sync([$input['sub_groups_id'] => ['active' => 1]]);
        }

        if (isset($input['member2level_group']) && is_array($input['member2level_group'])) {
            $member2level_group = $input['member2level_group'];

            if ($authSession->super_users) {
                $data->owner_level_groups = $data->level_groups()->where('admins_id', $authSession->id)->where('level_groups.groups_id', $authSession->groups_id)->get();
                $data->access_level_groups = $data->level_groups()->whereIn('level_groups_id', array_pluck($authSessionLevelGroups, 'id'))->get();
                $dataLevelGroups = $data->owner_level_groups->union($data->access_level_groups)->all();
                $data->level_groupsByLevelGroups(array_pluck($dataLevelGroups, 'id'))->sync($member2level_group);
            } else if (!$oRole->isSuper()) {
                $dataLevelGroups = $data->level_groups()->where('groups_id', array_pluck($authSessionGroups, 'id'))->get();
                $data->level_groupsByLevelGroups(array_pluck($dataLevelGroups, 'id'))->sync($member2level_group);
            } else {
                $data->level_groups()->sync($member2level_group);
            }
        }

        if ($is_success) {
            $message = "The members has been updated.";

            if ($isChangePassword) {
                $dataOldPwd = PasswordHistories::where('active', 1)->update(['active' => 0]);

                $dataPwdHistory = new PasswordHistories;
                $dataPwdHistory->member_id = $data->id;
                $dataPwdHistory->password = $data->password;
                $dataPwdHistory->create_datetime = date('Y-m-d H:i:s');
                $dataPwdHistory->create_by = Auth::user()->id;
                $dataPwdHistory->modify_datetime = date('Y-m-d H:i:s');
                $dataPwdHistory->modify_by = Auth::user()->id;
                $dataPwdHistory->save();
            }

            if ($isReActive) {
                /* BEGIN E-MAIL FUNCTION */
                // Notify Mail (Member's reactivated)
                $url = config('constants._BASE_URL').$data->groups->key."/login";
                $dataMail = array(
                    'dataMembers'=>$data,
                    'dataGroups'=> $data->groups,
                    'dataSubGroups' => $data->sub_groups()->first(),
                    'dataLevelGroups' => $data->level_groups()->first(),
                    'url' => $url
                );
                Mail::send('members-reactivated-mail', $dataMail, function($mail) use ($dataMail) {
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
            $message = "Failed to update the members.";
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
        if (!$oRole->haveAccess($id, "members")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Members::find($id);

        $countEnroll = Enroll::where('members_id', $id)->count();

        if ($countEnroll > 0) {
            $is_success = false;
            $message = "ไม่สามารถลบสมาชิกได้ เนื่องจากสมาชิกดังกล่าวมีการลงทะเบียนเรียนเรียบร้อยแล้ว <br> ** แนะนำให้ทำการปิดการใช้งานแทน **";
        } else {
            $data->delete();
            $data->sub_groupsList()->detach();
            $data->level_groups()->detach();
            $data->courses()->detach();
            $is_success = $data;
            if ($is_success) {
                $message = "The members has been deleted.";
            } else {
                $message = "Failed to delete the members.";
            }
        }
        return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }

    public function all(Request $request)
    {
        //
        $data = new Members;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();
        $authSessionLevelGroups = $authSession->admin2level_group()->get();

        $search = $request['search'];

        if($authSession->super_users){
            //Access
            $access = new Members;
            $access = $access->where('sub_groups_id', $authSession->sub_groups_id);
            $access = $access->whereHas('level_groups', function($query) use ($authSessionLevelGroups) {
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
            //End Access

            //Owner
            $data = $data->where('sub_groups_id', $authSession->sub_groups_id);
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
            //End Owner

            $data = $data->orderBy('first_name', 'asc')->orderBy('first_name_en', 'asc');
            $data = $data->union($access)->get();
        }else{
            $data = $data->with('groups');
            $data = $data->whereHas('groups', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
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

            $data = $data->orderBy('first_name', 'asc')->orderBy('first_name_en', 'asc');
            $data = $data->get();
        }

        for($i=0; $i<count($data); $i++) {
            $data[$i]->groups = $data[$i]->groups()->first();
            $data[$i]->sub_groups = $data[$i]->sub_groups()->first();
            $data[$i]->level_groups = $data[$i]->level_groups()->get();
            $data[$i]->approved_admin = Admins::select('first_name')->find($data[$i]->approved_by);
        }

        return response()->json($data, 200);
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

        $data = Members::find($id);
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The member has been updated.";
        } else {
            $message = "Failed to update the member.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function approve($id, Request $request)
    {
        //
        $data = Members::find($id);

        if ($data) {
            $data->approved_type = 2;
            $data->approved_by = Auth::user()->id;
            $data->approved_datetime = date('Y-m-d H:i:s');

            $data->reject_status = 0;
            $data->rejected_by = null;
            $data->rejected_datetime = null;

            $data->active = 1;
            $data->modify_datetime = date('Y-m-d H:i:s');
            $data->modify_by = Auth::user()->id;
            $is_success = $data->save();
        } else {
            $is_success = false;
        }

        if ($is_success) {
            $message = "The members has been approved.";

            if ($data->groups->internal == 1) {
                /* BEGIN E-MAIL FUNCTION */
                // Notify Mail (New Members)
                $url_login = config('constants._BASE_URL').$data->groups->key."/login";
                $dataMail = array(
                    'dataMembers'=>$data,
                    'dataGroups'=> $data->groups()->first(),
                    'dataSubGroups' => $data->sub_groups()->first(),
                    'dataLevelGroups' => $data->level_groups()->first(),
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
            }
        } else {
            $message = "Failed to approve the members.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function reject($id, Request $request)
    {
        //
        $data = Members::find($id);

        if ($data) {
            $data->approved_type = null;
            $data->approved_by = null;
            $data->approved_datetime = null;

            $data->reject_status = 1;
            $data->rejected_by = Auth::user()->id;
            $data->rejected_datetime = date('Y-m-d H:i:s');

            $data->active = 0;
            $data->modify_datetime = date('Y-m-d H:i:s');
            $data->modify_by = Auth::user()->id;
            $is_success = $data->save();
        } else {
            $is_success = false;
        }

        if ($is_success) {
            $message = "The members has been rejected.";

            if ($data->groups->internal == 1) {
                /* BEGIN E-MAIL FUNCTION */
                // Notify Mail (New Members)
                $url_login = config('constants._BASE_URL').$data->groups->key."/login";
                $dataMail = array(
                    'dataMembers'=>$data,
                    'dataGroups'=> $data->groups()->first(),
                    'dataSubGroups' => $data->sub_groups()->first(),
                    'dataLevelGroups' => $data->level_groups()->first(),
                    'url_login' => $url_login
                );
                Mail::send('members-rejected-mail', $dataMail, function($mail) use ($dataMail) {
                    if ($dataMail['dataMembers']['is_foreign'] != 1) {
                        $receiverName = $dataMail['dataMembers']['first_name']." ".$dataMail['dataMembers']['last_name'];
                    } else {
                        $receiverName = $dataMail['dataMembers']['first_name_en']." ".$dataMail['dataMembers']['last_name_en'];
                    }
                    $mail->to($dataMail['dataMembers']['email'], $receiverName)->subject('แจ้งปฏิเสธการเป็นสมาชิก '.$dataMail['dataGroups']['subject']);
                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                });
                /* END E-MAIL FUNCTION */
            }
        } else {
            $message = "Failed to reject the members.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function downloadExampleFileUpload($groups_key) {
        $dataGroup = Groups::where('key', $groups_key)->first();

        if (!$dataGroup) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "'Group'", config('constants._errorMessage._404'))), 404);
        }

        return response()->download(storage_path('app/members/example-files-upload/').$dataGroup->key.'.csv', 'example-file-'.str_replace('set', '', strtolower($dataGroup->key)).'.csv');
    }
}
