<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use DB;
use GuzzleHttp\Client as httpClient;
use GuzzleHttp\Exception\RequestException;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Jobs;
use App\Models\Videos;
use App\Models\Transcodings;
use App\Models\Courses;
use App\Models\Topics;
use App\Models\Admins;
use App\Models\CronJobs;
use Input;
use Auth;
use Mail;
use Carbon\Carbon;

class SwiftCoderController extends Controller
{
    public function callbackProgress(Request $request)
    {
        ini_set('max_execution_time', 1800);

        $input = $request->json()->all();

        if (isset($input['job']) && isset($input['job']['id'])) {
            $dataVideo = Videos::where('sc_job_id', $input['job']['id'])->with('courses')->with('topics')->first();

            if ($dataVideo) {
                $dataJob = Jobs::where('sc_job_id', $input['job']['id'])->first();
                if (!$dataJob) {
                    $dataJob = new Jobs;
                }

                if ($dataJob->is_notify == 1) {
                    return response()->json(['message' => 'Successfully pushed notification.'], 200);
                }

                $dataJob->video_id = $dataVideo->id;
                $dataJob->sc_job_id = $input['job']['id'];
                $dataJob->sc_job_filename = $input['job']['filename'];
                $dataJob->sc_job_status = $input['job']['status'];
                $dataJob->sc_job_error_code = $input['job']['error_code'];
                $dataJob->sc_job_error_message = $input['job']['error_message'];
                $dataJob->sc_job_submit_time = is_numeric($input['job']['timing']['submit_time_millis']) ? Carbon::createFromTimestamp(($input['job']['timing']['submit_time_millis'] / 1000))->toDateTimeString() : null;
                $dataJob->sc_job_start_time = is_numeric($input['job']['timing']['submit_time_millis']) ? Carbon::createFromTimestamp(($input['job']['timing']['start_time_millis'] / 1000))->toDateTimeString() : null;
                $dataJob->sc_job_finish_time = is_numeric($input['job']['timing']['submit_time_millis']) ? Carbon::createFromTimestamp(($input['job']['timing']['finish_time_millis'] / 1000))->toDateTimeString() : null;
                $dataJob->sc_raw_data = json_encode($input['job'], JSON_UNESCAPED_UNICODE);
                $dataJob->is_notify = 1;
                $dataJob->status = 1;
                $dataJob->modify_datetime = date("Y-m-d H:i:s");
                $is_success = $dataJob->save();

                if ($is_success) {

                    $dataTranscodings = Transcodings::where('video_id', $dataVideo->id)->where('transcode_status', '!=', 'inappropriate');

                    if ($input['job']['status'] == "Distributed") {

                        // $dataTranscodings->update(['transcode_status' => "converted"]);

                        if ($dataJob->is_moved_file != 1) {
                            /* ===== START MOVE VOD FILE ===== */
                            $params = array(
                                "filename" => $dataVideo->name,
                                "dir_name" => $dataVideo->dir_name,
                                "preset_paths" => array(),
                            );

                            $preset_results = array_pluck($input['job']['processing']['formats'][0]['result']['preset_group_results'], 'preset_results');
                            foreach ($preset_results as $preset_result) {
                                $params['preset_paths'][] = $preset_result[0]['preset_url'];
                            }

                            $httpClient = new httpClient();

                            try {
                                $responseMoveVod = $httpClient->request('POST', env('BASE_URL_API_MEDIA').'sc_videos/move-vod.php', [
                                    'json' => $params,
                                ]);

                                $dataJob->is_moved_file = 1;
                                $dataJob->modify_datetime = date("Y-m-d H:i:s");
                                $dataJob->save();

                                $respMoveVodData = json_decode($responseMoveVod->getBody(), true);

                                foreach ($respMoveVodData['bitrates'] as $bitrate) {
                                    $dataTranscodings = Transcodings::where('video_id', $dataVideo->id)->where('transcode_status', '!=', 'inappropriate')->where('title', $bitrate['bitrate'])->update([
                                        'filename' => $bitrate['filename'],
                                        'url' => $bitrate['download_url'],
                                        // 'url' => implode("|", $bitrate),
                                        'transcode_status' => "converted",
                                        'modify_datetime' => date('Y-m-d H:i:s')
                                    ]);
                                }
                            } catch(RequestException $e) {
                                if ($e->hasResponse()) {
                                    $respMoveVodData['statusCode'] = $e->getResponse()->getStatusCode();
                                    $respMoveVodData['errorInfo'] = json_decode($e->getResponse()->getBody());
                                }
                            }
                            /* ===== END MOVE VOD FILE ===== */
                        }

                    } else if ($input['job']['status'] == "Failed") {

                        $dataTranscodings->update(['transcode_status' => "error", 'transcode_status_remark' => $dataJob->sc_job_error_code.": ".$dataJob->sc_job_error_message]);

                        if ($dataJob->is_sent_finish_mail != 1) {
                            if ($dataJob->create_by) {
                                $admins = Admins::find($dataJob->create_by);
                                $action_by = $admins->first_name;
                            }

                            // Mail Error
                            if ($dataVideo->courses) {
                                $dataCourse = $dataVideo->courses;
                            } else {
                                $dataCourse = $dataVideo->topics->courses;
                            }

                            $toEmails = [];

                            for ($i=0; $i < count($dataCourse->groups); $i++) {
                                $toEmails[] = Admins::whereHas('admins_groups', function($query) use ($dataCourse, $i) {
                                    $query->whereIn('admins_groups_id', array_pluck($dataCourse->groups[$i]->admins_groups, 'id'));
                                })->whereNull('super_users')->where('status', 1)->orWhere(function($subQuery) use ($dataCourse) {
                                    $subQuery->where('id', $dataCourse->admins_id)->where('status', 1);
                                })->get();
                            }

                            $toEmails = array_flatten($toEmails);
                            $toEmails = array_values(array_unique(array_pluck($toEmails, 'email')));

                            $dataMail = array(
                                'dataJob'         => $dataJob,
                                'dataVideo'       => $dataVideo,
                                'dataTopic'       => $dataVideo->topics,
                                'dataParentTopic' => ($dataVideo->topics && $dataVideo->topics->parent) ? Topics::find($dataVideo->topics->parent) : null,
                                'dataCourse'      => ($dataVideo->topics) ? $dataVideo->topics->courses : $dataVideo->courses,
                                'action_by'       => $action_by,
                            );

                            Mail::send('job-error-mail', $dataMail, function($mail) use ($dataMail, $toEmails) {
                                $mail->to($toEmails);
                                // $mail->to("nawee.ku.dootvmedia@gmail.com");
                                $mail->subject('แจ้งการแปลงไฟล์วีดีโอล้มเหลว หลักสูตร '.$dataMail['dataCourse']['code']." - ".$dataMail['dataTopic']['title']." โดย ".$dataMail['action_by']);
                                $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                $mail->bcc(config('constants.EMAIL.BCC'), config('constants.EMAIL.BCC'));
                                // $mail->bcc('nawee.ku.dootvmedia@gmail.com', 'Nawee Kunrod');
                            });

                            if( count(Mail::failures()) == 0 ) {
                                $dataJob->is_sent_finish_mail = 1;
                                $dataJob->save();
                            }
                        }

                    } else {
                        $dataTranscodings->update(['transcode_status' => "converting"]);
                    }
                }
            }
        }

    }

