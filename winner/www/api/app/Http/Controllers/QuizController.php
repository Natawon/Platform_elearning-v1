<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\Courses;
use App\Models\Quiz;

use Input;
use Auth;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $data = new Quiz;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($authSession->super_users){
            $data = $data->with('courses');
            $data = $data->whereHas('courses',function ($query) use ($authSession){
                $query->where('admins_id', $authSession->id);
            });
        }else{
            $data = $data->with('courses');
            $data = $data->whereHas('courses',function ($query) use ($authSessionGroups){
                $query->whereHas('groups', function ($sub_query) use ($authSessionGroups){
                    $a=0;
                    foreach($authSessionGroups as $authSessionGroups){
                        $a++;
                        if($a == 1){
                            $sub_query->where('groups_id', $authSessionGroups->id);
                        }else{
                            $sub_query->orWhere('groups_id', $authSessionGroups->id);
                        }
                    }
                });
            });
        }

        $data = $data->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
            $data[$i]->courses = $data[$i]->courses()->first();
            $data[$i]->questions = $data[$i]->questions()->get();
            for($s=0; $s<count($data[$i]->questions); $s++) {
                $data[$i]->questions[$s]->answer = $data[$i]->questions[$s]->answer()->get();
            }
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
            'courses_id' => 'required|numeric',
            'title' => 'required|max:255',
            'time' => 'required|numeric',
            'type' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = new Quiz;
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if ($is_success) {
            $message = "The quiz has been created.";
        } else {
            $message = "Failed to create the quiz.";
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
        if (!$oRole->haveAccess($id, "quiz")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Quiz::find($id);
        $data->courses = $data->courses()->first();
        $data->questions = $data->questions()->get();
        for($i=0; $i<count($data->questions); $i++) {
            $data->questions[$i]->answer = $data->questions[$i]->answer()->get();
        }

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
        if (!$oRole->haveAccess($id, "quiz")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'courses_id' => 'required|numeric',
            'title' => 'required|max:255',
            'time' => 'required|numeric',
            'type' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Quiz::find($id);
        $data->fill($input);
        $data->passing_score = $input['passing_score']?: null;
        $data->take_new_exam = $input['take_new_exam']?: null;
        $data->time = $input['time']?: null;
        $data->limit_questions = $input['limit_questions']?: null;
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if ($is_success) {
            $message = "The quiz has been updated.";
        } else {
            $message = "Failed to update the quiz.";
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
        if (!$oRole->haveAccess($id, "quiz")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Quiz::find($id);
        $data->delete();

        $data->questions = $data->questions()->get();
        for($i=0; $i<count($data->questions); $i++) {
            $data->questions[$i]->delete();
            $data->questions[$i]->answer = $data->questions[$i]->answer()->get();
            for($a=0; $a<count($data->questions[$i]->answer); $a++) {
                $data->questions[$i]->answer[$a]->delete();
            }
        }


        $is_success = $data;

        if ($is_success) {
            $message = "The quiz has been deleted.";
        } else {
            $message = "Failed to delete the quiz.";
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

        $data = Quiz::find($id);
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The quiz has been updated.";
        } else {
            $message = "Failed to update the quiz.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function quiz2courses($id)
    {
        //
        $data = new Quiz;
        $data = $data->where('courses_id', $id);
        $data = $data->where('type', 2);
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($authSession->super_users){
            $data = $data->with('courses');
            $data = $data->whereHas('courses',function ($query) use ($authSession){
                $query->where('admins_id', $authSession->id);
            });
        }else{
            $data = $data->with('courses');
            $data = $data->whereHas('courses',function ($query) use ($authSessionGroups){
                $query->whereHas('groups', function ($sub_query) use ($authSessionGroups){
                    $a=0;
                    foreach($authSessionGroups as $authSessionGroups){
                        $a++;
                        if($a == 1){
                            $sub_query->where('groups_id', $authSessionGroups->id);
                        }else{
                            $sub_query->orWhere('groups_id', $authSessionGroups->id);
                        }
                    }
                });
            });
        }

        $data = $data->orderBy('order', 'asc')->get();
        for($i=0; $i<count($data); $i++) {
            $courses = Courses::find($data[$i]->courses_id);
            $data[$i]->title = $data[$i]->title." (".$courses->title.")";
        }
        return response()->json($data, 200);
    }

    public function all()
    {
        //
        $data = new Quiz;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($authSession->super_users){
            $data = $data->with('courses');
            $data = $data->whereHas('courses',function ($query) use ($authSession){
                $query->where('admins_id', $authSession->id);
            });
        }else{
            $data = $data->with('courses');
            $data = $data->whereHas('courses',function ($query) use ($authSessionGroups){
                $query->whereHas('groups', function ($sub_query) use ($authSessionGroups){
                    $a=0;
                    foreach($authSessionGroups as $authSessionGroups){
                        $a++;
                        if($a == 1){
                            $sub_query->where('groups_id', $authSessionGroups->id);
                        }else{
                            $sub_query->orWhere('groups_id', $authSessionGroups->id);
                        }
                    }
                });
            });
        }

        $data = $data->orderBy('order', 'asc')->get();
        for($i=0; $i<count($data); $i++) {
                $courses = Courses::find($data[$i]->courses_id);
                $data[$i]->title = $data[$i]->title." (".$courses->title.")";
        }
        return response()->json($data, 200);
    }

    public function questions($id, Request $request) {
        //
        $data = Quiz::find($id)->questions()->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->no = $i + 1;
            $data[$i]->modify_by = $admins->username;
            $data[$i]->quiz = $data[$i]->quiz()->first();
            $data[$i]->quiz->courses = Courses::find($data[$i]->quiz['courses_id']);
            $data[$i]->answer = $data[$i]->answer()->get();
        }
        return response()->json($data, 200);
    }

    public function quiz2questions_all($id) {
        //
        $data = Quiz::find($id)->questions()->get();
        return response()->json($data, 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = Quiz::find($input[$i]['id']);
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

        $entity = Quiz::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Quiz::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Quiz::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Quiz::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Quiz::where('order', '>', $request['order'])->min('id');
                    Quiz::find($next)->decrement('order');
                    $entity->moveBefore(Quiz::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The quiz has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

}
