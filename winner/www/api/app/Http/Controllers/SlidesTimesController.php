<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use GuzzleHttp\Client as httpClient;

use App\Models\Slides;
use App\Models\SlidesTimes;
use App\Models\Admins;
use App\Models\Courses;
use App\Models\Topics;
use Input;

class SlidesTimesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $data = SlidesTimes::orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->no = $i + 1;
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
        $data = new SlidesTimes;
        $input = $request->json()->all();
        $dataTopics = Topics::find($input['topics_id']);
        $data->fill($input);
        $time = date($data->time);

        if ($input['state'] == 'live') {
            $time = date('H:i:s',strtotime($time . "-".($dataTopics->slide_delay)." seconds"));
        }

        $data->time = $time;
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if ($is_success) {
            $dataSlidesCurrent = Slides::where("courses_id", '=', $input['courses_id'])->where('id', '=', $data->slides_id)->first();
            $dataSlidesBefore = Slides::where("courses_id", '=', $input['courses_id'])->where('order', '<', $dataSlidesCurrent->order)->first();
            $dataSlidesFirst= Slides::where("courses_id", '=', $input['courses_id'])->orderBy('order', 'asc')->first();

            if ($dataSlidesBefore !== null) {
                if ($dataSlidesBefore->id === $dataSlidesFirst->id) {
                    $dataSlidesTimes = SlidesTimes::where('slides_id', '=', $dataSlidesFirst->id)->get();
                    if (!$dataSlidesTimes->count()) {
                        $dataFirst = new SlidesTimes;
                        $input = $request->json()->all();
                        $dataCourses = Courses::find($input['courses_id']);
                        $data->fill($input);
                        $dataFirst->slides_id = $dataSlidesFirst->id;
                        $dataFirst->courses_id = $dataCourses->id;
                        $time = "00:00:00";
                        $dataFirst->time = $time;
                        $dataFirst->create_datetime = date('Y-m-d H:i:s');
                        $dataFirst->modify_datetime = date('Y-m-d H:i:s');
                        $dataFirst->modify_by = $input['admin_id'];
                        $is_success = $dataFirst->save();
                    }
                }
            }

            $data->time_live_control = date('H:i:s', strtotime('+30 seconds',strtotime($data->time)));

            $message = "The slides has been created.";
        } else {
            $message = "Failed to create the slides.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'time' => $data->time, 'time_live_control' => $data->time_live_control), 200);
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
        $data = SlidesTimes::find($id);
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
    public function update($id, Request $request)
    {
        //
        $data = SlidesTimes::find($id);
        $input = $request->json()->all();
        $data->fill($input);

        if ($data->time == "") {
            $data->time = null;
        }

        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if ($is_success) {
            $message = "The slides has been updated.";
        } else {
            $message = "Failed to update the slides.";
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
        $data = SlidesTimes::find($id);
        $data->delete();
        $is_success = $data;

        if ($is_success) {
            $message = "The slides has been deleted.";
        } else {
            $message = "Failed to delete the slides.";
        }

        return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }

    public function all()
    {
        //
        $data = SlidesTimes::get();
        return response()->json($data, 200);
    }


    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = SlidesTimes::find($input[$i]['id']);
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

        $entity = SlidesTimes::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = SlidesTimes::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = SlidesTimes::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = SlidesTimes::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = SlidesTimes::where('order', '>', $request['order'])->min('id');
                    SlidesTimes::find($next)->decrement('order');
                    $entity->moveBefore(SlidesTimes::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The slides has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

    public function testRedirect(Request $request)
    {        
        return redirect('https://elearning.set.or.th/');
    }

    public function testRedirect2(Request $request)
    {
        header('Location: https://elearning.set.or.th/', true, 302);
        exit();
    }

}
