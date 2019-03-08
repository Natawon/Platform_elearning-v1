<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\Members;
use App\Models\Groups;
use App\Models\QuestionnairePacks;

use Input;
use Auth;

class QuestionnairePacksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $data = new QuestionnairePacks;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($authSession->super_users){
            $data = $data->with('groups');
            $data = $data->whereHas('groups',function ($query) use ($authSession){
                $query->where('admins_id', $authSession->id);
            });
        }else{
            $data = $data->with('groups');
            $data = $data->whereHas('groups',function ($query) use ($authSessionGroups){
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }

        $data = $data->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
            $data[$i]->groups = $data[$i]->groups()->first();
            $data[$i]->questionnaires = $data[$i]->questionnaires()->get();
            for($s=0; $s<count($data[$i]->questionnaires); $s++) {
                $data[$i]->questionnaires[$s]->questionnaire_choices = $data[$i]->questionnaires[$s]->questionnaire_choices()->get();
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
    public function store(Request $request, _RolesController $oRole)
    {
        //
        $authSession = Auth::user();

        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
            'title' => 'required|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = new QuestionnairePacks;
        $data->fill($input);
        $data->force_datetime = date('Y-m-d H:i:s');
        $data->force_by = $authSession->id;
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->create_by = $authSession->id;
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();

        if ($is_success) {
            // if (QuestionnairePacks::where('groups_id', $data->groups_id)->where('status', 1)->count() == 0) {
            //     $data->status = 1;
            //     $data->save();
            // }

            $message = "The questionnaire packs has been created.";
        } else {
            $message = "Failed to create the questionnaire_packs.";
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
        if (!$oRole->haveAccess($id, "questionnaire_packs")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = QuestionnairePacks::find($id);
        $data->groups = $data->groups()->first();
        $data->questionnaires = $data->questionnaires()->get();
        for($i=0; $i<count($data->questionnaires); $i++) {
            $data->questionnaires[$i]->questionnaire_choices = $data->questionnaires[$i]->questionnaire_choices()->get();
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
        if (!$oRole->haveAccess($id, "questionnaire_packs")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $authSession = Auth::user();
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
            'title' => 'required|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = QuestionnairePacks::find($id);
        $oldData = $data->replicate();
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();

        if ($is_success) {

            if ($data->status == 1) {
                QuestionnairePacks::where("id", "!=", $data->id)->where("groups_id", $data->groups_id)->where("status", 1)->update(['status' => 0]);
            }

            if ((isset($input['forceChange']) && $input['forceChange'] == true) || ($data->status == 1 && $oldData->status == 0)) {
                Members::where('groups_id', $data->groups_id)->where('filter_courses_status', 2)->update(['filter_courses_status' => 0]);;

                $data->force_datetime = date('Y-m-d H:i:s');
                $data->force_by = $authSession->id;
                $data->save();
            }

            $message = "The questionnaire packs has been updated.";
        } else {
            $message = "Failed to update the questionnaire_packs.";
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
        if (!$oRole->haveAccess($id, "questionnaire_packs")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = QuestionnairePacks::find($id);
        $data->delete();

        $data->questionnaires = $data->questionnaires()->get();
        for($i=0; $i<count($data->questionnaires); $i++) {
            $data->questionnaires[$i]->delete();
            $data->questionnaires[$i]->questionnaire_choices = $data->questionnaires[$i]->questionnaire_choices()->get();
            for($a=0; $a<count($data->questionnaires[$i]->questionnaire_choices); $a++) {
                $data->questionnaires[$i]->questionnaire_choices[$a]->delete();
            }
        }


        $is_success = $data;

        if ($is_success) {

            // if (QuestionnairePacks::where('groups_id', $data->groups_id)->where('status', 1)->count() == 0) {
            //     $dataLatest = QuestionnairePacks::where('groups_id', $data->groups_id)->orderBy('create_datetime', 'desc')->first();

            //     if ($dataLatest) {
            //         $dataLatest->update(['status' => 1]);
            //     }
            // }

            $message = "The questionnaire packs has been deleted.";
        } else {
            $message = "Failed to delete the questionnaire_packs.";
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

        $data = QuestionnairePacks::find($id);
        $oldData = $data->replicate();
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            if ($data->status == 1) {
                QuestionnairePacks::where("id", "!=", $data->id)->where("groups_id", $data->groups_id)->where("status", 1)->update(['status' => 0]);
            }

            if ($data->status == 1 && $oldData->status == 0) {
                Members::where('groups_id', $data->groups_id)->where('filter_courses_status', 2)->update(['filter_courses_status' => 0]);;

                $data->force_datetime = date('Y-m-d H:i:s');
                $data->force_by = Auth::user()->id;
                $data->save();
            }

            $message = "The questionnaire pack has been updated.";
        } else {
            $message = "Failed to update the questionnaire pack.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function questionnaire_packs2groups($id)
    {
        //
        $data = new QuestionnairePacks;
        $data = $data->where('groups_id', $id);
        $data = $data->where('type', 2);
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($authSession->super_users){
            $data = $data->with('groups');
            $data = $data->whereHas('groups',function ($query) use ($authSession){
                $query->where('admins_id', $authSession->id);
            });
        }else{
            $data = $data->with('groups');
            $data = $data->whereHas('groups',function ($query) use ($authSessionGroups){
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
            $groups = Groups::find($data[$i]->groups_id);
            $data[$i]->title = $data[$i]->title." (".$groups->title.")";
        }
        return response()->json($data, 200);
    }

    public function all()
    {
        //
        $data = new QuestionnairePacks;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($authSession->super_users){
            $data = $data->with('groups');
            $data = $data->whereHas('groups',function ($query) use ($authSession){
                $query->where('admins_id', $authSession->id);
            });
        }else{
            $data = $data->with('groups');
            $data = $data->whereHas('groups',function ($query) use ($authSessionGroups){
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }

        $data = $data->orderBy('order', 'asc')->get();
        for($i=0; $i<count($data); $i++) {
                $groups = Groups::find($data[$i]->groups_id);
                $data[$i]->title = $data[$i]->title." (".$groups->title.")";
        }
        return response()->json($data, 200);
    }

    public function questionnaires($id, Request $request) {
        //
        $data = QuestionnairePacks::find($id)->questionnaires()->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->no = $i + 1;
            $data[$i]->modify_by = $admins->username;
            $data[$i]->questionnaire_packs = $data[$i]->questionnaire_packs()->first();
            $data[$i]->questionnaire_packs->groups = Groups::find($data[$i]->questionnaire_packs['groups_id']);
            $data[$i]->questionnaire_choices = $data[$i]->questionnaire_choices()->get();
        }
        return response()->json($data, 200);
    }

    public function questionnaire_packs2questionnaires_all($id) {
        //
        $data = QuestionnairePacks::find($id)->questionnaires()->get();
        return response()->json($data, 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = QuestionnairePacks::find($input[$i]['id']);
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

        $entity = QuestionnairePacks::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = QuestionnairePacks::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = QuestionnairePacks::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = QuestionnairePacks::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = QuestionnairePacks::where('order', '>', $request['order'])->min('id');
                    QuestionnairePacks::find($next)->decrement('order');
                    $entity->moveBefore(QuestionnairePacks::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The questionnaire packs has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

}
