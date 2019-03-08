<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\Discussions;
use App\Models\Topics;

use Input;
use Auth;
use Mail;

class DiscussionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $per_page = $request->input('per_page', 10);
        $order_by = $request->input('order_by', 'order');
        $order_direction = $request->input('order_direction', 'ASC');

        $data = new Discussions;

        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        // $data = $data->with(['courses', 'members']);
        $data = $data->with(['courses', 'instructors', 'members' => function($query) {
            $query->select('id', 'first_name', 'last_name');
        }]);

        if ($request->has('is_unread')) {
            if ($request['is_unread'] == 1) {
                $data = $data->where('is_read', 0);
            }
        }

        if($authSession->super_users){
            $data = $data->whereHas('courses',function ($query) use ($authSession){
                $query->where('admins_id', $authSession->id);
            });
        }else{
            $data = $data->whereHas('courses',function ($query) use ($authSessionGroups){
                $query->whereHas('groups', function ($sub_query) use ($authSessionGroups) {
                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            });
        }

        $data = $data->whereNull('parent_id')->orderBy($order_by, $order_direction)->paginate($per_page);
        for($i=0; $i<count($data); $i++) {

            $data[$i]->replies = Discussions::with('instructors')->where('parent_id', $data[$i]->id)->get();
            $data[$i]->count_reply = count($data[$i]->replies);
            $data[$i]->latest_reply_datetime = $data[$i]->replies->max('create_datetime');
            for($j=0; $j<count($data[$i]->replies); $j++) {
                $data[$i]->replies[$j]->replies = Discussions::with('instructors')->where('parent_id', $data[$i]->replies[$j]->id)->get();
                $data[$i]->count_reply += count($data[$i]->replies[$j]->replies);

                if (is_null($data[$i]->latest_reply_datetime) || $data[$i]->latest_reply_datetime < $data[$i]->replies[$j]->replies->max('create_datetime')) {
                    $data[$i]->latest_reply_datetime = $data[$i]->replies[$j]->replies->max('create_datetime');
                }
            }

            if ($data[$i]->reject_by) {
                $admins = Admins::find($data[$i]->reject_by);
                $data[$i]->reject_by = $admins->username;
            }

            if ($data[$i]->create_by) {
                $admins = Admins::find($data[$i]->create_by);
                $data[$i]->create_by = $admins->username;
            }

            if ($data[$i]->modify_by) {
                $admins = Admins::find($data[$i]->modify_by);
                $data[$i]->modify_by = $admins->username;
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
        $authSession = Auth::user();

        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
            'courses_id' => 'required|numeric',
            'topic' => 'required|max:255',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = new Discussions;
        $data->fill($input);
        $data->type = 1;
        $data->is_read = 1;
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->create_by = $authSession->id;
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The discussions has been created.";
        } else {
            $message = "Failed to create the discussions.";
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
        $data = Discussions::with(['courses', 'instructors', 'members' => function($query) {
            $query->select('id', 'first_name', 'last_name');
        }])->find($id);

        if ($data->parent_id) {
            $dataParent = Discussions::with(['instructors', 'members' => function($query) {
                $query->select('id', 'first_name', 'last_name');
            }])->find($data->parent_id);
            if ($dataParent->parent_id) {
                $dataParent2 = Discussions::with(['instructors', 'members' => function($query) {
                    $query->select('id', 'first_name', 'last_name');
                }])->find($dataParent->parent_id);
                $data->sub_discussion_reply = $dataParent;
                $data->discussion_reply = $dataParent2;
            } else {
                $data->sub_discussion_reply = null;
                $data->discussion_reply = $dataParent;
            }
        } else {
            $data->sub_discussion_reply = null;
            $data->discussion_reply = null;
        }

        if ($data->reject_by) {
            $admins = Admins::find($data->reject_by);
            $data->reject_by = $admins->username;
        }

        if ($data->create_by) {
            $admins = Admins::find($data->create_by);
            $data->create_by = $admins->username;
        }

        if ($data->modify_by) {
            $admins = Admins::find($data->modify_by);
            $data->modify_by = $admins->username;
        }

        $data->replies = Discussions::with('instructors')->where('parent_id', $data->id)->get();

        for($i=0; $i<count($data->replies); $i++) {
            $data->replies[$i]->members = $data->replies[$i]->members()->select('first_name', 'last_name')->first();
            $data->replies[$i]->parent = Discussions::with('instructors')->find($data->replies[$i]->parent_id);

            if ($data->replies[$i]->reject_by) {
                $admins = Admins::find($data->replies[$i]->reject_by);
                $data->replies[$i]->reject_by = $admins->username;
            }

            if ($data->replies[$i]->create_by) {
                $admins = Admins::find($data->replies[$i]->create_by);
                $data->replies[$i]->create_by = $admins->username;
            }

            if ($data->replies[$i]->modify_by) {
                $admins = Admins::find($data->replies[$i]->modify_by);
                $data->replies[$i]->modify_by = $admins->username;
            }

            $data->replies[$i]->replies = Discussions::with('instructors')->where('parent_id', $data->replies[$i]->id)->get();
            for($j=0; $j<count($data->replies[$i]->replies); $j++) {
                $data->replies[$i]->replies[$j]->members = $data->replies[$i]->replies[$j]->members()->select('first_name', 'last_name')->first();
                $data->replies[$i]->replies[$j]->parent = Discussions::with('instructors')->find($data->replies[$i]->replies[$j]->parent_id);

                if ($data->replies[$i]->replies[$j]->reject_by) {
                    $admins = Admins::find($data->replies[$i]->replies[$j]->reject_by);
                    $data->replies[$i]->replies[$j]->reject_by = $admins->username;
                }

                if ($data->replies[$i]->replies[$j]->create_by) {
                    $admins = Admins::find($data->replies[$i]->replies[$j]->create_by);
                    $data->replies[$i]->replies[$j]->create_by = $admins->username;
                }

                if ($data->replies[$i]->replies[$j]->modify_by) {
                    $admins = Admins::find($data->replies[$i]->replies[$j]->modify_by);
                    $data->replies[$i]->replies[$j]->modify_by = $admins->username;
                }
            }
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
    public function update($id, Request $request)
    {
        //
        $authSession = Auth::user();
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
            'courses_id' => 'required|numeric',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Discussions::find($id);
        $data->fill($input);

        if ($input['is_reject'] == 1) {
            $data->reject_datetime = date('Y-m-d H:i:s');
            $data->reject_by = Auth::user()->id;
        } else {
            $data->reject_remark = null;
            $data->reject_datetime = null;
            $data->reject_by = null;
        }

        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The discussions has been updated.";
        } else {
            $message = "Failed to update the discussions.";
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
        $data = Discussions::find($id);
        $data->delete();
        $is_success = $data;

        if ($is_success) {
            $message = "The discussions has been deleted.";
        } else {
            $message = "Failed to delete the discussions.";
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

        $data = Discussions::find($id);
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The discussion has been updated.";
        } else {
            $message = "Failed to update the discussion.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function all()
    {
        //
        $data = Discussions::get();
        return response()->json($data, 200);
    }

    public function allExcept($courses_id, $except_id)
    {
        //
        $data = Discussions::where('courses_id', $courses_id)->where('id', '!=', $except_id)->orderBy('order', 'asc')->get();
        for($i=0; $i<count($data); $i++) {
            // Do something...
        }

        return response()->json($data, 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = Discussions::find($input[$i]['id']);
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

        $entity = Discussions::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Discussions::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Discussions::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Discussions::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Discussions::where('order', '>', $request['order'])->min('id');
                    Discussions::find($next)->decrement('order');
                    $entity->moveBefore(Discussions::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The discussions has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

    public function send(Request $request)
    {
        //
        $authSession = Auth::user();

        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
            'courses_id' => 'required|numeric',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = new Discussions;
        $data->fill($input);
        $data->type = 1;
        $data->is_read = 1;
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->create_by = $authSession->id;
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The discussions has been sent.";
        } else {
            $message = "Failed to send the discussions.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'createdId' => $data->id), 200);
    }

    public function updateIsPublic($id, Request $request)
    {
        //
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'is_public' => 'required|max:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Discussions::find($id);
        $data->is_public = $input['is_public'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The discussion has been updated.";
        } else {
            $message = "Failed to update the discussion.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function updateIsSentInstructor($id, Request $request)
    {
        //
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'is_sent_instructor' => 'required|max:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Discussions::find($id);
        $data->is_sent_instructor = $input['is_sent_instructor'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The discussion has been updated.";

            $dataTopic = Topics::find($data->topics_id);

            if ($dataTopic->state == 'vod' && $data->is_sent_instructor == 1) {
                $dataCourse = $data->courses;

                $toEmails = array_values(array_unique(array_pluck($dataCourse->instructors, 'email')));

                $dataMail = array(
                    'dataDiscussion' => $data,
                    'dataCourse'     => $dataCourse,
                    'dataTopic'      => $dataTopic,
                    'dataMember'     => $data->members,
                    'url'            => str_replace("{GROUP_KEY}", $data->groups->key, config('constants.URL_GROUP.HOME'))."/courses/".$data->courses_id."/discussions/instructors"
                );

                Mail::send('create-discussion-instructors-mail', $dataMail, function($mail) use ($dataMail, $toEmails) {
                    $mail->to($toEmails);
                    // $mail->to("nawee.ku.dootvmedia@gmail.com");
                    $mail->subject('แจ้งการตั้งหัวเรื่องสนทนา หลักสูตร '.$dataMail['dataCourse']['code']." - ".$dataMail['dataCourse']['title']." โดย คุณ".$dataMail['dataDiscussion']['members']['first_name']." ".$dataMail['dataDiscussion']['members']['last_name']);
                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                    // $mail->bcc(config('constants.EMAIL.BCC'), config('constants.EMAIL.BCC'));
                    // $mail->bcc('nawee.ku.dootvmedia@gmail.com', 'Nawee Kunrod');
                });
            }
        } else {
            $message = "Failed to update the discussion.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function updateIsReject($id, Request $request)
    {
        //
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'is_reject' => 'required|max:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Discussions::find($id);
        $data->is_reject = $input['is_reject'];

        if ($input['is_reject'] == 1) {
            $data->reject_remark = $input['reject_remark'];
            $data->reject_datetime = date('Y-m-d H:i:s');
            $data->reject_by = Auth::user()->id;
        } else {
            $data->reject_remark = null;
            $data->reject_datetime = null;
            $data->reject_by = null;
        }

        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The discussion has been updated.";
        } else {
            $message = "Failed to update the discussion.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function read($id, Request $request)
    {
        //
        $authSession = Auth::user();

        $data = Discussions::find($id);

        if ($data->is_read == 0) {
            $data->is_read = 1;
            $data->read_datetime = date('Y-m-d H:i:s');
            $data->read_by = $authSession->id;
            $is_success = $data->save();
        }

        $dataReplies = Discussions::where('parent_id', $data->id)->get();

        for ($i=0; $i < count($dataReplies); $i++) {
            if ($dataReplies[$i]->is_read == 0) {
                $dataReplies[$i]->is_read = 1;
                $dataReplies[$i]->read_datetime = date('Y-m-d H:i:s');
                $dataReplies[$i]->read_by = $authSession->id;
                $is_success = $dataReplies[$i]->save();
            }

            Discussions::where('parent_id', $dataReplies[$i]->id)->where('is_read', 0)->update([
                'is_read' => 1,
                'read_datetime' => date('Y-m-d H:i:s'),
                'read_by' => $authSession->id
            ]);
        }

        $is_success = true;
        $message = "The discussion has been readed.";

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

}
