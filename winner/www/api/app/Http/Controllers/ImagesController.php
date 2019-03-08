<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\Images;
use App\Models\Certificates;

use Auth;

class ImagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request, _RolesController $oRole)
    {
        //
        $data = new Images;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        $data = $data->with('groups');

        if ($authSession->super_users) {
            $data = $data->where('admins_id', $authSession->id);
        } else if (!$oRole->isSuper()) {
            $data = $data->whereHas('groups', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }

        if ($request->has('type')) {
            $data = $data->where('type', $request['type']);
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
            'picture' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $authSession = Auth::user();

        $data = new Images;
        $data->fill($input);
        $data->admins_id = $authSession->id;
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->created_by = $authSession->id;
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();
        if ($is_success) {
            $message = "The image has been created.";
        } else {
            $message = "Failed to create the image.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
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
        if (!$oRole->haveAccess($id, "images")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Images::find($id);
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
        if (!$oRole->haveAccess($id, "images")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
            'title' => 'required|max:255',
            'icon' => 'required|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Images::find($id);
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();
        if ($is_success) {
            $message = "The image has been updated.";
        } else {
            $message = "Failed to update the image.";
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
        if (!$oRole->haveAccess($id, "images")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Images::find($id);

        $countOfCertificates = Certificates::where('logo_1', $data->picture)
                                         ->orWhere('logo_1_en', $data->picture)
                                         ->orWhere('logo_2', $data->picture)
                                         ->orWhere('logo_2_en', $data->picture)
                                         ->orWhere('logo_3', $data->picture)
                                         ->orWhere('logo_3_en', $data->picture)
                                         ->orWhere('signature_1', $data->picture)
                                         ->orWhere('signature_1_en', $data->picture)
                                         ->orWhere('signature_2', $data->picture)
                                         ->orWhere('signature_2_en', $data->picture)
                                         ->orWhere('signature_3', $data->picture)
                                         ->orWhere('signature_3_en', $data->picture)->get();

        if ($countOfCertificates->count() == 0) {
            $is_success = $data->delete();

            if ($is_success) {
                $message = "The image has been deleted.";
            } else {
                $message = "Failed to delete the image.";
            }
        } else {
            $is_success = false;
            $message = "The image is being used by another certificate.";
        }

        return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }

    public function parent()
    {
        //
        $data = Images::where('parent','0')->get();
        return response()->json($data, 200);
    }

    public function all(Request $request)
    {
        //
        $order_by = $request->input('order_by', 'id');
        $order_direction = $request->input('order_direction', 'ASC');

        $data = new Images;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        $data = $data->with('groups');

        if($authSession->super_users){
            $data = $data->where('admins_id', $authSession->id);
        }else{
            $data = $data->whereHas('groups', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }

        if ($request->has('type')) {
            $data = $data->where('type', $request['type']);
        }

        $data = $data->where('status', 1)->orderBy($order_by,$order_direction)->get();
        for($i=0; $i<count($data); $i++) {
        }
        return response()->json($data, 200);
    }

    public function all_images()
    {
        //
        $data = new Images;
        $data = $data->get();
        for($i=0; $i<count($data); $i++) {
            $data[$i]->groups = $data[$i]->groups()->first();
        }
        return response()->json($data, 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = Images::find($input[$i]['id']);
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

        $entity = Images::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Images::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Images::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Images::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Images::where('order', '>', $request['order'])->min('id');
                    Images::find($next)->decrement('order');
                    $entity->moveBefore(Images::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The images has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

}
