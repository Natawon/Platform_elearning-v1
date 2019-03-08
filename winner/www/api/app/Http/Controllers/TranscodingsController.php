<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Transcodings;
use App\Models\Videos;
use App\Models\Topics;
use App\Models\Admins;
use Input;
use Auth;
use Mail;

class TranscodingsController extends Controller
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
        $order_by = $request->input('order_by', 'transcodings.title');
        $order_direction = $request->input('order_direction', 'ASC');

        $data = new Transcodings;
        $data = $data->select('transcodings.*');

        if($request->has('video_id')) {
            $data = $data->where('transcodings.video_id', '=', $request->input('video_id'));
        }

        $data = $data->orderBy($order_by, $order_direction)->paginate($per_page);

        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
            $data[$i]->videos = $data[$i]->videos()->first();
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
        $data = new Transcodings;
        $input = $request->json()->all();
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The transcoding has been created.";
            $newId = $data->id;
        } else {
            $message = "Failed to create the transcoding.";
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
        $data = Transcodings::find($id);
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
        $data = Transcodings::find($id);
        $input = $request->json()->all();
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        // if ($data->transcode_status == "error") {
        //     // $url = config('constants._BASE_URL').$dataGroup->key."/login";
        //     $dataMail = array(
        //         'dataTranscoding' => $data,
        //         'dataVideo'       => $data->videos,
        //         'dataTopic'       => $data->videos->topics,
        //         'dataParentTopic' => ($data->videos->topics && $data->videos->topics->parent) ? Topics::find($data->videos->topics->parent) : null,
        //         'dataCourse'      => ($data->videos->topics) ? $data->videos->topics->courses : $data->videos->courses,
        //         'dataAdmin'       => Auth::user(),
        //         // 'url' => $url
        //     );

        //     Mail::send('transcoding-error-mail', $dataMail, function($mail) use ($dataMail) {
        //         $mail->to($dataMail['dataAdmin']['email'], $dataMail['dataAdmin']['first_name']." ".$dataMail['dataAdmin']['last_name'])->subject('แจ้งการ Transcode ล้มเหลว หลักสูตร '.$dataMail['dataCourse']['code']." - ".$dataMail['dataCourse']['title']);
        //         $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
        //         $mail->bcc('nawee.ku.dootvmedia@gmail.com', 'Nawee Kunrod');
        //     });
        // }

        if ($is_success) {
            $message = "The transcoding has been updated.";
        } else {
            $message = "Failed to update the transcoding.";
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
        $data = Transcodings::find($id);
        $data->delete();
        $is_success = $data;

        if ($is_success) {
            $message = "The transcodings has been deleted.";
        } else {
            $message = "Failed to delete the transcodings.";
        }
        return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }


    public function all()
    {
        //
        $data = Transcodings::get();
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

        $entity = Transcodings::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Transcodings::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Transcodings::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Transcodings::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Transcodings::where('order', '>', $request['order'])->min('id');
                    Transcodings::find($next)->decrement('order');
                    $entity->moveBefore(Transcodings::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The transcodings has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

    public function createByBitrates(Request $request)
    {
        //
        $validator = Validator::make($request->json()->all(), [
            'video_id' => 'required|numeric',
            'bitrates' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $input = $request->json()->all();

        $dataVideo = Videos::find($input['video_id']);

        if (!$dataVideo) {
            return response('Not found.', 404);
        }

        DB::beginTransaction();
        $is_success_all = true;

        $allBitrates = array(1080, 720, 480, 360, 240);

        foreach ($allBitrates as $bitrate) {

            if (isset($input['bitrates'][$bitrate])) {
                if ($input['bitrates'][$bitrate]) {
                    $transcodeStatus = 'waiting';
                } else {
                    $transcodeStatus = 'inappropriate';
                }

                $dataTranscoding = new Transcodings;
                $dataTranscoding->video_id = $dataVideo->id;
                $dataTranscoding->title = $bitrate;
                $dataTranscoding->transcode_status = $transcodeStatus;
                $dataTranscoding->create_datetime = date('Y-m-d H:i:s');
                $dataTranscoding->modify_datetime = date('Y-m-d H:i:s');
                $dataTranscoding->modify_by = Auth::user()->id;
                $is_success = $dataTranscoding->save();

                if (!$is_success) {
                    $is_success_all = false;
                    break;
                }
            }

        }

        if ($is_success_all) {
            DB::commit();
            $message = "The transcodings has been created.";
        } else {
            DB::rollBack();
            $message = "Failed to create the transcodings.";
        }

        return response()->json(array('is_error' => !$is_success_all, 'message' => $message), 200);
    }


}
