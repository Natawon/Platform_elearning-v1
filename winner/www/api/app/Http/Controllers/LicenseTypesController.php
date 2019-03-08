<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\AdminsGroups;
use App\Models\LicenseTypes;

use Auth;

class LicenseTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $data = LicenseTypes::with('members')->orderBy($request['order_by'], $request['order_direction'])->paginate($request['per_page']);
        for ($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->create_by);
            $data[$i]->create_by = $admins->username;
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
            'title_en' => 'required|max:255',
            'expire_datetime' => 'required|date|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $authSession = Auth::user();
        $data = new LicenseTypes;
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->create_by = $authSession->id;
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();
        if ($is_success) {
            $message = "The license types has been created.";
        } else {
            $message = "Failed to create the license types.";
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
        $data = LicenseTypes::find($id);
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
        $authSession = Auth::user();
        $data = LicenseTypes::find($id);
        $input = $request->json()->all();
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();
        if ($is_success) {
            $message = "The license types has been updated.";
        } else {
            $message = "Failed to update the license types.";
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
        $data = LicenseTypes::find($id);
        $is_success = $data->delete();
        if ($is_success) {
            $message = "The license types has been deleted.";
        } else {
            $message = "Failed to delete the license types.";
        }
        return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }

    public function all()
    {
        //
        $data = LicenseTypes::orderBy('order', 'asc')->get();
        return response()->json($data, 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = LicenseTypes::find($input[$i]['id']);
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

        $entity = LicenseTypes::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = LicenseTypes::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = LicenseTypes::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = LicenseTypes::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = LicenseTypes::where('order', '>', $request['order'])->min('id');
                    LicenseTypes::find($next)->decrement('order');
                    $entity->moveBefore(LicenseTypes::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The license types has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

}
