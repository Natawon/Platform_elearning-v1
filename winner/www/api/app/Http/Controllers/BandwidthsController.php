<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Bandwidths;
use App\Models\Admins;

class BandwidthsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $data = Bandwidths::orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);

        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->first_name;
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
        $data = new Bandwidths;
        $input = $request->json()->all();
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();
        if ($is_success) {
            if ($data->id > 1) {
                $entity = Bandwidths::find($data->id);
                $entity->moveBefore(Bandwidths::orderBy('order', 'asc')->first());
            }
            $message = "The bandwidth has been created.";
        } else {
            $message = "Failed to create the bandwidth.";
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
        $data = Bandwidths::find($id);
        $admin = Admins::find($data->modify_by);
        $data->modify_by = $admin->first_name;
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
        $data = Bandwidths::find($id);
        $input = $request->json()->all();
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();
        if ($is_success) {
            $message = "The bandwidth has been updated.";
        } else {
            $message = "Failed to update the bandwidth.";
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
        $data = Bandwidths::find($id);
        $is_success = $data->delete();
        Bandwidths::deleting(function ($model) {
            $model->next()->decrement('order');
        });
        if ($is_success) {
            $message = "The bandwidth has been deleted.";
        } else {
            $message = "Failed to delete the bandwidth.";
        }
        return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }

    public function createCurrent(Request $request)
    {
        //
        $data = new Bandwidths;
        $input = $request->json()->all();
        $data->fill($input);
        $data->datetime = date('Y-m-d H:i:s');
        $is_success = $data->save();
        if ($is_success) {
            $message = "The bandwidth has been created.";
        } else {
            $message = "Failed to create the bandwidth.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function currentBandwidth(Request $request)
    {
        //
        $data = new Bandwidths();

        if ($request->has('server_name')) {
            $data = $data->where('server_name', $request->input('server_name'));
        }

        $data = $data->latest('id')->first();
        return response()->json($data, 200);
    }

}
