<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\Categories;

use Auth;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request, _RolesController $oRole)
    {
        //
        $data = new Categories;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($request->has('search')){
            $data = $data->where(function ($query) use ($request) {
                $query->where('title', 'like', '%'.$request['search'].'%');
            });
        }

        $data = $data->with('groups');

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
            // $data = $data->with('groups');
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
            // $data[$i]->groups = $data[$i]->groups()->get();
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
            'title' => 'required|max:255',
            // 'icon' => 'required|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $authSession = Auth::user();

        $data = new Categories;
        $data->fill($input);

        if ($authSession->super_users) {
            $data->admins_id = $authSession->id;
        }

        $data->create_datetime = date('Y-m-d H:i:s');
        $data->create_by = $authSession->id;
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();
        if ($is_success) {
            $message = "The categories has been created.";
        } else {
            $message = "Failed to create the categories.";
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
        if (!$oRole->haveAccess($id, "categories")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Categories::find($id);
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
        if (!$oRole->haveAccess($id, "categories")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
            'title' => 'required|max:255',
            // 'icon' => 'required|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Categories::find($id);
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();
        if ($is_success) {
            $message = "The categories has been updated.";
        } else {
            $message = "Failed to update the categories.";
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
        if (!$oRole->haveAccess($id, "categories")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Categories::find($id);
        $is_success = $data->delete();
        if ($is_success) {
            $message = "The categories has been deleted.";
        } else {
            $message = "Failed to delete the categories.";
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

        $data = Categories::find($id);
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The category has been updated.";
        } else {
            $message = "Failed to update the category.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function parent()
    {
        //
        $data = Categories::where('parent','0')->get();
        return response()->json($data, 200);
    }

    public function all()
    {
        //
        $data = new Categories;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        $data = $data->with('groups')->where('status', 1);

        if($authSession->super_users){
            $data = $data->where('admins_id', $authSession->id)->orWhere(function($query) use ($authSession) {
                $query->whereNull('admins_id')->where('groups_id', $authSession->groups_id);
            });
        }else{
            $data = $data->whereHas('groups', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }

        $data = $data->get();
        for($i=0; $i<count($data); $i++) {
            $data[$i]->title = $data[$i]->title." (". $data[$i]->groups->title .")";
        }
        return response()->json($data, 200);
    }

    public function all_categories()
    {
        //
        $data = new Categories;
        $data = $data->with('groups')->get();
        for($i=0; $i<count($data); $i++) {
            $data[$i]->title = $data[$i]->title." (". $data[$i]->groups->title .")";
        }
        return response()->json($data, 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = Categories::find($input[$i]['id']);
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

        $entity = Categories::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Categories::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Categories::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Categories::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Categories::where('order', '>', $request['order'])->min('id');
                    Categories::find($next)->decrement('order');
                    $entity->moveBefore(Categories::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The categories has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

}
