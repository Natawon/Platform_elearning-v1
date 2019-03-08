<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Methods;
use App\Models\Admins;

use Auth;

class MethodsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $data = new Methods;

        if ($request->has('search')) {
            $data = $data->where(function ($query) use ($request) {
                $query->where('title', 'like', '%'.$request['search'].'%');
            });
        }

        if ($request->has('type')) {
            $data = $data->where('type', $request['type']);
        }

        $data = $data->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);

        for($i=0; $i<count($data); $i++) {
            switch ($data[$i]->type) {
                case 1: $data[$i]->type_title  = "บัตรเครดิต/เดบิต"; break;
                case 2: $data[$i]->type_title  = "โอนเงิน"; break;
                default: $data[$i]->type_title = ""; break;
            }

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
            'title' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = new Methods;
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();
        if ($is_success) {
            $message = "The methods has been created.";
        } else {
            $message = "Failed to create the methods.";
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
        $data = Methods::find($id);

        switch ($data->type) {
            case 1: $data->type_title  = "บัตรเครดิต/เดบิต"; break;
            case 2: $data->type_title  = "โอนเงิน"; break;
            default: $data->type_title = ""; break;
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
    public function update($id, Request $request)
    {
        //
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'title' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Methods::find($id);
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();
        if ($is_success) {
            $message = "The methods has been updated.";
        } else {
            $message = "Failed to update the methods.";
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
        $data = Methods::find($id);
        $is_success = $data->delete();
        if ($is_success) {
            $message = "The methods has been deleted.";
        } else {
            $message = "Failed to delete the methods.";
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

        $data = Methods::find($id);
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The method has been updated.";
        } else {
            $message = "Failed to update the method.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = Methods::find($input[$i]['id']);
            $data[$i]->fill($input[$i]);
            $data[$i]->save();
        }
    }

    public function all()
    {
        //
        $data = Methods::where('status', '=', 1)->orderBy('order', 'asc')->get();

        for($i=0; $i<count($data); $i++) {
            switch ($data[$i]->type) {
                case 1: $data[$i]->title_with_type  = $data[$i]->title." (บัตรเครดิต/เดบิต)"; break;
                case 2: $data[$i]->title_with_type  = $data[$i]->title." (โอนเงิน)"; break;
                default: $data[$i]->title_with_type = $data[$i]->title; break;
            }
        }

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

        $entity = Methods::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Methods::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Methods::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Methods::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Methods::where('order', '>', $request['order'])->min('id');
                    Methods::find($next)->decrement('order');
                    $entity->moveBefore(Methods::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The methods has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

}
