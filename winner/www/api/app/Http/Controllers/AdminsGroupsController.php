<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\AdminsGroups;

use Auth;

class AdminsGroupsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $data = new AdminsGroups;

        if ($request->has('search')) {
            $data = $data->where('title', 'like', '%'.$request['search'].'%');
        }

        $data = $data->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);

        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
            $data[$i]->groups = $data[$i]->groups()->get();
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
            'title' => 'required|max:255',
            'admins_menu2admins_groups' => 'required|array',
            'admin2group' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = new AdminsGroups;
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if (isset($input['admins_menu2admins_groups'])) {
            $admins_menu2admins_groups = $input['admins_menu2admins_groups'];
            $data->admins_menu()->sync($admins_menu2admins_groups);
        }

        if (isset($input['admin2group'])) {
            $admin2group = $input['admin2group'];
            $data->groups()->sync($admin2group);
        }

        if ($is_success) {
            $message = "The admins groups has been created.";
        } else {
            $message = "Failed to create the admins groups.";
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
        $data = AdminsGroups::find($id);
        $data->admins_menu = $data->admins_menu()->get();
        $data->groups = $data->groups()->get();
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

        $validator = Validator::make($input, [
            'title' => 'required|max:255',
            'admins_menu2admins_groups' => (empty($input['_mode']) ? 'required|' : '').'array',
            'admin2group' => (empty($input['_mode']) ? 'required|' : '').'array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = AdminsGroups::find($id);
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if (isset($input['admins_menu2admins_groups'])) {
            $admins_menu2admins_groups = $input['admins_menu2admins_groups'];
            $data->admins_menu()->sync($admins_menu2admins_groups);
        }

        if (isset($input['admin2group'])) {
            $admin2group = $input['admin2group'];
            $data->groups()->sync($admin2group);
        }

        if ($is_success) {
            $message = "The admins groups has been updated.";
        } else {
            $message = "Failed to update the admins groups.";
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
        $data = AdminsGroups::find($id);
        $is_success = $data->delete();
        $data->groups()->detach();
        $data->admins_menu()->detach();
        if ($is_success) {
            $message = "The admins groups has been deleted.";
        } else {
            $message = "Failed to delete the admins groups.";
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

        $data = AdminsGroups::find($id);
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The admins groups has been updated.";
        } else {
            $message = "Failed to update the admins groups.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function all()
    {
        //
        $data = AdminsGroups::get();
        return response()->json($data, 200);
    }

    public function super_user_all()
    {
        //
        $data = AdminsGroups::where('external', 1)->get();
        return response()->json($data, 200);
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

        $entity = AdminsGroups::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = AdminsGroups::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = AdminsGroups::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = AdminsGroups::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = AdminsGroups::where('order', '>', $request['order'])->min('id');
                    AdminsGroups::find($next)->decrement('order');
                    $entity->moveBefore(AdminsGroups::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The admin groups has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

}