    public function checkJobStart(Request $request, _FunctionsController $oFunc)
    {
        // $dataJobs = Jobs::with('videos.topics')->where('sc_job_status', 'Initail')->whereHas('videos')->limit(5)->get();
        $dataJobs = Jobs::with('videos.topics')->where('sc_job_status', '!=', 'Distributed')->where('sc_job_status', '!=', 'Failed')->where('is_sent_start_mail', 0)->whereHas('videos')->limit(5)->get();
        // $dataJobs = Jobs::with('videos.topics')->where('is_sent_start_mail', 0)->whereHas('videos')->limit(1)->get();

        // return response()->json($dataJobs, 500);

        $is_success = true;

        for ($i=0; $i < count($dataJobs); $i++) {

            $action_by = "";
            if ($dataJobs[$i]->create_by) {
                $admins = Admins::find($dataJobs[$i]->create_by);
                $action_by = $admins->first_name;
            }

            /* ===== START GET VIDEO JOB ===== */
            $params = array(
                "job_id" => $dataJobs[$i]->sc_job_id,
                "dir_name" => $dataJobs[$i]->videos->dir_name,
            );

            // return response()->json($params, 500);

            $httpClient = new httpClient();

            try {

                $responseGetVodJob = $httpClient->request('GET', env('BASE_URL_API_MEDIA').'sc_videos/get-vod-job.php', [
                    'query' => $params
                ]);

                $respGetVodJob = json_decode($responseGetVodJob->getBody());

                if ($respGetVodJob->job->status != "Initail" && $respGetVodJob->job->timing->start_time_millis) {
                    if ($dataJobs[$i]->is_sent_start_mail != 1) {
                        // Mail Start
                        if ($dataJobs[$i]->videos->courses) {
                            $dataCourse = $dataJobs[$i]->videos->courses;
                        } else {
                            $dataCourse = $dataJobs[$i]->videos->topics->courses;
                        }

                        $toEmails = [];

                        for ($j=0; $j < count($dataCourse->groups); $j++) {
                            $toEmails[] = Admins::whereHas('admins_groups', function($query) use ($dataCourse, $j) {
                                $query->whereIn('admins_groups_id', array_pluck($dataCourse->groups[$j]->admins_groups, 'id'));
                            })->whereNull('super_users')->where('status', 1)->orWhere(function($subQuery) use ($dataCourse) {
                                $subQuery->where('id', $dataCourse->admins_id)->where('status', 1);
                            })->get();
                        }

                        $toEmails = array_flatten($toEmails);
                        $toEmails = array_values(array_unique(array_pluck($toEmails, 'email')));

                        $dataMail = array(
                            'dataJob'                    => $dataJobs[$i],
                            'dataVideo'                  => $dataJobs[$i]->videos,
                            'dataTopic'                  => $dataJobs[$i]->videos->topics,
                            'dataParentTopic'            => ($dataJobs[$i]->videos->topics && $dataJobs[$i]->videos->topics->parent) ? Topics::find($dataJobs[$i]->videos->topics->parent) : null,
                            'dataCourse'                 => ($dataJobs[$i]->videos->topics) ? $dataJobs[$i]->videos->topics->courses : $dataJobs[$i]->videos->courses,
                            'startTranscodeDateTime'     => Carbon::createFromTimestamp(($respGetVodJob->job->timing->start_time_millis / 1000))->toDateTimeString(),
                            'estimatedTranscodeDateTime' => date("Y-m-d H:i:s", ($respGetVodJob->job->timing->start_time_millis / 1000) + $oFunc->secondsFromTime($respGetVodJob->job->_eta_full)),
                            'action_by'                  => $action_by,
                        );

                        // return response()->json($dataMail, 500);

                        Mail::send('job-start-mail', $dataMail, function($mail) use ($dataMail, $toEmails) {
                            $mail->to($toEmails);
                            // $mail->to("nawee.ku.dootvmedia@gmail.com");
                            $mail->subject('แจ้งการเริ่มแปลงไฟล์วีดีโอ หลักสูตร '.$dataMail['dataCourse']['code']." - ".$dataMail['dataTopic']['title']." โดย ".$dataMail['action_by']);
                            $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                            $mail->bcc(config('constants.EMAIL.BCC'), config('constants.EMAIL.BCC'));
                            // $mail->bcc('nawee.ku.dootvmedia@gmail.com', 'Nawee Kunrod');
                        });

                        if( count(Mail::failures()) == 0 ) {
                            $dataJobs[$i]->is_sent_start_mail = 1;
                            $dataJobs[$i]->save();
                        }
                    }
                }

            } catch(RequestException $e) {
                $is_success = false;
                if ($e->hasResponse()) {
                    $respGetVodJob['statusCode'] = $e->getResponse()->getStatusCode();
                    // $respGetVodJob['errorInfo'] = json_decode($e->getResponse()->getBody());
                    $respGetVodJob['errorInfo'] = $e->getResponse()->getBody();
                }

                // return response()->json($respGetVodJob, 500);
            }
            /* ===== END GET VIDEO JOB ===== */

        }

        $dataCronJob = new CronJobs;
        $dataCronJob->code = "CHECK_JOB_START";
        $dataCronJob->action_datetime = date("Y-m-d H:i:s");

        if (!$is_success) {
            $dataCronJob->status = 0;
            $dataCronJob->status_code = isset($respGetVodJob['statusCode']) ? $respGetVodJob['statusCode'] : null;
            $dataCronJob->status_remark = isset($respGetVodJob['errorInfo']) ? $respGetVodJob['errorInfo'] : null;
            $dataCronJob->save();
        } else {
            $dataCronJob->status = 1;
            $dataCronJob->save();
        }

        return response()->json(['is_success' => $is_success], 200);
    }

