<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\AdminsGroups;
use App\Models\Admins;

use App\Models\Groups;
use App\Models\SubGroups;
use App\Models\LevelGroups;
use App\Models\Courses;
use App\Models\Categories;
use App\Models\Topics;
use App\Models\Members;
use App\Models\MembersPreApproved;
use App\Models\Instructors;
use App\Models\Documents;
use App\Models\Quiz;
use App\Models\ClassRooms;
use App\Models\Slides;
use App\Models\Orders;
use App\Models\Payments;

use Auth;
use Mail;

class _2c2pController extends Controller
{
    public function reconcile($date = null, Request $request, _FunctionsController $oFunc)
    {
        // Should check whitelist in beginning

        $today = date("Y-m-d");
        $yesterday = date("Y-m-d", strtotime("-1 days"));
        $day_before_yesterday = date("Y-m-d", strtotime("-2 days"));

        // For test
        if (!is_null($date)) {
            $today = $date;
            $yesterday = date("Y-m-d", strtotime("-1 days", strtotime($today)));
            $day_before_yesterday = date("Y-m-d", strtotime("-2 days", strtotime($today)));
        }

        $from_time = $day_before_yesterday." 21:00:00";
        $to_time = $yesterday." 20:59:59";
        $filePattern = "Reconcile*".$today."*.csv";
        $reconcileReportsDir = storage_path('app/reconcile-reports/');

        $files = glob($reconcileReportsDir.$filePattern);

        if (empty($files)) {
            // Add fail log.

            $toEmails = [];

            $toEmails[] = Admins::select('email')->where('admins_groups_id', 1)->where('status', 1)->get();

            $toEmails = array_flatten($toEmails);
            $toEmails = array_values(array_unique(array_pluck($toEmails, 'email')));

            $dataMail = array(
                'date'       => $yesterday,
                'date_short' => date("d M Y", strtotime($yesterday)),
                'error_code' => "001",
            );

            Mail::send('reconcile-report-error-mail', $dataMail, function($mail) use ($dataMail, $toEmails) {
                $mail->to($toEmails);
                // $mail->to("nawee.ku.dootvmedia@gmail.com");
                $mail->subject('(Error) รายงานการตรวจสอบรายการการชำระค่าธรรมเนียมเข้าเรียนหลักสูตร ณ วันที่ '.$dataMail['date_short']);
                $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                $mail->bcc(config('constants.EMAIL.BCC'), config('constants.EMAIL.BCC'));
                // $mail->bcc('nawee.ku.dootvmedia@gmail.com', 'Nawee Kunrod');
            });

            // if( count(Mail::failures()) == 0 ) {
            //     // Do Something
            // }

            return response()->json(["is_success" => false, "message" => "Failed to load files."], 500);
        }

        $dataReconcile = [];
        $countHeader = 1;
        $countReconciles = 0;
        $filesAttachment = [];

        foreach ($files as $file) {
            $pathInfoFile = pathinfo($file);
            $filenamePattern = explode('_', $pathInfoFile['filename']);
            if (count($filenamePattern) == 5) {
                list($service, $merchantId, $date, $time, $type) = $filenamePattern;
            } else {
                list($service, $merchantId, $date, $time) = $filenamePattern;
            }

            if ($service == "Reconcile2c2p" || $service == "Reconcile123") {
                $filesAttachment[] = $file;
                $resourceFile = fopen($file, "r");

                if ($countHeader > 0) {
                    for ($i=0; $i < $countHeader; $i++) {
                        $arrHeader[] = fgetcsv($resourceFile);
                    }
                }

                while (($arrDetail = fgetcsv($resourceFile)) !== false) {

                    $countReconciles++;
                    $dataOrder = Orders::with('groups')->find($arrDetail[12]);

                    if (!$dataOrder) {
                        continue;
                    }

                    if (!isset($dataReconcile[$dataOrder->groups->key]['total'])) {
                        $dataReconcile[$dataOrder->groups->key]['total'] = 1;
                    } else {
                        $dataReconcile[$dataOrder->groups->key]['total']++;
                    }

                    $dataPayment = Payments::where('merchant_id', $arrDetail[11])
                                            ->where('orders_id', $arrDetail[12])
                                            ->where('payment_status', 'successful')
                                            ->whereBetween('approve_datetime',array($from_time, $to_time))
                                            ->first();

                    if (!$dataPayment) {
                        $dataReconcile[$dataOrder->groups->key]['invalid'][] = $arrDetail[12];
                    } else {
                        $dataPayment->validate_status = 1;
                        $dataPayment->validate_file_csv = $pathInfoFile['basename'];
                        $dataPayment->validate_datetime = date("Y-m-d H:i:s");
                        $dataPayment->save();

                        $dataReconcile[$dataOrder->groups->key]['valid'][] = $dataPayment->orders_id;
                    }
                }

            } else {
                continue;
            }
        }

        // Payments::where('validate_status', 0)
        //         ->where('payment_status', 'successful')
        //         ->whereBetween('approve_datetime',array($from_time, $to_time))
        //         ->update(['validate_status' => 2, 'validate_remark' => 'ไม่มีข้อมูลในไฟล์ Reconcile', 'validate_datetime' => date("Y-m-d H:i:s")]);

        // $dataPayments = Payments::where('validate_status', 2)
        //                         ->where('payment_status', 'successful')
        //                         ->whereBetween('approve_datetime',array($from_time, $to_time))
        //                         ->get();

        // $notMatch['db_payments'] = $dataPayments;

        if (empty($dataReconcile)){
            $countPaymentEndOfDay = Payments::where('validate_status', 0)
                                            ->where('payment_status', 'successful')
                                            ->whereBetween('approve_datetime',array($from_time, $to_time))->count();

            if ($countPaymentEndOfDay > 0 || $countReconciles > 0) {
                $toEmails = [];

                $toEmails[] = Admins::select('email')->where('admins_groups_id', 1)->where('status', 1)->get();

                $toEmails = array_flatten($toEmails);
                $toEmails = array_values(array_unique(array_pluck($toEmails, 'email')));

                $dataMail = array(
                    'date'                => $yesterday,
                    'date_short'          => date("d M Y", strtotime($yesterday)),
                    'error_code'          => "002",
                    'dataFilesAttachment' => $filesAttachment,
                );

                Mail::send('reconcile-report-error-mail', $dataMail, function($mail) use ($dataMail, $toEmails) {
                    $mail->to($toEmails);
                    // $mail->to("nawee.ku.dootvmedia@gmail.com");
                    $mail->subject('(Error) รายงานการตรวจสอบรายการการชำระค่าธรรมเนียมเข้าเรียนหลักสูตร ณ วันที่ '.$dataMail['date_short']);
                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                    $mail->bcc(config('constants.EMAIL.BCC'), config('constants.EMAIL.BCC'));
                    // $mail->bcc('nawee.ku.dootvmedia@gmail.com', 'Nawee Kunrod');

                    foreach ($dataMail['dataFilesAttachment'] as $fileAttachment) {
                        $mail->attach($fileAttachment);
                    }
                });

                // if( count(Mail::failures()) == 0 ) {
                //     // Do Something
                // }
            }

            return response()->json(["is_success" => false, "message" => "Reconcile data not found."], 404);
        }

        foreach ($dataReconcile as $groupKey => $reconcile) {
            $dataGroup = Groups::where('key', $groupKey)->first();

            if (isset($reconcile['valid'])) {
                sort($reconcile['valid']);
                $reconcile['valid_string'] = implode(',', $reconcile['valid']);
                $reconcile['valid_total'] = count($reconcile['valid']);
            } else {
                $reconcile['valid'] = [];
                $reconcile['valid_string'] = '';
                $reconcile['valid_total'] = 0;
            }

            if (isset($reconcile['invalid'])) {
                sort($reconcile['invalid']);
                $reconcile['invalid_string'] = implode(',', $reconcile['invalid']);
                $reconcile['invalid_total'] = count($reconcile['invalid']);
            } else {
                $reconcile['invalid'] = [];
                $reconcile['invalid_string'] = '';
                $reconcile['invalid_total'] = 0;
            }

            if ($reconcile['valid_total'] == $reconcile['total'] && $reconcile['invalid_total'] == 0) {
                $reconcile['status'] = "Complete";
            } else {
                $reconcile['status'] = "Incomplete";
            }

            $reconcile['date'] = $yesterday;
            $reconcile['date_short'] = date("d M Y", strtotime($yesterday));

            $toEmails = [];

            // $toEmails[] = Admins::whereHas('admins_groups', function($query) use ($dataGroup) {
            //     $query->whereIn('admins_groups_id', array_pluck($dataGroup->admins_groups, 'id'));
            // })->whereNull('super_users')->where('status', 1)->get();

            // $toEmails = array_flatten($toEmails);
            // $toEmails = array_values(array_unique(array_pluck($toEmails, 'email')));

            $dataMail = array(
                'dataReconcile'       => $reconcile,
                'dataGroup'           => $dataGroup,
                'dataFilesAttachment' => $filesAttachment,
            );

            Mail::send('reconcile-report-mail', $dataMail, function($mail) use ($dataMail, $toEmails) {
                $mail->to($toEmails);
                // $mail->to("nawee.ku.dootvmedia@gmail.com");
                $mail->subject('รายงานการตรวจสอบรายการการชำระค่าธรรมเนียมเข้าเรียนหลักสูตรใน Group '.$dataMail['dataGroup']['title'].' ณ วันที่ '.$dataMail['dataReconcile']['date_short'].' ('.$dataMail['dataReconcile']['status'].')');
                $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                $mail->bcc(config('constants.EMAIL.BCC'), config('constants.EMAIL.BCC'));
                // $mail->bcc('nawee.ku.dootvmedia@gmail.com', 'Nawee Kunrod');

                foreach ($dataMail['dataFilesAttachment'] as $fileAttachment) {
                    $mail->attach($fileAttachment);
                }
            });

            // if( count(Mail::failures()) == 0 ) {
            //     // Do Something
            // }
        }


        return response()->json(["is_success" => true, "message" => "Successfully validated reconcile reports."], 200);

        // $extracted = $oFunc->extractZipAllFiles($reconcileReportsDir.$download['filename'], storage_path('app/reconcile-reports/'));

        // if (!$extracted['status']) {
        //     // Add fail log.
        //     return response()->json(["is_success" => false, "message" => "Failed to extract file."], 500);
        // }

        // $dataReconcile['date'] = $yesterday;
        // $dataReconcile['files'] = $extracted['extracted_files'];
        // $dataReconcile['total'] = 0;
        // $dataReconcile['total_complete'] = 0;
        // $dataReconcile['total_incomplete'] = 0;

        // $loop = 1;
        // $countHeader = 1;
        // $notMatch = [
        //     "db_payments" => [],
        //     "file_payments" => []
        // ];
        // $matched = [];
        // foreach ($extracted['extracted_files'] as $extracted_file) {
        //     $pathInfoFile = pathinfo($extracted_file);
        //     if ($pathInfoFile['extension'] == "csv") {
        //         $filenamePattern = explode('_', $pathInfoFile['filename']);
        //         if (count($filenamePattern) == 5) {
        //             list($service, $merchantId, $date, $time, $type) = $filenamePattern;
        //         } else {
        //             list($service, $merchantId, $date, $time) = $filenamePattern;
        //         }

        //         if ($service == "Reconcile2c2p" || $service == "Reconcile123") {
        //             // echo 'Round: '.$loop.' --> '.$extracted_file;
        //             $resourceFile = fopen($extracted_file, "r");

        //             if ($countHeader > 0) {
        //                 for ($i=0; $i < $countHeader; $i++) {
        //                     $arrHeader = fgetcsv($resourceFile);
        //                     $dataReconcile['total'] += $arrHeader[1];
        //                 }
        //             }

        //             while (($arrDetail = fgetcsv($resourceFile)) !== false) {
        //                 $dataPayment = Payments::where('merchant_id', $arrDetail[11])
        //                                         ->where('orders_id', $arrDetail[12])
        //                                         ->where('payment_status', 'successful')
        //                                         ->whereBetween('approve_datetime',array($from_time, $to_time))
        //                                         ->first();

        //                 if (!$dataPayment) {
        //                     $notMatch['file_payments'][] = $arrDetail;
        //                 } else {
        //                     $dataPayment->validate_status = 1;
        //                     $dataPayment->file_csv = $pathInfoFile['basename'];
        //                     $dataPayment->validate_datetime = date("Y-m-d H:i:s");
        //                     $dataPayment->save();

        //                     $matched[] = $dataPayment;
        //                 }
        //             }

        //         } else {
        //             continue;
        //         }
        //     }

        //     $loop++;
        // }

        // // Payments::where('validate_status', 0)
        // //         ->where('payment_status', 'successful')
        // //         ->whereBetween('approve_datetime',array($from_time, $to_time))
        // //         ->update(['validate_status' => 2, 'validate_remark' => 'ไม่มีข้อมูลในไฟล์ Reconcile', 'validate_datetime' => date("Y-m-d H:i:s")]);

        // // $dataPayments = Payments::where('validate_status', 2)
        // //                         ->where('payment_status', 'successful')
        // //                         ->whereBetween('approve_datetime',array($from_time, $to_time))
        // //                         ->get();

        // // $notMatch['db_payments'] = $dataPayments;


        // // $toEmails = [];

        // // // for ($j=0; $j < count($dataCourse->groups); $j++) {
        // // //     $toEmails[] = Admins::whereHas('admins_groups', function($query) use ($dataCourse, $j) {
        // // //         $query->whereIn('admins_groups_id', array_pluck($dataCourse->groups[$j]->admins_groups, 'id'));
        // // //     })->whereNull('super_users')->where('status', 1)->orWhere(function($subQuery) use ($dataCourse) {
        // // //         $subQuery->where('id', $dataCourse->admins_id)->where('status', 1);
        // // //     })->get();
        // // // }

        // // // $toEmails = array_flatten($toEmails);
        // // // $toEmails = array_values(array_unique(array_pluck($toEmails, 'email')));

        // // $dataMail = array(
        // //     'dataReconcile' => $dataReconcile,
        // //     'dataGroup'     => $dataGroup,
        // // );

        // // Mail::send('reconcile-report-mail', $dataMail, function($mail) use ($dataMail, $toEmails) {
        // //     // $mail->to($toEmails);
        // //     $mail->to("nawee.ku.dootvmedia@gmail.com");
        // //     $mail->subject('รายงานการตรวจสอบรายการการชำระค่าธรรมเนียมเข้าเรียนหลักสูตรใน Group '.$dataMail['dataGroup']['title'].' ณ วันที่ { Date -1 }   ({Status = ถ้าตรวจสอบรายการแล้ว Complete ทั้งหมดให้แสดง Complete แต่ถ้าไม่ใช่ให้แสดง Incomplete})');
        // //     $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
        // //     // $mail->bcc(config('constants.EMAIL.BCC'), config('constants.EMAIL.BCC'));
        // //     // $mail->bcc('nawee.ku.dootvmedia@gmail.com', 'Nawee Kunrod');
        // //     // $mail->attach(storage_path('app/pdf/estimations/').$dataPdf->pdfFielName);
        // // });

        // // if( count(Mail::failures()) == 0 ) {
        // //     $dataJobs[$i]->is_sent_finish_mail = 1;
        // //     $dataJobs[$i]->save();
        // // }

        // return response()->json(['matched' => $matched, 'not_match' => $notMatch, 'dataReconcile' => $dataReconcile], 200);

    }
}










