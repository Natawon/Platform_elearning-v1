<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\AdminsGroups;
use App\Models\Institutions;

use Auth;

class InstitutionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $data = new Institutions;
        $data = $data->with('groups');
        $data = $data->whereHas('groups', function($query) {

            $authSession = Auth::user();
            $authAdminsGroups = AdminsGroups::find($authSession->admins_groups_id);
            $authGroups = $authAdminsGroups->admin2group()->get();

            for($a=0; $a<count($authGroups); $a++) {
                if($a==0){
                    $query->where('groups_id', $authGroups[$a]->id);
                }else{
                    $query->orWhere('groups_id', $authGroups[$a]->id);
                }
            }

        });
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
    public function store(Request $request)
    {
        //
        $data = new Institutions;
        $input = $request->json()->all();
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();
        if ($is_success) {
            $message = "The instructors has been created.";
        } else {
            $message = "Failed to create the instructors.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
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
        $data = Institutions::find($id);
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
        $data = Institutions::find($id);
        $input = $request->json()->all();
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
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
    public function destroy($id)
    {
        //
        $data = Institutions::find($id);
        $is_success = $data->delete();
        if ($is_success) {
            $message = "The instructors has been deleted.";
        } else {
            $message = "Failed to delete the instructors.";
        }
        return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }

    public function all()
    {
        //
        $data = new Institutions;
        $data = $data->with('groups');
        $data = $data->whereHas('groups', function($query) {

            $authSession = Auth::user();
            $authAdminsGroups = AdminsGroups::find($authSession->admins_groups_id);
            $authGroups = $authAdminsGroups->admin2group()->get();
            foreach($authGroups as $authGroup){
                $query->where('groups_id', $authGroup->id);
            }

        });
        $data = $data->orderBy('order', 'asc')->get();
        return response()->json($data, 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = Institutions::find($input[$i]['id']);
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

        $entity = Institutions::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Institutions::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Institutions::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Institutions::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Institutions::where('order', '>', $request['order'])->min('id');
                    Institutions::find($next)->decrement('order');
                    $entity->moveBefore(Institutions::find($next));
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