    public function checkJobTransfer(Request $request, _FunctionsController $oFunc)
    {
        ini_set('max_execution_time', 1800);

        $dataJobs = Jobs::with('videos.topics')->where('sc_job_status', 'Distributed')->where('is_generate_smil', 0)->whereHas('videos')->limit(5)->get();

        $preset_paths = [];
        for ($i=0; $i < count($dataJobs); $i++) {
            $scDataJob = json_decode($dataJobs[$i]->sc_raw_data, true);
            $preset_results = array_pluck($scDataJob['processing']['formats'][0]['result']['preset_group_results'], 'preset_results');
            foreach ($preset_results as $preset_result) {
                $preset_paths[] = $preset_result[0]['preset_url'];
            }

            $params = array(
                "filename" => $dataJobs[$i]->videos->name,
                "dir_name" => $dataJobs[$i]->videos->dir_name,
                "preset_paths" => $preset_paths,
            );

            $httpClient = new httpClient();

            try {
                $responseMoveVod = $httpClient->request('POST', env('BASE_URL_API_MEDIA').'sc_videos/check-copy-vod-file.php', [
                    'json' => $params,
                ]);

                $respMoveVodData = json_decode($responseMoveVod->getBody());

                if ($respMoveVodData->is_success) {

                    /* ===== START DELETING TMP JOB ===== */
                    $params = array(
                        "type" => "job",
                        "job_id" => $dataJobs[$i]->sc_job_id,
                    );

                    $httpClient = new httpClient();

                    try {
                        $responseDelete = $httpClient->request('DELETE', env('BASE_URL_API_MEDIA').'sc_videos/delete-tmp-file.php', [
                            'json' => $params
                        ]);

                        $respDeleteData = json_decode($responseDelete->getBody(), true);
                    } catch(RequestException $e) {
                        if ($e->hasResponse()) {
                            $respDeleteData['statusCode'] = $e->getResponse()->getStatusCode();
                            $respDeleteData['errorInfo'] = json_decode($e->getResponse()->getBody());
                        }
                    }
                    /* ===== END DELETING TMP JOB ===== */

                    /* ===== START CREATE SMIL FILE ===== */
                    $params = array(
                        "filename" => $dataJobs[$i]->videos->name,
                        "dir_name" => $dataJobs[$i]->videos->dir_name,
                    );

                    $httpClient = new httpClient();

                    try {

                        $responseCreateSmil = $httpClient->request('POST', env('BASE_URL_API_MEDIA').'ffmpeg/create-smil.php', [
                            'json' => $params
                        ]);

                        $respCreateSmilData = json_decode($responseCreateSmil->getBody(), true);

                        $dataJobs[$i]->videos->smil_name = $respCreateSmilData['smil_name'];
                        $dataJobs[$i]->videos->smil_url = $respCreateSmilData['vodPath'];
                        $dataJobs[$i]->videos->modify_datetime = date("Y-m-d H:i:s");
                        $dataJobs[$i]->videos->save();

                        $dataJobs[$i]->is_generate_smil = 1;
                        $dataJobs[$i]->modify_datetime = date("Y-m-d H:i:s");
                        $dataJobs[$i]->save();

                        if ($dataJobs[$i]->videos->courses) {
                            $dataCourse = $dataJobs[$i]->videos->courses()->first();
                            if ($dataJobs[$i]->streaming_url_type == "full") {
                                $dataCourse->streaming_url = $dataJobs[$i]->videos->smil_url;
                            } else {
                                $dataCourse->review_streaming_url = $dataJobs[$i]->videos->smil_url;
                            }
                            $dataCourse->save();
                        } else {
                            $dataTopic = $dataJobs[$i]->videos->topics()->first();

                            $dataTopic->is_auto_convert = 0;
                            $dataTopic->streaming_status = 1;
                            // if ($dataTopic->is_auto_convert == 1) {

                            //     if ($dataTopic->streaming_status == 0) {
                            //     }
                            // }

                            $dataTopic->videos_id = $dataJobs[$i]->videos->id;
                            $dataTopic->streaming_url = $dataJobs[$i]->videos->smil_url;
                            $dataTopic->save();
                        }

                        if ($dataJobs[$i]->is_sent_finish_mail != 1) {
                            if ($dataJobs[$i]->create_by) {
                                $admins = Admins::find($dataJobs[$i]->create_by);
                                $action_by = $admins->first_name;
                            }

                            // Mail Success
                            if ($dataJobs[$i]->videos->courses) {
                                $dataCourse = $dataJobs[$i]->videos->courses;
                            } else {
                                $dataCourse = $dataJobs[$i]->videos->topics->courses;
                            }

                            $toEmails = [];

                            for ($j=0; $j < count($dataCourse->groups); $j++) {
                                $toEmails[] = Admins::whereHas('admins_groups', function($query) use ($dataCourse, $j) {
                                    $query->whereIn('admins_groups_id', array_pluck($dataCourse->groups[$j]->admins_groups, 'id'));
                                })->whereNull('super_users')->where('status', 1)->orWhere(function($subQuery) use ($dataCourse) {
                                    $subQuery->where('id', $dataCourse->admins_id)->where('status', 1);
                                })->get();
                            }

                            $toEmails = array_flatten($toEmails);
                            $toEmails = array_values(array_unique(array_pluck($toEmails, 'email')));

                            $dataMail = array(
                                'dataJob'         => $dataJobs[$i],
                                'dataVideo'       => $dataJobs[$i]->videos,
                                'dataTopic'       => $dataJobs[$i]->videos->topics,
                                'dataParentTopic' => ($dataJobs[$i]->videos->topics && $dataJobs[$i]->videos->topics->parent) ? Topics::find($dataJobs[$i]->videos->topics->parent) : null,
                                'dataCourse'      => ($dataJobs[$i]->videos->topics) ? $dataJobs[$i]->videos->topics->courses : $dataJobs[$i]->videos->courses,
                                'action_by'       => $action_by,
                            );

                            Mail::send('job-success-mail', $dataMail, function($mail) use ($dataMail, $toEmails) {
                                $mail->to($toEmails);
                                // $mail->to("nawee.ku.dootvmedia@gmail.com");
                                $mail->subject('แจ้งการแปลงไฟล์วีดีโอสำเร็จ หลักสูตร '.$dataMail['dataCourse']['code']." - ".$dataMail['dataTopic']['title']." โดย ".$dataMail['action_by']);
                                $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                $mail->bcc(config('constants.EMAIL.BCC'), config('constants.EMAIL.BCC'));
                                // $mail->bcc('nawee.ku.dootvmedia@gmail.com', 'Nawee Kunrod');
                            });

                            if( count(Mail::failures()) == 0 ) {
                                $dataJobs[$i]->is_sent_finish_mail = 1;
                                $dataJobs[$i]->save();
                            }
                        }

                    } catch(RequestException $e) {
                        if ($e->hasResponse()) {
                            $respCreateSmilData['statusCode'] = $e->getResponse()->getStatusCode();
                            $respCreateSmilData['errorInfo'] = json_decode($e->getResponse()->getBody());
                        }
                    }
                    /* ===== END CREATE SMIL FILE ===== */
                }
            } catch(RequestException $e) {
                if ($e->hasResponse()) {
                    $respMoveVodData['statusCode'] = $e->getResponse()->getStatusCode();
                    $respMoveVodData['errorInfo'] = json_decode($e->getResponse()->getBody());
                    return response()->json($respMoveVodData, 500);
                }
            }
        }
    }

    public function debug(Request $request, _FunctionsController $oFunc)
    {
        $resp = [
            "start" => "2018-08-20 17:18:58",
            "eta" => "00:01:13"
        ];

        // $resp['start'] = strtotime($resp['start']);
        // Carbon::createFromTime($hour, $minute, $second, $tz);
        // $resp['eta'] = strtotime($resp['start']) + $oFunc->secondsFromTime($resp['eta']);
        // $resp['eta'] = Carbon::parse($resp['start'])->add($resp['eta'])->toDateTimeString();

        // $resp['start'] = date("Y-m-d H:i:s", $resp['start']);
        // $resp['eta'] = date("Y-m-d H:i:s", $resp['eta']);

        $st = Carbon::parse("2018-08-21 17:14:17");
        $ed = Carbon::parse("2018-08-21 17:39:22");

        $xxx = $st->diff($ed);

        $xxx = $xxx->h.":".$xxx->i.":".$xxx->s;

        return response()->json($xxx, 500);
    }


}







