<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\Groups;

use Auth;

class GroupsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        // $data = Groups::orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);

        // for($i=0; $i<count($data); $i++) {
        //     $admins = Admins::find($data[$i]->modify_by);
        //     $data[$i]->modify_by = $admins->username;
        // }

        // return response()->json($data, 200);

        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $data = $authSessionGroups->groups();

        if ($request->has('search')) {
            $data = $data->where(function ($query) use ($request) {
                $query->where('title', 'like', '%'.$request['search'].'%')
                      ->orWhere('subject', 'like', '%'.$request['search'].'%');
            });
        }

        $data = $data->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);

        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
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
    public function store(Request $request, _RolesController $oRole)
    {
        //
        // Check Permission Acces
        if (!$oRole->haveAccess(null, "groups", "store")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'title' => 'required|max:255',
            'subject' => 'required|max:255',
            'key' => 'required|unique:groups,key|max:255',
            'targetaudience' => 'required|max:2',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = new Groups;
        $data->fill($input);
        $data->internal = 1;
        $data->is_connect_regis = 0;
        $data->keyset = $data->key;
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();
        if ($is_success) {
            $message = "The groups has been created.";
        } else {
            $message = "Failed to create the groups.";
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
        if (!$oRole->haveAccess($id, "groups")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Groups::find($id);
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
        if (!$oRole->haveAccess($id, "groups")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $authSession = Auth::user();
        $data = Groups::find($id);
        $input = $request->json()->all();

        if ($oRole->isSuper()) {
            $validator = Validator::make($input, [
                'title' => 'required|max:255',
                'subject' => 'required|max:255',
                // 'key' => 'required|unique:groups,key|max:255',
                'key' => 'required|unique:groups,key,'.$data->key.',key|max:255',
                'targetaudience' => 'required|max:2',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->messages(), 422);
            }

            $data->fill($input);
        } else {
            $data->fill(array_only($input, ['contact_profile_editing']));
        }

        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The groups has been updated.";
        } else {
            $message = "Failed to update the groups.";
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
        if (!$oRole->haveAccess(null, "groups", "destroy")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Groups::find($id);
        $is_success = $data->delete();
        if ($is_success) {
            $message = "The groups has been deleted.";
        } else {
            $message = "Failed to delete the groups.";
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

        $data = Groups::find($id);
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The group has been updated.";
        } else {
            $message = "Failed to update the group.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function sub_groups($id)
    {
        //
        $data = new Groups;
        $authSession = Auth::user();
        if($authSession->super_users){
            $data = $data->find($id);
            $data = $data->sub_groups()->where('id', $authSession->sub_groups_id);
        }else{
            $data = $data->find($id);
            $data = $data->sub_groups();
        }

        $data = $data->orderByRaw('CONVERT (title USING tis620) ASC')->get();

        return response()->json($data, 200);
    }

    public function courses($id, _RolesController $oRole)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "groups")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Groups::find($id)->courses;

        for($i=0; $i<count($data); $i++) {
            $data[$i]->title = $data[$i]->code." - ".$data[$i]->title;
        }

        return response()->json($data, 200);
    }

    public function all()
    {
        //
        $authSession = Auth::user();
        if($authSession->super_users){
            $authSession = $authSession->groups()->get();
        }else{
            $authSession = $authSession->admins_groups()->first();
            $authSession = $authSession->groups()->get();
        }
        return response()->json($authSession, 200);
    }

    public function all_groups()
    {
        //
        $data = new Groups;
        $data = $data->get();
        return response()->json($data, 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = Groups::find($input[$i]['id']);
            $data[$i]->fill($input[$i]);
            $data[$i]->save();
        }
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

        $entity = Groups::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Groups::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Groups::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Groups::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Groups::where('order', '>', $request['order'])->min('id');
                    Groups::find($next)->decrement('order');
                    $entity->moveBefore(Groups::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The groups has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

    public function questionnaire_packs($id, Request $request) {
        //
        $data = Groups::find($id)->questionnaire_packs()->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->no = $i + 1;
            $data[$i]->modify_by = $admins->username;
            $data[$i]->groups = $data[$i]->groups()->first();
            $data[$i]->questionnaires = $data[$i]->questionnaires()->get();
            for($s=0; $s<count($data[$i]->questionnaires); $s++) {
                $data[$i]->questionnaires[$s]->questionnaire_choices = $data[$i]->questionnaires[$s]->questionnaire_choices()->get();
            }
        }
        return response()->json($data, 200);
    }

}
