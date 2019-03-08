<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Subtitles;
use App\Models\Videos;
use App\Models\Admins;
use Auth;
use Input;

class SubtitlesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $data = Subtitles::orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->no = $i + 1;
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
        $authSession = Auth::user();

        $data = new Subtitles;
        $input = $request->json()->all();
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->create_by = $authSession->id;
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The subtitle has been created.";
        } else {
            $message = "Failed to create the subtitle.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
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
        $data = Subtitles::with('videos')->find($id);

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

        $data = Subtitles::find($id);
        $input = $request->json()->all();
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The subtitle has been updated.";
        } else {
            $message = "Failed to update the subtitle.";
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
        $data = Subtitles::find($id);
        $data->delete();
        $is_success = $data;

        if ($is_success) {
            $message = "The subtitle has been deleted.";
        } else {
            $message = "Failed to delete the subtitle.";
        }
        return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }


    public function all()
    {
        //
        $data = Subtitles::get();
        return response()->json($data, 200);
    }


    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = Subtitles::find($input[$i]['id']);
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

        $entity = Subtitles::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Subtitles::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Subtitles::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Subtitles::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Subtitles::where('order', '>', $request['order'])->min('id');
                    Subtitles::find($next)->decrement('order');
                    $entity->moveBefore(Subtitles::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The subtitles has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

    public function createByVideo(Request $request, $video_id)
    {
        //
        // return response('test', 200);

        $validator = Validator::make($request->json()->all(), [
            'id' => 'required|numeric',
            'subtitles' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $authSession = Auth::user();
        $input = $request->json()->all();

        $dataVideo = Videos::find($input['id']);

        if (!$dataVideo && $input['id'] != $video_id) {
            return response('Not found.', 404);
        }

        $dataVideo->subtitle_edge_style = $input['subtitle_edge_style'];
        $dataVideo->subtitle_font_color = $input['subtitle_font_color'];
        $dataVideo->subtitle_font_opacity = $input['subtitle_font_opacity'];
        $dataVideo->subtitle_background_color = $input['subtitle_background_color'];
        $dataVideo->subtitle_background_opacity = $input['subtitle_background_opacity'];
        $dataVideo->subtitle_window_color = $input['subtitle_window_color'];
        $dataVideo->subtitle_window_opacity = $input['subtitle_window_opacity'];
        $dataVideo->save();

        DB::beginTransaction();
        $is_success_all = true;

        foreach ($input['subtitles'] as $subtitle) {
            if ((!isset($subtitle['from_time']) || $subtitle['from_time'] == "") && (!isset($subtitle['to_time']) || $subtitle['to_time'] == "")  && (!isset($subtitle['message']) || $subtitle['message'] == "")) {
                continue;
            }

            if (isset($subtitle['id'])) {
                $dataSubtitle = Subtitles::find($subtitle['id']);
                if ($dataSubtitle) {
                    $dataSubtitle->video_id = $dataVideo->id;
                    $dataSubtitle->fill($subtitle);
                    $dataSubtitle->modify_datetime = date('Y-m-d H:i:s');
                    $dataSubtitle->modify_by = $authSession->id;
                    $is_success = $dataSubtitle->save();
                } else {
                    $dataSubtitle = new Subtitles;
                    $dataSubtitle->fill($subtitle);
                    $dataSubtitle->create_datetime = date('Y-m-d H:i:s');
                    $dataSubtitle->create_by = $authSession->id;
                    $dataSubtitle->modify_datetime = date('Y-m-d H:i:s');
                    $dataSubtitle->modify_by = $authSession->id;
                    $is_success = $dataSubtitle->save();
                }
            } else {
                $dataSubtitle = new Subtitles;
                $dataSubtitle->fill($subtitle);
                $dataSubtitle->create_datetime = date('Y-m-d H:i:s');
                $dataSubtitle->create_by = $authSession->id;
                $dataSubtitle->modify_datetime = date('Y-m-d H:i:s');
                $dataSubtitle->modify_by = $authSession->id;
                $is_success = $dataSubtitle->save();
            }

            if (!$is_success) {
                $is_success_all = false;
                break;
            }
        }

        if ($is_success_all) {
            DB::commit();
            $message = "The subtitles has been created.";
        } else {
            DB::rollBack();
            $message = "Failed to create the subtitles.";
        }

        return response()->json(array('is_error' => !$is_success_all, 'message' => $message), 200);
    }

    public function getByVideo(Request $request, $video_id)
    {
        //
        $data = Videos::find($video_id);
        $data->subtitles = $data->subtitles()->orderBy('order')->get();
        // for($i=0; $i<count($data); $i++) {
        //     $admins = Admins::find($data[$i]->modify_by);
        //     $data[$i]->no = $i + 1;
        //     $data[$i]->modify_by = $admins->username;
        //     $data[$i]->videos = $data[$i]->videos()->first();
        // }
        return response()->json($data, 200);
    }

    public function getFile($id)
    {
        //
        $data = Videos::find($id);
        $data->subtitles = $data->subtitles()->orderBy('from_time')->get();

        // return response()->json($data->toArray(), 200);

        // $content = "WEBVTT"."\n\n";
        $content = "";
        $numberOrder = 1;

        for($i=0; $i<count($data->subtitles); $i++) {
            if (isset($data->subtitles[$i]->from_time) && $data->subtitles[$i]->from_time != "" && isset($data->subtitles[$i]->to_time) && $data->subtitles[$i]->to_time != "" && isset($data->subtitles[$i]->message) && $data->subtitles[$i]->message != "") {
                $content .= $numberOrder."\n";
                $content .= $data->subtitles[$i]->from_time.",000 --> ".$data->subtitles[$i]->to_time.",000"."\n";
                $content .= $data->subtitles[$i]->message."\n\n";
                $numberOrder++;
            }
        }

        $fileName = "subtitles.srt";
        $headers = ['Content-type'=>'text/srt', 'Content-Disposition'=>sprintf('; filename="%s"', $fileName)];
        return response()->make($content, 200, $headers);


    }

    public function downloadFile($id)
    {
        //
        $data = Videos::find($id);
        $data->subtitles = $data->subtitles()->orderBy('from_time')->get();

        // return response()->json($data->toArray(), 200);

        // $content = "WEBVTT"."\n\n";
        $content = "";
        $numberOrder = 1;

        for($i=0; $i<count($data->subtitles); $i++) {
            if (isset($data->subtitles[$i]->from_time) && $data->subtitles[$i]->from_time != "" && isset($data->subtitles[$i]->to_time) && $data->subtitles[$i]->to_time != "" && isset($data->subtitles[$i]->message) && $data->subtitles[$i]->message != "") {
                $content .= $numberOrder."\n";
                $content .= $data->subtitles[$i]->from_time.",000 --> ".$data->subtitles[$i]->to_time.",000"."\n";
                $content .= $data->subtitles[$i]->message."\n\n";
                $numberOrder++;
            }
        }

        $fileName = pathinfo($data->name, PATHINFO_FILENAME).".srt";
        $headers = ['Content-type'=>'text/srt', 'Content-Disposition'=>sprintf('attachment; filename="%s"', $fileName)];
        return response()->make($content, 200, $headers);


    }

    public function uploadFile(Request $request, _FunctionsController $oFunc, $id)
    {
        ini_set('max_execution_time', 300);

        $authSession = Auth::user();
        $dataVideos = Videos::with('subtitles')->find($id);

        if (!$dataVideos) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "'Video'", config('constants._errorMessage._404'))), 404);
        }

        if (!$request->hasFile('file')) {
            $excelExtension = "";
        } else {
            $fileSubtitle = $request->file('file');
            $excelExtension = strtolower($fileSubtitle->getClientOriginalExtension());
        }

        $validator = Validator::make(
            [
                'file'   => $excelExtension,
            ],
            [
                'file'   => 'required|in:srt',
            ],
            [
                'file.in' => 'The :attribute must be one of the following types: .srt',
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $fileConverted = $oFunc->convertFileUTF8($fileSubtitle);
        if ($fileConverted['file'] === false) {
            if (!empty($fileConverted['encoding'])) {
                $message = "The encoding '".$fileConverted['encoding']."' is not supported.";
            } else {
                $message = "This file encoding not supported.";
            }

            return response()->json(['message' => $message], 422);
        }

        $arrInserts = array();
        $row = 0;
        $section = 1;
        $file_lines = file($fileSubtitle, FILE_IGNORE_NEW_LINES);
        $actionDate = date('Y-m-d H:i:s');
        foreach ($file_lines as $line) {
            if ($section == 1) {
                $section++;
            } else if ($section == 2) {
                list($from_time_full, $to_time_full) = explode(" --> ", $line);

                list($from_time, $from_mill_time) = explode(",", $from_time_full);
                $arrInserts[$row]['from_time'] = $from_time;
                $arrInserts[$row]['from_mill_time'] = $from_mill_time;

                list($to_time, $to_mill_time) = explode(",", $to_time_full);
                $arrInserts[$row]['to_time'] = $to_time;
                $arrInserts[$row]['to_mill_time'] = $to_mill_time;

                $section++;
            } else if ($section >= 3) {
                if ($line == "") {
                    $arrInserts[$row]['video_id'] = $dataVideos->id;
                    $arrInserts[$row]['create_datetime'] = $actionDate;
                    $arrInserts[$row]['create_by'] = $authSession->id;
                    $arrInserts[$row]['modify_datetime'] = $actionDate;
                    $arrInserts[$row]['modify_by'] = $authSession->id;
                    $section = 1;
                    $row++;
                } else {
                    if (isset($arrInserts[$row]['message']) && $arrInserts[$row]['message'] != "") {
                        $arrInserts[$row]['message'] .= "\n".$line;
                        $section++;
                    } else {
                        $arrInserts[$row]['message'] = $line;
                        $section++;
                    }
                }
            }

            // break;
        }

        $is_success = Subtitles::insert($arrInserts);

        if ($is_success) {
            $message = "The subtitle has been created.";

            Subtitles::whereIn('id', array_pluck($dataVideos->subtitles, 'id'))->delete();
        } else {
            $message = "Failed to create the subtitle.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);

    }


}
