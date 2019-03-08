<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\Instructors;

use Auth;

class InstructorsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $data = new Instructors;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($request->has('search')){
            $data = $data->where(function ($query) use ($request) {
                $query->where('title', 'like', '%'.$request['search'].'%');
            });
        }

        if ($authSession->super_users) {
            $data = $data->where('admins_id', $authSession->id);
        } else {
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
        }

        $data = $data->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
            $data[$i]->sub_groups = $data[$i]->sub_groups()->first();
            $data[$i]->groups = $data[$i]->groups()->first();
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
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
            'title' => 'required|max:255',
            'email' => 'required|email',
            'short_remark' => 'max:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $authSession = Auth::user();

        $data = new Instructors;
        $data->fill($input);
        $data->admins_id = $authSession->id;
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;

        if (empty(trim($data->code))) {
            $data->code = strtoupper($oFunc->generateRandomString(6));
        } else {
            $uniqueCode = Instructors::where('code', '=', $data->code)->count();

            if ($uniqueCode > 0) {
                $message = "The code ".$data->code." already exist.";
                $is_success = false;

                return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
            }
        }

        $is_success = $data->save();

        if ($is_success) {
            $message = "The instructors has been created.";
        } else {
            $message = "Failed to create the instructors.";
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
        if (!$oRole->haveAccess($id, "instructors")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Instructors::find($id);
        $data->courses = $data->courses()->get();
        $data->groups = $data->groups()->first();
        $data->sub_groups = $data->sub_groups()->first();
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
    public function update($id, Request $request, _RolesController $oRole, _FunctionsController $oFunc)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "instructors")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
            'title' => 'required|max:255',
            'email' => 'required|email',
            'short_remark' => 'max:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Instructors::find($id);
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];

        if (empty(trim($data->code))) {
            $data->code = strtoupper($oFunc->generateRandomString(6));
        } else {
            $uniqueCode = Instructors::where('id', '!=', $data->id)->where('code', '=', $data->code)->count();

            if ($uniqueCode > 0) {
                $message = "The code ".$data->code." already exist.";
                $is_success = false;

                return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
            }
        }

        $is_success = $data->save();

        if ($is_success) {
            $message = "The instructors has been updated.";
        } else {
            $message = "Failed to update the instructors.";
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
        if (!$oRole->haveAccess($id, "instructors")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Instructors::find($id);
        $is_success = $data->delete();
        if ($is_success) {
            $message = "The instructors has been deleted.";
        } else {
            $message = "Failed to delete the instructors.";
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

        $data = Instructors::find($id);
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The instructor has been updated.";
        } else {
            $message = "Failed to update the instructor.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function all()
    {
        //
        $data = new Instructors;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();
        if($authSession->super_users){
            $data = $data->where('sub_groups_id', $authSession->sub_groups_id);
        }else{
            $data = $data->with('groups');
            $data = $data->whereHas('groups', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }

        $data = $data->get();
        for($i=0; $i<count($data); $i++) {
            $data[$i]->groups = $data[$i]->groups()->first();
            $data[$i]->title = $data[$i]->title." (". $data[$i]->groups->title .")";
        }
        return response()->json($data, 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = Instructors::find($input[$i]['id']);
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

        $entity = Instructors::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Instructors::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Instructors::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Instructors::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Instructors::where('order', '>', $request['order'])->min('id');
                    Instructors::find($next)->decrement('order');
                    $entity->moveBefore(Instructors::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The instructors has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

}
