<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as httpClient;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

use App\Models\Admins;
use App\Models\Topics;
use App\Models\Courses;
use App\Models\SlidesTimes;
use App\Models\LiveResults;
use App\Models\Slides;

use Input;
use Auth;

class TopicsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request, _RolesController $oRole)
    {
        //
        $data = new Topics;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($authSession->super_users){
            $data = $data->with('courses');
            $data = $data->whereHas('courses',function ($query){
                $authSession = Auth::user();
                $query->where('admins_id', $authSession->id);
            });
        } else if (!$oRole->isSuper()) {
            $data = $data->with('courses');
            $data = $data->whereHas('courses',function ($query) use ($authSessionGroups){
                $query->whereHas('groups', function ($sub_query) use ($authSessionGroups){
                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            });
        }

        $data = $data->whereNull('parent')->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
            $data[$i]->courses = $data[$i]->courses()->first();

            $data[$i]->sub_topics = Topics::where('parent', $data[$i]->id)->orderBy('order', 'ASC')->get();

            for ($j=0; $j < count($data[$i]->sub_topics); $j++) {
                $admins = Admins::find($data[$i]->sub_topics[$j]->modify_by);
                $data[$i]->sub_topics[$j]->modify_by = $admins->username;
                $data[$i]->sub_topics[$j]->courses = $data[$i]->sub_topics[$j]->courses()->first();

                $data[$i]->sub_topics[$j]->startTime = (strtotime($data[$i]->sub_topics[$j]->start_time) - strtotime('TODAY')) * 1000;
                $data[$i]->sub_topics[$j]->endTime = (strtotime($data[$i]->sub_topics[$j]->end_time) - strtotime('TODAY')) * 1000;
                $data[$i]->sub_topics[$j]->duration = $data[$i]->sub_topics[$j]->endTime - $data[$i]->sub_topics[$j]->startTime;

                if($data[$i]->sub_topics[$j]->streaming_url){
                    $data[$i]->sub_topics[$j]->streaming_url = $data[$i]->sub_topics[$j]->streaming_url;
                    $data[$i]->sub_topics[$j]->streaming_url_cut = $data[$i]->sub_topics[$j]->streaming_url.'?wowzaplaystart='.$data[$i]->sub_topics[$j]->startTime.'&wowzaplayduration='.$data[$i]->sub_topics[$j]->duration;
                }else{
                    $data[$i]->sub_topics[$j]->streaming_url = $data[$i]->sub_topics[$j]->courses['streaming_url'];
                    $data[$i]->sub_topics[$j]->streaming_url_cut = $data[$i]->sub_topics[$j]->courses['streaming_url'].'?wowzaplaystart='.$data[$i]->sub_topics[$j]->startTime.'&wowzaplayduration='.$data[$i]->sub_topics[$j]->duration;
                }
                $data[$i]->sub_topics[$j]->quiz = $data[$i]->sub_topics[$j]->quiz()->first();

                if (strpos($data[$i]->sub_topics[$j]->streaming_url, env('STREAMING_SERVER_CDN')) === false) {
                    $data[$i]->sub_topics[$j]->use_streaming_server = false;
                } else {
                    $data[$i]->sub_topics[$j]->use_streaming_server = true;
                }
            }

            $data[$i]->startTime = (strtotime($data[$i]->start_time) - strtotime('TODAY')) * 1000;
            $data[$i]->endTime = (strtotime($data[$i]->end_time) - strtotime('TODAY')) * 1000;
            $data[$i]->duration = $data[$i]->endTime - $data[$i]->startTime;

            if($data[$i]->streaming_url){
                $data[$i]->streaming_url = $data[$i]->streaming_url;
                $data[$i]->streaming_url_cut = $data[$i]->streaming_url.'?wowzaplaystart='.$data[$i]->startTime.'&wowzaplayduration='.$data[$i]->duration;
            }else{
                $data[$i]->streaming_url = $data[$i]->courses['streaming_url'];
                $data[$i]->streaming_url_cut = $data[$i]->courses['streaming_url'].'?wowzaplaystart='.$data[$i]->startTime.'&wowzaplayduration='.$data[$i]->duration;
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

        $error = array();
        $validator = Validator::make($input, [
            'courses_id' => 'required|numeric',
            'title' => 'required|max:255',
            'start_time' => 'required_with:parent',
            'end_time' => 'required_with:parent',
            'live_start_datetime' => 'required_if:state,"live"',
            'live_end_datetime' => 'required_if:state,"live"',
        ]);

        if ($validator->fails() || !empty($error)) {
            $data = array_merge($validator->errors()->toArray(), $error);
            return response()->json($data, 422);
        }

        $data = new Topics;
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if (isset($input['parent'])) {
            $dataTopics = Topics::find($data->id);
            $dataSlidesTimes = SlidesTimes::where('courses_id', '=', $data->courses_id)->whereNull('topics_id')->where('time', '>=', $dataTopics->start_time)->where('time', '<=', $dataTopics->end_time)->get();

            if (count($dataSlidesTimes) > 0) {
                $count_dataSlidesTimes = count($dataSlidesTimes);
                for ($i=0; $i<$count_dataSlidesTimes; $i++) {
                    $time_start = strtotime($dataTopics->start_time);
                    $time_end = strtotime($dataSlidesTimes[$i]->time);

                    $time_diff = abs($time_start - $time_end);

                    $time_diff_h = floor($time_diff / 3600); // จำนวนชั่วโมงที่ต่างกัน
                    $time_diff_m = floor(($time_diff % 3600) / 60); // จำวนวนนาทีที่ต่างกัน
                    $time_diff_s = ($time_diff % 3600) % 60; // จำนวนวินาทีที่ต่างกัน

                    if (strlen($time_diff_h) < 1) {
                        $time_diff_h = '0'.time_diff_h;
                    }

                    if (strlen($time_diff_m) < 1) {
                        $time_diff_m = '0'.time_diff_m;
                    }

                    if (strlen($time_diff_s) < 1) {
                        $time_diff_s = '0'.time_diff_s;
                    }

                    $convert_time = $time_diff_h.":".$time_diff_m.":".$time_diff_s;

                    $dataSlidesTimes[$i]->time = $convert_time;
                    $dataSlidesTimes[$i]->topics_id = $dataTopics->id;
                    $dataSlidesTimes[$i]->save();
                }
            }
        }

        if ($is_success) {
            $message = "The topics has been created.";
            if (isset($data->parent)) {

                // $data->live_transcode_server = env('LIVE_TRANSCODE_SERVER');
                $data->streaming_server = env('STREAMING_SERVER');
                $data->streaming_server_cdn = env('STREAMING_SERVER_CDN');
                $data->streaming_applications = env('STREAMING_APPLICATIONS');
                // $data->streaming_prefix_streamname = "t_" . $data->id . str_random(4);
                $data->streaming_streamname = "live".$data->id;
                $data->streaming_record_part = "T".$data->id;
                $data->streaming_record_filename = "live".$data->id;

                if ($data->state == 'live') {
                    $data->streaming_url = $data->streaming_server_cdn . '/' . $data->streaming_applications . '/ngrp:' . $data->streaming_streamname .'_all/playlist.m3u8';

                    $live_results = new LiveResults;
                    $live_results->topic_id = $data->id;
                    $live_results->create_datetime = date('Y-m-d H:i:s');
                    $live_results->modify_datetime = date('Y-m-d H:i:s');
                    $live_results->modify_by = $input['admin_id'];
                    $is_success = $live_results->save();
                }

                // Start Create Live Events
                $oFunc = new _FunctionsController;
                $client = new httpClient();

                // $url_create_live_event = '/live_events';
                // $authentication_data = $oFunc->liveTranscodeAuthentication($url_create_live_event);

                // $body = $oFunc->createXMLLiveEvent($data->streaming_prefix_streamname, $data->streaming_streamname);

                // try {
                //     $response = $client->request('POST', env('LIVE_TRANSCODE_API').$url_create_live_event, [
                //         'headers' => [
                //             'Accept' => 'application/xml',
                //             'X-Auth-User' => 'admin',
                //             'X-Auth-Expires' => $authentication_data['expires'],
                //             'X-Auth-Key' => $authentication_data['key'],
                //             'Content-Type' => 'application/xml'
                //         ],
                //         'body' => $body
                //     ]);
                // } catch(RequestException $e) {
                //     if ($e->hasResponse()) {
                //         return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
                //     }
                // }

                // $dataXML = $this->namespacedXMLToArray($response->getBody());

                // $event_name = 'DooTV_'.$data->streaming_prefix_streamname;

                // if ($dataXML['name'] != $event_name) {
                //     DB::rollBack();
                //     DB::update("ALTER TABLE topics AUTO_INCREMENT = 1;");

                //     $message = "Failed to create the topics.";

                //     return response()->json(array('is_error' => true, 'message' => $message), 200);
                // }

                // $data->live_event_id = $dataXML['id'];
                // $data->live_event_status = $dataXML['input']['status'];
                // // End Create Live Events

                $is_success = $data->save();

                // // Start Start Live Events Status
                // // $url_start_live_event = '/live_events/'.$data->live_event_id.'/start';
                // // $authentication_data = $oFunc->liveTranscodeAuthentication($url_start_live_event);

                // // try {
                // //     $response = $client->request('POST', env('LIVE_TRANSCODE_API').$url_start_live_event, [
                // //         'headers' => [
                // //             'Accept' => 'application/xml',
                // //             'X-Auth-User' => 'admin',
                // //             'X-Auth-Expires' => $authentication_data['expires'],
                // //             'X-Auth-Key' => $authentication_data['key'],
                // //             'Content-Type' => 'application/xml'
                // //         ]
                // //     ]);
                // // } catch(RequestException $e) {
                // //     if ($e->hasResponse()) {
                // //         return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
                // //     }
                // // }

                // $dataXML = $this->namespacedXMLToArray($response->getBody());

                // if ($dataXML['id'] != $data->live_event_id) {
                //     DB::rollBack();
                //     DB::update("ALTER TABLE topics AUTO_INCREMENT = 1;");

                //     $message = "Failed to create the topics.";

                //     return response()->json(array('is_error' => true, 'message' => $message), 200);
                // }
                // // End Start Live Events Status

                // // Start Create SMIL
                // $params = array(
                //     "streaming_prefix_streamname" => $data->streaming_prefix_streamname,
                // );

                // try {
                //     $response = $client->request('POST', env('BASE_URL_API_MEDIA').'ffmpeg/create-smil-live.php', [
                //         'json' => $params
                //     ]);
                // } catch(RequestException $e) {
                //     if ($e->hasResponse()) {
                //         $respData['statusCode'] = $e->getResponse()->getStatusCode();
                //         $respData['errorInfo'] = json_decode($e->getResponse()->getBody());
                //     }
                // }
                // End Create SMIL

                /* ===== START CREATE DIRECTORY FOR RECORD ===== */
                $params = array(
                    "dir_name" => $data->streaming_record_part,
                    "base_dir" => 'topics',
                    "isMultiBaseDir" => true // for base directories
                );

                try {
                    $responseCretateDirectory = $client->request('POST', env('BASE_URL_API_MEDIA').'directories/create_dir.php', [
                        'json' => $params
                    ]);

                    $respData = json_decode($responseCretateDirectory->getBody(), true);

                    // if (isset($respData['dir_name']) && $respData['dir_name'] != $data->streaming_record_part) {
                    //     $data->streaming_record_part = $respData['dir_name'];
                    //     $data->save();
                    // }

                } catch(RequestException $e) {
                    if ($e->hasResponse()) {
                        $respData['statusCode'] = $e->getResponse()->getStatusCode();
                        $respData['errorInfo'] = json_decode($e->getResponse()->getBody());
                    }
                }
                /* ===== END CREATE DIRECTORY FOR RECORD ===== */

                $subTopics = Topics::where('parent', $data->parent)->orderBy('order', 'asc')->get();
                for ($i=0; $i < count($subTopics); $i++) {
                    if ($i == 0) {
                        $subTopics[$i]->moveAfter(Topics::find($data->parent));
                        continue;
                    }

                    Topics::find($subTopics[$i]->id)->moveAfter(Topics::find($subTopics[$i-1]->id));
                }
            }
        } else {
            $message = "Failed to create the topics.";
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
        if (!$oRole->haveAccess($id, "topics")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Topics::find($id);
        $data->courses = $data->courses()->first();

        $data->startTime = (strtotime($data->start_time) - strtotime('TODAY')) * 1000;
        $data->endTime = (strtotime($data->end_time) - strtotime('TODAY')) * 1000;
        $data->duration = $data->endTime - $data->startTime;

        if($data->streaming_url){
            $data->streaming_url = $data->streaming_url;
            $data->streaming_url_cut = $data->streaming_url.'?wowzaplaystart='.$data->startTime.'&wowzaplayduration='.$data->duration;
        }else{
            $data->streaming_url = $data->courses['streaming_url'];
            $data->streaming_url_cut = $data->courses['streaming_url'].'?wowzaplaystart='.$data->startTime.'&wowzaplayduration='.$data->duration;
        }

        $data->url_for_streaming = $data->streaming_server . '/' . $data->streaming_applications;
        $data->videos = $data->videos()->get();


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
        if (!$oRole->haveAccess($id, "topics")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $input = $request->json()->all();

        $error = array();
        $validator = Validator::make($input, [
            'courses_id' => 'required|numeric',
            'title' => 'required|max:255',
            'start_time' => 'required_with:parent',
            'end_time' => 'required_with:parent',
            'live_start_datetime' => 'required_if:state,live',
            'live_end_datetime' => 'required_if:state,live',
        ]);

        if ($input['state'] == 'vod') {
            if ($input['end_time'] == '00:00:00' && $input['status'] == 1) {
                $error['end_time'][] = "The selected end time is invalid.";
            }
        }

        if ($validator->fails() || !empty($error)) {
            $data = array_merge($validator->errors()->toArray(), $error);
            return response()->json($data, 422);
        }

        $data = Topics::find($id);
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($input['parent'] != null) {
            $dataTopics = Topics::find($data->id);
            $dataSlidesTimes = SlidesTimes::where('courses_id', '=', $data->courses_id)->whereNull('topics_id')->where('time', '>=', $dataTopics->start_time)->where('time', '<=', $dataTopics->end_time)->get();

            if ($dataSlidesTimes) {
                $count_dataSlidesTimes = count($dataSlidesTimes);
                for ($i=0; $i<$count_dataSlidesTimes; $i++) {
                    $time_start = strtotime($dataTopics->start_time);
                    $time_end = strtotime($dataSlidesTimes[$i]->time);

                    $time_diff = abs($time_start - $time_end);

                    $time_diff_h = floor($time_diff / 3600); // จำนวนชั่วโมงที่ต่างกัน
                    $time_diff_m = floor(($time_diff % 3600) / 60); // จำวนวนนาทีที่ต่างกัน
                    $time_diff_s = ($time_diff % 3600) % 60; // จำนวนวินาทีที่ต่างกัน

                    if (strlen($time_diff_h) < 1) {
                        $time_diff_h = '0'.time_diff_h;
                    }

                    if (strlen($time_diff_m) < 1) {
                        $time_diff_m = '0'.time_diff_m;
                    }

                    if (strlen($time_diff_s) < 1) {
                        $time_diff_s = '0'.time_diff_s;
                    }

                    $convert_time = $time_diff_h.":".$time_diff_m.":".$time_diff_s;

                    $dataSlidesTimes[$i]->time = $convert_time;
                    $dataSlidesTimes[$i]->topics_id = $dataTopics->id;
                    $dataSlidesTimes[$i]->save();
                }
            }
        }

        if ($is_success) {
            $message = "The topics has been updated.";

            if (isset($data->parent)) {
                if ($data->state == 'live') {

                    $live_results_exits = LiveResults::where('topic_id', $data->id)->first();
                    if (!$live_results_exits) {
                        $live_results = new LiveResults;
                        $live_results->topic_id = $data->id;
                        $live_results->create_datetime = date('Y-m-d H:i:s');
                        $live_results->modify_datetime = date('Y-m-d H:i:s');
                        $live_results->modify_by = $input['admin_id'];
                        $is_success = $live_results->save();
                    }

                }

                $is_success = $data->save();
                
                $subTopics = Topics::where('parent', $data->parent)->orderBy('order', 'asc')->get();
                for ($i=0; $i < count($subTopics); $i++) {
                    if ($i == 0) {
                        $subTopics[$i]->moveAfter(Topics::find($data->parent));
                        continue;
                    }

                    Topics::find($subTopics[$i]->id)->moveAfter(Topics::find($subTopics[$i-1]->id));
                }
            }
        } else {
            $message = "Failed to update the topics.";
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
        if (!$oRole->haveAccess($id, "topics")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Topics::find($id);

        if ($data->live_event_id) {
            // Start Cancel Live Events
            $oFunc = new _FunctionsController;
            $client = new httpClient();

            $url_create_live_event = '/live_events/'.$data->live_event_id.'/status';
            $authentication_data = $oFunc->liveTranscodeAuthentication($url_create_live_event);

            try {
                $response = $client->request('GET', env('LIVE_TRANSCODE_API').$url_create_live_event, [
                    'headers' => [
                        'Accept' => 'application/xml',
                        'X-Auth-User' => 'admin',
                        'X-Auth-Expires' => $authentication_data['expires'],
                        'X-Auth-Key' => $authentication_data['key'],
                        'Content-Type' => 'application/xml'
                    ]
                ]);
            } catch(RequestException $e) {
                if ($e->hasResponse()) {
                    return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
                }
            }

            $dataXML = $this->namespacedXMLToArray($response->getBody());

            if ($dataXML['status'] == 'running') {
                // Stop Live Event
                $url_stop_live_event = '/live_events/'.$data->live_event_id.'/stop';
                $authentication_data = $oFunc->liveTranscodeAuthentication($url_stop_live_event);

                try {
                    $response = $client->request('POST', env('LIVE_TRANSCODE_API').$url_stop_live_event, [
                        'headers' => [
                            'Accept' => 'application/xml',
                            'X-Auth-User' => 'admin',
                            'X-Auth-Expires' => $authentication_data['expires'],
                            'X-Auth-Key' => $authentication_data['key'],
                            'Content-Type' => 'application/xml'
                        ]
                    ]);
                } catch(RequestException $e) {
                    if ($e->hasResponse()) {
                        return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
                    }
                }
            } else if ($dataXML['status'] == 'pending') {
                // Cancel Live Event
                $url_cancel_live_event = '/live_events/'.$data->live_event_id.'/cancel';
                $authentication_data = $oFunc->liveTranscodeAuthentication($url_cancel_live_event);

                try {
                    $response = $client->request('POST', env('LIVE_TRANSCODE_API').$url_cancel_live_event, [
                        'headers' => [
                            'Accept' => 'application/xml',
                            'X-Auth-User' => 'admin',
                            'X-Auth-Expires' => $authentication_data['expires'],
                            'X-Auth-Key' => $authentication_data['key'],
                            'Content-Type' => 'application/xml'
                        ]
                    ]);
                } catch(RequestException $e) {
                    if ($e->hasResponse()) {
                        return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
                    }
                }
            }

            // Delete Live Event
            $url_delete_live_event = '/live_events/'.$data->live_event_id;
            $authentication_data = $oFunc->liveTranscodeAuthentication($url_delete_live_event);

            try {
                $response = $client->request('DELETE', env('LIVE_TRANSCODE_API').$url_delete_live_event, [
                    'headers' => [
                        'Accept' => 'application/xml',
                        'X-Auth-User' => 'admin',
                        'X-Auth-Expires' => $authentication_data['expires'],
                        'X-Auth-Key' => $authentication_data['key'],
                        'Content-Type' => 'application/xml'
                    ]
                ]);
            } catch(RequestException $e) {
                if ($e->hasResponse()) {
                    return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
                }
            }
        }

        $data->delete();

        $parent = Topics::where('parent', $id);
        $parent->delete();

        $is_success = $data;

        if ($is_success) {
            $message = "The topics has been deleted.";
        } else {
            $message = "Failed to delete the topics.";
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

        $data = Topics::find($id);
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The topic has been updated.";
        } else {
            $message = "Failed to update the topic.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function parents($id)
    {
        //
        $data = Topics::where('courses_id', $id)->whereNull('parent')->orderBy('order', 'asc')->get();
        for($i=0; $i<count($data); $i++) {
            if(!$data[$i]->parent){
                $courses = Courses::find($data[$i]->courses_id);
                $data[$i]->title = $data[$i]->title." (".$courses->title.")";
            }else{
                $data[$i]->title = "- ".$data[$i]->title;
            }
        }
        return response()->json($data, 200);
    }

    public function children($id)
    {
        //
        $data = Topics::where('courses_id', $id)->whereNotNull('parent')->orderBy('order', 'asc')->get();
        for($i=0; $i<count($data); $i++) {
            if(!$data[$i]->parent){
                $courses = Courses::find($data[$i]->courses_id);
                $data[$i]->title = $data[$i]->title." (".$courses->title.")";
            }else{
                $data[$i]->title = "- ".$data[$i]->title;
            }
        }

        if (count($data) == 0) {
            $data = null;
        }

        return response()->json($data, 200);
    }

    public function topics2parents($id)
    {
        //
        $data = Topics::where('courses_id', $id)->orderBy('order', 'asc')->get();
        for($i=0; $i<count($data); $i++) {
            if(!$data[$i]->parent){
                $courses = Courses::find($data[$i]->courses_id);
                $data[$i]->title = $data[$i]->title." (".$courses->title.")";
            }else{
                $data[$i]->title = "- ".$data[$i]->title;
            }
        }
        return response()->json($data, 200);
    }

    public function topicsHasParents($id)
    {
        //
        $data = Topics::where('courses_id', $id)->whereNotNull('parent')->orderBy('order', 'asc')->get();
        // for($i=0; $i<count($data); $i++) {
        //     if(!$data[$i]->parent){
        //         $courses = Courses::find($data[$i]->courses_id);
        //         $data[$i]->title = $data[$i]->title." (".$courses->title.")";
        //     }else{
        //         $data[$i]->title = "- ".$data[$i]->title;
        //     }
        // }
        return response()->json($data, 200);
    }

    public function all(_RolesController $oRole)
    {
        //
        $data = new Topics;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();
        $data = $data->whereNull('parent');
        if($authSession->super_users){
            $data = $data->with('courses');
            $data = $data->whereHas('courses',function ($query){
                $authSession = Auth::user();
                $query->where('admins_id', $authSession->id);
            });
        } else if (!$oRole->isSuper()) {
            $data = $data->with('courses');
            $data = $data->whereHas('courses',function ($query) use ($authSessionGroups){
                $query->whereHas('groups', function ($sub_query) use ($authSessionGroups){
                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            });
        }
        $data = $data->orderBy('order', 'asc')->get();
        for($i=0; $i<count($data); $i++) {
            if(!$data[$i]->parent){
                $courses = Courses::find($data[$i]->courses_id);
                $data[$i]->title = $data[$i]->title." (".$courses->title.")";
            }else{
                $data[$i]->title = "- ".$data[$i]->title;
            }
        }
        return response()->json($data, 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = Topics::find($input[$i]['id']);
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

        $entity = Topics::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Topics::find($request['positionEntityId']);
            $subPositionEntity = Topics::where('parent', $positionEntity->id)->orderBy('order', 'desc')->first();

            if ($request['type'] == "moveAfter") {
                if ($subPositionEntity) {
                    $entity->moveAfter($subPositionEntity);
                } else {
                    $entity->moveAfter($positionEntity);
                }
            } else {
                $entity->moveBefore($positionEntity);
            }

            $subEntity = Topics::where('parent', $entity->id)->orderBy('order', 'asc')->get();
            for ($i=0; $i < count($subEntity); $i++) {
                if ($i == 0) {
                    $subEntity[$i]->moveAfter($entity);
                    continue;
                }

                Topics::find($subEntity[$i]->id)->moveAfter(Topics::find($subEntity[$i-1]->id));
            }
        } else if (is_numeric($request['order'])) {

            if (isset($entity->parent)) {
                $minOrderSibling = Topics::where('parent', $entity->parent)->min('order');
                $maxOrderSibling = Topics::where('parent', $entity->parent)->max('order');

                if ($request['order'] < $minOrderSibling) {
                    $request['order'] = $minOrderSibling;
                } else if ($request['order'] > $maxOrderSibling) {
                    $request['order'] = $maxOrderSibling;
                }
            }

            $data = Topics::where('order', '=', $request['order'])->first();

            if ($data) {
                if (isset($data->parent)) {
                    $data = Topics::where('parent', $data->parent)->orderBy('order', 'desc')->first();
                    $subPositionEntity = Topics::where('parent', $data->id)->orderBy('order', 'desc')->first();
                    if ($subPositionEntity) {
                        $entity->moveAfter($subPositionEntity);
                    } else {
                        $entity->moveAfter($data);
                    }
                } else {
                    if ($data->order > $entity['order']) {
                        $subPositionEntity = Topics::where('parent', $data->id)->orderBy('order', 'desc')->first();
                        if ($subPositionEntity) {
                            $entity->moveAfter($subPositionEntity);
                        } else {
                            $entity->moveAfter($data);
                        }
                    } else if ($data->order < $entity['order']) {
                        $entity->moveBefore($data);
                    }
                }

            } else {
                $last = Topics::orderBy('order', 'desc')->first();
                $subPositionEntity = Topics::where('parent', $last->id)->orderBy('order', 'desc')->first();

                if ($request['order'] > $last->order) {
                    if ($subPositionEntity) {
                        $entity->moveAfter($subPositionEntity);
                    } else {
                        $entity->moveAfter($last);
                    }
                } else {
                    $next = Topics::where('order', '>', $request['order'])->min('id');
                    Topics::find($next)->decrement('order');
                    $entity->moveBefore(Topics::find($next));
                }
            }

            $subEntity = Topics::where('parent', $entity->id)->orderBy('order', 'asc')->get();
            for ($i=0; $i < count($subEntity); $i++) {
                if ($i == 0) {
                    $subEntity[$i]->moveAfter($entity);
                    continue;
                }

                Topics::find($subEntity[$i]->id)->moveAfter(Topics::find($subEntity[$i-1]->id));
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The topics has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

    private function removeNamespaceFromXML( $xml )
    {
        // Because I know all of the the namespaces that will possibly appear in
        // in the XML string I can just hard code them and check for
        // them to remove them
        $toRemove = ['rap', 'turss', 'crim', 'cred', 'j', 'rap-code', 'evic'];
        // This is part of a regex I will use to remove the namespace declaration from string
        $nameSpaceDefRegEx = '(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?';

        // Cycle through each namespace and remove it from the XML string
        foreach( $toRemove as $remove ) {
            // First remove the namespace from the opening of the tag
            $xml = str_replace('<' . $remove . ':', '<', $xml);
            // Now remove the namespace from the closing of the tag
            $xml = str_replace('</' . $remove . ':', '</', $xml);
            // This XML uses the name space with CommentText, so remove that too
            $xml = str_replace($remove . ':commentText', 'commentText', $xml);
            // Complete the pattern for RegEx to remove this namespace declaration
            $pattern = "/xmlns:{$remove}{$nameSpaceDefRegEx}/";
            // Remove the actual namespace declaration using the Pattern
            $xml = preg_replace($pattern, '', $xml, 1);
        }

        // Return sanitized and cleaned up XML with no namespaces
        return $xml;
    }

    private function namespacedXMLToArray($xml)
    {
        // One function to both clean the XML string and return an array
        return json_decode(json_encode(simplexml_load_string($this->removeNamespaceFromXML($xml))), true);
    }

    public function getSlides($id)
    {
        //
        $topic = Topics::find($id);

        $data = Slides::where('courses_id', '=', $topic->courses_id)->orderBy('order', 'asc')->get();
        for($i=0; $i<count($data); $i++) {
            $data[$i]->slides_times = $data[$i]->slides_times()->where('topics_id', $id)->get();
            // for($t=0; $t<count($data[$i]->slides_times); $t++) {
            //     $data[$i]->slides_times[$t]->topics = $data[$i]->slides_times[$t]->topics()->first();

            // }
        }

        return response()->json($data, 200);
    }

    public function generateLiveUrl($id)
    {
        $authSession = Auth::user();

        $data = Topics::find($id);

        if (!$data) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "'Topic'", config('constants._errorMessage._404'))), 404);
        }

        $live_url = $data->streaming_server_cdn . '/' . $data->streaming_applications . '/ngrp:' . $data->streaming_streamname .'_all/playlist.m3u8';
        // $live_url = $data->streaming_server_cdn . '/' . $data->streaming_applications . '/smil:' . $data->streaming_prefix_streamname .'.smil/playlist.m3u8';

        return response()->json(array('live_url' => $live_url), 200);
    }
}
