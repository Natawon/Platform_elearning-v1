<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use DB;
use GuzzleHttp\Client as httpClient;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Videos;
use App\Models\Events;
use App\Models\Jobs;
use App\Models\Admins;
use App\Models\Topics;
use Input;
use Auth;

class VideosController extends Controller
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
        $order_by = $request->input('order_by', 'videos.modify_datetime');
        $order_direction = $request->input('order_direction', 'ASC');

        $data = new Videos;
        $data = $data->select('videos.*');

        if($request->has('course_id')) {
            $data = $data->where('videos.course_id', '=', $request->input('course_id'));
        } else if($request->has('topic_id')) {
            $data = $data->where('videos.topic_id', '=', $request->input('topic_id'));
        }

        $data = $data->where('videos.status', 1);

        $data = $data->orderBy($order_by, $order_direction)->paginate($per_page);

        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
            $data[$i]->size = (int)$data[$i]->size;
            // $data[$i]->transcodings = $data[$i]->transcodings()->orderBy('order', 'desc')->get();
            $data[$i]->transcodings = $data[$i]->transcodings()->orderBy('order', 'asc')->get();
            $data[$i]->jobs = Jobs::where('sc_job_id', $data[$i]->sc_job_id)->first();
            if ($data[$i]->jobs) {
                unset($data[$i]->jobs->sc_raw_data);
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
        $data = new Videos;
        $input = $request->json()->all();
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The video has been created.";
            $newId = $data->id;
        } else {
            $message = "Failed to create the video.";
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
        $data = Videos::with(['subtitles', 'topics'])->find($id);

        $admins = Admins::find($data->modify_by);
        $data->modify_by = $admins->username;
        $data->size = (int)$data->size;
        // $data->events = $data->events()->first();
        $data->transcodings = $data->transcodings()->orderBy('order', 'asc')->get();

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
        $data = Videos::find($id);
        $input = $request->json()->all();
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The video has been updated.";
        } else {
            $message = "Failed to update the video.";
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
        $data = Videos::find($id);

        $dataTranscodings = $data->transcodings()->get();

        $data->jobs()->delete();
        $data->delete();
        $is_success = $data;

        if ($is_success) {

            if ($data->smil_name != "") {
                /* ===== START DELETING SMIL FILE ===== */
                $params = array(
                    "filename" => $data->smil_name,
                    "dir_name" => $data->dir_name,
                );

                $httpClient = new httpClient();

                try {
                    $responseDeleteSmil = $httpClient->request('DELETE', env('BASE_URL_API_MEDIA').'ffmpeg/delete-smil.php', [
                        'json' => $params
                    ]);

                    $respDeleteSmilData = json_decode($responseDeleteSmil->getBody(), true);
                } catch(RequestException $e) {
                    if ($e->hasResponse()) {
                        $respDeleteSmilData['statusCode'] = $e->getResponse()->getStatusCode();
                        $respDeleteSmilData['errorInfo'] = json_decode($e->getResponse()->getBody());
                    }
                }
                /* ===== END DELETING SMIL FILE ===== */
            }

            /* ===== START DELETING ORIGINAL VIDEO FILE ===== */
            $params = array(
                "filename" => $data->name,
                "dir_name" => $data->dir_name,
            );

            $httpClient = new httpClient();

            try {
                $responseDelete = $httpClient->request('DELETE', env('BASE_URL_API_MEDIA').'ffmpeg/delete.php', [
                    'json' => $params
                ]);

                $respDeleteData = json_decode($responseDelete->getBody(), true);
            } catch(RequestException $e) {
                if ($e->hasResponse()) {
                    $respDeleteData['statusCode'] = $e->getResponse()->getStatusCode();
                    $respDeleteData['errorInfo'] = json_decode($e->getResponse()->getBody());
                }
            }
            /* ===== END DELETING ORIGINAL VIDEO FILE ===== */

            foreach ($dataTranscodings as $index => $transcoding) {

                $transcoding->delete();
                $isDeleted = $transcoding;

                if ($isDeleted && $transcoding->filename != "") {

                    /* ===== START DELETING TRANSCODE FILE ===== */
                    $params = array(
                        "filename" => $transcoding->filename,
                        "dir_name" => $data->dir_name,
                        "type" => "transcode"
                    );

                    $httpClient = new httpClient();

                    try {
                        $responseDelete = $httpClient->request('DELETE', env('BASE_URL_API_MEDIA').'ffmpeg/delete.php', [
                            'json' => $params
                        ]);

                        $respDeleteData = json_decode($responseDelete->getBody(), true);
                    } catch(RequestException $e) {
                        if ($e->hasResponse()) {
                            $respDeleteData['statusCode'] = $e->getResponse()->getStatusCode();
                            $respDeleteData['errorInfo'] = json_decode($e->getResponse()->getBody());
                        }
                    }
                    /* ===== END DELETING TRANSCODE FILE ===== */

                }

            }

            $message = "The videos has been deleted.";
        } else {
            $message = "Failed to delete the videos.";
        }
        return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }


    public function all()
    {
        //
        $data = Videos::get();
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

        $entity = Videos::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Videos::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Videos::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Videos::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Videos::where('order', '>', $request['order'])->min('id');
                    Videos::find($next)->decrement('order');
                    $entity->moveBefore(Videos::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The videos has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

    public function createVideo(Request $request)
    {
        $input = $request->json()->all();
        $dataBefore = Videos::where('topic_id', '=', $input['topic_id'])->where('name', '=', $input['name'])->first();

        if (!$dataBefore) {
            $exists = false;
            $data = new Videos;
            $input = $request->json()->all();
            $data->fill($input);
            $data->create_datetime = date('Y-m-d H:i:s');
            $data->modify_datetime = date('Y-m-d H:i:s');
            $data->modify_by = Auth::user()->id;

            $is_success = $data->save();

            $dataTopic = Topics::where('id', '=', $input['topic_id'])->first();
            $dataTopic->current_duration_record = null;
            $dataTopic->is_stop_record = 0;

            $is_success = $dataTopic->save();

            if ($is_success) {
                $message = "The video has been created.";
                $newId = $data->id;
            } else {
                $message = "Failed to create the video.";
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

    public function subtitles($id, Request $request) {
        //
        $data = Videos::with(['topics.courses'])->find($id);
        $data->topics->parent_topics = Topics::find($data->topics->parent);
        $data->subtitles = $data->subtitles()->orderBy('from_time')->get();

        return response()->json($data, 200);
    }


}
