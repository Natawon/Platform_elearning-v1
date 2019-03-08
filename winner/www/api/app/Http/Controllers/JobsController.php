<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Jobs;
use App\Models\Transcodings;
use App\Models\Videos;
use App\Models\Topics;
use App\Models\Admins;
use Input;
use Auth;
use Mail;

class JobsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        // $per_page = $request->input('per_page', 10);
        // $order_by = $request->input('order_by', 'transcodings.title');
        // $order_direction = $request->input('order_direction', 'ASC');

        // $data = new Jobs;
        // $data = $data->select('transcodings.*');

        // if($request->has('video_id')) {
        //     $data = $data->where('transcodings.video_id', '=', $request->input('video_id'));
        // }

        // $data = $data->orderBy($order_by, $order_direction)->paginate($per_page);

        // for($i=0; $i<count($data); $i++) {
        //     $admins = Admins::find($data[$i]->modify_by);
        //     $data[$i]->modify_by = $admins->username;
        //     $data[$i]->videos = $data[$i]->videos()->first();
        // }
        // return response()->json($data, 200);
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
        $data = new Jobs;
        $input = $request->json()->all();
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->create_by = $authSession->id;
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();

        if ($is_success) {
            $dataTranscodings = Transcodings::where('video_id', $data->video_id)->where('transcode_status', '!=', 'inappropriate');

            if ($data->sc_job_status == "Initial") {
                $dataTranscodings->update(['transcode_status' => "submitted", 'modify_datetime' => date('Y-m-d H:i:s'), 'modify_by' => $authSession->id]);
            } else if ($data->sc_job_status == "Distributed") {
                $dataTranscodings->update(['transcode_status' => "converted", 'modify_datetime' => date('Y-m-d H:i:s'), 'modify_by' => $authSession->id]);
            } else if ($data->sc_job_status == "Failed") {
                $dataTranscodings->update(['transcode_status' => "error", 'transcode_status_remark' => $data->sc_job_error_code.": ".$data->sc_job_error_message, 'modify_datetime' => date('Y-m-d H:i:s'), 'modify_by' => $authSession->id]);
            } else {
                $dataTranscodings->update(['transcode_status' => "converting", 'modify_datetime' => date('Y-m-d H:i:s'), 'modify_by' => $authSession->id]);
            }

            $message = "The job has been created.";
            $newId = $data->id;
        } else {
            $message = "Failed to create the job.";
            $newId = null;
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'id' => $newId), 200);
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
        $data = Jobs::find($id);
        $data->videos;

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
        $data = Jobs::find($id);
        $input = $request->json()->all();
        $input['sc_raw_data'] = json_encode($input['sc_raw_data'], JSON_UNESCAPED_UNICODE);
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();

        if ($is_success) {
            $dataTranscodings = Transcodings::where('video_id', $data->video_id)->where('transcode_status', '!=', 'inappropriate');

            if ($data->sc_job_status == "Initial") {
                $dataTranscodings->update(['transcode_status' => "submitted", 'modify_datetime' => date('Y-m-d H:i:s'), 'modify_by' => $authSession->id]);
            } else if ($data->sc_job_status == "Distributed") {
                $dataTranscodings->update(['transcode_status' => "converted", 'modify_datetime' => date('Y-m-d H:i:s'), 'modify_by' => $authSession->id]);
            } else if ($data->sc_job_status == "Failed") {
                $dataTranscodings->update(['transcode_status' => "error", 'transcode_status_remark' => $data->sc_job_error_code.": ".$data->sc_job_error_message, 'modify_datetime' => date('Y-m-d H:i:s'), 'modify_by' => $authSession->id]);
            } else {
                $dataTranscodings->update(['transcode_status' => "converting", 'modify_datetime' => date('Y-m-d H:i:s'), 'modify_by' => $authSession->id]);
            }

            $message = "The job has been updated.";
        } else {
            $message = "Failed to update the job.";
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
        // $data = Jobs::find($id);
        // $data->delete();
        // $is_success = $data;

        // if ($is_success) {
        //     $message = "The transcodings has been deleted.";
        // } else {
        //     $message = "Failed to delete the transcodings.";
        // }
        // return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }


    public function all()
    {
        //
        $data = Jobs::get();
        return response()->json($data, 200);
    }

    public function sort(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'id' => 'required|numeric',
        //     'type' => 'in:moveAfter,moveBefore',
        //     'positionEntityId' => 'numeric',
        //     'order' => 'numeric',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json($validator->messages(), 422);
        // }

        // $entity = Jobs::find($request['id']);

        // if (is_numeric($request['positionEntityId'])) {
        //     $positionEntity = Jobs::find($request['positionEntityId']);

        //     if ($request['type'] == "moveAfter") {
        //         $entity->moveAfter($positionEntity);
        //     } else {
        //         $entity->moveBefore($positionEntity);
        //     }
        // } else if (is_numeric($request['order'])) {
        //     $data = Jobs::where('order', '=', $request['order'])->first();

        //     if ($data) {
        //         if ($data->order > $entity['order']) {
        //             $entity->moveAfter($data);
        //         } else if ($data->order < $entity['order']) {
        //             $entity->moveBefore($data);
        //         }
        //     } else {
        //         $last = Jobs::orderBy('order', 'desc')->first();
        //         if ($request['order'] > $last->order) {
        //             $entity->moveAfter($last);
        //         } else {
        //             $next = Jobs::where('order', '>', $request['order'])->min('id');
        //             Jobs::find($next)->decrement('order');
        //             $entity->moveBefore(Jobs::find($next));
        //         }
        //     }
        // } else {
        //     $message = "Failed to sort.";
        //     return response()->json(array('message' => $message), 500);
        // }

        // $message = "The transcodings has been sorted.";
        // return response()->json(array('message' => $message), 200);
    }

    

    public function get_by_video($video_id) {
        $data = Jobs::where('video_id', $video_id)->orderBy('create_datetime', 'desc')->first();

        return response()->json($data, 200);
    }

    public function createJob(Request $request)
    {
        //
        $input = $request->json()->all();

        $dataBefore = Jobs::where('job_id', '=', $input['job_id'])->where('event_id', '=', $input['event_id'])->where('video_id', '=', $input['video_id'])->first();

        if (!$dataBefore) {
            $exists = false;
            $data = new Jobs;
            $input = $request->json()->all();
            $data->fill($input);

            $epoch = (int)($input['submit_datetime'] / 1000);
            $dt = new DateTime("@$epoch");  // convert UNIX timestamp to PHP DateTime
            $submit_datetime = $dt->format('Y-m-d H:i:s');

            $date_create = date_create($submit_datetime);
            date_add($date_create, date_interval_create_from_date_string('7 hours'));
            $submit_datetime = date_format($date_create, 'Y-m-d H:i:s');

            $data->submit_datetime = $submit_datetime;
            $data->create_datetime = date('Y-m-d H:i:s');
            $data->modify_datetime = date('Y-m-d H:i:s');
            $data->modify_by = Auth::user()->id;

            $is_success = $data->save();

            if ($is_success) {
                $dataEvents = Events::where('id', '=', $input['event_id'])->first();
                $dataEvents->current_duration_record = null;
                $dataEvents->is_stop_record = 0;

                $is_success = $dataEvents->save();

                if ($is_success) {
                    $dataJobs = Jobs::where('video_id', '=', $data->video_id)->get();

                    for ($i=0; $i < count($dataJobs); $i++) {
                        if ($dataJobs[$i]->transcode_status != "inappropriate") {
                            $dataJobs[$i]->transcode_status = "submitted";
                            $is_success = $dataJobs[$i]->save();
                        }
                    }

                    if ($is_success) {
                        $message = "Started convert the original video.";
                        $newId = $data->id;
                    } else {
                        $message = "Failed to create the transcodings job.";
                        $newId = null;
                    }
                } else {
                    $message = "Failed to create the transcodings job.";
                    $newId = null;
                }
            } else {
                $message = "Failed to create the transcodings job.";
                $newId = null;
            }

            return response()->json(array('is_error' => !$is_success, 'message' => $message, 'id' => $newId, 'exists' => $exists), 200);
        } else {
            $is_success = true;
            $message = "This video already exists.";
            $exists = true;

            return response()->json(array('is_error' => !$is_success, 'message' => $message, 'exists' => $exists), 200);
        }
    }

    public function get_jobs(Request $request) {
        $input = $request->json()->all();

        $videos_id = $input['videos_id'];
        $data_length = count($videos_id);

        $data = array();
        for ($i=0; $i < $data_length; $i++) {
            $transcodings_data = Jobs::where('video_id', $videos_id[$i])->orderBy('create_datetime', 'desc')->first();
            array_push($data, $transcodings_data);
        }

        return response()->json(array('data' => $data), 200);
    }

    public function updateJob(Request $request) {
        $data = Jobs::where('job_id', $request->job_id)->first();
        $input = $request->json()->all();
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $is_success = $data->save();

        if ($is_success) {
            $message = "The transcoding has been updated.";
        } else {
            $message = "Failed to update the transcoding.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }


}
