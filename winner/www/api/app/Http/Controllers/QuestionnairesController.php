<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;


use App\Models\Admins;
use App\Models\AdminsGroups;
use App\Models\Members;
use App\Models\Groups;
use App\Models\Questionnaires;
use App\Models\QuestionnaireChoices;
use App\Models\QuestionnairePacks;

use Input;
use Auth;

class QuestionnairesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $data = new Questionnaires;

        $authSession = Auth::user();
        if($authSession->super_users){
            $data = $data->with('questionnaire_packs');
            $data = $data->whereHas('questionnaire_packs',function ($query){
                $query->whereHas('groups', function ($sub_query) {
                    $authSession = Auth::user();
                    $sub_query->where('super_users_id', $authSession->id);
                });
            });
        }else{
            $data = $data->with('questionnaire_packs');
            $data = $data->whereHas('questionnaire_packs',function ($query){
                $query->whereHas('groups', function ($sub_query) {
                    $authSession = Auth::user();
                    $authSessionGroups = $authSession->admins_groups()->first();
                    $authSessionGroups = $authSessionGroups->groups()->get();

                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            });
        }

        $data = $data->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
            $data[$i]->questionnaire_packs = $data[$i]->questionnaire_packs()->first();
            $data[$i]->questionnaire_packs->groups = Groups::find($data[$i]->questionnaire_packs['groups_id']);
            $data[$i]->questionnaire_choices = $data[$i]->questionnaire_choices()->orderBy('order','asc')->get();
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
        $authSession = Auth::user();
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'questionnaire_packs_id' => 'required|numeric',
            'question' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = new Questionnaires;
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->create_by = $authSession->id;
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();


        foreach ($input['questionnaire_choices'] as $questionnaire_choices) {
            if (isset($questionnaire_choices['id'])) {
                $data_questionnaire_choices = QuestionnaireChoices::find($questionnaire_choices['id']);
                if ($data_questionnaire_choices) {
                    $data_questionnaire_choices->questionnaires_id = $data->id;
                    $data_questionnaire_choices->fill($questionnaire_choices);
                    $data_questionnaire_choices->create_datetime = date('Y-m-d H:i:s');
                    $data_questionnaire_choices->create_by = $authSession->id;
                    $data_questionnaire_choices->modify_datetime = date('Y-m-d H:i:s');
                    $data_questionnaire_choices->modify_by = $authSession->id;
                    $is_success = $data_questionnaire_choices->save();
                } else {
                    $data_questionnaire_choices = new QuestionnaireChoices;
                    $data_questionnaire_choices->fill($questionnaire_choices);
                    $data_questionnaire_choices->questionnaires_id = $data->id;
                    $data_questionnaire_choices->create_datetime = date('Y-m-d H:i:s');
                    $data_questionnaire_choices->create_by = $authSession->id;
                    $data_questionnaire_choices->modify_datetime = date('Y-m-d H:i:s');
                    $data_questionnaire_choices->modify_by = $authSession->id;
                    $is_success = $data_questionnaire_choices->save();
                }
            } else {
                $data_questionnaire_choices = new QuestionnaireChoices;
                $data_questionnaire_choices->fill($questionnaire_choices);
                $data_questionnaire_choices->questionnaires_id = $data->id;
                $data_questionnaire_choices->create_datetime = date('Y-m-d H:i:s');
                $data_questionnaire_choices->create_by = $authSession->id;
                $data_questionnaire_choices->modify_datetime = date('Y-m-d H:i:s');
                $data_questionnaire_choices->modify_by = $authSession->id;
                $is_success = $data_questionnaire_choices->save();
            }
        }


        if ($is_success) {
            if (isset($input['forceChange']) && $input['forceChange'] == true) {
                Members::where('groups_id', $data->questionnaire_packs->groups_id)->where('filter_courses_status', 2)->update(['filter_courses_status' => 0]);;

                $dataQuestionnairePacks = $data->questionnaire_packs()->first();
                $dataQuestionnairePacks->force_datetime = date('Y-m-d H:i:s');
                $dataQuestionnairePacks->force_by = $authSession->id;
                $dataQuestionnairePacks->status = 1;
                $dataQuestionnairePacks->save();

                QuestionnairePacks::where("id", "!=", $dataQuestionnairePacks->id)->where("groups_id", $dataQuestionnairePacks->groups_id)->where("status", 1)->update(['status' => 0]);
            }

            $message = "The questionnaires has been created.";
        } else {
            $message = "Failed to create the questionnaires.";
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
        $data = Questionnaires::find($id);
        $data->questionnaire_packs = $data->questionnaire_packs()->first();
        $data->questionnaire_packs->groups = Groups::find($data->questionnaire_packs['groups_id']);
        $data->questionnaire_choices = $data->questionnaire_choices()->orderBy('order','asc')->get();

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
        $authSession = Auth::user();
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'questionnaire_packs_id' => 'required|numeric',
            'question' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Questionnaires::find($id);
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();

        foreach ($input['questionnaire_choices'] as $questionnaire_choices) {
            if (isset($questionnaire_choices['id'])) {
                $data_questionnaire_choices = QuestionnaireChoices::find($questionnaire_choices['id']);
                if ($data_questionnaire_choices) {
                    $data_questionnaire_choices->questionnaires_id = $data->id;
                    $data_questionnaire_choices->fill($questionnaire_choices);
                    $data_questionnaire_choices->create_datetime = date('Y-m-d H:i:s');
                    $data_questionnaire_choices->modify_datetime = date('Y-m-d H:i:s');
                    $data_questionnaire_choices->modify_by = $authSession->id;
                    $is_success = $data_questionnaire_choices->save();
                } else {
                    $data_questionnaire_choices = new QuestionnaireChoices;
                    $data_questionnaire_choices->fill($questionnaire_choices);
                    $data_questionnaire_choices->questionnaires_id = $data->id;
                    $data_questionnaire_choices->create_datetime = date('Y-m-d H:i:s');
                    $data_questionnaire_choices->modify_datetime = date('Y-m-d H:i:s');
                    $data_questionnaire_choices->modify_by = $authSession->id;
                    $is_success = $data_questionnaire_choices->save();
                }
            } else {
                $data_questionnaire_choices = new QuestionnaireChoices;
                $data_questionnaire_choices->fill($questionnaire_choices);
                $data_questionnaire_choices->questionnaires_id = $data->id;
                $data_questionnaire_choices->create_datetime = date('Y-m-d H:i:s');
                $data_questionnaire_choices->modify_datetime = date('Y-m-d H:i:s');
                $data_questionnaire_choices->modify_by = $authSession->id;
                $is_success = $data_questionnaire_choices->save();
            }
        }

        if ($is_success) {
            if (isset($input['forceChange']) && $input['forceChange'] == true) {
                Members::where('groups_id', $data->questionnaire_packs->groups_id)->where('filter_courses_status', 2)->update(['filter_courses_status' => 0]);;

                $dataQuestionnairePacks = $data->questionnaire_packs()->first();
                $dataQuestionnairePacks->force_datetime = date('Y-m-d H:i:s');
                $dataQuestionnairePacks->force_by = $authSession->id;
                $dataQuestionnairePacks->status = 1;
                $dataQuestionnairePacks->save();

                QuestionnairePacks::where("id", "!=", $dataQuestionnairePacks->id)->where("groups_id", $dataQuestionnairePacks->groups_id)->where("status", 1)->update(['status' => 0]);
            }

            $message = "The questionnaires has been updated.";
        } else {
            $message = "Failed to update the questionnaires.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }


    public function questionnaire_choices($id, Request $request) {
        //
        $data = Questionnaires::find($id)->questionnaire_choices()->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
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
        $data = Questionnaires::find($id);
        $data->delete();
        $is_success = $data;

        if ($is_success) {
            $message = "The questionnaires has been deleted.";
        } else {
            $message = "Failed to delete the questionnaires.";
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

        $data = Questionnaires::find($id);
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The questionnaire has been updated.";
        } else {
            $message = "Failed to update the questionnaire.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function all()
    {
        //
        $data = Questionnaires::get();
        return response()->json($data, 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = Questionnaires::find($input[$i]['id']);
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

        $entity = Questionnaires::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Questionnaires::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Questionnaires::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Questionnaires::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Questionnaires::where('order', '>', $request['order'])->min('id');
                    Questionnaires::find($next)->decrement('order');
                    $entity->moveBefore(Questionnaires::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The questionnaires has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

}
