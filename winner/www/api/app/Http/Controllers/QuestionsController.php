<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;


use App\Models\Admins;
use App\Models\AdminsGroups;
use App\Models\Courses;
use App\Models\Questions;
use App\Models\Answer;

use Input;
use Auth;

class QuestionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $data = new Questions;

        $authSession = Auth::user();
        if($authSession->super_users){
            $data = $data->with('quiz');
            $data = $data->whereHas('quiz',function ($query){
                $query->whereHas('courses', function ($sub_query) {
                    $authSession = Auth::user();
                    $sub_query->where('super_users_id', $authSession->id);
                });
            });
        }else{
            $data = $data->with('quiz');
            $data = $data->whereHas('quiz',function ($query){
                $query->whereHas('courses', function ($sub_query) {
                    $sub_query->whereHas('groups', function ($min_query) {

                        $authSession = Auth::user();
                        $authSessionGroups = $authSession->admins_groups()->first();
                        $authSessionGroups = $authSessionGroups->groups()->get();

                        $min_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));

                    });
                });
            });
        }

        $data = $data->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
            $data[$i]->quiz = $data[$i]->quiz()->first();
            $data[$i]->quiz->courses = Courses::find($data[$i]->quiz['courses_id']);
            $data[$i]->answer = $data[$i]->answer()->orderBy('order','asc')->get();
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
            'quiz_id' => 'required|numeric',
            'questions' => 'required',
            'type' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = new Questions;
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();


        foreach ($input['answer'] as $answer) {
            if (isset($answer['id'])) {
                $data_answer = Answer::find($answer['id']);
                if ($data_answer) {
                    $data_answer->questions_id = $data->id;
                    $data_answer->fill($answer);
                    $data_answer->create_datetime = date('Y-m-d H:i:s');
                    $data_answer->modify_datetime = date('Y-m-d H:i:s');
                    $data_answer->modify_by = $input['admin_id'];
                    $is_success = $data_answer->save();
                } else {
                    $data_answer = new Answer;
                    $data_answer->fill($answer);
                    $data_answer->questions_id = $data->id;
                    $data_answer->create_datetime = date('Y-m-d H:i:s');
                    $data_answer->modify_datetime = date('Y-m-d H:i:s');
                    $data_answer->modify_by = $input['admin_id'];
                    $is_success = $data_answer->save();
                }
            } else {
                $data_answer = new Answer;
                $data_answer->fill($answer);
                $data_answer->questions_id = $data->id;
                $data_answer->create_datetime = date('Y-m-d H:i:s');
                $data_answer->modify_datetime = date('Y-m-d H:i:s');
                $data_answer->modify_by = $input['admin_id'];
                $is_success = $data_answer->save();
            }
        }


        if ($is_success) {
            $message = "The questions has been created.";
        } else {
            $message = "Failed to create the questions.";
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
        $data = Questions::find($id);
        $data->quiz = $data->quiz()->first();
        $data->quiz->courses = Courses::find($data->quiz['courses_id']);
        $data->answer = $data->answer()->orderBy('order','asc')->get();

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
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'quiz_id' => 'required|numeric',
            'questions' => 'required',
            'type' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Questions::find($id);
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        foreach ($input['answer'] as $answer) {
            if (isset($answer['id'])) {
                $data_answer = Answer::find($answer['id']);
                if ($data_answer) {
                    $data_answer->questions_id = $data->id;
                    $data_answer->fill($answer);
                    $data_answer->create_datetime = date('Y-m-d H:i:s');
                    $data_answer->modify_datetime = date('Y-m-d H:i:s');
                    $data_answer->modify_by = $input['admin_id'];
                    $is_success = $data_answer->save();
                } else {
                    $data_answer = new Answer;
                    $data_answer->fill($answer);
                    $data_answer->questions_id = $data->id;
                    $data_answer->create_datetime = date('Y-m-d H:i:s');
                    $data_answer->modify_datetime = date('Y-m-d H:i:s');
                    $data_answer->modify_by = $input['admin_id'];
                    $is_success = $data_answer->save();
                }
            } else {
                $data_answer = new Answer;
                $data_answer->fill($answer);
                $data_answer->questions_id = $data->id;
                $data_answer->create_datetime = date('Y-m-d H:i:s');
                $data_answer->modify_datetime = date('Y-m-d H:i:s');
                $data_answer->modify_by = $input['admin_id'];
                $is_success = $data_answer->save();
            }
        }

        if ($is_success) {
            $message = "The questions has been updated.";
        } else {
            $message = "Failed to update the questions.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }


    public function answer($id, Request $request) {
        //
        $data = Questions::find($id)->answer()->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->no = $i + 1;
            $data[$i]->modify_by = $admins->username;
        }
        return response()->json($data, 200);
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
        $data = Questions::find($id);
        $data->delete();
        $is_success = $data;

        if ($is_success) {
            $message = "The questions has been deleted.";
        } else {
            $message = "Failed to delete the questions.";
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

        $data = Questions::find($id);
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The question has been updated.";
        } else {
            $message = "Failed to update the question.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function all()
    {
        //
        $data = Questions::get();
        return response()->json($data, 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = Questions::find($input[$i]['id']);
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

        $entity = Questions::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Questions::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Questions::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Questions::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Questions::where('order', '>', $request['order'])->min('id');
                    Questions::find($next)->decrement('order');
                    $entity->moveBefore(Questions::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The questions has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

}
