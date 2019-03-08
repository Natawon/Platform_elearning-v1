<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\AdminsMenu;
use App\Models\Admins;

class AdminsMenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $data = AdminsMenu::orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);

        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->first_name;

            // $parent = AdminsMenu::find($data[$i]->parent_id);

            // if ($parent) {
            //     $data[$i]->parent_title = $parent->title;
            // } else {
            //     $data[$i]->parent_title = "";
            // }
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
        $data = new AdminsMenu;
        $input = $request->json()->all();
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();
        if ($is_success) {
            if ($data->id > 1) {
                $entity = AdminsMenu::find($data->id);
                $entity->moveBefore(AdminsMenu::orderBy('order', 'asc')->first());
            }
            $message = $data->id." The admin menu has been created.";
        } else {
            $message = "Failed to create the admin menu.";
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
        $data = AdminsMenu::find($id);
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
        $data = AdminsMenu::find($id);
        $input = $request->json()->all();
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();
        if ($is_success) {
            $message = "The admin menu has been updated.";
        } else {
            $message = "Failed to update the admin menu.";
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
        $data = AdminsMenu::find($id);
        $is_success = $data->delete();
        if ($is_success) {
            $message = "The admin menu has been deleted.";
        } else {
            $message = "Failed to delete the admin menu.";
        }
        return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }

    // public function all(Request $request)
    // {
    //     //
    //     $data = AdminsMenu::where('parent_id', '=', '0')->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);

    //     for($i=0; $i<count($data); $i++) {
    //         $admins = Admins::find($data[$i]->modify_by);
    //         $data[$i]->modify_by = $admins->first_name;

    //         $sub_menu = AdminsMenu::where('status', '=', '1')->where('parent_id', '=', $data[$i]->id)->orderBy('order', 'asc')->get();

    //         for($index = 0; $index < count($sub_menu); $index++) {
    //             $admins = Admins::find($sub_menu[$index]->modify_by);
    //             $sub_menu[$index]->modify_by = $admins->first_name;
    //         }

    //         $data[$i]->sub_menu = $sub_menu;
    //     }

    //     return response()->json($data, 200);
    // }

    public function all(Request $request)
    {
        //
        $datas = AdminsMenu::orderBy("order", "asc")->get();
        $dataResponse = array();

        for($i = 0; $i < count($datas); $i++) {
            $dataSubMenu = AdminsMenu::where('parent_id', '=', $datas[$i]->id)->get();
            if (empty($dataSubMenu->toArray())) {
                $dataResponse[] = $datas[$i];
            }
        }

        return response()->json($dataResponse, 200);
    }

    public function allParent(Request $request)
    {
        //
        $datas = AdminsMenu::where('parent_id', '=', '0')->orderBy("order", "asc")->get();
        $dataResponse = array();

        return response()->json($datas, 200);
    }

    public function menuByGroup($id, Request $request)
    {
        //
        $data = AdminsMenu::where('parent_id', '=', '0')->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);

        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->first_name;

            $sub_menu = AdminsMenu::where('status', '=', '1')->where('parent_id', '=', $data[$i]->id)->orderBy('order', 'asc')->get();

            for($index = 0; $index < count($sub_menu); $index++) {
                $admins = Admins::find($sub_menu[$index]->modify_by);
                $sub_menu[$index]->modify_by = $admins->first_name;
            }

            $data[$i]->sub_menu = $sub_menu;
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

        $entity = AdminsMenu::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = AdminsMenu::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = AdminsMenu::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = AdminsMenu::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = AdminsMenu::where('order', '>', $request['order'])->min('id');
                    AdminsMenu::find($next)->decrement('order');
                    $entity->moveBefore(AdminsMenu::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The admin menu has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

}
