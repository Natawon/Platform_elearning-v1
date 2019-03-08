<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use DB;
use GuzzleHttp\Client as httpClient;
use GuzzleHttp\Exception\RequestException;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\CronJobs;
use Input;
use Auth;
use Mail;
use Carbon\Carbon;

class CronJobsController extends Controller
{
    public function monitor($code, Request $request)
    {
        $dataLastSuccess = CronJobs::where('code', $code)->where('status', 1)->orderBy('id', 'desc')->first();
        $dataLast = CronJobs::where('code', $code)->orderBy('id', 'desc')->first();

        $action = Carbon::parse($dataLastSuccess->action_datetime);
        $now = Carbon::now();
        $diffInMinute = $action->diffInMinutes($now);

        if ($diffInMinute > 2) {
            if ($dataLastSuccess->is_notify_mail != 1) {

                $dataMail = array(
                    'dataCronJob' => $dataLast,
                    'site'        => config('constants._ENV_SITE'),
                    'url'         => config('constants._BASE_URL')
                );

                if ($dataLast->status == 0) {
                    // Mail Notify CronJob
                    Mail::send('cron-job-notify-mail', $dataMail, function($mail) use ($dataMail) {
                        $mail->to("nawee.ku.dootvmedia@gmail.com");
                        $mail->subject("IMPORTANT: ".config('constants._ENV_SITE')." - Cron Job 'CHECK_JOB_START' is FAILED");
                        $mail->from(config('constants.EMAIL.USERNAME'), "Cron Job Monitor");
                        // $mail->bcc(config('constants.EMAIL.BCC'), config('constants.EMAIL.BCC'));
                        // $mail->bcc('nawee.ku.dootvmedia@gmail.com', 'Nawee Kunrod');
                    });
                } else {
                    // Mail Notify CronJob
                    Mail::send('cron-job-notify-mail', $dataMail, function($mail) use ($dataMail) {
                        // $mail->to("nawee.ku.dootvmedia@gmail.com");
                        $mail->to(config('constants.EMAIL.BCC'), config('constants.EMAIL.BCC'));
                        $mail->subject("IMPORTANT: ".config('constants._ENV_SITE')." - Cron Job 'CHECK_JOB_START' is NOT RUNNING");
                        $mail->from(config('constants.EMAIL.USERNAME'), "Cron Job Monitor");
                    });
                }

                if( count(Mail::failures()) == 0 ) {
                    $dataLastSuccess->is_notify_mail = 1;
                    $dataLastSuccess->notify_mail_datetime = date("Y-m-d H:i:s");
                    $dataLastSuccess->save();
                }
            }
        }

        return response()->json($diffInMinute, 200);
    }



}







