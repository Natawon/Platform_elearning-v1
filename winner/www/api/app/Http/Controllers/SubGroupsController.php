<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\LevelGroups;
use App\Models\SubGroups;
use App\Models\Domains;

use Auth;

class SubGroupsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $per_page = $request->input('per_page', 10);
        $order_by = $request->input('order_by', 'sub_groups.title');
        $order_direction = $request->input('order_direction', 'ASC');

        $data = new SubGroups;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($request->has('search')){
            // $data = $data->select('events.*')
                // ->leftJoin('members', 'events.member_id', '=', 'members.id')
                $data = $data->where(function ($query) use ($request) {
                    $query->where('sub_groups.title', 'like', '%'.$request['search'].'%')
                          ->orWhere('sub_groups.code', 'like', '%'.$request['search'].'%');
                });
        }

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

        if ($order_by == "sub_groups.title" || $order_by == "title") {
            $data = $data->orderByRaw('CONVERT (sub_groups.title USING tis620) '.$order_direction);
        } else {
            $data = $data->orderBy($order_by, $order_direction);
        }

        $data = $data->paginate($per_page);

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
    public function store(Request $request)
    {
        //
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
            'title' => 'required|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = new SubGroups;
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        foreach ($input['domains'] as $domain) {
            if (isset($domain['id'])) {
                $dataDomain = Domains::find($domain['id']);
                if ($dataDomain) {
                    $dataDomain->fill($domain);
                    $dataDomain->sub_groups_id = $data->id;
                    $dataDomain->create_datetime = date('Y-m-d H:i:s');
                    $dataDomain->create_by = $input['admin_id'];
                    $dataDomain->modify_datetime = date('Y-m-d H:i:s');
                    $dataDomain->modify_by = $input['admin_id'];
                    $is_success = $dataDomain->save();
                } else {
                    $dataDomain = new Domains;
                    $dataDomain->fill($domain);
                    $dataDomain->sub_groups_id = $data->id;
                    $dataDomain->create_datetime = date('Y-m-d H:i:s');
                    $dataDomain->create_by = $input['admin_id'];
                    $dataDomain->modify_datetime = date('Y-m-d H:i:s');
                    $dataDomain->modify_by = $input['admin_id'];
                    $is_success = $dataDomain->save();
                }
            } else {
                $dataDomain = new Domains;
                $dataDomain->fill($domain);
                $dataDomain->sub_groups_id = $data->id;
                $dataDomain->create_datetime = date('Y-m-d H:i:s');
                $dataDomain->create_by = $input['admin_id'];
                $dataDomain->modify_datetime = date('Y-m-d H:i:s');
                $dataDomain->modify_by = $input['admin_id'];
                $is_success = $dataDomain->save();
            }
        }

        if ($is_success) {
            $message = "The sub group has been created.";
        } else {
            $message = "Failed to create the sub group.";
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
        if (!$oRole->haveAccess($id, "sub_groups")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = SubGroups::with('domains')->find($id);
        // $data->domains = $data->domains()->orderBy('title','asc')->get();
        return response()->json($data, 200);
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
        if (!$oRole->haveAccess($id, "sub_groups")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
            'title' => 'required|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = SubGroups::find($id);
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if (!empty($input['domains'])) {
            foreach ($input['domains'] as $domain) {
                if (isset($domain['id'])) {
                    $dataDomain = Domains::find($domain['id']);
                    if ($dataDomain) {
                        $dataDomain->fill($domain);
                        $dataDomain->sub_groups_id = $data->id;
                        $dataDomain->create_datetime = date('Y-m-d H:i:s');
                        $dataDomain->create_by = $input['admin_id'];
                        $dataDomain->modify_datetime = date('Y-m-d H:i:s');
                        $dataDomain->modify_by = $input['admin_id'];
                        $is_success = $dataDomain->save();
                    } else {
                        $dataDomain = new Domains;
                        $dataDomain->fill($domain);
                        $dataDomain->sub_groups_id = $data->id;
                        $dataDomain->create_datetime = date('Y-m-d H:i:s');
                        $dataDomain->create_by = $input['admin_id'];
                        $dataDomain->modify_datetime = date('Y-m-d H:i:s');
                        $dataDomain->modify_by = $input['admin_id'];
                        $is_success = $dataDomain->save();
                    }
                } else {
                    $dataDomain = new Domains;
                    $dataDomain->fill($domain);
                    $dataDomain->sub_groups_id = $data->id;
                    $dataDomain->create_datetime = date('Y-m-d H:i:s');
                    $dataDomain->create_by = $input['admin_id'];
                    $dataDomain->modify_datetime = date('Y-m-d H:i:s');
                    $dataDomain->modify_by = $input['admin_id'];
                    $is_success = $dataDomain->save();
                }
            }
        }

        if ($is_success) {
            $message = "The sub group has been updated.";
        } else {
            $message = "Failed to update the sub group.";
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
        if (!$oRole->haveAccess($id, "sub_groups")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = SubGroups::find($id);
        $is_success = $data->delete();
        if ($is_success) {
            $message = "The sub group has been deleted.";
        } else {
            $message = "Failed to delete the sub group.";
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

        $data = SubGroups::find($id);
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The sub group has been updated.";
        } else {
            $message = "Failed to update the sub group.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function all()
    {
        //
        $data = new SubGroups;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();
        if($authSession->super_users){
            $data = $data->where('id', $authSession->sub_groups_id);
        }else{
            $data = $data->whereHas('groups', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }

        $data = $data->orderByRaw('CONVERT (title USING tis620) ASC')->get();
        return response()->json($data, 200);


    }

    public function level_groups($id)
    {
        //
        $data_owner = new LevelGroups;
        $data_owner = $data_owner->where('sub_groups_id', $id);
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
        $data_owner = $data_owner->orderBy('order','asc')->get();
        for($i=0; $i<count($data_owner); $i++) {
            $data_owner[$i]->sub_groups = $data_owner[$i]->sub_groups()->first();
            $data_owner[$i]->title = $data_owner[$i]->title." - ".$data_owner[$i]->sub_groups->title;
        }

        $data_access = new Admins;
        if($authSession->super_users){
            $data_access = $data_access->find($authSession->id);
        }
        $data_access = $data_access->admin2level_group()->where('sub_groups_id', $id);
        $data_access = $data_access->orderBy('order','asc');
        $data_access = $data_access->get();
        for($i=0; $i<count($data_access); $i++) {
            $data_access[$i]->sub_groups = $data_access[$i]->sub_groups()->first();
            $data_access[$i]->title = $data_access[$i]->title." - ".$data_access[$i]->sub_groups->title;
        }

        return response()->json(array('owner' => $data_owner, 'access' => $data_access), 200);


    }

    public function domains($id, Request $request) {
        //
        $data = SubGroups::find($id);
        $data->domains = $data->domains()->orderBy('title')->get();

        return response()->json($data, 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = SubGroups::find($input[$i]['id']);
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

        $entity = SubGroups::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = SubGroups::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = SubGroups::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = SubGroups::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = SubGroups::where('order', '>', $request['order'])->min('id');
                    SubGroups::find($next)->decrement('order');
                    $entity->moveBefore(SubGroups::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The sub group has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

}
