<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Configuration;
use App\Models\Admins;

use Validator;
use Auth;

class ConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $id = '1';
        $data = Configuration::find($id);
        $admins = Admins::find($data->modify_by);
        $data->modify_by = $admins->username;
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
            'logo' => 'required|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Configuration::find($id);
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();
        if ($is_success) {
            $message = "The configuration has been updated.";
        } else {
            $message = "Failed to update the configuration.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }


}
